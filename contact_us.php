<?php
/*
  $Id: contact_us.php,v 1.42 2003/06/12 12:17:07 hpdl Exp $

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

$error = false;
if (isset($_GET['action']) && ($_GET['action'] == 'send') && $_POST['trigger']!='' && $_SESSION['contact_trigger']==$_POST['trigger'] ) {
    unset($_SESSION['contact_trigger']);
    $name          = xprios_prepare_post('name');
    $email_address = xprios_prepare_post('email');
    $enquiry       = xprios_prepare_post('enquiry');
    $products_id = '';
    if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
        $products_id = '&products_id=' . $_POST['products_id'];
    }

    if (tep_validate_email($email_address)) {
        tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, EMAIL_SUBJECT, $enquiry, $name, $email_address);
        tep_redirect(tep_href_link(FILENAME_CONTACT_US, 'action=success' . $products_id));
      
    } else {
        $error = true;
        $messageStack->add('contact', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }
}

if (isset($_GET['keywords']) && $_GET['keywords'] != '') {
    $key_array = explode(' ', $_GET['keywords']);
    $words = '';
    for ($i=0; $i<sizeof($key_array); $i++) {
      $word = trim($key_array[$i]);
      if ($word!='') $words .= (($words=='')?'':', ') . ucfirst($word);
    }
    $enquiry = sprintf(TEXT_CONTACT_US_KEYWORD, "\n\n" . $words . "\n\n"). "\n" . (isset($_SESSION['customer_id']) ? $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] : '') ;
} elseif (isset($_GET['products_id']) && is_numeric($_GET['products_id'])) {
    $enquiry = sprintf(TEXT_CONTACT_US_PRODUCT, "\n\n" . '"' . tep_get_products_name($_GET['products_id']) . '"' . "\n\n"). "\n" . (isset($_SESSION['customer_id']) ? $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] : '') ;
} else {
    $enquiry = xprios_prepare_post('enquiry');
}
  
$_SESSION['contact_trigger'] = md5(time());

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_CONTACT_US));

$this_head_include = "<script type=\"text/javascript\" language=\"javascript\"><!--
function backbutton() {
  document.write ('<a href=\"javascript:history.back();\">" . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '<\/a>' . "');
  document.write ('" . tep_draw_separator('pixel_trans.gif', '10', '1') . "');
}
//--></script>";
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('contact_us', tep_href_link(FILENAME_CONTACT_US, 'action=send'));
$heading_image = 'table_background_contact_us.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
if ($messageStack->size('contact') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('contact'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
}

if (isset($_GET['action']) && ($_GET['action'] == 'success')) {

    if ((isset($_GET['products_id'])) && (is_numeric($_GET['products_id']))) {
        $new_link_page = ingo_product_link($_GET['products_id']);
    } else {
        $new_link_page = tep_href_link(FILENAME_DEFAULT);
    }
?>
      <tr>
        <td class="main" align="center"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_man_on_board.gif', HEADING_TITLE, '0', '0', 'align="left"') . TEXT_SUCCESS; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <div style="text-align:right;margin:2px 12px"><?php echo '<a href="' . $new_link_page . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></div>
          </div>
        </td>
      </tr>
<?php
} else {
    $crypted = '<a href="' . $email_address_crypted_mailto . $store_owner_email_address . '">' . $store_owner_email_address . '</a>';
?>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><?php echo ENTRY_NAME . tep_draw_hidden_field('trigger', $_SESSION['contact_trigger']); ?></td>
                <td align="right" width="33%" rowspan="5" class="main"><div style="text-align:left"><?php echo '<b>' . nl2br(STORE_NAME_ADDRESS) . '</b><br />' . $crypted; ?></div></td>
              </tr>
              <tr>
                <td class="main"><?php echo tep_draw_input_field('name', isset($_SESSION['customer_id']) ? $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'] : '', '', 'text', false); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_EMAIL; ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo tep_draw_input_field('email', isset($_SESSION['customer_email_address']) ? $_SESSION['customer_email_address'] : '', '', 'text', false); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo ENTRY_ENQUIRY; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_textarea_field('enquiry', 'soft', 50, 15, $enquiry); ?></td>
              </tr>
            </table>
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
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); if (isset($_GET['products_id']) && is_numeric($_GET['products_id'])) echo tep_draw_hidden_field('products_id', $_GET['products_id']); ?></td>
                <td>
                  <script type="text/javascript">
                    backbutton();
                  </script>
                  <noscript>
                    &nbsp;
                  </noscript>
                </td>
                <td align="right"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'style="margin-right:10px;"'); ?></td>
              </tr>
            </table> 
          </div>
        </td>
      </tr>
<?php
}
?>
    </table></form></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
