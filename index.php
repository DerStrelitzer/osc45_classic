<?php
/*
  $Id: index.php,v 1.1 2003/06/11 17:37:59 hpdl Exp $

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

$output = '';
if (isset($_GET['manufacturers_id']) && !is_numeric($_GET['manufacturers_id']=='')) {
    unset($_GET['manufacturers_id']);
}
// the following cPath references come from application_top.php
$category_depth = 'top';
if (isset($cPath) && tep_not_null($cPath)) {
    $categories_products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
    $cateqories_products = tep_db_fetch_array($categories_products_query);
    if ($cateqories_products['total'] > 0) {
        if ($cateqories_products['total'] == 1) {
            $query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
            $result = tep_db_fetch_array($query);
            tep_redirect(ingo_product_link($result['products_id']));
        }
        $category_depth = 'products'; // display products
    } else {
        $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "'");
        $category_parent = tep_db_fetch_array($category_parent_query);
        if ($category_parent['total'] > 0) {
            $category_depth = 'nested'; // navigate through the categories

// Ingo Beginn
            $category_query = tep_db_query("select c.categories_image, cd.categories_name, cd.categories_description from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
            $category = tep_db_fetch_array($category_query);
            if ($category['categories_description']!='') {
                $page_description = strip_tags($category['categories_description']);
            }
            define('HEADING_TITLE', '<a href="' . tep_href_link(FILENAME_ALL_PRODUCTS, 'cPath=' . ingo_make_link($current_category_id, 'c', $current_category_name)) . '">' . $current_category_name . '</a>');
            $heading_image_tag = tep_image(DIR_WS_IMAGES . $category['categories_image'], $category['categories_name'], HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'class="pageicon"');
            $output = "\n" . '
      <tr>
        <td>' . tep_draw_separator('pixel_trans.gif', '100%', '10') . '</td>
      </tr>' . (($category['categories_description']!='')? '
      <tr>
        <td class="main" style="padding: 3px;">' . $category['categories_description'] . '</td>
      </tr>' : '') . "\n" . '
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>';

            if (isset($cPath) && strpos('_', $cPath)) {
// check to see if there are deeper categories within the current category
                $category_links = array_reverse($cPath_array);
                for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
                    $categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
                    $categories = tep_db_fetch_array($categories_query);
                    if ($categories['total'] < 1) {
                        // do nothing, go through the loop
                    } else {
                        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by sort_order, cd.categories_name");
                        break; // we've found the deepest category the customer is in
                    }
                }
            } else {
                $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by sort_order, cd.categories_name");
            }

            $number_of_categories = tep_db_num_rows($categories_query);

            $rows = 0;
            while ($categories = tep_db_fetch_array($categories_query)) {
                $rows++;
                $cPath_new = tep_get_path($categories['categories_id']);
                $width = (int)(100 / MAX_DISPLAY_CATEGORIES_PER_ROW) . '%';
                $output .= '                <td align="center" class="smallText" width="' . $width . '" valign="top"><a href="' . ingo_category_link($cPath_new) . '">' . tep_image(DIR_WS_IMAGES . $categories['categories_image'], $categories['categories_name'], SUBCATEGORY_IMAGE_WIDTH, SUBCATEGORY_IMAGE_HEIGHT) . '<br />' . $categories['categories_name'] . '</a></td>' . "\n";
                $page_keywords .= ($page_keywords!=''?',':'') . $categories['categories_name'];
                if (($rows / MAX_DISPLAY_CATEGORIES_PER_ROW) == floor($rows / MAX_DISPLAY_CATEGORIES_PER_ROW) && $rows != $number_of_categories) {
                    $output .= '              </tr>' . "\n";
                    $output .= '              <tr>' . "\n";
                }
            }

// needed for the new products module shown below
            $new_products_category_id = $current_category_id;
            $output .= '
              </tr>
            </table></td>
          </tr>' . "\n";
// Ingo Ende

        } else {
            $category_depth = 'products'; // category has no products, but display the 'no products' message
        }
    }
}

// Ingo Beginn
if ($category_depth == 'products' || isset($_GET['manufacturers_id'])) {
// create column list
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
    foreach($define_list as $key => $value) {
        if ($value > 0) $column_list[] = $key;
    }

    $select_column_list = 'p.products_model, pd.products_description, m.manufacturers_name, ';

    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
        switch ($column_list[$i]) {
            case 'PRODUCT_LIST_MODEL':
            //$select_column_list .= 'p.products_model, ';
            break;

            case 'PRODUCT_LIST_NAME':
            $select_column_list .= 'pd.products_name, ';
            break;

            case 'PRODUCT_LIST_MANUFACTURER':
            //$select_column_list .= 'm.manufacturers_name, ';
            break;

            case 'PRODUCT_LIST_QUANTITY':
            $select_column_list .= 'p.products_quantity, ';
            break;

            case 'PRODUCT_LIST_IMAGE':
            $select_column_list .= 'p.products_image, ';
            break;

            case 'PRODUCT_LIST_WEIGHT':
            $select_column_list .= 'p.products_weight, ';
            break;
        }
    }

// show the products of a specified manufacturer
    if (isset($_GET['manufacturers_id'])) {
        if (isset($_GET['filter_id']) && intval($_GET['filter_id'])>0) {
            // We are asked to show only a specific category
            $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p2c.categories_id = '" . (int)$_GET['filter_id'] . "'";
        } else {
            // We show them all
            $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and pd.products_id = p.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'";
        }
    } else {
        // show the products in a given categorie
        if (isset($_GET['filter_id']) && intval($_GET['filter_id'])>0) {
            // We are asked to show only specific catgeory
            $listing_sql = 'select ' . $select_column_list . ' '
                . 'p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, '
                . 'IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, '
                . 'IF(s.status, s.specials_new_products_price, p.products_price) as final_price '
                . 'from ' . TABLE_PRODUCTS . ' p left join ' . TABLE_SPECIALS . ' s on p.products_id = s.products_id, ' 
                . TABLE_PRODUCTS_DESCRIPTION . ' pd, ' 
                . TABLE_MANUFACTURERS . ' m, ' 
                . TABLE_PRODUCTS_TO_CATEGORIES . ' p2c '
                . 'where p.products_status = 1 and p.manufacturers_id = m.manufacturers_id '
                . 'and m.manufacturers_id = ' . intval($_GET['filter_id']) . ' and p.products_id = p2c.products_id '
                . 'and pd.products_id = p2c.products_id and '
                . 'pd.language_id = ' . (int)$_SESSION['languages_id'] . ' and p2c.categories_id = ' . (int)$current_category_id;
        } else {
            // We show them all
            $listing_sql = 'select ' . $select_column_list . ' '
                . 'p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, '
                . 'IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, '
                . 'IF(s.status, s.specials_new_products_price, p.products_price) as final_price '
                . 'from ' . TABLE_PRODUCTS_DESCRIPTION . ' pd, ' 
                . TABLE_PRODUCTS . ' p left join ' . TABLE_MANUFACTURERS . ' m on p.manufacturers_id = m.manufacturers_id '
                . 'left join ' . TABLE_SPECIALS . ' s on p.products_id = s.products_id, ' 
                . TABLE_PRODUCTS_TO_CATEGORIES . ' p2c '
                . 'where p.products_status = 1 and p.products_id = p2c.products_id and pd.products_id = p2c.products_id '
                . 'and pd.language_id = ' . (int)$_SESSION['languages_id'] . ' and p2c.categories_id = ' . (int)$current_category_id;
        }
    }

    $sort_sql = '';
    if (isset($_GET['sort']) && intval(substr($_GET['sort'], 0, 1)) <= sizeof($column_list) && preg_match('/[1-8][ad]/', $_GET['sort'])) { 
        $sort_col   = substr($_GET['sort'], 0 , 1);
        $sort_order = substr($_GET['sort'], 1);
        switch ($column_list[$sort_col-1]) {
            case 'PRODUCT_LIST_MODEL':
            $sort_sql = 'p.products_model ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
            break;
            case 'PRODUCT_LIST_NAME':
            $sort_sql = 'pd.products_name ' . ($sort_order == 'd' ? 'desc' : '');
            break;
            case 'PRODUCT_LIST_MANUFACTURER':
            $sort_sql = 'm.manufacturers_name ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
            break;
            case 'PRODUCT_LIST_QUANTITY':
            $sort_sql = 'p.products_quantity ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
            break;
            case 'PRODUCT_LIST_IMAGE':
            $sort_sql = 'pd.products_name';
            break;
            case 'PRODUCT_LIST_WEIGHT':
            $sort_sql = 'p.products_weight ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
            break;
            case 'PRODUCT_LIST_PRICE':
            $sort_sql = 'final_price ' . ($sort_order == 'd' ? 'desc' : '') . ', pd.products_name';
            break;
        }
    } 
    if ($sort_sql=='') {
        for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
            if ($column_list[$i] == 'PRODUCT_LIST_NAME') {
                $_GET['sort'] = $i+1 . 'a';
                $sort_sql = 'pd.products_name';
                break;
            }
        }
    }
    if ($sort_sql!='') {
        $listing_sql .= ' order by ' . $sort_sql;
    }

    if (isset($_GET['cPath'])) {
        define('HEADING_TITLE', '<a href="' . tep_href_link(FILENAME_ALL_PRODUCTS, 'cPath=' . ingo_make_link($_GET['cPath'], 'c', $current_category_name)) . '">' . $current_category_name . '</a>');
    } elseif (isset($_GET['manufacturers_id']) && isset($manufacturers['manufacturers_name'])) {
        define('HEADING_TITLE', $manufacturers['manufacturers_name']);
    } else {
        define('HEADING_TITLE', '&nbsp;');
    }

// optional Product List Filter
    if (PRODUCT_LIST_FILTER > 0) {
        if (isset($_GET['manufacturers_id'])) {
            $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' order by cd.categories_name";
        } else {
            $filterlist_sql= "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by m.manufacturers_name";
        }
        $filterlist_query = tep_db_query($filterlist_sql);
        if (tep_db_num_rows($filterlist_query) > 1) {
            $output .= '
            <tr>
              <td width="100%" align="center" class="main">' . tep_draw_form('filter', FILENAME_DEFAULT, 'get') . TEXT_SHOW . '&nbsp;';
            if (isset($_GET['manufacturers_id'])) {
                $output .= tep_draw_hidden_field('manufacturers_id', $_GET['manufacturers_id']);
                $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));
            } else {
                $output .= tep_draw_hidden_field('cPath', $cPath);
                $options = array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));
            }
            $output .= tep_draw_hidden_field('sort', $_GET['sort']);
            while ($filterlist = tep_db_fetch_array($filterlist_query)) {
                $options[] = array('id' => $filterlist['id'], 'text' => $filterlist['name']);
            }
            $output .= tep_draw_pull_down_menu('filter_id', $options, (isset($_GET['filter_id']) ? $_GET['filter_id'] : ''), 'onchange="this.form.submit()"');
            $output .= tep_hide_session_id();
            $output .= "</form></td></tr>\n";
        }
    }

// Get the right image for the top-right
    $image = DIR_WS_IMAGES . 'desk/table_background_list.gif';
    $alt_text = HEADING_TITLE_WELCOME;
    if (isset($_GET['manufacturers_id'])) {
        $image = tep_db_query("select manufacturers_image, manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'");
        $image_result = tep_db_fetch_array($image);
        $image = 'manu/' . $image_result['manufacturers_image'];
        $alt_text = $image_result['manufacturers_name'];
    } elseif ($current_category_id) {
        $image = tep_db_query("select c.categories_image, cd.categories_name, cd.categories_description from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");
        $image_result = tep_db_fetch_array($image);
        $image = $image_result['categories_image'];
        $alt_text = $image_result['categories_name'];
        if (isset($image_result['categories_description']) && $image_result['categories_description']!='') {
            $output .= '
      <tr>
        <td class="main" style="padding: 5px;">' . $image_result['categories_description'] . '</td>
      </tr>' . "\n";
        }
    }

    $heading_image_tag = tep_image(DIR_WS_IMAGES . $image, $alt_text, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'class="pageicon"');

    $listing_split = new SplitPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id');
    if ($listing_split->number_of_rows > 0) {
        $listing_query = tep_db_query($listing_split->sql_query);
        while ($listing = tep_db_fetch_array($listing_query)) {
            if ($listing_split->number_of_rows == 1) {
                tep_redirect(ingo_product_link($listing['products_id'], $listing['products_name']));
            }
            $page_keywords .= ($page_keywords!=''?',':'') . strip_tags($listing['products_name']);
        }
        tep_db_data_seek($listing_query);
    }
}

$page_title = $title_path != '' ? $title_path : '';

require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
if (!defined('HEADING_TITLE')) {
    define('HEADING_TITLE', HEADING_TITLE_WELCOME);
    $heading_image = 'table_background_default.gif';
}
require(DIR_WS_INCLUDES . 'column_center.php');

if ($category_depth == 'nested') {
    echo $output;
?>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
           <td><?php include(DIR_WS_MODULES . FILENAME_FEATURED_PRODUCTS); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
    include (DIR_WS_INCLUDES . 'center_footer.php');
// Ingo Ende Austausch

} elseif ($category_depth == 'products' || isset($_GET['manufacturers_id'])) {

    echo $output;
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><?php include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING); ?></td>
      </tr>
<?php
    include (DIR_WS_INCLUDES . 'center_footer.php');

} else { // default page
  
    if (defined('READ_INFO_TEXTE_FROM_DATABASE') && READ_INFO_TEXTE_FROM_DATABASE=='ja') {
        $info_text = '';
        $text_query = tep_db_query('SELECT text FROM ' . TABLE_INFO_TEXTE . ' WHERE code = "main_page" AND languages_id = ' . (int)$_SESSION['languages_id']);
        if ($text_result = tep_db_fetch_array($text_query)) {
            $info_text = trim($text_result['text']);
        }
    } else {
        $info_text = TEXT_MAIN;
    }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
    if (isset($_SESSION['customer_id'])) {
?>
          <tr>
            <td class="main"><?php echo tep_customer_greeting(); ?></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '1'); ?></td>
          </tr>
<?php
    }
    if ($info_text!='') {
?>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '1'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo $info_text; ?></td>
          </tr>
<?php
    }
    if (!isset($_SESSION['customer_id'])) {
?>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '1'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo tep_customer_greeting(); ?></td>
          </tr>
<?php
    }
    if (defined('LATEST_NEWS_MODULE') && LATEST_NEWS_MODULE=='ja'){
?>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
          </tr>
          <tr>
           <td><?php include(DIR_WS_MODULES . 'latest_news.php'); ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '1'); ?></td>
          </tr>
          <tr>
            <td><?php include(DIR_WS_MODULES . FILENAME_FEATURED_PRODUCTS); ?></td>
          </tr>
<?php
    include(DIR_WS_MODULES . FILENAME_UPCOMING_PRODUCTS);
?>
        </table></td>
      </tr>
<?php
    include (DIR_WS_INCLUDES . 'center_footer.php');

}
?>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');

