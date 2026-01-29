<?php
/*
  $Id: affiliate_help.php, v 1.4 2003/02/17 17:21:11 harley_vb Exp $

  OSC-Affiliate

  Contribution based on:

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
  
$id = xprios_prepare_get('id');

if (!defined('TEXT_HELP_' . $id)) {
    $this_head_include .= "<script type=\"text/javascript\">
  window.blur();
  window.close();
</script>";
} else {
    $this_head_include = "<style type=\"text/css\"><!--
  body { margin-bottom: 10px; margin-left: 10px; margin-right: 10px; margin-top: 10px; }
//--></style>";
}
require(DIR_WS_INCLUDES . 'meta.php');

$info_box_contents = [];
$info_box_contents[] = array('text' => HEADING_HELP_SUMMARY);

new infoBoxHeading($info_box_contents, true, true);

$info_box_contents = [];
$info_box_contents[] = array('text' =>constant('TEXT_HELP_' . $id));

new infoBox($info_box_contents);
?>
<p class="smallText" align="right"><?php echo '<a href="javascript:window.close()">' . TEXT_CLOSE_WINDOW . '</a>'; ?></p>
</body>
</html>
<?php 
require('includes/application_bottom.php'); 
