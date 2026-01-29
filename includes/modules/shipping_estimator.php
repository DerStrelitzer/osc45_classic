<?php
/*
  $Id: shipping_estimator.php,v 2.00 2004/07/01 15:16:07 eml Exp $

  v2.00 by Acheron
  (see Install.txt for partial version history)

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004

  Released under the GNU General Public License
*/
?>
<!-- shipping_estimator //-->
<script type="text/javascript">
  function shipincart_submit(sid){
    if(sid){
      document.estimator.sid.value=sid;
    }
    document.estimator.submit();
    return false;
  }
</script>
              <table align="center"><tr valign="top"><td>

<?php
// Only do when something is in the cart
if ($_SESSION['cart']->count_contents() > 0) {

  
  $extra = '';
  $payment = '';
  $shipping_weigth = $_SESSION['cart']->show_weight();

  require(DIR_WS_LANGUAGES . $GLOBALS['language'] . '/modules/' . FILENAME_SHIPPING_ESTIMATOR);

  //if($_SESSION['cart']->get_content_type() !== 'virtual') {
    if (isset($_SESSION['customer_id'])) {
      // user is logged in
      if (isset($_POST['address_id'])){
        // user changed address
        $_SESSION['sendto'] = $_POST['address_id'];
      } elseif (isset($_SESSION['cart_address_id'])){
        // user once changed address
        $_SESSION['sendto'] = $_SESSION['cart_address_id'];
      } else {
        // first timer
        $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
      }
      // set session now
      $_SESSION['cart_address_id'] = $_SESSION['sendto'];
      // set shipping to null ! multipickup changes address to store address...
      $_SESSION['shipping'] = array('id'=>'', 'cost'=>0, 'title'=>'');
      // include the order class (uses the sendto !)
      $order = new Order;
    } else {
      
      if (!isset($_SESSION['pwa_array_address']['entry_country_id'])) {
        $_SESSION['pwa_array_address']['entry_country_id'] = STORE_COUNTRY;
      }
      $order = new Order;
      // user not logged in !
      if (isset($_POST['country_id'])) {
        // country is selected
        $country_info = tep_get_countries($_POST['country_id'],true);
        $order->delivery = array(
          'postcode' => $_POST['zip_code'],
          'country' => array('id' => $_POST['country_id'], 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
          'country_id' => $_POST['country_id'],
          'zone_id' => $_POST['state'],
          'format_id' => tep_get_address_format_id($_POST['country_id'])
        );
        $_SESSION['cart_country_id'] = $_POST['country_id'];
        $_SESSION['cart_zone'] = $_POST['zone_id'];
        $_SESSION['cart_zip_code'] = $_POST['zip_code'];

      } elseif (isset($_SESSION['cart_zone'])) {
        $country_info = tep_get_countries($_SESSION['cart_country_id'], true);
        $order->tax_max = array('rate' => $shopping_cart_tax_max, 'description' => tep_get_tax_description($shopping_cart_tax_max_id, STORE_COUNTRY, 0));
        $order->delivery = array(
          'postcode' => $_SESSION['cart_zip_codezone'],
          'country' => array('id' => $cart_country_id, 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
          'country_id' => $cart_country_id,
          'format_id' => tep_get_address_format_id($_SESSION['cart_country_id'])
        );
        //echo '<pre>'; print_r($order); echo '</pre>';
      } else {
        $_SESSION['cart_country_id'] = STORE_COUNTRY;
        $country_info = tep_get_countries(STORE_COUNTRY, true);

        $order->tax_max = array('rate' => $shopping_cart_tax_max, 'description' => tep_get_tax_description($shopping_cart_tax_max_id, STORE_COUNTRY, 0));
        $order->delivery = array(
          'postcode' => '',
          'country' => array('id' => STORE_COUNTRY, 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' =>  $country_info['countries_iso_code_3']),
          'country_id' => STORE_COUNTRY,
          'format_id' => tep_get_address_format_id(STORE_COUNTRY) 
        );
      }
      // set the cost to be able to calculate free shipping
      $order->info = array(
        'total' => $_SESSION['cart']->show_total(),
        'currency' => $GLOBALS['currency'],
        'currency_value'=> $currencies->currencies[$GLOBALS['currency']]['value']
      );
    }
    $order->info['tax'] = 0;
    if (!isset($order->delivery['zone_id'])) $order->delivery['zone_id'] = '0';

// weight and count needed for shipping
    $total_weight = $_SESSION['cart']->show_weight();
    $total_count = $_SESSION['cart']->count_contents();
    
    $shipping_modules = new Shipping;
    $quotes = $shipping_modules->quote();
    $order->info['subtotal'] = $_SESSION['cart']->total;

// set selections for displaying
    $selected_country = $order->delivery['country']['id'];
    $selected_address = isset($sendto)?$sendto:'';
  //}
// eo shipping cost

  // check free shipping based on order total
  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
      case 'both':
        $pass = true; break;
      default:
        $pass = false; break;
    }
    $free_shipping = false;
    if ( $pass == true && $order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) {
      $free_shipping = true;
      include(DIR_WS_LANGUAGES . $GLOBALS['language'] . '/modules/order_total/ot_shipping.php');
    }
  } else {
    $free_shipping = false;
  }
  // begin shipping cost
  if (!$free_shipping && $_SESSION['cart']->get_content_type() !== 'virtual'){
    if (isset($_POST['sid']) && tep_not_null($_POST['sid'])){
      list($module, $method) = explode('_', $_POST['sid']);
      $_SESSION['cart_sid'] = $_POST['sid'];

    } elseif (isset($_SESSION['cart_sid'])) {
      list($module, $method) = explode('_', $_SESSION['cart_sid']);
    } else {
      $module = '';
      $method = '';
    }
    if (tep_not_null($module)) {
      $selected_quote = $shipping_modules->quote($method, $module);
      if(isset($selected_quote[0]['error']) || !tep_not_null($selected_quote[0]['methods'][0]['cost'])){
        $selected_shipping = $shipping_modules->cheapest();
        $order->info['shipping_method'] = $selected_shipping['title'];
        $order->info['shipping_cost'] = $selected_shipping['cost'];
        $order->info['total']+= $selected_shipping['cost'];
      }else{
        $order->info['shipping_method'] = $selected_quote[0]['module'] . ' (' . $selected_quote[0]['methods'][0]['title'] . ')';
        $order->info['shipping_cost'] = $selected_quote[0]['methods'][0]['cost'];
        $order->info['total']+= $selected_quote[0]['methods'][0]['cost'];
        $selected_shipping['title'] = $order->info['shipping_method'];
        $selected_shipping['cost'] = $order->info['shipping_cost'];
        $selected_shipping['id'] = $selected_quote[0]['id'] . '_' . $selected_quote[0]['methods'][0]['id'];
      }
    } else {
      $selected_shipping = $shipping_modules->cheapest();
      $order->info['shipping_method'] = $selected_shipping['title'];
      $order->info['shipping_cost'] = $selected_shipping['cost'];
      $order->info['total']+= $selected_shipping['cost'];
    }
  }

// virtual products use free shipping
  if ($_SESSION['cart']->get_content_type() == 'virtual') {
    $order->info['shipping_method'] = CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS;
    $order->info['shipping_cost'] = 0;
  }
  if ($free_shipping) {
    $order->info['shipping_method'] = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
    $order->info['shipping_cost'] = 0;
  }
  if (!isset($selected_shipping)) $selected_shipping = array('id'=>'');
  $shipping=$selected_shipping;

// end of shipping cost
// end free shipping based on order total

  $contents = array( 
    array(
      array(
        'params' => 'width="100%" class="infoBoxHeading"',
        'text' => CART_SHIPPING_OPTIONS
      )
    )
  );
  $box_heading = new TableBox;
  $box_heading->set_param('cellpadding', 0);
  $box_heading->get_box($contents, true);

  $ship_txt = tep_draw_form('estimator', tep_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'), 'post'); //'onSubmit="return check_form();"'
  //$ShipTxt.=tep_draw_hidden_field('sid', $selected_shipping['id']);
  $ship_txt .= '<table>';
  if(sizeof($quotes)) {

    if (CARTSHIP_SHOWWT == 'ja') {
      $showweight = '&nbsp;(' . $total_weight . '&nbsp;' . CARTSHIP_WTUNIT . ')';
    } else {
      $showweight = '';
    }

    if (isset($_SESSION['customer_id'])) {
      // logged in

      if(CARTSHIP_SHOWIC == 'ja'){
        $ship_txt .= '<tr><td class="main"> <b>' . ($total_count == 1 ? CART_SHIPPING_ITEM : CART_SHIPPING_ITEMS) . ':</b></td><td colspan="2" class="main">' . $total_count . $showweight . '</td></tr>';
      }
      $addresses_query = tep_db_query("select address_book_id, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
      // only display addresses if more than 1
      if (tep_db_num_rows($addresses_query) > 1){
        while ($addresses = tep_db_fetch_array($addresses_query)) {
          $addresses_array[] = array('id' => $addresses['address_book_id'], 'text' => tep_address_format(tep_get_address_format_id($addresses['country_id']), $addresses, 0, ' ', ' '));
        }
        $ship_txt .= '<tr><td colspan="3" class="main" nowrap>' .
                     CART_SHIPPING_METHOD_ADDRESS .'&nbsp;'. tep_draw_pull_down_menu('address_id', $addresses_array, $selected_address, 'onchange="return shipincart_submit(\'\');"').'</td></tr>';
      }
      $ship_txt .= '<tr valign="top"><td class="main"><b>' . CART_SHIPPING_METHOD_TO .'</b>&nbsp;</td><td colspan="2" class="main">' . tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />') . '</td></tr>';
    } else {
// not logged in
//      $ship_txt.=tep_output_warning(CART_SHIPPING_OPTIONS_LOGIN);

      if (CARTSHIP_SHOWIC == 'ja'){
        $ship_txt .= '<tr><td class="main"> <b>' . ($total_count == 1 ? CART_SHIPPING_ITEM :CART_SHIPPING_ITEMS) . ':</b></td><td colspan="2" class="main">' . $total_count . $showweight . '</td></tr>';
      }

      if ($_SESSION['cart']->get_content_type() != 'virtual'){

        if(CARTSHIP_SHOWCDD == 'ja'){
          $ship_txt .= '<tr><td colspan="3" class="main" nowrap>' .
                      ENTRY_COUNTRY .'&nbsp;'. tep_get_country_list('country_id', $selected_country,'style="width=200"');
        }

//add state zone_id
        $state_array[] = array('id' => '', 'text' => PULL_DOWN_DEFAULT);
        $state_query = tep_db_query("select zone_name, zone_id from " . TABLE_ZONES . " where zone_country_id = '$selected_country' order by zone_country_id DESC, zone_name");
        while ($state_values = tep_db_fetch_array($state_query)) {
          $state_array[] = array('id' => $state_values['zone_id'],
                               'text' => $state_values['zone_name']);
        }

        if (CARTSHIP_SHOWSDD == 'ja') {
         $ship_txt .= ' &nbsp;' .ENTRY_STATE .'&nbsp;'. tep_draw_pull_down_menu('state',$state_array);
        }

        if (CARTSHIP_SHOWZDD == 'ja') {
          $ship_txt .= '&nbsp;'.ENTRY_POST_CODE .'&nbsp;'. tep_draw_input_field('zip_code', $selected_zip, 'size="5"');
        }
//        $ship_txt.='&nbsp;<a href="_" onclick="return shipincart_submit(\'\');">'.CART_SHIPPING_METHOD_RECALCULATE.'</a></td></tr>';

        if (CARTSHIP_SHOWUB == 'ja') {
          $ship_txt .= '&nbsp;<td><a href="_" onclick="return shipincart_submit(\'\');">'. tep_image_button('button_update_cart.gif', IMAGE_BUTTON_UPDATE_CART) . ' </a></td></td></tr>';
        }
      }
    }
    if ($_SESSION['cart']->get_content_type() == 'virtual') {
      // virtual product-download
      //$ship_txt.='<tr><td colspan="3" class="main">'.tep_draw_separator().'</td></tr>';
      $ship_txt .= '<tr><td class="main" colspan="3">&nbsp;</td></tr><tr><td class="main" colspan="3"><i>' . CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS . '</i></td></tr>';
    } elseif ($free_shipping==1) {
      // order $total is free
      //$ship_txt.='<tr><td colspan="3" class="main">'.tep_draw_separator().'</td></tr>';
      $ship_txt .= '<tr><td class="main" colspan="3">&nbsp;</td></tr><tr><td class="main" colspan="3"><i>' . sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . '</i></td><td>&nbsp;</td></tr>';
    } else {
      // shipping display
      $ship_txt .= '<tr><td class="main"><b>' . CART_SHIPPING_CARRIER_TEXT . '</b></td><td class="main" align="left"><b>' . CART_SHIPPING_METHOD_TEXT . '</b></td><td class="main" align="right"><b>' . CART_SHIPPING_METHOD_RATES . '</b></td></tr>';
      $ship_txt .= '<tr><td colspan="3" class="main">'.tep_draw_separator().'</td></tr>';
      for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
        if(sizeof($quotes[$i]['methods'])==1){
          // simple shipping method
          $thisquoteid = $quotes[$i]['id'].'_'.$quotes[$i]['methods'][0]['id'];
          $ship_txt .= '<tr class="'.$extra.'">';
          $ship_txt .= '<td class="main">'. (isset($quotes[$i]['icon'])?$quotes[$i]['icon']:'').'&nbsp;&nbsp;&nbsp;</td>';
          if(isset($quotes[$i]['error'])){
            $ship_txt .= '<td colspan="2" class="main">'.$quotes[$i]['module'].'&nbsp;';
            $ship_txt .= '('.$quotes[$i]['error'].')</td></tr>';
          } else {
            if ($selected_shipping['id'] == $thisquoteid) {
              // $ship_txt.='<td class="main"><a title="' . CART_SHIPPING_SELECT_THIS . '" href="' . tep_href_link(basename($PHP_SELF)) . '"  onclick="return shipincart_submit(\''.$thisquoteid.'\');"><b>'.$quotes[$i]['module'].'&nbsp;';
              // $ship_txt.= '('.$quotes[$i]['methods'][0]['title'].')</b></a>&nbsp;&nbsp;&nbsp;</td><td align="right" class="main"><b>'.$currencies->format(tep_add_tax($quotes[$i]['methods'][0]['cost'], $quotes[$i]['tax'])).'</b></td></tr>';
              $ship_txt .= '<td class="main"><b>'.$quotes[$i]['module'].'&nbsp;';
              $ship_txt .= ($quotes[$i]['methods'][0]['title']!=''? '('.$quotes[$i]['methods'][0]['title'].')':'') . '</b>&nbsp;&nbsp;&nbsp;</td><td align="right" class="main"><b>'.$currencies->format(tep_add_tax($quotes[$i]['methods'][0]['cost'], $quotes[$i]['tax'])).'</b></td></tr>';
            } else {
              $ship_txt .= '<td class="main"><a title="' . CART_SHIPPING_SELECT_THIS . '" href="' . tep_href_link(basename($PHP_SELF)) . '" onclick="return shipincart_submit(\''.$thisquoteid.'\');">'.$quotes[$i]['module'].'&nbsp;';
              $ship_txt .= ($quotes[$i]['methods'][0]['title']!=''? '('.$quotes[$i]['methods'][0]['title'].')':'') . '</a>&nbsp;&nbsp;&nbsp;</td><td align="right" class="main">'.$currencies->format(tep_add_tax($quotes[$i]['methods'][0]['cost'], $quotes[$i]['tax'])).'</td></tr>';
            }
          }
        } else {
          // shipping method with sub methods (multipickup)
          for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
            $thisquoteid = $quotes[$i]['id'].'_'.$quotes[$i]['methods'][$j]['id'];
            $ship_txt .= '<tr class="'.$extra.'">';
            $ship_txt .= '<td class="main">'.$quotes[$i]['icon'].'&nbsp;&nbsp;&nbsp;</td>';
            if ($quotes[$i]['error']) {
              $ship_txt.='<td colspan="2" class="main">'.$quotes[$i]['module'].'&nbsp;';
              $ship_txt.= '('.$quotes[$i]['error'].')</td></tr>';
            } else {
              if ($selected_shipping['id'] == $thisquoteid) {
                $ship_txt.='<td class="main"><a title="' . CART_SHIPPING_SELECT_THIS . '" href="_" onclick="return shipincart_submit(\''.$thisquoteid.'\');"><b>'.$quotes[$i]['module'].'&nbsp;';
                $ship_txt.= ($quotes[$i]['methods'][$j]['title']!=''? '('.$quotes[$i]['methods'][$j]['title'].')':'') . '</b></a>&nbsp;&nbsp;&nbsp;</td><td align="right" class="main"><b>'.$currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax'])).'</b></td><td class="main">'.tep_image(DIR_WS_ICONS . 'selected.gif', 'Selected').'</td></tr>';
              } else {
                $ship_txt.='<td class="main"><a title="' . CART_SHIPPING_SELECT_THIS . '" href="_" onclick="return shipincart_submit(\''.$thisquoteid.'\');">'.$quotes[$i]['module'].'&nbsp;';
                $ship_txt.= ($quotes[$i]['methods'][$j]['title']!=''?'('.$quotes[$i]['methods'][$j]['title'].')':'') . '</a>&nbsp;&nbsp;&nbsp;</td><td align="right" class="main">'.$currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax'])).'</td><td class="main">&nbsp;</td></tr>';
              }
            }
          }
        }
      }
    }
  }
    $ship_txt.= '</table></form>'; 

    $info_box_contents = [
        ['text' => $ship_txt]
    ];

    $content = new TableBox();
    $content->set_param('cellpadding', 4);
    $content->set_param('parameters', 'class="infoBoxContents"');
    $content_box_contents = array(array('text' => $content->get_box($info_box_contents)));
        
    $content_box = new TableBox();
    $content_box->set_param('cellpadding', 1);
    $content_box->set_param('parameters', 'class="infoBox"');
    $content_box->get_box($content_box_contents, true);
  
  if (CARTSHIP_SHOWOT == 'ja') {
    // BOF get taxes if not logged in
    if (!isset($_SESSION['customer_id'])){
      $products = $_SESSION['cart']->get_products();
      for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        $products_tax = tep_get_tax_rate($products[$i]['tax_class_id'], $order->delivery['country_id'],$order->delivery['zone_id']);
        $products_tax_description = tep_get_tax_description($products[$i]['tax_class_id'], $order->delivery['country_id'], $order->delivery['zone_id']);
        if (!isset($order->info['tax_groups']["$products_tax_description"])) $order->info['tax_groups']["$products_tax_description"] = 0;
        if (DISPLAY_PRICE_WITH_TAX == 'ja') {
         //Modified by Strider 42 to correct the tax calculation when a customer is not logged in
         // $tax_val = ($products[$i]['final_price']-(($products[$i]['final_price']*100)/(100+$products_tax)))*$products[$i]['quantity'];
          $tax_val = (($products[$i]['final_price']/100)*$products_tax)*$products[$i]['quantity'];
        } else {
          $tax_val = (($products[$i]['final_price']*$products_tax)/100)*$products[$i]['quantity'];
        }
        $order->info['tax'] += $tax_val;
        $order->info['tax_groups']["$products_tax_description"] += $tax_val;
        // Modified by Strider 42 to correct the order total figure when shop displays prices with tax
        if (DISPLAY_PRICE_WITH_TAX == 'ja') {
           $order->info['total'];
        } else {
          $order->info['total']+=$tax_val;
        }
      }
    }
    // EOF get taxes if not logged in (seems like less code than in order class)
    $order_total_modules = new Ordertotal;

    $order_total_modules->process();

    $contents = array( 
      array(
        array(
          'params' => 'width="100%" class="infoBoxHeading"',
          'text' => CART_OT
        )
      )
    );
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);

    $otTxt = '<table align="right">';
    $otTxt .= $order_total_modules->output() . '</table>';

    $info_box_contents = array(
      array('text' => $otTxt)
    );
    new InfoBox($info_box_contents);
  }
} // Use only when cart_contents > 0
?>
             </td></tr></table>