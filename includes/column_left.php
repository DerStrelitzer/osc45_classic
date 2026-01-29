<?php
/*
  $Id: column_left.php,v 1.15 2003/07/01 14:34:54 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- left_navigation //-->
<?php
echo '    <td width="' . BOX_WIDTH . '" rowspan="2" valign="top" class="columnleft"><table border="0" width="' . BOX_WIDTH . '" cellspacing="3" cellpadding="3">' . "\n";

if (STORE_PAGE_PARSE_TIME == 'ja') {
    error_log(xprios_date_string(STORE_PARSE_DATE_TIME_FORMAT, time()) . ' [BEGIN: column_left.php] ' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
}

if ((USE_CACHE == 'ja') && empty($SID)) {
    echo tep_cache_categories_box();
} else {
    include(DIR_WS_BOXES . 'categories.php');
}

require(DIR_WS_BOXES . 'search.php');

if ((USE_CACHE == 'ja') && empty($SID)) {
    echo tep_cache_manufacturers_box();
} else {
    include(DIR_WS_BOXES . 'manufacturers.php');
}

require(DIR_WS_BOXES . 'whats_new.php');

// Include OSC-AFFILIATE if enabled
if (AFFILIATE_ENABLED == 'ja') require (DIR_WS_BOXES . 'affiliate.php');

require(DIR_WS_BOXES . 'information.php');

if (STORE_PAGE_PARSE_TIME == 'ja') {
    error_log(xprios_date_string(STORE_PARSE_DATE_TIME_FORMAT, time()) . ' [END: column_left.php] ' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
}
?>
    </table></td>
<!-- left_navigation_eof //-->
