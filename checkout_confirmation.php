<?php
/*
  $Id: checkout_confirmation.php,v 1.139 2003/06/11 17:34:53 hpdl Exp $

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

require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
if (!isset($_SESSION['customer_id'])) {
    xprios_set_snapshot(['mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT]);
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
}

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset($_SESSION['shipping'])) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// if conditions are not accepted, redirect the customer to the payment method selection page
if (!(isset($_POST['conditions']) && $_POST['conditions'] == '1')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_CONDITIONS_NOT_ACCEPTED), 'SSL', true, false));
}

if (isset($_POST['payment'])) $_SESSION['payment'] = $_POST['payment'];

if (tep_not_null($_POST['comments'])) {
    $_SESSION['comments'] = xprios_prepare_post('comments');
}

$order = new Order;

// load the selected payment module
$payment_modules = new payment($_SESSION['payment']);
//$payment_modules->update_status();

if ( ( is_array($payment_modules->modules) && (sizeof($payment_modules->modules) > 1) && !is_object(${$_SESSION['payment']}) ) || (is_object(${$_SESSION['payment']}) && (${$_SESSION['payment']}->enabled == false)) ) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
}

if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
}

// load the selected shipping module
$shipping_modules = new Shipping($_SESSION['shipping']);

$order_total_modules = new OrderTotal;

// Stock Check
$any_out_of_stock = false;
if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
        if (tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
            $any_out_of_stock = true;
        }
    }
    // Out of Stock
    if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
    }
}

$breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2);

$this_head_include = "<script type=\"text/javascript\"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}
//--></script>";
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$heading_image_tag = '<div class="checkoutbarnew"><div class="checkoutactiv" title=" ' . CHECKOUT_BAR_DELIVERY . ' "><a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">1</a></div><div class="checkoutactiv" title=" ' . CHECKOUT_BAR_PAYMENT . ' "><a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '">2</a></div><div class="checkoutactiv" title=" ' . CHECKOUT_BAR_CONFIRMATION . ' ">3</div><div class="checkoutpassiv" title=" ' . CHECKOUT_BAR_FINISHED . ' ">4</div></div>';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
                <td class="main"><?php echo TEXT_INFORMATION; ?></td>
            </tr>
          </table></td>
        </tr> 

      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td width="<?php echo ($_SESSION['sendto'] != false ? '50%' : '100%'); ?>" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><?php echo '<b>' . HEADING_BILLING_ADDRESS . '</b> <a href="' . (($_SESSION['customer_id']>0)?tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL'):tep_href_link(FILENAME_CREATE_ACCOUNT, 'guest=guest', 'SSL')) . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br />'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo '<b>' . HEADING_PAYMENT_METHOD . '</b> <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo $order->info['payment_method']; ?></td>
              </tr>
            </table></td>  
<?php
if ($_SESSION['sendto'] != false) {
?>
            <td width="50%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><?php echo '<b>' . HEADING_DELIVERY_ADDRESS . '</b>' . (($_SESSION['customer_id']>0  || (defined('PURCHASE_WITHOUT_ACCOUNT_SEPARATE_SHIPPING')&&PURCHASE_WITHOUT_ACCOUNT_SEPARATE_SHIPPING=='ja'))? ' <a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>':''); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br />'); ?></td>
              </tr>
<?php
    if ($order->info['shipping_method']) {
?>
              <tr>
                <td class="main"><?php echo '<b>' . HEADING_SHIPPING_METHOD . '</b> <a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo $order->info['shipping_method']; ?></td>
              </tr>
<?php
    }
?>
            </table></td>
<?php
}
?>
          </tr>
        </table></td>
      </tr>
<?php
if (is_array($payment_modules->modules)) {
    if ($confirmation = $payment_modules->confirmation()) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td><b><?php echo HEADING_PAYMENT_INFORMATION; ?></b></td>
              </tr>
              <tr>
                <td class="main" colspan="4"><?php echo $confirmation['title']; ?></td>
              </tr>
<?php
        if (!isset($confirmation['fields'])) $confirmation['fields'] = [];
        for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
?>
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td class="main"><?php echo $confirmation['fields'][$i]['title']; ?></td>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td class="main"><?php echo $confirmation['fields'][$i]['field']; ?></td>
              </tr>
<?php
        }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
    }
}

if (tep_not_null($order->info['comments'])) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><?php echo '<b>' . HEADING_ORDER_COMMENTS . '</b> <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo nl2br(tep_output_string_protected($order->info['comments'])) . tep_draw_hidden_field('comments', $order->info['comments']); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
}
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContentsConfirm">
            <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
if (sizeof($order->info['tax_groups']) > 1) {
?>
                  <tr>
                    <td class="main" colspan="3"><?php echo '<b>' . HEADING_PRODUCTS . '</b> <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></td>
                    <td class="smallText" align="right"><b><?php echo (DISPLAY_PRICE_WITH_TAX=='ja'? SIMPLE_WORD_INCL:SIMPLE_WORD_EXCL) . HEADING_TAX; ?></b></td>
                    <td class="smallText" align="right"><b><?php echo HEADING_TOTAL; ?></b></td>
                  </tr>
<?php
} else {
?>
                  <tr>
                    <td class="main" colspan="4"><?php echo '<b>' . HEADING_PRODUCTS . '</b> <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></td>
                  </tr>
<?php
}
for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
?>
                  <tr>
                    <td class="main" align="right" valign="top" width="30"><?php echo $order->products[$i]['qty']; ?>&times;</td>
                    <td width="<?php echo intval(THUMBNAIL_IMAGE_WIDTH/2); ?>" style="padding:0 2px 2px 0">
            <script type="text/javascript"><!--
              document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . tep_href_link(FILENAME_POPUP_IMAGE, 'pID=' . $order->products[$i]['id']) . '\\\')">' . tep_image_product(DIR_WS_IMAGES . $order->products[$i]['image'], htmlspecialchars($order->products[$i]['name'], ENT_QUOTES, CHARSET), 30) . '</a>'; ?>');
              //-->
            </script>
            <noscript><?php echo '<a href="' . tep_href_link(DIR_WS_IMAGES . $order->products[$i]['image']) . '" target="_blank">' . tep_image_product(DIR_WS_IMAGES . $order->products[$i]['image'], htmlspecialchars($order->products[$i]['name'], ENT_QUOTES, CHARSET), 30) . '</a>'; ?></noscript>
                    </td>
                    <td class="main" valign="top"><?php echo $order->products[$i]['name'] . '<br><a href="' . ingo_product_link($order->products[$i]['id'], $order->products[$i]['name']) . '"><span class="orderEdit">(' . TEXT_PRODUCT_DETAILS . ')</span></a>';

    if (STOCK_CHECK == 'true') {
        echo tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty']);
    }

    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
        for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
            echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
        }
    }

    echo '</td>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) {
        echo '                    <td class="main" valign="top" align="right">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n";
    }
?>
                    <td class="main" align="right" valign="top"><?php echo $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']); ?></td>
                  </tr>
<?php
}
?>
                </table></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_black.gif', '100%', '1'); ?></td>
              </tr>
              <tr>
                <td width="50%" rowspan="3" valign="middle">
<?php
if (isset(${$_SESSION['payment']}->confirmation_text) && ${$_SESSION['payment']}->confirmation_text!='') {
    echo '                 ' . ${$_SESSION['payment']}->confirmation_text . '<br>' . "\n";
}

if ($order->delivery['country_id']!=STORE_COUNTRY) {
    $geozones = [];
    $query = tep_db_query('select geo_zone_id from ' . TABLE_ZONES_TO_GEO_ZONES . ' where zone_country_id in(' . intval($order->delivery['country_id']) . ',' . intval(STORE_COUNTRY) . ')');
    while ($result = tep_db_fetch_array($query)) {
        if (!in_array($result['geo_zone_id'], $geozones)) {
            $geozones[] = $result['geo_zone_id'];
        }
    }
    if (count($geozones)!=1) {
        echo '                <br>' . TEXT_INFORMATION_FOREIGN_TAX . '<br>' . "\n";
    }    
} 
?>
                </td>
                <td width="50%" valign="top" align="right"><table border="0" cellspacing="0" cellpadding="2">
<?php
if (MODULE_ORDER_TOTAL_INSTALLED) {
    $order_total_modules->process();
    echo $order_total_modules->output();
}
?>
                </table></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
              </tr>
              <tr>
                <td width="50%" valign="top" align="right"><?php
if (isset(${$_SESSION['payment']}->form_action_url) && ${$_SESSION['payment']}->form_action_url != '') {
    $form_action_url = ${$_SESSION['payment']}->form_action_url;
} else {
    $form_action_url = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
}

echo tep_draw_form('checkout_confirmation', $form_action_url, 'post');

if (is_array($payment_modules->modules)) {
    echo $payment_modules->process_button();
}

echo tep_image_submit('button_confirm_order.gif', IMAGE_BUTTON_CONFIRM_ORDER, 'style="margin-right:10px;"') . '</form>';
?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>

      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td width="25%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="50%" align="right"><?php echo tep_draw_separator('pixel_silver.gif', '1', '5'); ?></td>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
              </tr>
            </table></td>
            <td width="25%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
            <td width="25%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
                <td><?php echo tep_image(DIR_WS_IMAGES . 'desk/checkout_bullet.gif'); ?></td>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
              </tr>
            </table></td>
            <td width="25%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '1', '5'); ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td align="center" width="25%" class="checkoutBarFrom"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '" class="checkoutBarFrom">' . CHECKOUT_BAR_DELIVERY . '</a>'; ?></td>
            <td align="center" width="25%" class="checkoutBarFrom"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '" class="checkoutBarFrom">' . CHECKOUT_BAR_PAYMENT . '</a>'; ?></td>
            <td align="center" width="25%" class="checkoutBarCurrent"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></td>
            <td align="center" width="25%" class="checkoutBarTo"><?php echo CHECKOUT_BAR_FINISHED; ?></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
