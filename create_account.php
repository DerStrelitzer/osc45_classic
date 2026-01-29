<?php
/*
  $Id: create_account.php,v 1.65 2003/06/09 23:03:54 hpdl Exp $

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

// Ingo PWA
if (isset($_GET['guest']) && $cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
}

$process = false;
if (isset($_POST['action']) && $_POST['action'] == 'process') {
    $process = true;

    if (ACCOUNT_GENDER == 'true') {
        if (isset($_POST['gender'])) {
            $gender = xprios_prepare_post('gender');
        } else {
            $gender = false;
        }
    }
    $firstname = ucfirst(xprios_prepare_post('firstname'));
    $lastname = ucfirst(xprios_prepare_post('lastname'));
    if (ACCOUNT_DOB == 'true') $dob = xprios_prepare_post('dob');
    $email_address = xprios_prepare_post('email_address');
    if (ACCOUNT_COMPANY == 'true') $company = xprios_prepare_post('company');
    $street_address = ucfirst(xprios_prepare_post('street_address'));
    $suburb = xprios_prepare_post('suburb');
    $postcode = xprios_prepare_post('postcode');
    $city = ucfirst(xprios_prepare_post('city'));
    if (ACCOUNT_STATE == 'true') {
        $state = xprios_prepare_post('state');
        if (isset($_POST['zone_id'])) {
            $zone_id = xprios_prepare_post('zone_id');
        } else {
            $zone_id = false;
        }
    }
    $country = xprios_prepare_post('country');
    $telephone = xprios_prepare_post('telephone');
    $fax = xprios_prepare_post('fax');
    if (isset($_POST['newsletter'])) {
        $newsletter = xprios_prepare_post('newsletter');
    } else {
        $newsletter = false;
    }
    $password = xprios_prepare_post('password');
    $confirmation = xprios_prepare_post('confirmation');

    $error = false;

    if (ACCOUNT_GENDER == 'true') {
        if ( ($gender != 'm') && ($gender != 'f') ) {
            $error = true;
            $messageStack->add('create_account', ENTRY_GENDER_ERROR);
        }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_LAST_NAME_ERROR);
    }

    if (ACCOUNT_DOB == 'true') {
        if (checkdate(substr(tep_date_raw($dob), 4, 2), substr(tep_date_raw($dob), 6, 2), substr(tep_date_raw($dob), 0, 4)) == false) {
            $error = true;
            $messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
        }
    }

    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR);
      
    } elseif (tep_validate_email($email_address) == false) {
        $error = true;
        $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
      
    } else {
        $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
        $check_email = tep_db_fetch_array($check_email_query);
        if ($check_email['total'] > 0) {
            $error = true;
            $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
        }
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_POST_CODE_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_CITY_ERROR);
    }

    if (is_numeric($country) == false) {
        $error = true;
        $messageStack->add('create_account', ENTRY_COUNTRY_ERROR);
    }

    $zone_id = 0;
    if (ACCOUNT_STATE == 'true') {
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
                $messageStack->add('create_account', ENTRY_STATE_ERROR_SELECT);
            }
        
        } else {
            if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
                $error = true;
                $messageStack->add('create_account', ENTRY_STATE_ERROR);
            }
        }
    }


    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
        $error = true;
        $messageStack->add('create_account', ENTRY_TELEPHONE_NUMBER_ERROR);
    }

// Ingo PWA
    if (!isset($_GET['guest'])) {

        if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
            $error = true;
            $messageStack->add('create_account', ENTRY_PASSWORD_ERROR);
        
        } elseif ($password != $confirmation) {
            $error = true;
            $messageStack->add('create_account', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
        }
    }
// Ingo PWA
    
    if ($error == false) {
        $sql_data_array = [
            'customers_firstname' => $firstname,
            'customers_lastname' => $lastname,
            'customers_email_address' => $email_address,
            'customers_telephone' => $telephone,
            'customers_fax' => $fax,
            'customers_newsletter' => $newsletter,
            'customers_password' => tep_encrypt_password($password)
        ];

        if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
        if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);

// Ingo PWA Beginn
        if (isset($_GET['guest'])) {
            $_SESSION['pwa_array_customer'] = $sql_data_array;
            $_SESSION['customer_id'] = 0;
        } else {
            tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
            $_SESSION['customer_id'] = tep_db_insert_id();
        }
// Ingo PWA Ende

        $sql_data_array = [
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
        ];

        if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
        if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
        if (ACCOUNT_SUBURB == 'true' && $suburb != '') {
            $sql_data_array['entry_suburb'] = $suburb;
        }
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
        if (isset($_GET['guest'])) {
            $_SESSION['pwa_array_address'] = $sql_data_array;
            $address_id = 0;
        } else {

            tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);
            $address_id = tep_db_insert_id();
            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$_SESSION['customer_id'] . "'");
            tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$_SESSION['customer_id'] . "', '0', now())");
        }
// Ingo PWA Ende

        if (SESSION_RECREATE == 'True') {
            tep_session_recreate();
        }

        $_SESSION['customer_first_name']         = $firstname;
        $_SESSION['customer_last_name']          = $lastname;
        $_SESSION['customer_default_address_id'] = $address_id;
        $_SESSION['customer_country_id']         = $country;
        $_SESSION['customer_zone_id']            = $zone_id;     
        $_SESSION['customer_email_address']      = $email_address;
        if (ACCOUNT_GENDER == 'true') {
            $_SESSION['customer_gender'] = $gender=='f' ? FEMALE:MALE;
        }

// Ingo PWA
        if (isset($_GET['guest'])) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING));
        }

// restore cart contents
        $_SESSION['cart']->restore_contents();

// build the message content
        $name = $firstname . ' ' . $lastname;

        if (ACCOUNT_GENDER == 'true') {
            if ($gender == 'm') {
                $email_text = sprintf(EMAIL_GREET_MR, $lastname);
            } else {
                $email_text = sprintf(EMAIL_GREET_MS, $lastname);
            }
        } else {
            $email_text = sprintf(EMAIL_GREET_NONE, $firstname);
        }

        $email_text .= EMAIL_WELCOME . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
        tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

        tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
    }
}

$dob = '';
if (isset($_SESSION['pwa_array_customer']) && isset($_SESSION['pwa_array_address'])) {
    $gender    = isset($_SESSION['pwa_array_customer']['customers_gender']) ? $_SESSION['pwa_array_customer']['customers_gender']:'';
    $company   = isset($_SESSION['pwa_array_address']['entry_company']) ? $_SESSION['pwa_array_address']['entry_company']:'';
    $firstname = isset($_SESSION['pwa_array_customer']['customers_firstname']) ? $_SESSION['pwa_array_customer']['customers_firstname']:'';
    $lastname  = isset($_SESSION['pwa_array_customer']['customers_lastname']) ? $_SESSION['pwa_array_customer']['customers_lastname']:'';
    if (isset($_SESSION['pwa_array_customer']['customers_dob'])) {
      $dob = date(DATE_FORMAT, $_SESSION['pwa_array_customer']['customers_dob']);
    }
    $email_address = isset($_SESSION['pwa_array_customer']['customers_email_address']) ? $_SESSION['pwa_array_customer']['customers_email_address']:'';
    $street_address = isset($_SESSION['pwa_array_address']['entry_street_address']) ? $_SESSION['pwa_array_address']['entry_street_address']:'';
    $suburb = isset($_SESSION['pwa_array_address']['entry_suburb']) ? $_SESSION['pwa_array_address']['entry_suburb']:'';
    $postcode = isset($_SESSION['pwa_array_address']['entry_postcode']) ? $_SESSION['pwa_array_address']['entry_postcode']:'';
    $city = isset($_SESSION['pwa_array_address']['entry_city']) ? $_SESSION['pwa_array_address']['entry_city']:'';
    $state = isset($_SESSION['pwa_array_address']['entry_state']) ? $_SESSION['pwa_array_address']['entry_state']:'0';
    $country = isset($_SESSION['pwa_array_address']['entry_country_id']) ? $_SESSION['pwa_array_address']['entry_country_id']:'';
    $telephone = isset($_SESSION['pwa_array_customer']['customers_telephone']) ? $_SESSION['pwa_array_customer']['customers_telephone']:'';
    $fax = isset($_SESSION['pwa_array_customer']['customers_fax']) ? $_SESSION['pwa_array_customer']['customers_fax']:'';
}

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));

$this_head_file = 'includes/form_check.js.php';
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('create_account', tep_href_link(FILENAME_CREATE_ACCOUNT, (isset($_GET['guest'])? 'guest=guest':''), 'SSL'), 'post', 'onsubmit="return check_form(create_account);"') . tep_draw_hidden_field('action', 'process');
$heading_image = 'table_background_account.gif';
define('HEADING_TITLE', isset($_GET['guest'])? '':HEADING_TITLE_RAW);
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="smallText"><br /><?php echo sprintf(TEXT_ORIGIN_LOGIN, tep_href_link(FILENAME_LOGIN, tep_get_all_get_params(), 'SSL')); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
if ($messageStack->size('create_account') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('create_account'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo CATEGORY_PERSONAL; ?></b></td>
           <td class="inputRequirement" align="right"><?php echo FORM_REQUIRED_INFORMATION; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td> 
          <div class="divbox">
            <table border="0" cellspacing="2" cellpadding="2">
<?php
if (ACCOUNT_GENDER == 'true') {
?>
              <tr>
                <td class="main"><?php echo ENTRY_GENDER; ?></td>
                <td class="main"><?php echo tep_draw_radio_field('gender', 'f', (!isset($gender)||(isset($gender)&&$gender=='f'))) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'm',(isset($gender)&&$gender=='f')) . '&nbsp;&nbsp;' . MALE . '&nbsp;' . (tep_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></td>
              </tr>
<?php
}
?>
              <tr>
                <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
                <td class="main"><?php echo tep_draw_input_field('firstname') . '&nbsp;' . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
                <td class="main"><?php echo tep_draw_input_field('lastname') . '&nbsp;' . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?></td>
              </tr>
<?php
if (ACCOUNT_DOB == 'true') {
?>
              <tr>
                <td class="main"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
                <td class="main"><?php echo tep_draw_input_field('dob', $dob) . '&nbsp;' . (tep_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>': ''); ?></td>
              </tr>
<?php
}
?>
              <tr>
                <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
                <td class="main"><?php echo tep_draw_input_field('email_address') . '&nbsp;' . (tep_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?></td>
              </tr>
            </table> 
          </div>
        </td>
      </tr>
<?php
if (ACCOUNT_COMPANY == 'true') {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><b><?php echo CATEGORY_COMPANY; ?></b></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td class="main"><?php echo ENTRY_COMPANY; ?></td>
                <td class="main"><?php echo tep_draw_input_field('company') . '&nbsp;' . (tep_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?></td>
              </tr>
            </table><
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
        <td class="main"><b><?php echo CATEGORY_ADDRESS; ?></b></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td class="main"><?php echo ENTRY_STREET_ADDRESS; ?></td>
                <td class="main"><?php echo tep_draw_input_field('street_address') . '&nbsp;' . (tep_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>': ''); ?></td>
              </tr>
<?php
if (ACCOUNT_SUBURB == 'true') {
?>
              <tr>
                <td class="main"><?php echo ENTRY_SUBURB; ?></td>
                <td class="main"><?php echo tep_draw_input_field('suburb') . '&nbsp;' . (tep_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">' . ENTRY_SUBURB_TEXT . '</span>': ''); ?></td>
              </tr>
<?php
}
?>
              <tr>
                <td class="main"><?php echo ENTRY_POST_CODE; ?></td>
                <td class="main"><?php echo tep_draw_input_field('postcode') . '&nbsp;' . (tep_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>': ''); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_CITY; ?></td>
                <td class="main"><?php echo tep_draw_input_field('city') . '&nbsp;' . (tep_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>': ''); ?></td>
              </tr>
<?php
if (ACCOUNT_STATE == 'true') {
?>
              <tr>
                <td class="main"><?php echo ENTRY_STATE; ?></td>
                <td class="main">
<?php
    if ($process == true) {
        if ($entry_state_has_zones == true) {
            $zones_array = [];
            $zones_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country . "' order by zone_name");
            while ($zones_values = tep_db_fetch_array($zones_query)) {
                $zones_array[] = [
                    'id' => $zones_values['zone_name'], 
                    'text' => $zones_values['zone_name']
                ];
            }
            echo tep_draw_pull_down_menu('state', $zones_array);
        } else {
            echo tep_draw_input_field('state');
        }
    } else {
        echo tep_draw_input_field('state');
    }

    if (tep_not_null(ENTRY_STATE_TEXT)) echo '&nbsp;<span class="inputRequirement">' . ENTRY_STATE_TEXT;
?>
                </td>
              </tr>
<?php
}
?>
              <tr>
                <td class="main"><?php echo ENTRY_COUNTRY; ?></td>
                <td class="main"><?php echo tep_get_country_list('country', STORE_COUNTRY) . '&nbsp;' . (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>': ''); ?></td>
              </tr>
            </table> 
          </div>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><b><?php echo CATEGORY_CONTACT; ?></b></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
                <td class="main"><?php echo tep_draw_input_field('telephone') . '&nbsp;' . (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>': ''); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
                <td class="main"><?php echo tep_draw_input_field('fax') . '&nbsp;' . (tep_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_FAX_NUMBER_TEXT . '</span>': ''); ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
<?php
// Ingo PWA
if (!isset($_GET['guest'])) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><b><?php echo CATEGORY_OPTIONS; ?></b></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td class="main"><?php echo ENTRY_NEWSLETTER; ?></td>
                <td class="main"><?php echo tep_draw_checkbox_field('newsletter', '1') . '&nbsp;' . (tep_not_null(ENTRY_NEWSLETTER_TEXT) ? '<span class="inputRequirement">' . ENTRY_NEWSLETTER_TEXT . '</span>': ''); ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><b><?php echo CATEGORY_PASSWORD; ?></b></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" cellspacing="2" cellpadding="2">
              <tr>
                <td class="main"><?php echo ENTRY_PASSWORD; ?></td>
                <td class="main"><?php echo tep_draw_password_field('password') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_TEXT . '</span>': ''); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></td>
                <td class="main"><?php echo tep_draw_password_field('confirmation') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
<?php
// Ingo PWA
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
                <td><?php echo '<a href="' . tep_href_link(FILENAME_LOGIN, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'style="margin-left:10px;"') . '</a>'; ?></td>
                <td align="right"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'style="margin-right:10px;"'); ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
    </table></form></td>
<?php
include(DIR_WS_INCLUDES . 'column_right.php');
include(DIR_WS_INCLUDES . 'footer.php');
