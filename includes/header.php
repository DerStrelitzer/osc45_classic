<?php
/*
  $Id: header.php,v 1.42 2003/06/10 18:20:38 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

// check if the session folder is writeable
if (WARN_SESSION_DIRECTORY_NOT_WRITEABLE == 'true' && STORE_SESSIONS == '') {
    if (!is_dir(tep_session_save_path())) {
        $messageStack->add('header', WARNING_SESSION_DIRECTORY_NON_EXISTENT, 'warning');
    } elseif (!is_writeable(tep_session_save_path())) {
        $messageStack->add('header', WARNING_SESSION_DIRECTORY_NOT_WRITEABLE, 'warning');
    }
}

// check session.auto_start is disabled
if ( (function_exists('ini_get')) && (WARN_SESSION_AUTO_START == 'true') ) {
    if (ini_get('session.auto_start') == '1') {
        $messageStack->add('header', WARNING_SESSION_AUTO_START, 'warning');
    }
}

if ( (WARN_DOWNLOAD_DIRECTORY_NOT_READABLE == 'true') && (DOWNLOAD_ENABLED == 'true') ) {
    if (!is_dir(DIR_FS_DOWNLOAD)) {
        $messageStack->add('header', WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT, 'warning');
    }
}

if ($messageStack->size('header') > 0) {
    echo $messageStack->output('header');
}

// Links im Header zu anderen Seiten
$header_links = [];
if ($spider_flag==true) {
    $header_links[] = array ('link' => tep_href_link(FILENAME_ALL_PRODUCTS), 'class' => 'headerNavigation', 'text' => ALL_PRODUCTS_LINK, 'param' => '');
    $header_links[] = array ('link' => tep_href_link(FILENAME_PRODUCTS_NEW), 'class' => 'headerNavigation', 'text' => BOX_HEADING_WHATS_NEW, 'param' => '');
    $header_links[] = array ('link' => tep_href_link(FILENAME_SPECIALS), 'class' => 'headerNavigation', 'text' => BOX_HEADING_SPECIALS, 'param' => '');
} else {
    if (isset($_SESSION['customer_id'])) {
        $header_links[] = array( 'link' => tep_href_link(FILENAME_ACCOUNT, '', 'SSL'), 'class' => 'headerNavigation', 'text' => HEADER_TITLE_MY_ACCOUNT, 'param' => '');
    } else {
        $header_links[] = array ('link' => tep_href_link(FILENAME_LOGIN, '', 'SSL'), 'class' => 'headerNavigation', 'text' => HEADER_TITLE_LOGIN, 'param' => '');
    }
    if ($cart->count_contents() > 0 ) {
        $header_links[] = array ('link' => tep_href_link(FILENAME_SHOPPING_CART), 'class' => 'headerNavigation', 'text' => HEADER_TITLE_CART_CONTENTS, 'param' => '');
        $header_links[] = array ('link' => tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'), 'class' => 'headerNavigation', 'text' => HEADER_TITLE_CHECKOUT, 'param' => '');
    }
    if (isset($_SESSION['customer_id'])) {
      $header_links[] = array ('link' => tep_href_link(FILENAME_LOGOFF, '', 'SSL'), 'class' => 'headerNavigation', 'text' => HEADER_TITLE_LOGOFF, 'param' => '');
    }
}

?>
<!-- header //-->
<div id="window">
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="header">
  <tr class="header">
    <td class="header"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image(DIR_WS_IMAGES . 'pixel_trans.gif', STORE_NAME) . '</a>'; ?></td>
  </tr>
</table>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr class="headerNavigation">
    <td class="breadcrump"><?php echo $breadcrumb->trail(' &raquo; '); ?></td>
    <td align="right" class="headerNavigation"><?php
for ($i=0,$j=count($header_links); $i<$j; $i++) {
    if ($i>0) {
        echo '&nbsp;|&nbsp;';
    }
    echo '<a href="' . $header_links[$i]['link'] . '"' . ($header_links[$i]['class']!=''? ' class="' . $header_links[$i]['class'] . '"':'') . ($header_links[$i]['param']!=''? ' ' . $header_links[$i]['param']:'') . '>' . $header_links[$i]['text'] . '</a>';
} ?></td>
  </tr>
</table>
<?php
if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="headerError">
    <td class="headerError"><?php echo htmlspecialchars(urldecode($_GET['error_message']), ENT_QUOTES, CHARSET, false); ?></td>
  </tr>
</table>
<?php
}
if (isset($_GET['info_message']) && tep_not_null($_GET['info_message'])) {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="headerInfo">
    <td class="headerInfo"><?php echo htmlspecialchars($_GET['info_message'], ENT_QUOTES, CHARSET, false); ?></td>
  </tr>
</table>
<?php
}
if (defined('HEADER_MESSAGE_WARNING') && trim(HEADER_MESSAGE_WARNING)!='') {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="headerError">
    <td class="headerError"><?php echo trim(HEADER_MESSAGE_WARNING); ?></td>
  </tr>
</table>
<?php
}
if (defined('HEADER_MESSAGE_INFO') && trim(HEADER_MESSAGE_INFO)!='') {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr class="headerInfo">
    <td class="headerInfo"><?php echo trim(HEADER_MESSAGE_INFO); ?></td>
  </tr>
</table>
<?php
}
?>
<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>