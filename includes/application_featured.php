<?php
/*
  $Id: application_featured.php.php,v 3.0 2006/02/23 by Ingo <http://forums.oscommerce.de/index.php?showuser=36>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

////
// Auto expire featured products
//
tep_db_query("update " . TABLE_FEATURED_PRODUCTS . " set status = '0', date_status_change = now() where now() >= expires_date and expires_date > 0");

////
// Include rquired language
//
include (DIR_WS_LANGUAGES . $language . '/' . FILENAME_FEATURED_PRODUCTS);

if ((defined('FEATURED_PRODUCTS_MODUL_DISPLAY') && FEATURED_PRODUCTS_MODUL_DISPLAY == 'ja') || (defined('FEATURED_PRODUCTS_BOX_DISPLAY') && FEATURED_PRODUCTS_BOX_DISPLAY == 'ja')) {

////
// read all featured products
//
    if (!isset($_SESSION['featured_for'][0])) {
        $_SESSION['featured_for'][0] = '';
        $featured_query = tep_db_query("select f.products_id from " . TABLE_FEATURED_PRODUCTS . " f, " . TABLE_PRODUCTS . " p where p.products_id = f.products_id and f.status = '1' and p.products_status = '1'");
        while ($result = tep_db_fetch_array($featured_query)) {
            $_SESSION['featured_for'][0] .= (($_SESSION['featured_for'][0]!='')? ',':'') . $result['products_id'];
        }
    }
    if (isset($current_category_id) && $current_category_id>0) {
        if (!isset($_SESSION['featured_for'][$current_category_id])) {
            $_SESSION['featured_for'][$current_category_id] = '';
            if (function_exists('ingo_categories_info') && '1'!='1') {
                if (!isset($categories_info_array)) {
                    ingo_categories_info();
                }
                $featured_used_categories = $categories_info_array[$current_category_id]['sons'];
            } else {
                $featured_used_categories = $current_category_id;
                $temp_current = $current_category_id;
                if (tep_db_num_rows($featured_path_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$temp_current . "'"))) {
                    $temp_current = '';
                    while ($result = tep_db_fetch_array($featured_path_query)) {
                        $featured_used_categories .= ',' . $result['categories_id'];
                        $temp_current .= ($temp_current != '' ? ',':'') . $result['categories_id'];
                    }
                }
            }
            $featured_query = tep_db_query("select f.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_FEATURED_PRODUCTS . " f, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where f.products_id = p.products_id and p.products_status = '1' and f.status = '1' and p2c.products_id = p.products_id and find_in_set(p2c.categories_id, '" . $featured_used_categories . "') > 0 ");
            while ($result = tep_db_fetch_array($featured_query)) {
                $_SESSION['featured_for'][$current_category_id] .= ($_SESSION['featured_for'][$current_category_id] != '' ? ',':'') .  $result['products_id'];
            }
        }
    }
    $featured_products = $_SESSION['featured_for'][$current_category_id] != '' ? $_SESSION['featured_for'][$current_category_id] : $_SESSION['featured_for'][0];
    $featured_exclude = [];
}
