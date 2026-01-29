<?php
/*
  $Id: shopping_cart.php,v 1.73 2003/06/09 23:03:56 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require("includes/application_top.php");

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_SHOPPING_CART));

require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('cart_quantity', tep_href_link(FILENAME_SHOPPING_CART, 'action=update_product'));
$heading_image = 'table_background_cart.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
if ($cart->count_contents() > 0) {
?>
      <tr>
        <td style="padding-left: 3px; padding-right: 3px;">
<?php
    $info_box_contents = [];
    $info_box_contents[0][] = array(
      'align' => 'center',
      'params' => 'class="productListing-heading"',
      'text' => TABLE_HEADING_REMOVE
    );

    $info_box_contents[0][] = array(
      'params' => 'class="productListing-heading"',
      'text' => TABLE_HEADING_PRODUCTS
    );

    $info_box_contents[0][] = array(
      'align' => 'center',
      'params' => 'class="productListing-heading"',
      'text' => TABLE_HEADING_QUANTITY
    );

    $info_box_contents[0][] = array(
      'align' => 'right',
      'params' => 'class="productListing-heading"',
      'text' => TABLE_HEADING_TOTAL
    );

    $any_out_of_stock = 0;
    $products = $cart->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
// Push all attributes information in an array
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        foreach ($products[$i]['attributes'] as $option => $value) {
          echo tep_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
          $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                      from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                      where pa.products_id = '" . $products[$i]['id'] . "'
                                       and pa.options_id = '" . $option . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . $value . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                       and poval.language_id = '" . (int)$_SESSION['languages_id'] . "'");
          $attributes_values = tep_db_fetch_array($attributes);

          $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
          $products[$i][$option]['options_values_id'] = $value;
          $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
          $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
          $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
        }
      }
    }

    $shopping_cart_tax_max = 0;
    $shopping_cart_tax_max_id = 0;
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      $this_product_tax_rate = tep_get_tax_rate($products[$i]['tax_class_id']);
      if ($this_product_tax_rate > $shopping_cart_tax_max) {
        $shopping_cart_tax_max = $this_product_tax_rate;
        $shopping_cart_tax_max_id = $products[$i]['tax_class_id'];
      }
      if (($i/2) == floor($i/2)) {
        $info_box_contents[] = array('params' => 'class="productListing-even"');
      } else {
        $info_box_contents[] = array('params' => 'class="productListing-odd"');
      }

      $cur_row = sizeof($info_box_contents) - 1;

      $info_box_contents[$cur_row][] = array(
        'align' => 'center',
        'params' => 'class="productListing-data" valign="top"',
        'text' => tep_draw_checkbox_field('cart_delete[]', $products[$i]['id'])
      );

      $products_name = '<table border="0" cellspacing="2" cellpadding="2">' .
                       '  <tr>' .
                       '    <td class="productListing-data" align="center"><a href="' . ingo_product_link($products[$i]['id'], $products[$i]['name']) . '">' . $thumbnail->get(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></td>' .
                       '    <td class="productListing-data" valign="top"><a href="' . ingo_product_link($products[$i]['id'], $products[$i]['name']) . '"><b>' . $products[$i]['name'] . '</b></a>';

      if (STOCK_CHECK == 'true') {
        $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
        if (tep_not_null($stock_check)) {
          $any_out_of_stock = 1;

          $products_name .= $stock_check;
        }
      }

      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        foreach($products[$i]['attributes'] as $option => $value) {
          $products_name .= '<br /><small><i> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</i></small>';
        }
      }

      $products_name .= '    </td>' .
                        '  </tr>' .
                        '</table>';

      $info_box_contents[$cur_row][] = array(
        'params' => 'class="productListing-data"',
        'text'   => $products_name
      );

      $info_box_contents[$cur_row][] = array(
        'align'  => 'center',
        'params' => 'class="productListing-data" valign="top"',
        'text'   => tep_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="4"') . tep_draw_hidden_field('products_id[]', $products[$i]['id'])
      );

      $info_box_contents[$cur_row][] = array(
        'align'  => 'right',
        'params' => 'class="productListing-data" valign="top"',
        'text'   => '<b>' . $currencies->display_price($products[$i]['final_price'], $this_product_tax_rate, $products[$i]['quantity']) . '</b>' .
                    ingo_price_added($this_product_tax_rate, false)
      );
    }

    $product_listing = new TableBox;
    $product_listing->set_param('parameters', 'class="productListing"');
    $product_listing->get_box($info_box_contents, true);
?>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td align="right" class="main" style="padding-left: 3px; padding-right: 3px;"><b><?php echo SUB_TITLE_SUB_TOTAL . ' ' . $currencies->format($cart->show_total()) . '</b>' . ingo_price_added(); ?></td>
      </tr>
<?php
    if ($any_out_of_stock == 1) {
      if (STOCK_ALLOW_CHECKOUT == 'true') {
?>
      <tr>
        <td class="stockWarning" align="center" style="padding-left: 3px; padding-right: 3px;"><br /><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></td>
      </tr>
<?php
      } else {
?>
      <tr>
        <td class="stockWarning" align="center" style="padding-left: 3px; padding-right: 3px;"><br /><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></td>
      </tr>
<?php
      }
    }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><?php echo tep_image_submit('button_update_cart.gif', IMAGE_BUTTON_UPDATE_CART, 'style="margin-left:10px;"'); ?></td>
<?php
    if (isset($_SESSION['navigation']) && is_object($_SESSION['navigation'])) {
        $back = sizeof($_SESSION['navigation']->path)-2;
        if (isset($_SESSION['navigation']->path[$back])) {
?>
                <td class="main"><?php echo '<a href="' . tep_href_link($_SESSION['navigation']->path[$back]['page'], tep_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']) . '">' . tep_image_button('button_continue_shopping.gif', IMAGE_BUTTON_CONTINUE_SHOPPING, 'style="margin:0px 10px;"') . '</a>'; ?></td>
<?php
        }
    }
?>
                <td align="right" class="main"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT, 'style="margin-right:10px;"') . '</a>'; ?></td>
              </tr>
            </table> 
          </div>
        </td>
      </tr>
<?php
} else {
?>
      <tr>
        <td align="center" class="main"><?php new infoBox([['text' => TEXT_CART_EMPTY]]); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <div style="text-align:right;margin:2px 12px"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></div>
          </div>
        </td>
      </tr>
<?php
}
?>
     </table></form>
<?php
if (SHOW_SHIP_IN_CART == 'ja') {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><br /><?php require(DIR_WS_MODULES . 'shipping_estimator.php'); ?></td>
      </tr>
    </table>
<?php
}
?>
    </td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
