<?php
/*
  $Id: login.php,v 1.80 2003/06/05 23:28:24 hpdl Exp $

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

// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
if ($session_started == false) {
    tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
}

if (isset($_POST['mode']) && $_POST['email_address']=='' && $_POST['password']=='') {
    if ($_POST['mode']=='create') tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
    if ($_POST['mode']=='pwa') tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT, 'guest=guest', 'SSL'));
}

$error = false;
if (isset($_GET['action']) && $_GET['action'] == 'process') {
    $email_address = xprios_prepare_post('email_address');
    $password = xprios_prepare_post('password');

// Check if email exists
// Ingo: add "customers_gender, customers_lastname" zu query (anrede, nachname)
    $check_customer_query = tep_db_query("select customers_id, customers_gender, customers_firstname, customers_lastname, customers_password, customers_email_address, customers_default_address_id from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
    if (!tep_db_num_rows($check_customer_query)) {
        $error = true;
    } else {
        $check_customer = tep_db_fetch_array($check_customer_query);
// Check that password is good
        if (!tep_validate_password($password, $check_customer['customers_password'])) {
            $error = true;
        } else {
            if (SESSION_RECREATE == 'True') {
                tep_session_recreate();
            }

            $check_country_query = tep_db_query("select entry_country_id, entry_zone_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$check_customer['customers_id'] . "' and address_book_id = '" . (int)$check_customer['customers_default_address_id'] . "'");
            $check_country = tep_db_fetch_array($check_country_query);

            $_SESSION['customer_id']                 = $check_customer['customers_id'];
            $_SESSION['customer_default_address_id'] = $check_customer['customers_default_address_id'];
            $_SESSION['customer_first_name']         = $check_customer['customers_firstname'];
            $_SESSION['customer_last_name']          = $check_customer['customers_lastname'];
            $_SESSION['customer_country_id']         = $check_country['entry_country_id'];
            $_SESSION['customer_zone_id']            = $check_country['entry_zone_id'];
            $_SESSION['customer_email_address']      = $email_address;
            if (ACCOUNT_GENDER == 'true') {
                $_SESSION['customer_gender']           = $check_customer['customers_gender']=='f' ? FEMALE : MALE;
            }

            tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_of_last_logon = now(), customers_info_number_of_logons = customers_info_number_of_logons+1 where customers_info_id = '" . (int)$_SESSION['customer_id'] . "'");

// restore cart contents
            $_SESSION['cart']->restore_contents();

            if (sizeof($_SESSION['navigation']->snapshot) > 0) {
                $origin_href = tep_href_link($_SESSION['navigation']->snapshot['page'], tep_array_to_string($_SESSION['navigation']->snapshot['get'], array(tep_session_name())), $_SESSION['navigation']->snapshot['mode']);
                $_SESSION['navigation']->clear_snapshot();
                tep_redirect($origin_href);
            } else {
                tep_redirect(tep_href_link(FILENAME_DEFAULT));
            }
        }
    }
}

if ($error == true) {
    $messageStack->add('login', TEXT_LOGIN_ERROR);
}

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_LOGIN, '', 'SSL'));

$this_head_include = "<script type=\"text/javascript\"><!--
function session_win() {
  window.open('" . tep_href_link(FILENAME_INFO_SHOPPING_CART) . "','info_shopping_cart','height=460,width=430,toolbar=no,statusbar=no,scrollbars=yes').focus();
}
//--></script>";
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('login', tep_href_link(FILENAME_LOGIN, 'action=process', 'SSL'));
$heading_image = 'table_background_login.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
if ($messageStack->size('login') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('login'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
}

if ($cart->count_contents() > 0) {
?>
      <tr>
        <td class="smallText"><?php echo TEXT_VISITORS_CART; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td align="right" width="100%"><table cellspacing="0" cellpadding="5" border="0" width="90%">

          <tr>
            <td width="20" valign="top"><?php echo tep_draw_radio_field('mode', 'login', false); ?></td>
            <td class="main" align="left"><?php echo TEXT_RETURNING_CUSTOMER; ?><br />
              <table cellspacing="3" border="0">
                <tr>
                  <td class="main" valign="bottom"><?php echo ENTRY_EMAIL_ADDRESS . '<br />' . tep_draw_input_field('email_address', false, 'onfocus="this.form.mode[0].checked=true;"'); ?></td>
                  <td class="main" valign="bottom"><?php echo ENTRY_PASSWORD . '<br />' . tep_draw_password_field('password', false, 'onfocus="this.form.mode[0].checked=true;"'); ?></td>
                  <td class="smallText" valign="bottom"><?php echo '<a href="' . tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td>&nbsp;</td>
            <td><?php echo tep_draw_separator(); ?></td>
          </tr>
          <tr>
            <td width="20" valign="top"><?php echo tep_draw_radio_field('mode', 'create', ($cart->count_contents()==0?true:false)); ?></td>
            <td class="main" align="left"><?php echo TEXT_NEW_CUSTOMER; ?></td>
          </tr>
<?php
if (defined('PURCHASE_WITHOUT_ACCOUNT') && PURCHASE_WITHOUT_ACCOUNT == 'ja' && $cart->count_contents() > 0) {
?>
          <tr>
            <td>&nbsp;</td>
            <td><?php echo tep_draw_separator(); ?></td>
          </tr>
          <tr>
            <td width="20" valign="top"><?php echo tep_draw_radio_field('mode', 'pwa', true); ?></td>
            <td class="main" align="left"><?php echo TEXT_PWA_CUSTOMER . '<br /><span class="smallText">' . TEXT_PWA_CUSTOMER_NOTICE . '</span>'; ?></td>
          </tr>
<?php
}
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td colspan="2">
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
<?php
$back = sizeof($_SESSION['navigation']->path)-3;
if (isset($_SESSION['navigation']->path[$back])) {
?>
                <td><?php echo '<a href="' . tep_href_link($_SESSION['navigation']->path[$back]['page'], tep_array_to_string($_SESSION['navigation']->path[$back]['get'], array('action')), $_SESSION['navigation']->path[$back]['mode']) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'style="margin-left:10px;"') . '</a>'; ?></td>
<?php
} else {
?>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'style="margin-left:10px;"') . '</a>'; ?></td>
<?php
}
?>
                <td align="right" ><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'style="margin-right:10px;"'); ?></td>
              </tr>
            </table> 
          </div>
        </td>
      </tr>
    </table></form></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
