<?php
/*
  $Id: create_order.php,v 1.2i 2006/09/25 Ingo <www.strelitzer.de>
   v1.0: frankl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License

*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

// get payment modules
$ot_module_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_INSTALLED'");
$ot_module_raw = tep_db_fetch_array($ot_module_query);
$ot_module = explode( ";", $ot_module_raw['configuration_value']);
$payment_array = [];
for ($i=0; $i<sizeof($ot_module); $i++) {
    include (DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $ot_module[$i]);
    include(DIR_FS_CATALOG_MODULES . '/payment/' . $ot_module[$i]);
    $class = substr($ot_module[$i], 0, strrpos($ot_module[$i], '.'));
    if (tep_class_exists($class)) {
       $module = new $class;
       $payment_array[] = array('id' => $class, 'text' => $module->title);
    }
}

if (isset($_GET['action']) && $_GET['action']=='process') {

    $customers_id = intval(xprios_prepare_post('customers_id'));
    
    if (ACCOUNT_GENDER == 'true') {
        if (isset($_POST['gender'])) {
            $gender = xprios_prepare_post('gender');
        } else {
            $gender = false;
        }
    }
    $firstname      = xprios_prepare_post('firstname');
    $lastname       = xprios_prepare_post('lastname');
    $fullname = $firstname . ' ' . $lastname;
    $company        = xprios_prepare_post('company');
    $telephone      = xprios_prepare_post('telephone');
    $email_address  = xprios_prepare_post('email_address');
    $fax            = xprios_prepare_post('fax');
    $street_address = xprios_prepare_post('street_address');
    $company        = xprios_prepare_post('company');
    $suburb         = xprios_prepare_post('suburb');
    $postcode       = xprios_prepare_post('postcode');
    $city           = xprios_prepare_post('city');
    $zone_id        = xprios_prepare_post('zone_id');
    $state          = xprios_prepare_post('state');
    $country_id     = xprios_prepare_post('country_id');
    $country        = tep_get_country_name($country_id);
    
    $create_order_shipping_amount = floatval(str_replace(',', '.', xprios_prepare_post('shipping_amount')));
    $create_order_shipping_tax_rate = number_format(floatval(str_replace(',', '.', xprios_prepare_post('shipping_tax'))), 2, '.', '');

    $format_query = tep_db_query("select address_format_id from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$country_id . "'");
    $format = tep_db_fetch_array($format_query);
    $format_id = $format['address_format_id'];

    $payment_class = xprios_prepare_post('payment');
    $payment_method = '';
    for ($i=0; $i<count($payment_array); $i++) {
        if ($payment_array[$i]['id']==$payment_class) {
            $payment_method = $payment_array[$i]['text'];
        }
    }

    if (isset($_POST['register_new']) && $_POST['register_new']==1) {

        $check_email_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
        if (tep_db_num_rows($check_email_query)) {
            $check_email = tep_db_fetch_array($check_email_query);
            $messageStack->add_session($email_address . '" already exists!', 'error');
            tep_redirect(tep_href_link(FILENAME_CREATE_ORDER, 'cID=' . $check_email['customers_id'], 'SSL'));
        }

        $password = '';
        while (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
            $char = chr(tep_rand(0,255));
            if (preg_match('/^[a-z0-9]$/', $char)) {
                $password .= $char;
            }
        }
        $crypt_password = '';
        for ($i=0; $i<10; $i++) $crypt_password .= tep_rand();
        $salt = substr(md5($crypt_password), 0, 2);
        $crypt_password = md5($salt . $password) . ':' . $salt;

        $sql_data_array = [
            'customers_firstname' => $firstname,
            'customers_lastname' => $lastname,
            'customers_email_address' => $email_address,
            'customers_telephone' => $telephone,
            'customers_fax' => $fax,
            'customers_newsletter' => '',
            'customers_password' => $crypt_password
        ];
        if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
        tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
        $customers_id = tep_db_insert_id();

        $sql_data_array = [
            'customers_id' => $customers_id,
            'entry_firstname' => $firstname,
            'entry_lastname' => $lastname,
            'entry_street_address' => $street_address,
            'entry_postcode' => $postcode,
            'entry_city' => $city,
            'entry_country_id' => $country_id
        ];
        if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
        tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
        $address_id = tep_db_insert_id();

        tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customers_id . "'");
        tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customers_id . "', '0', now())");

        if (ACCOUNT_GENDER == 'true') {
            if ($gender == 'm') {
                $email_text = sprintf(EMAIL_GREET_MR, $lastname);
            } else {
                $email_text = sprintf(EMAIL_GREET_MS, $lastname);
            }
        } else {
            $email_text = sprintf(EMAIL_GREET_NONE, $firstname);
        }
        $email_text .= EMAIL_WELCOME . EMAIL_TEXT . EMAIL_CONTACT . sprintf(EMAIL_WARNING, $password);
        tep_mail($fullname, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

        if (isset($_POST['register_only']) && $_POST['register_only']=='1') {
            tep_redirect(tep_href_link(FILENAME_CUSTOMERS, 'search=' . $lastname, 'SSL'));
        }
    }

    if ($suburb == '') {
        $suburb = 'null';
    }
    $sql_data_array = [
        'customers_id'                => $customers_id,
        'customers_name'              => $fullname,
        'customers_company'           => $company,
        'customers_street_address'    => $street_address,
        'customers_suburb'            => $suburb,
        'customers_city'              => $city,
        'customers_postcode'          => $postcode,
        'customers_state'             => $state,
        'customers_country'           => $country,
        'customers_telephone'         => $telephone,
        'customers_email_address'     => $email_address,
        'customers_address_format_id' => $format_id,
        'delivery_name'               => $fullname,
        'delivery_company'            => $company,
        'delivery_street_address'     => $street_address,
        'delivery_suburb'             => $suburb,
        'delivery_city'               => $city,
        'delivery_postcode'           => $postcode,
        'delivery_state'              => $state,
        'delivery_country'            => $country,
        'delivery_address_format_id'  => $format_id,
        'billing_name'                => $fullname,
        'billing_company'             => $company,
        'billing_street_address'      => $street_address,
        'billing_suburb'              => $suburb,
        'billing_city'                => $city,
        'billing_postcode'            => $postcode,
        'billing_state'               => $state,
        'billing_country'             => $country,
        'billing_address_format_id'   => $format_id,
        'payment_method'              => $payment_method,
        'shipping_method'             => '',
        'shipping_class'              => '',
        'orders_status'               => DEFAULT_ORDERS_STATUS_ID,
        'currency'                    => DEFAULT_CURRENCY,
        'currency_value'              => '1'
    ];
// CAO- Felder
    $iso_code_query = tep_db_query("select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$country_id . "'");
    $result = tep_db_fetch_array($iso_code_query);
    $sql_data_array = array_merge(
        $sql_data_array, 
        [
            'delivery_firstname' => $firstname,
            'delivery_lastname' => $lastname,
            'billing_firstname' => $firstname,
            'billing_lastname' => $lastname,
            'delivery_country_iso_code_2' => $result['countries_iso_code_2'],
            'billing_country_iso_code_2' => $result['countries_iso_code_2'],
            'payment_class' => $payment_class
        ]
    );

    tep_db_perform(TABLE_ORDERS, $sql_data_array);
    $insert_id = tep_db_insert_id();
    $_SESSION['create_order'] = $insert_id;

    $ot_module_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_INSTALLED'");
    $ot_module_raw = tep_db_fetch_array($ot_module_query);
    $ot_module = explode( ";", $ot_module_raw['configuration_value']);
    for ($i=0; $i<sizeof($ot_module); $i++) {
        include (DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/order_total/' . $ot_module[$i]);
        include(DIR_FS_CATALOG_MODULES . '/order_total/' . $ot_module[$i]);
        $class = substr($ot_module[$i], 0, strrpos($ot_module[$i], '.'));
        if (tep_class_exists($class)) {
            $module = new $class;
            $this_value = 0;
            if ($class == 'ot_shipping') {
                $this_value = $create_order_shipping_amount;
            } elseif ($class == 'ot_total') {
                if (DISPLAY_PRICE_WITH_TAX == 'ja') {
                    $this_value = $create_order_shipping_amount;
                } else {
                    $this_value = $create_order_shipping_amount * (100 + $create_order_shipping_tax_rate) / 100;
                }
            } elseif ($class == 'ot_tax') {
                if (DISPLAY_PRICE_WITH_TAX == 'ja') {
                    $this_value = $create_order_shipping_amount / (100 + $create_order_shipping_tax_rate) * $create_order_shipping_tax_rate;
                } else {
                    $this_value = $create_order_shipping_amount / 100 * $create_order_shipping_tax_rate;
                }
                $module->title .= $create_order_shipping_tax_rate . '% ';
            }
            $this_text = $currencies->display_price($this_value, 0, 1);
            if ($module->code == 'ot_total') {
                $this_text = '<b>' . $this_text . '</b>';
            }
            $sql_data_array = [
                'orders_id'  => $insert_id,
                'title'      => $module->title,
                'text'       => $this_text,
                'value'      => $this_value,
                'class'      => $module->code,
                'sort_order' => $module->sort_order
            ];
            tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
        }
    }

    $sql_data_array = [
        'orders_id'        => $insert_id,
        'orders_status_id' => DEFAULT_ORDERS_STATUS_ID,
        'date_added'       => 'now()'
    ];
    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

    tep_redirect(tep_href_link(FILENAME_EDIT_ORDERS, 'oID=' . $insert_id, 'SSL'));
}

// get taxrates
$tax_rate_query = tep_db_query('select distinct tax_rate from ' . TABLE_TAX_RATES . ' order by tax_rate desc');
$tax_array = [];
while ($tax_rate_query_array = tep_db_fetch_array($tax_rate_query)) {
    $this_rate = number_format($tax_rate_query_array['tax_rate'],2);
    $tax_array[] = [
        'id'   => $this_rate,
        'text' => $this_rate
    ];
}
$tax_array[] = ['id' => '0', 'text' => '0'];

// get customers
$customers_count_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS);
if (tep_db_num_rows($customers_count_query)>100) {
    $char_array = [['id' => 'A', 'text' => '??']];
    $char_query = tep_db_query("select distinct (left(customers_lastname,1)) as lchar from " . TABLE_CUSTOMERS . " order by left(customers_lastname,1)");
    while ($char = tep_db_fetch_array($char_query)) {
        $char_array[] = [
            'id' => $char['lchar'], 
            'text' => $char['lchar']
        ];
    }
}

$customers_limit = isset($_GET['char']) ? " where left(customers_lastname,1) = '" . $_GET['char'] . "'" : '';
$customers_array = [['id' => '', 'text' => TEXT_PLEASE_SELECT]];
$customers_query = tep_db_query("select customers_id, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . $customers_limit . " ORDER BY customers_lastname DESC");
while ($customers=tep_db_fetch_array($customers_query)) {
    $customers_array[] = array('id' => $customers['customers_id'], 'text' => $customers['customers_lastname'] . ', ' . $customers['customers_firstname']);
}

$account = $address = [];
$is_read_only = false;
if (isset($_GET['cID'])) {
    $cID = max(0, intval(xprios_prepare_get('cID')));
    $query = tep_db_query('select * from ' . TABLE_CUSTOMERS . ' where customers_id = ' . $cID);
    if ($result = tep_db_fetch_array($query)) {
        $is_read_only = true;
        $account = $result;
        $customers_default_address_id = intval($account['customers_default_address_id']);
        $query = tep_db_query('select * from ' . TABLE_ADDRESS_BOOK . ' where customers_id = ' . $cID . ' and address_book_id = ' . $customers_default_address_id);
        if ($result = tep_db_fetch_array($query)) {
            $address = $result;
        }
    }
}
if (sizeof($address) == 0) {
    $query = tep_db_query('show fields from ' . TABLE_ADDRESS_BOOK);
    while ($result = tep_db_fetch_array($query)) {
        $address[strtolower($result['Field'])] = isset($result['Default']) ? $result['Default'] : '';
    }
}
if (sizeof($account) == 0) {
    $query = tep_db_query('show fields from ' . TABLE_CUSTOMERS);
    while ($result = tep_db_fetch_array($query)) {
        $account[strtolower($result['Field'])] = isset($result['Default']) ? $result['Default'] : '';
    }
}
  
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title>Step-by-Step Manual Order Entry - Step 1</title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body>
<?php 
  require(DIR_WS_INCLUDES . 'header.php'); 
?>

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<?php 
  require(DIR_WS_INCLUDES . 'column_left.php'); 
?>
<!-- body_text //-->
  <td valign="top">
  <table border="0" bgcolor="#7c6bce" width="100%">
    <tr>
     <td class=main><font color="#ffffff"><b><?php echo TEXT_STEP_1; ?></b></td>
    </tr>
  </table>
  <table border="0" cellpadding="7">
    <tr>
      <td class="main" valign="top">
        <b><?php echo TEXT_SELECT_CUSTOMER; ?></b>
        &nbsp;<?php
         echo tep_draw_form('customer', FILENAME_CREATE_ORDER, '', 'get') . "\n       " .
              tep_draw_pull_down_menu('cID', $customers_array, (isset($_GET['cID']) ? $_GET['cID'] : ''), 'onchange="this.form.submit();" size="1"') . " &nbsp; \n";
         if (isset($char_array) && is_array($char_array)) echo tep_draw_pull_down_menu('char', $char_array, (isset($_GET['char']) ? $_GET['char'] : ''), 'onchange="this.form.submit();" size="1"');
?>
       </form>
      </td>
    </tr>
    <tr>
      <td width="100%" valign="top">
        <?php echo tep_draw_form('create_order', FILENAME_CREATE_ORDER, 'action=process', 'post'); ?>
        <table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
           <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
             <td class="pageHeading"><?php echo HEADING_CREATE; ?></td>
            </tr>
           </table></td>
          </tr>
          <tr>
           <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
           <td>
<?php

function sbs_get_zone_name($country_id, $zone_id)
{
    $zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . $country_id . "' and zone_id = '" . $zone_id . "'");
    if (tep_db_num_rows($zone_query)) {
        $zone = tep_db_fetch_array($zone_query);
        return $zone['zone_name'];
    } else {
        return '';
    }
}
/*
 // Returns an array with countries
// TABLES: countries
  function sbs_get_countries($countries_id = '', $with_iso_codes = false) {
    $countries_array = [];
    if ($countries_id) {
      if ($with_iso_codes) {
        $countries = tep_db_query("select countries_name, countries_iso_code_2, countries_iso_code_3 from " . TABLE_COUNTRIES . " where countries_id = '" . $countries_id . "' order by countries_name");
        $countries_values = tep_db_fetch_array($countries);
        $countries_array = array('countries_name' => $countries_values['countries_name'],
                                 'countries_iso_code_2' => $countries_values['countries_iso_code_2'],
                                 'countries_iso_code_3' => $countries_values['countries_iso_code_3']);
      } else {
        $countries = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . $countries_id . "'");
        $countries_values = tep_db_fetch_array($countries);
        $countries_array = array('countries_name' => $countries_values['countries_name']);
      }
    } else {
      $countries = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " order by countries_name");
      while ($countries_values = tep_db_fetch_array($countries)) {
        $countries_array[] = array('countries_id' => $countries_values['countries_id'],
                                   'countries_name' => $countries_values['countries_name']);
      }
    }
    return $countries_array;
  }
  ////
function sbs_get_country_list($name, $selected = '', $parameters = '') {
   $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
   $countries = sbs_get_countries();
   $size = sizeof($countries);
   for ($i=0; $i<$size; $i++) {
     $countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
   }
   return tep_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
}
*/
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td class="formAreaTitle"><?php echo CATEGORY_PERSONAL; ?></td>
  </tr>
  <tr>
    <td class="main"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="formArea">
      <tr>
        <td class="main"><table border="0" cellspacing="0" cellpadding="2">
          <tr>
<?php
  if (isset($account['customers_id']) && $account['customers_id']>0) {
?>
            <td class="main">&nbsp;<?php echo ENTRY_CUSTOMERS_ID; ?></td>
            <td class="main">&nbsp;<b><?php echo $account['customers_id'] . tep_draw_hidden_field('customers_id', $account['customers_id']); ?></b></td>
<?php
  } else {
    $account['customers_id'] = '0';
?>
            <td class="main" colspan="2"><table cellspacing="0" cellpadding="2" border="0">
              <tr>
                <td class="main"><?php echo ENTRY_REGISTER_NEW; ?></td><td class="main"><?php echo tep_draw_checkbox_field('register_new', '1', false); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_REGISTER_ONLY; ?></td><td class="main"><?php echo tep_draw_checkbox_field('register_only', '1', false); ?></td>
              </tr>
            </table></td>
<?php
  }
?>
          </tr>
<?php
  if (ACCOUNT_GENDER == 'true') {
?>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_GENDER; ?></td>
            <td class="main">&nbsp;<?php echo tep_draw_radio_field('gender', 'm', ($account['customers_gender']=='m')) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f', ($account['customers_gender']=='f')) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (tep_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></td>
          </tr>
<?php
  }
?>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_FIRST_NAME; ?></td>
            <td class="main">&nbsp;<?php
  if ($is_read_only) {
    echo $account['customers_firstname'] . tep_draw_hidden_field('firstname', $account['customers_firstname']);
  } else {
    echo tep_draw_input_field('firstname', $account['customers_firstname']) . '&nbsp;' . ENTRY_FIRST_NAME_TEXT;
  } ?></td>
          </tr>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_LAST_NAME; ?></td>
            <td class="main">&nbsp;<?php
  if ($is_read_only) {
    echo $account['customers_lastname'] . tep_draw_hidden_field('lastname', $account['customers_lastname']);
  } else {
    echo tep_draw_input_field('lastname', $account['customers_lastname']) . '&nbsp;' . ENTRY_LAST_NAME_TEXT;
  } ?></td>
          </tr>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_EMAIL_ADDRESS; ?></td>
            <td class="main">&nbsp;<?php
  if ($is_read_only) {
    echo $account['customers_email_address'] . tep_draw_hidden_field('email_address', $account['customers_email_address']);
  } else {
    echo tep_draw_input_field('email_address', $account['customers_email_address']) . '&nbsp;' . ENTRY_EMAIL_ADDRESS_TEXT . '&nbsp;' . TEXT_FIELD_REQUIRED;
  }
  ?></td>
          </tr>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
            <td class="main">&nbsp;<?php
  if ($is_read_only) {
    echo $account['customers_telephone'] . tep_draw_hidden_field('telephone', $account['customers_telephone']);
  } else {
    echo tep_draw_input_field('telephone', $account['customers_telephone']) . '&nbsp;';
  } ?></td>
          </tr>

        </table></td>
      </tr>
    </table></td>
  </tr>
<?php
  if (ACCOUNT_COMPANY == 'true' || (isset($address['entry_company']) && $address['entry_company']!='')) {
?>
  <tr>
    <td class="formAreaTitle"><br><?php echo CATEGORY_COMPANY; ?></td>
  </tr>
  <tr>
    <td class="main"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="formArea">
      <tr>
        <td class="main"><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_COMPANY; ?></td>
            <td class="main">&nbsp;<?php
    if ($is_read_only) {
      echo $address['entry_company'] . tep_draw_hidden_field('company', $address['entry_company']);
    } else {
      echo tep_draw_input_field('company', $address['entry_company']) . '&nbsp;' . ENTRY_COMPANY_TEXT;
    } ?></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
<?php
  }
?>
  <tr>
    <td class="formAreaTitle"><br><?php echo CATEGORY_ADDRESS; ?></td>
  </tr>
  <tr>
    <td class="main"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="formArea">
      <tr>
        <td class="main"><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_STREET_ADDRESS; ?></td>
            <td class="main">&nbsp;<?php
  if ($is_read_only) {
    echo $address['entry_street_address'] . tep_draw_hidden_field('street_address', $address['entry_street_address']);
  } else {
    echo tep_draw_input_field('street_address', $address['entry_street_address']) . '&nbsp;' . ENTRY_STREET_ADDRESS_TEXT;
  } ?></td>
          </tr>
<?php
  if (ACCOUNT_SUBURB == 'true' || (isset($address['entry_suburb']) && $address['entry_suburb']!='')) {
?>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_SUBURB; ?></td>
            <td class="main">&nbsp;<?php
    if ($is_read_only) {
      echo $address['entry_suburb'] . tep_draw_hidden_field('suburb', $address['entry_suburb']);
    } else {
      echo tep_draw_input_field('suburb', $address['entry_suburb']) . '&nbsp;' . ENTRY_SUBURB_TEXT;
    } ?></td>
          </tr>
<?php
  }
?>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_POST_CODE; ?></td>
            <td class="main">&nbsp;<?php
  if ($is_read_only) {
    echo $address['entry_postcode'] . tep_draw_hidden_field('postcode', $address['entry_postcode']);
  } else {
    echo tep_draw_input_field('postcode', $address['entry_postcode']) . '&nbsp;' . ENTRY_POST_CODE_TEXT;
  } ?></td>
          </tr>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_CITY; ?></td>
            <td class="main">&nbsp;<?php
  if ($is_read_only) {
    echo $address['entry_city'] . tep_draw_hidden_field('city', $address['entry_city']);
  } else {
    echo tep_draw_input_field('city', $address['entry_city']) . '&nbsp;' . ENTRY_CITY_TEXT;
  } ?></td>
          </tr>
<?php
  if (ACCOUNT_STATE == 'true' || (isset($address['entry_zone_id']) && $address['entry_zone_id']!='')) {
?>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_STATE; ?></td>
            <td class="main">&nbsp;<?php
    //$state = sbs_get_zone_name($address['entry_country_id'], $address['entry_zone_id'], $address['entry_state']);
    if ($is_read_only) {
      echo sbs_get_zone_name($address['entry_country_id'], $address['entry_zone_id'], $address['entry_state']) . tep_draw_hidden_field('state', sbs_get_zone_name($address['entry_country_id'], $address['entry_zone_id'], $address['entry_state']));
    } else {
      echo tep_draw_input_field('state', sbs_get_zone_name($address['entry_country_id'], $address['entry_zone_id'], $address['entry_state'])) . '&nbsp;' . ENTRY_STATE_TEXT;
    } ?></td>
          </tr>
<?php
  }
?>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_COUNTRY; ?></td>
            <td class="main">&nbsp;<?php
    if ($is_read_only) {
      echo tep_get_country_name($address['entry_country_id']) . tep_draw_hidden_field('country_id', $address['entry_country_id']);
    } else {
      echo tep_draw_pull_down_menu('country_id', tep_get_countries(), (isset($address['entry_country_id'])? $address['entry_country_id']:STORE_COUNTRY)) . '&nbsp;' . ENTRY_COUNTRY_TEXT;
    }
    echo tep_draw_hidden_field('step', '3'); ?></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td class="formAreaTitle"><br><?php echo CATEGORY_SHIPPING_AND_PAYMENT; ?></td>
  </tr>
  <tr>
    <td class="main"><table border="0" width="100%" cellspacing="0" cellpadding="2" class="formArea">
      <tr>
        <td class="main"><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_SHIPPING; ?></td>
            <td class="main">&nbsp;<?php
             echo tep_draw_input_field('shipping_amount', CREATE_ORDERS_SHIPPING_AMOUNT) . '&nbsp;' .
                  (DISPLAY_PRICE_WITH_TAX == 'ja'? 'incl.':'zzgl.') . '&nbsp;' .
                  tep_draw_pull_down_menu('shipping_tax', $tax_array, number_format(str_replace(",", ".", CREATE_ORDERS_SHIPPING_TAX_RATE),2)); ?>%</td>
          </tr>
          <tr>
            <td class="main">&nbsp;<?php echo ENTRY_PAYMENT; ?></td>
            <td class="main">&nbsp;<?php echo tep_draw_pull_down_menu('payment', $payment_array); ?></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
</table>
           </td>
          </tr>
          <tr>
           <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
           <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'SSL') . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
            <td class="main" align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_CONFIRM); ?></td>
          </tr>
        </table></td>
      </tr>
    </table></form></td>
<!-- body_text_eof //-->

  </tr>
</table>
<!-- body_eof //-->

<?php  
require(DIR_WS_INCLUDES . 'footer.php'); 
?>
</body>
</html>
<?php
require(DIR_WS_INCLUDES . 'application_bottom.php');
