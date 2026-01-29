<?php
/*
  $Id: checkout_shipping_address.php,v 1.15 2003/06/09 23:03:53 hpdl Exp $

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

$order = new Order;

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
if ($order->content_type == 'virtual') {
    $_SESSION['shipping'] = false;
    $_SESSION['sendto'] = false;
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

$error = false;
$process = false;
if (isset($_POST['action']) && ($_POST['action'] == 'submit')) {
// process a new shipping address
    if (tep_not_null($_POST['firstname']) && tep_not_null($_POST['lastname']) && tep_not_null($_POST['street_address'])) {
      $process = true;

      $gender         = xprios_prepare_post('gender');
      $company        = xprios_prepare_post('company');
      $firstname      = xprios_prepare_post('firstname');
      $lastname       = xprios_prepare_post('lastname');
      $street_address = xprios_prepare_post('street_address');
      $suburb         = xprios_prepare_post('suburb');
      $postcode       = xprios_prepare_post('postcode');
      $city           = xprios_prepare_post('city');
      $country        = max(0, intval(xprios_prepare_post('country')));
      if (ACCOUNT_STATE == 'true') {
        if (isset($_POST['zone_id'])) {
          $zone_id = xprios_prepare_post('zone_id');
        } else {
          $zone_id = false;
        }
        $state = xprios_prepare_post('state');
      }

      if (ACCOUNT_GENDER == 'true') {
        if ($gender != 'm' && $gender != 'f') {
          $error = true;
          $messageStack->add('checkout_address', ENTRY_GENDER_ERROR);
        }
      }

      if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
        $error = true;
        $messageStack->add('checkout_address', ENTRY_FIRST_NAME_ERROR);
      }

      if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
        $error = true;
        $messageStack->add('checkout_address', ENTRY_LAST_NAME_ERROR);
      }

      if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
        $error = true;
        $messageStack->add('checkout_address', ENTRY_STREET_ADDRESS_ERROR);
      }

      if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
        $error = true;
        $messageStack->add('checkout_address', ENTRY_POST_CODE_ERROR);
      }

      if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
        $error = true;
        $messageStack->add('checkout_address', ENTRY_CITY_ERROR);
      }

      if (ACCOUNT_STATE == 'true') {
        $zone_id = 0;
        $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "'");
        $check = tep_db_fetch_array($check_query);
        $entry_state_has_zones = ($check['total'] > 0);
        if ($entry_state_has_zones == true) {
          $zone_query = tep_db_query("select distinct zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' and (zone_name like '" . tep_db_input($state) . "%' or zone_code like '%" . tep_db_input($state) . "%')");
          if (tep_db_num_rows($zone_query) == 1) {
            $zone = tep_db_fetch_array($zone_query);
            $zone_id = $zone['zone_id'];
            
          } else {
            $error = true;
            $messageStack->add('checkout_address', ENTRY_STATE_ERROR_SELECT);
          }
          
        } else {
          if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
            $error = true;
            $messageStack->add('checkout_address', ENTRY_STATE_ERROR);
          }
        }
      }

      if ($country < 1) {
        $error = true;
        $messageStack->add('checkout_address', ENTRY_COUNTRY_ERROR);
      }

      if ($error == false) {
        $sql_data_array = array(
          'customers_id' => $_SESSION['customer_id'],
          'entry_firstname' => $firstname,
          'entry_lastname' => $lastname,
          'entry_street_address' => $street_address,
          'entry_postcode' => $postcode,
          'entry_city' => $city,
          'entry_country_id' => $country,
          'entry_gender' => '',
          'entry_company' => '',
          'entry_zone_id' => 0,
          'entry_state' => ''
        );

        if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
        if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
        if (ACCOUNT_SUBURB == 'true' && $suburb != '') $sql_data_array['entry_suburb'] = $suburb;
        if (ACCOUNT_STATE == 'true') {
          if ($zone_id > 0) {
            $sql_data_array['entry_zone_id'] = $zone_id;
            $sql_data_array['entry_state'] = '';
          } else {
            $sql_data_array['entry_zone_id'] = '0';
            $sql_data_array['entry_state'] = $state;
          }
        }

// Ingo PWA Beginn
        if ($_SESSION['customer_id']==0) {
          $_SESSION['sendto'] = 1;
          $_SESSION['pwa_array_shipping'] = $sql_data_array;
          unset($_SESSION['shipping']);
          tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }
// Ingo PWA Ende

        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

        $_SESSION['sendto'] = tep_db_insert_id();

        unset($_SESSION['shipping']);

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
      }
// process the selected shipping destination
    } elseif (isset($_POST['address'])) {
      $reset_shipping = false;
      if (isset($_SESSION['sendto']) && $_SESSION['sendto'] != $_POST['address'] && isset($_SESSION['shipping'])) {
        $reset_shipping = true;
      }

      $_SESSION['sendto'] = $_POST['address'];

      $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$_SESSION['customer_id'] . "' and address_book_id = '" . (int)$_SESSION['sendto'] . "'");
      $check_address = tep_db_fetch_array($check_address_query);

      if ($check_address['total'] == '1') {
        if ($reset_shipping == true) {
          unset($_SESSION['shipping']);
        }
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
      } else {
        unset($_SESSION['sendto']);
      }
    } else {
      $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
}

// if no shipping destination address was selected, use their own address as default
if (!isset($_SESSION['sendto'])) {
    $_SESSION['sendto'] = $_SESSION['customer_default_address_id'];
}

// Ingo PWA Beginn
if (isset($_SESSION['pwa_array_shipping']) && is_array($_SESSION['pwa_array_shipping']) && count($_SESSION['pwa_array_shipping'])) {
    if (isset($_SESSION['pwa_array_shipping']['entry_gender'])) $gender = $_SESSION['pwa_array_shipping']['entry_gender'];
    $firstname = $_SESSION['pwa_array_shipping']['entry_firstname'];
    $lastname = $_SESSION['pwa_array_shipping']['entry_lastname'];
    if (isset($_SESSION['pwa_array_shipping']['entry_company'])) $company = $_SESSION['pwa_array_shipping']['entry_company'];
    $street_address = $_SESSION['pwa_array_shipping']['entry_street_address'];
    if (isset($_SESSION['pwa_array_shipping']['entry_suburb'])) $suburb = $_SESSION['pwa_array_shipping']['entry_suburb'];
    $postcode = $_SESSION['pwa_array_shipping']['entry_postcode'];
    $city = $_SESSION['pwa_array_shipping']['entry_city'];
    if (isset($_SESSION['pwa_array_shipping']['entry_state'])) $state = $_SESSION['pwa_array_shipping']['entry_state'];
    $country = $_SESSION['pwa_array_shipping']['entry_country_id'];
}
// Ingo PWA Ende

$breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));

$addresses_count = tep_count_customer_address_book_entries();

$this_head_include = "<script type=\"text/javascript\"><!--
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
  if (document.checkout_address.address[0]) {
    document.checkout_address.address[buttonSelect].checked=true;
  } else {
    document.checkout_address.address.checked=true;
  }
}

function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}

function check_form_optional(form_name) {
  var form = form_name;

  var firstname = form.elements['firstname'].value;
  var lastname = form.elements['lastname'].value;
  var street_address = form.elements['street_address'].value;

  if (firstname == '' && lastname == '' && street_address == '') {
    return true;
  } else {
    return check_form(form_name);
  }
}
//--></script>";
$this_head_file = DIR_WS_INCLUDES . 'form_check.js.php';
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('checkout_address', tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'), 'post', 'onsubmit="return check_form_optional(checkout_address);"');
$heading_image = 'table_background_delivery.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
if ($messageStack->size('checkout_address') > 0) {
?>
      <tr>
        <td><div style="margin:2px; margin-top:10px;"><?php echo $messageStack->output('checkout_address'); ?></div></td>
      </tr>
<?php
}

if ($process == false) {
?>
      <tr>
        <td><div style="margin:2px; margin-top:10px;"><b><?php echo TABLE_HEADING_SHIPPING_ADDRESS; ?></b></div></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <div style="margin:0 10px">
              <table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="main" width="50%" valign="top"><?php echo TEXT_SELECTED_SHIPPING_DESTINATION; ?></td>
                  <td align="right" width="50%" valign="top"><table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td class="main" align="center" valign="top"><?php echo '<b>' . TITLE_SHIPPING_ADDRESS . '</b><br />' . tep_image(DIR_WS_IMAGES . 'desk/arrow_south_east.gif'); ?></td>
                      <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      <td class="main" valign="top"><?php echo tep_address_label($_SESSION['customer_id'], $_SESSION['sendto'], true, ' ', '<br />'); ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table>
            </div>  
          </div>
        </td>
      </tr>
<?php
    if ($addresses_count > 1) {
?>
      <tr>
        <td><div style="margin:2px; margin-top:10px;"><b><?php echo TABLE_HEADING_ADDRESS_BOOK_ENTRIES; ?></b></div></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <div style="margin:0px 10px;">
              <table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                  <td class="main" width="50%" valign="top"><?php echo TEXT_SELECT_OTHER_SHIPPING_DESTINATION; ?></td>
                  <td class="main" width="50%" valign="top" align="right"><?php echo '<b>' . TITLE_PLEASE_SELECT . '</b><br />' . tep_image(DIR_WS_IMAGES . 'desk/arrow_east_south.gif'); ?></td>
                  <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                </tr>
<?php
      $radio_buttons = 0;

      $addresses_query = tep_db_query("select address_book_id, entry_firstname as firstname, entry_lastname as lastname, entry_company as company, entry_street_address as street_address, entry_suburb as suburb, entry_city as city, entry_postcode as postcode, entry_state as state, entry_zone_id as zone_id, entry_country_id as country_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
      while ($addresses = tep_db_fetch_array($addresses_query)) {
        $format_id = tep_get_address_format_id($addresses['country_id']);
?>
                <tr>
                  <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                  <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
       if ($addresses['address_book_id'] == $_SESSION['sendto']) {
          echo '                    <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
        } else {
          echo '                    <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
        }
?>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      <td class="main" colspan="2"><b><?php echo tep_output_string_protected($addresses['firstname'] . ' ' . $addresses['lastname']); ?></b></td>
                      <td class="main" align="right"><?php echo tep_draw_radio_field('address', $addresses['address_book_id'], ($addresses['address_book_id'] == $_SESSION['sendto'])); ?></td>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                    </tr>
                    <tr>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      <td colspan="3"><table border="0" cellspacing="0" cellpadding="2">
                        <tr>
                          <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                          <td class="main"><?php echo tep_address_format($format_id, $addresses, true, ' ', ', '); ?></td>
                          <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                        </tr>
                      </table></td>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                    </tr>
                  </table></td>
                  <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
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
<?php
    }
}

if ($addresses_count < MAX_ADDRESS_BOOK_ENTRIES) {
?>
      <tr>
        <td><div style="margin:2px; margin-top:10px;"><b><?php echo TABLE_HEADING_NEW_SHIPPING_ADDRESS; ?></b></div></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <div style="margin:2px 10px;">
              <table border="0" width="100%" cellspacing="0" cellpadding="2">
                <tr>
                  <td class="main" width="100%" valign="top"><?php echo TEXT_CREATE_NEW_SHIPPING_ADDRESS; ?></td>
                </tr>
                <tr>
                  <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                    <tr>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      <td><?php require(DIR_WS_MODULES . 'checkout_new_address.php'); ?></td>
                      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                    </tr>
                  </table></td>
                </tr>
              </table>
            </div>
          </div>
        </td>
      </tr>
<?php
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
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td class="main"><?php echo '<b>' . TITLE_CONTINUE_CHECKOUT_PROCEDURE . '</b><br />' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></td>
                <td class="main" align="right"><?php echo tep_draw_hidden_field('action', 'submit') . tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'style="margin-right:10px;"'); ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
<?php
if ($process == true) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'style="margin-left:10px;"') . '</a>'; ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td width="25%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="50%" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'desk/checkout_bullet.gif'); ?></td>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
              </tr>
            </table></td>
            <td width="25%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
            <td width="25%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
            <td width="25%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '100%', '1'); ?></td>
                <td width="50%"><?php echo tep_draw_separator('pixel_silver.gif', '1', '5'); ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td align="center" width="25%" class="checkoutBarCurrent"><?php echo CHECKOUT_BAR_DELIVERY; ?></td>
            <td align="center" width="25%" class="checkoutBarTo"><?php echo CHECKOUT_BAR_PAYMENT; ?></td>
            <td align="center" width="25%" class="checkoutBarTo"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></td>
            <td align="center" width="25%" class="checkoutBarTo"><?php echo CHECKOUT_BAR_FINISHED; ?></td>
          </tr>
        </table></td>
      </tr>
    </table></form></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
