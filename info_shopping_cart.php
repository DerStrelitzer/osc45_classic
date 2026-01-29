<?php
/*
  $Id: info_shopping_cart.php,v 1.19 2003/02/13 03:01:48 hpdl Exp $

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

require(DIR_WS_INCLUDES . 'meta.php');
?>
<p class="main"><b><?php echo HEADING_TITLE; ?></b><br /><?php echo tep_draw_separator(); ?></p>
<p class="main"><b><i><?php echo SUB_HEADING_TITLE_1; ?></i></b><br /><?php echo SUB_HEADING_TEXT_1; ?></p>
<p class="main"><b><i><?php echo SUB_HEADING_TITLE_2; ?></i></b><br /><?php echo SUB_HEADING_TEXT_2; ?></p>
<p class="main"><b><i><?php echo SUB_HEADING_TITLE_3; ?></i></b><br /><?php echo SUB_HEADING_TEXT_3; ?></p>
<p align="right" class="main"><a href="javascript:window.close();"><?php echo TEXT_CLOSE_WINDOW; ?></a></p>
</body>
</html>
<?php
require(DIR_WS_INCLUDES . 'counter.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
