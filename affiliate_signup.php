<?php
/*
  $Id: affiliate_signup.php,v 1.12 2003/07/12 11:47:59 simarilius Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

if (isset($_POST['action'])) {
    $a_gender = xprios_prepare_post('a_gender');
    $a_firstname = xprios_prepare_post('a_firstname');
    $a_lastname = xprios_prepare_post('a_lastname');
    $a_dob = xprios_prepare_post('a_dob');
    $a_email_address = xprios_prepare_post('a_email_address');
    $a_company = xprios_prepare_post('a_company');
    $a_company_taxid = xprios_prepare_post('a_company_taxid');
    $a_payment_check = xprios_prepare_post('a_payment_check');
    $a_payment_paypal = xprios_prepare_post('a_payment_paypal');
    $a_payment_bank_name = xprios_prepare_post('a_payment_bank_name');
    $a_payment_bank_branch_number = xprios_prepare_post('a_payment_bank_branch_number');
    $a_payment_bank_swift_code = xprios_prepare_post('a_payment_bank_swift_code');
    $a_payment_bank_account_name = xprios_prepare_post('a_payment_bank_account_name');
    $a_payment_bank_account_number = xprios_prepare_post('a_payment_bank_account_number');
    $a_street_address = xprios_prepare_post('a_street_address');
    $a_suburb = xprios_prepare_post('a_suburb');
    $a_postcode = xprios_prepare_post('a_postcode');
    $a_city = xprios_prepare_post('a_city');
    $a_country = xprios_prepare_post('a_country');
    $a_zone_id = xprios_prepare_post('a_zone_id');
    $a_state = xprios_prepare_post('a_state');
    $a_telephone = xprios_prepare_post('a_telephone');
    $a_fax = xprios_prepare_post('a_fax');
    $a_homepage = xprios_prepare_post('a_homepage');
    $a_password = xprios_prepare_post('a_password');
    $a_confirmation = xprios_prepare_post('a_confirmation'); // neu gegenüber Download
    $a_agb = isset($_POST['a_agb']) && $_POST['a_agb']=='1' ? '1':'0'; // neu gegenüber Download

    $error = false; // reset error flag

    if (ACCOUNT_GENDER == 'true') {
        if (($a_gender == 'm') || ($a_gender == 'f')) {
            $entry_gender_error = false;
        } else {
            $error = true;
            $entry_gender_error = true;
        }
    }

    if (strlen($a_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
        $error = true;
        $entry_firstname_error = true;
    } else {
        $entry_firstname_error = false;
    }

    if (strlen($a_lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
        $error = true;
        $entry_lastname_error = true;
    } else {
        $entry_lastname_error = false;
    }

    if (ACCOUNT_DOB == 'true') {
        if (checkdate(substr(tep_date_raw($a_dob), 4, 2), substr(tep_date_raw($a_dob), 6, 2), substr(tep_date_raw($a_dob), 0, 4))) {
            $entry_date_of_birth_error = false;
        } else {
            $error = true;
            $entry_date_of_birth_error = true;
        }
    }

    if (strlen($a_email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
        $error = true;
        $entry_email_address_error = true;
    } else {
        $entry_email_address_error = false;
    }

    if (!tep_validate_email($a_email_address)) {
        $error = true;
        $entry_email_address_check_error = true;
    } else {
        $entry_email_address_check_error = false;
    }

    if (strlen($a_street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
        $error = true;
        $entry_street_address_error = true;
    } else {
        $entry_street_address_error = false;
    }

    if (strlen($a_postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
        $error = true;
        $entry_post_code_error = true;
    } else {
        $entry_post_code_error = false;
    }

    if (strlen($a_city) < ENTRY_CITY_MIN_LENGTH) {
        $error = true;
        $entry_city_error = true;
    } else {
        $entry_city_error = false;
    }

    if (!$a_country) {
        $error = true;
        $entry_country_error = true;
    } else {
        $entry_country_error = false;
    }

    if (ACCOUNT_STATE == 'true') {
        if ($entry_country_error) {
            $entry_state_error = true;
        } else {
            $a_zone_id = 0;
            $entry_state_error = false;
            $check_query = tep_db_query("select count(*) as total from " . TABLE_ZONES . " where zone_country_id = '" . tep_db_input($a_country) . "'");
            $check_value = tep_db_fetch_array($check_query);
            $entry_state_has_zones = ($check_value['total'] > 0);
            if ($entry_state_has_zones) {
                $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . tep_db_input($a_country) . "' and zone_name = '" . tep_db_input($a_state) . "'");
                if (tep_db_num_rows($zone_query) == 1) {
                    $zone_values = tep_db_fetch_array($zone_query);
                    $a_zone_id = $zone_values['zone_id'];
                } else {
                    $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . tep_db_input($a_country) . "' and zone_code = '" . tep_db_input($a_state) . "'");
                    if (tep_db_num_rows($zone_query) == 1) {
                        $zone_values = tep_db_fetch_array($zone_query);
                        $a_zone_id = $zone_values['zone_id'];
                    } else {
                        $error = true;
                        $entry_state_error = true;
                    }
                }
            } else {
                if (!$a_state) {
                    $error = true;
                    $entry_state_error = true;
                }
            }
        }
    }

    if (strlen($a_telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
        $error = true;
        $entry_telephone_error = true;
    } else {
        $entry_telephone_error = false;
    }

    $passlen = strlen($a_password);
    if ($passlen < ENTRY_PASSWORD_MIN_LENGTH) {
        $error = true;
        $entry_password_error = true;
    } else {
        $entry_password_error = false;
    }

    if ($a_password != $a_confirmation) {
        $error = true;
        $entry_password_error = true;
    }

    $check_email = tep_db_query("select affiliate_email_address from " . TABLE_AFFILIATE . " where affiliate_email_address = '" . tep_db_input($a_email_address) . "'");
    if (tep_db_num_rows($check_email)) {
        $error = true;
        $entry_email_address_exists = true;
    } else {
        $entry_email_address_exists = false;
    }

    // Check Suburb
    $entry_suburb_error = false;

    // Check Fax
    $entry_fax_error = false;

    if (!affiliate_check_url($a_homepage)) { echo 'ERROR';
        $error = true;
        $entry_homepage_error = true;
    } else {
        $entry_homepage_error = false;
    }

    if ($a_agb!=1) {
        $error=true;
        $entry_agb_error=true;
    }

    // Check Company
    $entry_company_error = false;
    $entry_company_taxid_error = false;

    // Check Payment
    $entry_payment_check_error = false;
    $entry_payment_paypal_error = false;
    $entry_payment_bank_name_error = false;
    $entry_payment_bank_branch_number_error = false;
    $entry_payment_bank_swift_code_error = false;
    $entry_payment_bank_account_name_error = false;
    $entry_payment_bank_account_number_error = false;

    if ($error == false) {

        $sql_data_array = [
            'affiliate_firstname' => $a_firstname,
            'affiliate_lastname' => $a_lastname,
            'affiliate_email_address' => $a_email_address,
            'affiliate_payment_check' => $a_payment_check,
            'affiliate_payment_paypal' => $a_payment_paypal,
            'affiliate_payment_bank_name' => $a_payment_bank_name,
            'affiliate_payment_bank_branch_number' => $a_payment_bank_branch_number,
            'affiliate_payment_bank_swift_code' => $a_payment_bank_swift_code,
            'affiliate_payment_bank_account_name' => $a_payment_bank_account_name,
            'affiliate_payment_bank_account_number' => $a_payment_bank_account_number,
            'affiliate_street_address' => $a_street_address,
            'affiliate_postcode' => $a_postcode,
            'affiliate_city' => $a_city,
            'affiliate_country_id' => $a_country,
            'affiliate_telephone' => $a_telephone,
            'affiliate_fax' => $a_fax,
            'affiliate_homepage' => $a_homepage,
            'affiliate_password' => tep_encrypt_password($a_password),
            'affiliate_agb' => '1'
        ];

        if (ACCOUNT_GENDER == 'true') $sql_data_array['affiliate_gender'] = $a_gender;
        if (ACCOUNT_DOB == 'true') {
            $sql_data_array['affiliate_dob'] = tep_date_raw($a_dob);
        } else {
            $sql_data_array['affiliate_dob'] = 'null';
        }
        if (ACCOUNT_COMPANY == 'true') {
            $sql_data_array['affiliate_company'] = $a_company;
            $sql_data_array['affiliate_company_taxid'] = $a_company_taxid;
        }
        if (ACCOUNT_SUBURB == 'true') $sql_data_array['affiliate_suburb'] = $a_suburb;
        if (ACCOUNT_STATE == 'true') {
            if ($a_zone_id > 0) {
                $sql_data_array['affiliate_zone_id'] = $a_zone_id;
                $sql_data_array['affiliate_state'] = '';
            } else {
                $sql_data_array['affiliate_zone_id'] = '0';
                $sql_data_array['affiliate_state'] = $a_state;
            }
        }

        $sql_data_array['affiliate_date_account_created'] = 'now()';

        $_SESSION['affiliate_id']    = affiliate_insert ($sql_data_array, $_SESSION['affiliate_ref']);
        $_SESSION['affiliate_email'] = $a_email_address;
        $_SESSION['affiliate_name']  = $a_firstname . ' ' . $a_lastname;

        $aemailbody = 
            MAIL_AFFILIATE_HEADER . "\n" .
            MAIL_AFFILIATE_ID . $_SESSION['affiliate_id'] . "\n" .
            MAIL_AFFILIATE_USERNAME . $a_email_address . "\n" .
            MAIL_AFFILIATE_PASSWORD . $a_password . "\n\n" .
            MAIL_AFFILIATE_LINK .
            HTTP_SERVER . DIR_WS_CATALOG . FILENAME_AFFILIATE . "\n\n" .
            MAIL_AFFILIATE_FOOTER
        ;
                  
        tep_mail($_SESSION['affiliate_name'], $_SESSION['affiliate_email'], MAIL_AFFILIATE_SUBJECT, nl2br($aemailbody), STORE_OWNER, AFFILIATE_EMAIL_ADDRESS);

        tep_redirect(tep_href_link(FILENAME_AFFILIATE_SIGNUP_OK, '', 'SSL'));
    }
}

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_AFFILIATE_SIGNUP, '', 'SSL'));

$this_head_include = "
<script type=\"text/javascript\"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}
//--></script>";
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('affiliate_signup',  tep_href_link(FILENAME_AFFILIATE_SIGNUP, '', 'SSL'), 'post') . tep_draw_hidden_field('action', 'process');
$heading_image = 'table_background_account.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
<?php
if (isset($_GET['affiliate_email_address'])) {
    $a_email_address = xprios_prepare_get('affiliate_email_address');
}
$affiliate['affiliate_country_id'] = STORE_COUNTRY;

require(DIR_WS_MODULES . 'affiliate_account_details.php');
?>
        </td>
      </tr>
      <tr>
        <td align="right" class="main"><br /><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
      </tr>
    </table></form></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
