<?php
/*
  $Id: account_password.php,v 1.1 2003/05/19 19:55:45 hpdl Exp $

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

if (!isset($_SESSION['customer']['id'])) {
    xprios_set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    $password_current = xprios_prepare_post('password_current');
    $password_new = xprios_prepare_post('password_new');
    $password_confirmation = xprios_prepare_post('password_confirmation');

    $error = false;

    if (strlen($password_current) < ENTRY_PASSWORD_MIN_LENGTH) {
        $error = true;
        $messageStack->add('account_password', ENTRY_PASSWORD_CURRENT_ERROR);
    } elseif (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
        $error = true;
        $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR);
    } elseif ($password_new != $password_confirmation) {
        $error = true;
        $messageStack->add('account_password', ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING);
    }

    if ($error == false) {
        $check_customer_query = tep_db_query('select customers_password from ' . TABLE_CUSTOMERS . ' where customers_id = ' . (int)$_SESSION['customer_id']);
        $check_customer = tep_db_fetch_array($check_customer_query);

        if (tep_validate_password($password_current, $check_customer['customers_password'])) {
            tep_db_query('update ' . TABLE_CUSTOMERS . ' set customers_password = "' . tep_encrypt_password($password_new) . '" where customers_id = ' . (int)$_SESSION['customer_id']);
            tep_db_query('update ' . TABLE_CUSTOMERS_INFO . ' set customers_info_date_account_last_modified = now() where customers_info_id = ' . (int)$_SESSION['customer_id']);
            $messageStack->add_session('account', SUCCESS_PASSWORD_UPDATED, 'success');
            tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        
        } else {
            $error = true;
            $messageStack->add('account_password', ERROR_CURRENT_PASSWORD_NOT_MATCHING);
        }
    }
}

$breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
$breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'));

$this_head_file = 'includes/form_check.js.php';
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('account_password', tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'), 'post', 'onsubmit="return check_form(account_password);"') . tep_draw_hidden_field('action', 'process');
$heading_image = 'table_background_account.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
if ($messageStack->size('account_password') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('account_password'); ?></td>
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
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><b><?php echo MY_PASSWORD_TITLE; ?></b></td>
                <td class="inputRequirement" align="right"><?php echo FORM_REQUIRED_INFORMATION; ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td> 
              <div class="divbox">
                <table border="0" cellspacing="2" cellpadding="2">
                  <tr>
                    <td class="main"><?php echo ENTRY_PASSWORD_CURRENT; ?></td>
                    <td class="main"><?php echo tep_draw_password_field('password_current') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_CURRENT_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CURRENT_TEXT . '</span>': ''); ?></td>
                  </tr>
                  <tr>
                    <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo ENTRY_PASSWORD_NEW; ?></td>
                    <td class="main"><?php echo tep_draw_password_field('password_new') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_NEW_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_NEW_TEXT . '</span>': ''); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></td>
                    <td class="main"><?php echo tep_draw_password_field('password_confirmation') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?></td>
                  </tr>
                </table> 
              </div
            </td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td> 
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'style="margin-left:10px;"') . '</a>'; ?></td>
                <td align="right"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'style="margin-right:10px;"'); ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
    </table></form></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
