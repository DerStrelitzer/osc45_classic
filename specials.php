<?php
/*
  $Id: specials.php,v 1.49 2003/06/09 22:35:33 hpdl Exp $

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

$define_list = [
    'PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
    'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
    'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
    'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
    'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
    'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
    'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
    'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW
];
asort($define_list);
$column_list = [];
foreach ($define_list as $key => $value) {
    if ($value > 0) $column_list[] = $key;
}

$listing_sql = "select p.products_id, pd.products_name, pd.products_description, p.products_price, p.products_tax_class_id, p.products_image, s.specials_new_products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_status = '1' and s.products_id = p.products_id and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and s.status = '1' order by s.specials_date_added DESC";
$listing_split = new SplitPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id');

$page_keywords = '';
  if ($listing_split->number_of_rows > 0) {
    $listing_query = tep_db_query($listing_split->sql_query);
    while ($listing = tep_db_fetch_array($listing_query)) {
        if ($listing_split->number_of_rows == 1) {
            tep_redirect(ingo_product_link($listing['products_id'], $listing['products_name']));
        }
        $page_keywords .= ($page_keywords!=''?',':'') . str_replace('"', "'", strip_tags($listing['products_name']));
    }
    tep_db_data_seek($listing_query);
}

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_SPECIALS));

require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$heading_image = 'table_background_specials.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
if ($listing_split->number_of_rows > 0) {
    $_GET['sort'] = false;
?>
      <tr>
        <td><?php include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING); ?></td>
      </tr>

<?php
} else {
?>
      <tr>
        <td class="main"><?php echo TEXT_NO_NEW_SPECIALS; ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
