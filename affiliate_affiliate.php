<?php
/*
  $Id: affiliate_affiliate.php,v 1.8 2003/02/19 00:28:16 harley_vb Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

  require('includes/application_top.php');

  if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
    $affiliate_username = xprios_prepare_post('affiliate_username');
    $affiliate_password = xprios_prepare_post('affiliate_password');

// Check if username exists
    $check_affiliate_query = tep_db_query("select affiliate_id, affiliate_firstname, affiliate_password, affiliate_email_address from " . TABLE_AFFILIATE . " where affiliate_email_address = '" . tep_db_input($affiliate_username) . "'");
    if (!tep_db_num_rows($check_affiliate_query)) {
      $_GET['login'] = 'fail';
    } else {
      $check_affiliate = tep_db_fetch_array($check_affiliate_query);
// Check that password is good
      if (!tep_validate_password($affiliate_password, $check_affiliate['affiliate_password'])) {
        $_GET['login'] = 'fail';
      } else {
        $_SESSION['affiliate_id'] = $check_affiliate['affiliate_id'];

        $date_now = date('Ymd');

        tep_db_query('update ' . TABLE_AFFILIATE . ' set affiliate_number_of_logons = affiliate_number_of_logons + 1 where affiliate_id = ' . intval($_SESSION['affiliate_id']));

        tep_redirect(tep_href_link(FILENAME_AFFILIATE_SUMMARY,'','SSL'));
      }
    }
  }

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_AFFILIATE, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'meta.php');
  require(DIR_WS_INCLUDES . 'header.php');
  require(DIR_WS_INCLUDES . 'column_left.php');
  $heading_image = 'table_background_login.gif';
  require(DIR_WS_INCLUDES . 'column_center.php');

  if (isset($_GET['login']) && ($_GET['login'] == 'fail')) {
    $info_message = TEXT_LOGIN_ERROR;
  }
  if (isset($info_message)) {
?>
      <tr>
        <td class="smallText"><div style="margin:3px;margin-top:10px;"><?php echo $info_message; ?></div></td>
      </tr>
<?php
  }
?>
      <tr>
        <td><?php echo tep_draw_form('login', tep_href_link(FILENAME_AFFILIATE, 'action=process', 'SSL')); ?><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><div style="margin:3px;margin-top:10px;"><b>1. <?php echo HEADING_RETURNING_AFFILIATE; ?></b></div></td>
          </tr>
          <tr>
            <td>
              <div class="divbox">
                <div style="margin:10px;">
                  <div><?php echo TEXT_RETURNING_AFFILIATE; ?></div>
                  <table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                      <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
                    </tr>
                    <tr>
                     <td class="main"><b><?php echo TEXT_AFFILIATE_ID; ?></b>&nbsp;</td>
                      <td class="main"><?php echo tep_draw_input_field('affiliate_username'); ?></td>
                    </tr>
                    <tr>
                      <td class="main"><b><?php echo TEXT_AFFILIATE_PASSWORD; ?></b>&nbsp;</td>
                      <td class="main"><?php echo tep_draw_password_field('affiliate_password'); ?></td>
                    </tr>
                    <tr>
                      <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
                    </tr>
                  </table>
                  <div class="smallText"><?php echo TEXT_RETURNING_AFFILIATE; ?></div>
                </div>  
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="divbox">
                <div style="margin:2px 10px;text-align:right;"><?php echo tep_image_submit('button_login.gif', IMAGE_BUTTON_LOGIN); ?></div>
              </div>
            </td>  
          </tr>
          <tr>
            <td><div style="margin:3px;margin-top:10px;"><b>2. <?php echo HEADING_NEW_AFFILIATE; ?></b></div></td>
          </tr>
          <tr>
            <td>
              <div class="divbox">
                <div style="margin:10px;" class="main">
                  <div><b><?php echo TEXT_NEW_AFFILIATE; ?></b></div>
                  <div><?php echo TEXT_NEW_AFFILIATE_INTRODUCTION; ?></div>
                  <br />
                  <div class="smallText"><?php echo '<a  href="' . tep_href_link(FILENAME_AFFILIATE_TERMS, '', 'SSL') . '">' . TEXT_NEW_AFFILIATE_TERMS . '</a>'; ?></div>
                </div>  
              </div>
            </td>
          </tr>
          <tr>
            <td>
              <div class="divbox">
                <div style="margin:2px 10px;text-align:right;"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE_SIGNUP, '', 'SSL') . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></div>
              </div>
            </td>  
          </tr>
        </table></form></td>
      </tr>
    </table></td>
<?php
  require(DIR_WS_INCLUDES . 'column_right.php');
  require(DIR_WS_INCLUDES . 'footer.php');
?>