<?php
/*
  $Id: checkout_payment.php,v 1.113 2003/06/29 23:03:27 hpdl Exp $

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
    xprios_set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// if there is nothing in the customers cart, redirect them to the shopping cart page
if ($_SESSION['cart']->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
}

// if no shipping method has been selected, redirect the customer to the shipping method selection page
if (!isset($_SESSION['shipping'])) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
}

// Stock Check
if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        if (tep_check_stock($products[$i]['id'], $products[$i]['quantity'])) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
            break;
        }
    }
}

// if no billing destination address was selected, use the customers own address as default
if (!isset($_SESSION['billto'])) {
    $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
} else {
// verify the selected billing address
    $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$_SESSION['customer_id'] . "' and address_book_id = '" . (int)$_SESSION['billto'] . "'");
    $check_address = tep_db_fetch_array($check_address_query);

    if ($check_address['total'] != 1) {
        $_SESSION['billto'] = $_SESSION['customer_default_address_id'];
        unset($_SESSION['payment']);
    }
}

$order = new Order;

$total_weight = $_SESSION['cart']->show_weight();
$total_count = $_SESSION['cart']->count_contents();

// load all enabled payment modules
$payment_modules = new Payment;

$breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

$this_head_include = "<script type=\"text/javascript\" language=\"javascript\"><!--
var selected;

function selectRowEffect(object, buttonSelect) {
  if (!selected) {
    if (document.getElementById) {
      selected = document.getElementById('defaultSelected');
    } else {
      selected = document.all['defaultSelected'];
    }
  }

  if (selected) selected.className = 'moduleRow';
  object.className = 'moduleRowSelected';
  selected = object;

// one button is not an array
  if (document.checkout_payment.payment[0]) {
    document.checkout_payment.payment[buttonSelect].checked=true;
  } else {
    document.checkout_payment.payment.checked=true;
  }
}

function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}
//--></script>" . "\n" . $payment_modules->javascript_validation();
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('checkout_payment', tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'onsubmit="return check_form();"');
$heading_image_tag = '<div class="checkoutbarnew"><div class="checkoutactiv" title=" ' . CHECKOUT_BAR_DELIVERY . ' "><a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">1</a></div><div class="checkoutactiv" title=" ' . CHECKOUT_BAR_PAYMENT . ' ">2</div><div class="checkoutpassiv" title=" ' . CHECKOUT_BAR_CONFIRMATION . ' ">3</div><div class="checkoutpassiv" title=" ' . CHECKOUT_BAR_FINISHED . ' ">4</div></div>';
require(DIR_WS_INCLUDES . 'column_center.php');

if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
?>
      <tr>
        <td><div style="margin:2px;"><b><?php echo tep_output_string_protected($error['title']); ?></b></div></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBoxNotice">
          <tr class="infoBoxNoticeContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td class="main" width="100%" valign="top"><?php echo tep_output_string_protected($error['error']); ?></td>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
}
if ($_SESSION['customer_id']>0) {
?>
      <tr>
        <td><div style="margin:2px;"><b><?php echo TABLE_HEADING_BILLING_ADDRESS; ?></b></div></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <div style="margin:0 10px">
              <table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="main" width="50%" valign="top"><?php echo TEXT_SELECTED_BILLING_DESTINATION . '<br /><br /><a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . tep_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS) . '</a>'; ?></td>
                  <td align="right" width="50%" valign="top"><table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td class="main" align="center" valign="top"><b><?php echo TITLE_BILLING_ADDRESS; ?></b><br /><?php echo tep_image(DIR_WS_IMAGES . 'desk/arrow_south_east.gif'); ?></td>
                      <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      <td class="main" valign="top"><?php echo tep_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br />'); ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table> 
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td><div style="margin:2px;"><b><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></b></div></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <div style="margin:0 10px;">
              <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
$selection = $payment_modules->selection();

if (sizeof($selection) > 1) {
?>
                <tr>
                  <td class="main" valign="top" colspan="2"> &nbsp; <?php echo tep_image(DIR_WS_IMAGES . 'desk/arrow_west_south.gif'); ?> <b style="vertical-align:top;"><?php echo TEXT_SELECT_PAYMENT_METHOD; ?></b></td>
                  <!-- td class="main" width="50%" valign="top"><?php echo TEXT_SELECT_PAYMENT_METHOD; ?></td>
                  <td class="main" width="50%" valign="top" align="right"><b><?php echo TITLE_PLEASE_SELECT; ?></b><br /><?php echo tep_image(DIR_WS_IMAGES . 'desk/arrow_east_south.gif'); ?></td -->
                </tr>
<?php
} else {
?>
                <tr>
                  <td class="main" width="100%" colspan="2"><?php echo TEXT_ENTER_PAYMENT_INFORMATION; ?></td>
                </tr>
<?php
}

$radio_buttons = 0;
for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
    if ($radio_buttons>0) {
?>
                <tr>
                  <td colspan="2"><?php echo tep_draw_separator('pixel_black.gif', '100%', '1'); ?></td>
                </tr>
<?php
    }
?>
                <tr>
                  <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
    if ( (isset($_SESSION['payment']) && $selection[$i]['id']==$_SESSION['payment']) || ($n == 1) ) {
        echo '                    <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
    } else {
        echo '                    <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
    }
?>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      <td class="main" colspan="4"><?php
    if (sizeof($selection) > 1) {
        echo tep_draw_radio_field('payment', $selection[$i]['id']);
    } else {
        echo tep_draw_hidden_field('payment', $selection[$i]['id']);
    }
    echo tep_draw_separator('pixel_trans.gif', '10', '1') .'<b>' . $selection[$i]['module'];
    ?></b></td>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                    </tr>
<?php
    if (isset($selection[$i]['error'])) {
?>
                    <tr>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      <td class="main" colspan="4"><?php echo $selection[$i]['error']; ?></td>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                    </tr>
<?php  
    } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
?>
                    <tr>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      <td colspan="4"><table border="0" cellspacing="0" cellpadding="2">
<?php
        for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>
                        <tr>
                          <td width="30"><?php echo tep_draw_separator('pixel_trans.gif', '30', '1'); ?></td>
<?php
            if ($selection[$i]['fields'][$j]['field']=='') {
?>
                          <td class="main" colspan="3"><?php echo $selection[$i]['fields'][$j]['title']; ?></td>
<?php
            } else {
?>
                          <td class="main"><?php echo str_replace('onfocus=""', 'onfocus="document.checkout_payment.payment[' .$i . '].checked=true;"', $selection[$i]['fields'][$j]['title']); ?></td>
                          <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                          <td class="main"><?php echo str_replace('/>', ((strpos($selection[$i]['fields'][$j]['field'], 'select')===false)?' onfocus="document.checkout_payment.payment[' .$i . '].checked=true;"/>':'/>'), $selection[$i]['fields'][$j]['field']); ?></td>
<?php
            }
?>
                          <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                        </tr>
<?php
        }
?>
                      </table></td>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                    </tr>
<?php
    }
?>
                  </table></td>
                </tr>
<?php
    $radio_buttons++;
}
?>
              </table>
            </div>  
          </div>
        </td>
      </tr>
      <tr>
        <td><div style="margin:2px;margin-top:10px;"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></div></td>
      </tr>
      <tr>
        <td> 
          <div class="divbox">
            <div style="margin:2px;"><?php echo tep_draw_textarea_field('comments', 'soft', '60', '5', (isset($_SESSION['comments'])&& $_SESSION['comments']!=''?$_SESSION['comments']:'') ); ?></div>
          </div>
        </td>
      </tr>
<?php
$text_query = tep_db_query("SELECT text FROM " . TABLE_INFO_TEXTE . " WHERE code = 'checkout_conditions' AND languages_id = '" . (int)$_SESSION['languages_id'] . "'");
if ($text_result = tep_db_fetch_array($text_query)) {
    $replace_array = ['<br>' => "\n", '<br />' => "\n", '<BR>' => "\n", '<BR />' => "\n"];
    $info_text = trim(strip_tags(strtr($text_result['text'], $replace_array)));
}
?>
      <tr>
        <td><div style="margin:2px;margin-top:10px;"><b><?php echo HEADING_CONDITIONS_INFORMATION; ?></b></div></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <div style="margin:2px;">
              <textarea name="condition" class="small" cols="80" rows="10" readonly="readonly">
<?php
if (READ_INFO_TEXTE_FROM_DATABASE == 'ja' && $info_text != '') {
    echo $info_text;
} else {
    require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_CHECKOUT_CONDITIONS);
}
?>
              </textarea> 
              <table border="0" cellspacing="0" cellpadding="2" style="margin:0 10px">
                <tr>
                  <td class="main" valign="middle"><input type="checkbox" id="conditions" name="conditions" value="1" /></td>
                  <td class="main"><?php echo TEXT_CONDITIONS_CONFIRM; ?></td>
                </tr>
                <tr>
                  <td class="main" align="right" colspan="2"><?php echo '<a href="' . tep_href_link(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_CONDITIONS_DOWNLOAD, '', 'SSL') . '" target="_blank"><b>' . TEXT_CONDITIONS_DOWNLOAD . '</b></a>'; ?></td>
                </tr>
              </table>
            </div>  
          </div>
        </td>
      </tr>

      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td class="main"><?php echo '<b>' . TITLE_CONTINUE_CHECKOUT_PROCEDURE . '</b><br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></td>
                <td class="main" align="right"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'style="margin-right:10px;"'); ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
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
            <td width="25%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
                <td><?php echo tep_image(DIR_WS_IMAGES . 'desk/checkout_bullet.gif'); ?></td>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
              </tr>
            </table></td>
            <td width="25%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
            <td width="25%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '1', '5'); ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td align="center" width="25%" class="checkoutBarFrom"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '" class="checkoutBarFrom">' . CHECKOUT_BAR_DELIVERY . '</a>'; ?></td>
            <td align="center" width="25%" class="checkoutBarCurrent"><?php echo CHECKOUT_BAR_PAYMENT; ?></td>
            <td align="center" width="25%" class="checkoutBarTo"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></td>
            <td align="center" width="25%" class="checkoutBarTo"><?php echo CHECKOUT_BAR_FINISHED; ?></td>
          </tr>
        </table></td>
      </tr>
    </table></form></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
