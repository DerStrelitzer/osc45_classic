<?php
/*
  $Id: popup_image.php,v 1.18 2003/06/05 23:26:23 hpdl Exp $

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

if (isset($_SESSION['navigation']) && is_object($_SESSION['navigation'])) {
    $_SESSION['navigation']->remove_current_page();
}

if (isset($_GET['iID']) && is_numeric($_GET['iID'])) {
    $products_query = tep_db_query("select pd.products_name, pi.image_name as products_image from " . TABLE_PRODUCTS_IMAGES . " pi left join " . TABLE_PRODUCTS_DESCRIPTION . " pd USING (products_id) where pi.image_id = '" . (int)$_GET['iID'] . "' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
} else {
    $products_query = tep_db_query("select pd.products_name, p.products_image from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where p.products_status = '1' and p.products_id = '" . (int)$_GET['pID'] . "' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
}

$products = tep_db_fetch_array($products_query);

if (file_exists(DIR_FS_CATALOG . 'images/big/' . $products['products_image'] )) { $products['products_image'] = 'big/' . $products['products_image'];}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<title><?php echo $products['products_name']; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
<style type="text/css">
  * {margin:0;padding:0;border:0}
</style>
<script type="text/javascript">
// <![CDATA[
var i=0;
function resize() {
  i = 47;
  if (navigator.appName == 'Netscape') i += 2;
  if (window.navigator.userAgent.indexOf('MSIE 7.0') > 0) {
    if (window.location.href.indexOf('localhost') > 0) {
      i -= 16;
    } else {
      i += 25;
    }
  }
  height = document.images[0].height + i;
  if (document.images[0]) window.resizeTo(document.images[0].width+8, height);
  self.focus();
}
window.onblur = function(){self.close()}
// ]]>
</script>
</head>
<body onload="resize();">
<?php echo tep_image(DIR_WS_IMAGES . $products['products_image'], $products['products_name']); ?>
</body>
</html>
<?php 
require('includes/application_bottom.php');
