<?php
/*
  $Id: affiliate_contact.php,v 1.5 2003/02/17 20:11:59 thomasamoulton Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2002 - 2003 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

  require('includes/application_top.php');

  $action = isset($_GET['action']) ? $_GET['action'] : '';

  if ($action == 'send_email_to_user' && isset($_POST['affiliate_email_address']) && !isset($_POST['back_x']) ) {
    switch ($_POST['affiliate_email_address']) {
      case '***':
        $mail_query = tep_db_query("select affiliate_firstname, affiliate_lastname, affiliate_email_address from " . TABLE_AFFILIATE . " ");
        $mail_sent_to = TEXT_ALL_AFFILIATES;
      break;
//      case '**D':
//        $mail_query = tep_db_query("select affiliate_firstname, affiliate_lastname, affiliate_email_address from " . TABLE_AFFILIATE . " where affiliate_newsletter = '1'");
//        $mail_sent_to = TEXT_NEWSLETTER_AFFILIATE;
//        break;
      default:
        $affiliate_email_address = xprios_prepare_post('affiliate_email_address');

        $mail_query = tep_db_query("select affiliate_firstname, affiliate_lastname, affiliate_email_address from " . TABLE_AFFILIATE . " where affiliate_email_address = '" . tep_db_input($affiliate_email_address) . "'");
        $mail_sent_to = $_POST['affiliate_email_address'];
      break;
    }

    $from = xprios_prepare_post('from');
    $subject = xprios_prepare_post('subject');
    $message = xprios_prepare_post('message');

    // Instantiate a new mail object
    $mimemessage = new Email(['X-Mailer: osCommerce mailer']);

    // Build the text version
    $text = strip_tags($text);
    if (EMAIL_USE_HTML == 'true') {
      $mimemessage->add_html($message);
    } else {
      $mimemessage->add_text($message);
    }

    // Send message
    $mimemessage->build_message();
    while ($mail = tep_db_fetch_array($mail_query)) {
      $mimemessage->send($mail['affiliate_firstname'] . ' ' . $mail['affiliate_lastname'], $mail['affiliate_email_address'], '', $from, $subject);
    }

    tep_redirect(tep_href_link(FILENAME_AFFILIATE_CONTACT, 'mail_sent_to=' . urlencode($mail_sent_to)));
  }

  if ($action == 'preview' && !isset($_POST['affiliate_email_address'])) {
    $messageStack->add(ERROR_NO_AFFILIATE_SELECTED, 'error');
  }

  if (isset($_GET['mail_sent_to']) && tep_not_null($_GET['mail_sent_to'])) {
    $messageStack->add(sprintf(NOTICE_EMAIL_SENT_TO, $_GET['mail_sent_to']), 'notice');
  }
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%" class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if ($action == 'preview' && isset($_POST['affiliate_email_address'])) {
    switch ($_POST['affiliate_email_address']) {
      case '***':
        $mail_sent_to = TEXT_ALL_AFFILIATES;
        break;
//      case '**D':
//        $mail_sent_to = TEXT_NEWSLETTER_AFFILIATES;
//        break;
      default:
        $mail_sent_to = $_POST['affiliate_email_address'];
        break;
    }
?>
          <tr><?php echo tep_draw_form('mail', FILENAME_AFFILIATE_CONTACT, 'action=send_email_to_user'); ?>
            <td><table border="0" width="100%" cellpadding="0" cellspacing="2">
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_AFFILIATE; ?></b><br><?php echo $mail_sent_to; ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_FROM; ?></b><br><?php echo htmlspecialchars(stripslashes($_POST['from']), ENT_NOQUOTES, CHARSET, false); ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_SUBJECT; ?></b><br><?php echo htmlspecialchars(stripslashes($_POST['subject']), ENT_NOQUOTES, CHARSET, false); ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="smallText"><b><?php echo TEXT_MESSAGE; ?></b><br><?php echo nl2br(htmlspecialchars(stripslashes($_POST['message']), ENT_NOQUOTES, CHARSET, false)); ?></td>
              </tr>
              <tr>
                <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td>
<?php
/* Re-Post all POST'ed variables */
    foreach ($_POST as $key => $value) {
      if (!is_array($_POST[$key])) {
        echo tep_draw_hidden_field($key, htmlspecialchars(stripslashes($value), ENT_COMPAT, CHARSET, false));
      }
    }
?>
                <table border="0" width="100%" cellpadding="0" cellspacing="2">
                  <tr>
                    <td><?php echo tep_image_submit('button_back.gif', IMAGE_BACK, 'name="back"'); ?></td>
                    <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE_CONTACT) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a> ' . tep_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </form></tr>
<?php
  } else {
?>
          <tr><?php echo tep_draw_form('mail', FILENAME_AFFILIATE_CONTACT, 'action=preview'); ?>
            <td><table border="0" cellpadding="0" cellspacing="2">
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
<?php
    $affiliate = array( 
      array('id' => '', 'text' => TEXT_SELECT_AFFILIATE),
      array('id' => '***', 'text' => TEXT_ALL_AFFILIATES)
    );
//    $affiliate[] = array('id' => '**D', 'text' => TEXT_NEWSLETTER_AFFILIATES);
    $mail_query = tep_db_query("select affiliate_email_address, affiliate_firstname, affiliate_lastname from " . TABLE_AFFILIATE . " order by affiliate_lastname");
    while($affiliate_values = tep_db_fetch_array($mail_query)) {
      $affiliate[] = array(
        'id'   => $affiliate_values['affiliate_email_address'],
        'text' => $affiliate_values['affiliate_lastname'] . ', ' . $affiliate_values['affiliate_firstname'] . ' (' . $affiliate_values['affiliate_email_address'] . ')'
      );
    }
?>
              <tr>
                <td class="main"><?php echo TEXT_AFFILIATE; ?></td>
                <td><?php echo tep_draw_pull_down_menu('affiliate_email_address', $affiliate, isset($_GET['affiliate']) ? $_GET['affiliate'] : '');?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_FROM; ?></td>
                <td><?php echo tep_draw_input_field('from', AFFILIATE_EMAIL_ADDRESS, 'size="60"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_SUBJECT; ?></td>
                <td><?php echo tep_draw_input_field('subject', '', 'size="60"'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td valign="top" class="main"><?php echo TEXT_MESSAGE; ?></td>
                <td><?php echo tep_draw_textarea_field('message', 'soft', '60', '15'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td colspan="2" align="right"><?php echo tep_image_submit('button_send_mail.gif', IMAGE_SEND_EMAIL); ?></td>
              </tr>
            </table></td>
          </form></tr>
<?php
  }
?>
<!-- body_text_eof //-->
        </table></td>
      </tr>
    </table></td>
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
?>