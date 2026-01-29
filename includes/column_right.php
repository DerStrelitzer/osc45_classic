<?php
/*
  $Id: column_right.php,v 1.17 2003/06/09 22:06:41 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- right_navigation //-->
<?php
echo '    <td width="' . BOX_WIDTH . '" valign="top" rowspan="2" class="columnright"><table border="0" width="' . BOX_WIDTH . '" cellspacing="3" cellpadding="3">' . "\n";

if (STORE_PAGE_PARSE_TIME == 'ja') {
    error_log(xprios_date_string(STORE_PARSE_DATE_TIME_FORMAT, time()) . ' [BEGIN: column_right.php] ' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
}

require(DIR_WS_BOXES . 'shopping_cart.php');

require(DIR_WS_BOXES . 'viewed_products.php');

if (isset($_GET['products_id'])) {
    include(DIR_WS_BOXES . 'manufacturer_info.php');
}
  
if (isset($_SESSION['customer_id']) && $_SESSION['customer_id']>0) {
    include(DIR_WS_BOXES . 'order_history.php');
}
  
if (isset($_GET['products_id'])) {
    if (isset($_SESSION['customer_id']) && $_SESSION['customer_id']>0) {
        $check_query = tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int)$_SESSION['customer_id'] . "' and global_product_notifications = '1'");
        $check = tep_db_fetch_array($check_query);
        if ($check['count'] > 0) {
            include(DIR_WS_BOXES . 'best_sellers.php');
        } else {
            include(DIR_WS_BOXES . 'product_notifications.php');
        }
    } else {
        include(DIR_WS_BOXES . 'product_notifications.php');
    }
} else {
    include(DIR_WS_BOXES . 'best_sellers.php');
}

if (isset($_GET['products_id']) && defined('TELL_A_FRIEND_SHOW') && TELL_A_FRIEND_SHOW=='ja' && basename($PHP_SELF) != FILENAME_TELL_A_FRIEND) {
    include(DIR_WS_BOXES . 'tell_a_friend.php');
} else {
    include(DIR_WS_BOXES . 'specials.php');
}

require(DIR_WS_BOXES . 'reviews.php');

if (defined('FEATURED_PRODUCTS_BOX_DISPLAY') && FEATURED_PRODUCTS_BOX_DISPLAY=='ja') {
    include(DIR_WS_BOXES . 'featured_products.php');
}

if (defined('LATEST_NEWS_BOX') && LATEST_NEWS_BOX=='ja' && basename($PHP_SELF) == FILENAME_DEFAULT) {
    include(DIR_WS_BOXES . 'latest_news.php');
}

if (substr(basename($PHP_SELF), 0, 8) != 'checkout') {
    include(DIR_WS_BOXES . 'languages.php');
    include(DIR_WS_BOXES . 'currencies.php');
}

if (defined('WHOS_ONLINE_BOX_SHOW') && WHOS_ONLINE_BOX_SHOW == 'ja') {
    include (DIR_WS_BOXES . 'whos_online.php');
}

if (STORE_PAGE_PARSE_TIME == 'ja') {
    error_log(xprios_date_string(STORE_PARSE_DATE_TIME_FORMAT, time()) . ' [END: column_right.php] ' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
}
?>
<!-- right_navigation_eof //-->
      <tr>
        <td align="center" style="padding:5px;">
          <a href="http://validator.w3.org/check?uri=referer" target="_blank"><?php echo tep_image(DIR_WS_ICONS . 'valid_xhtml1.gif', 'Dieses Dokument wurde als XHTML 1.0 Transitional geprüft', '88', '31'); ?></a>
          <br />
          <a href="http://jigsaw.w3.org/css-validator/validator?uri=<?php echo HTTP_SERVER . DIR_WS_HTTP_CATALOG . basename($PHP_SELF); ?>&amp;warning=0&amp;profile=css2" target="_blank"><?php echo tep_image(DIR_WS_ICONS . 'valid_css2.gif', 'Dieses Dokument wurde als CSS 2.0 geprüft!', '88', '31'); ?></a>
        </td>
      </tr>
    </table></td>
