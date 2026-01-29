<?php
/*
  $Id: affiliate_popup_image.php,v 1.1 2003/02/24 00:48:43 harley_vb Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2001 - 2003 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

  require('includes/application_top.php');

  $banners_id = intval(xprios_prepare_get('banner'));
  if ($banners_id > 0) {
    $banner_query = tep_db_query("select affiliate_banners_title, affiliate_banners_image, affiliate_banners_html_text from " . TABLE_AFFILIATE_BANNERS . " where affiliate_banners_id = '" . $banners_id . "'");
    $banner = tep_db_fetch_array($banner_query);

    $page_title = $banner['affiliate_banners_title'];

    if ($banner['affiliate_banners_html_text']) {
      $image_source = $banner['affiliate_banners_html_text'];
    } elseif ($banner['affiliate_banners_image']) {
      $image_source = tep_image(HTTP_CATALOG_SERVER . DIR_WS_CATALOG_IMAGES . $banner['affiliate_banners_image'], $page_title);
    }
  }

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<title><?php echo $page_title; ?></title>
<script type="text/javascript"><!--
var i=0;

function resize() {
  if (navigator.appName == 'Netscape') i = 40;
  window.resizeTo(document.images[0].width + 30, document.images[0].height + 60 - i);
}
//--></script>
</head>
<body onload="resize();">
<?php 
  echo $image_source; 
?>
</body>
</html>
