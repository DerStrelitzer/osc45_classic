<?php
/*
  $Id: advanced_search_result.php,v 1.72 2003/06/23 06:50:11 project3000 Exp $

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

$error = false;

if (isset($_GET['manufacturers_id']) && $_GET['manufacturers_id']=='') unset($_GET['manufacturers_id']);

if ( (isset($_GET['keywords']) && empty($_GET['keywords'])) &&
       (isset($_GET['dfrom']) && (empty($_GET['dfrom']) || ($_GET['dfrom'] == DOB_FORMAT_STRING))) &&
       (isset($_GET['dto']) && (empty($_GET['dto']) || ($_GET['dto'] == DOB_FORMAT_STRING))) &&
       (isset($_GET['pfrom']) && !is_numeric($_GET['pfrom'])) &&
       (isset($_GET['pto']) && !is_numeric($_GET['pto'])) ) {

    $error = true;
    $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);

} else {
    $dfrom = '';
    $dto = '';
    $pfrom = '';
    $pto = '';
    $keywords = '';
    $search_keywords = [];

    if (isset($_GET['dfrom'])) {
        $dfrom = (($_GET['dfrom'] == DOB_FORMAT_STRING) ? '' : $_GET['dfrom']);
    }

    if (isset($_GET['dto'])) {
        $dto = (($_GET['dto'] == DOB_FORMAT_STRING) ? '' : $_GET['dto']);
    }

    if (isset($_GET['pfrom'])) {
        $pfrom = $_GET['pfrom'];
    }

    if (isset($_GET['pto'])) {
        $pto = $_GET['pto'];
    }

    if (isset($_GET['keywords']) && $_GET['keywords']!='') {
        $keywords = xprios_prepare_get('keywords'); 
    }

    $date_check_error = false;
    if (tep_not_null($dfrom)) {
        if (!tep_checkdate($dfrom, DOB_FORMAT_STRING, $dfrom_array)) {
            $error = true;
            $date_check_error = true;
            $messageStack->add_session('search', ERROR_INVALID_FROM_DATE);
        }
    }

    if (tep_not_null($dto)) {
        if (!tep_checkdate($dto, DOB_FORMAT_STRING, $dto_array)) {
            $error = true;
            $date_check_error = true;
            $messageStack->add_session('search', ERROR_INVALID_TO_DATE);
        }
    }

    if (($date_check_error == false) && tep_not_null($dfrom) && tep_not_null($dto)) {
        if (mktime(0, 0, 0, $dfrom_array[1], $dfrom_array[2], $dfrom_array[0]) > mktime(0, 0, 0, $dto_array[1], $dto_array[2], $dto_array[0])) {
            $error = true;
            $messageStack->add_session('search', ERROR_TO_DATE_LESS_THAN_FROM_DATE);
        }
    }

    $price_check_error = false;
    if (tep_not_null($pfrom)) {
        if (!settype($pfrom, 'double')) {
            $error = true;
            $price_check_error = true;
            $messageStack->add_session('search', ERROR_PRICE_FROM_MUST_BE_NUM);
        }
    }

    if (tep_not_null($pto)) {
        if (!settype($pto, 'double')) {
            $error = true;
            $price_check_error = true;
            $messageStack->add_session('search', ERROR_PRICE_TO_MUST_BE_NUM);
        }
    }

    if (($price_check_error == false) && is_float($pfrom) && is_float($pto)) {
        if ($pfrom >= $pto) {
            $error = true;
            $messageStack->add_session('search', ERROR_PRICE_TO_LESS_THAN_PRICE_FROM);
        }
    }

    if (tep_not_null($keywords)) {
        if (!tep_parse_search_string($search_keywords, $keywords)) {
            $error = true;
            $messageStack->add_session('search', ERROR_INVALID_KEYWORDS);
        }
    }
}

if (empty($dfrom) && empty($dto) && empty($pfrom) && empty($pto) && empty($keywords)) {
    $error = true;
    $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
}

if ($error == true) {
    tep_redirect(tep_href_link(FILENAME_ADVANCED_SEARCH, tep_get_all_get_params(), 'NONSSL', true, false));
}

// create column list
$define_list = [
    'PRODUCT_LIST_MODEL'        => PRODUCT_LIST_MODEL,
    'PRODUCT_LIST_NAME'         => PRODUCT_LIST_NAME,
    'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
    'PRODUCT_LIST_PRICE'        => PRODUCT_LIST_PRICE,
    'PRODUCT_LIST_QUANTITY'     => PRODUCT_LIST_QUANTITY,
    'PRODUCT_LIST_WEIGHT'       => PRODUCT_LIST_WEIGHT,
    'PRODUCT_LIST_IMAGE'        => PRODUCT_LIST_IMAGE,
    'PRODUCT_LIST_BUY_NOW'      => PRODUCT_LIST_BUY_NOW
];

asort($define_list);

$column_list = [];
foreach ($define_list as $key => $value) {
    if ($value > 0) $column_list[] = $key;
}

$select_column_list = 'pd.products_description, ';

for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
    switch ($column_list[$i]) {
        case 'PRODUCT_LIST_MODEL':
            $select_column_list .= 'p.products_model, ';
        break;
        case 'PRODUCT_LIST_MANUFACTURER':
            $select_column_list .= 'm.manufacturers_name, ';
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

$select_str = "select distinct " . $select_column_list . " m.manufacturers_id, p.products_id, pd.products_name, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price ";

if ( DISPLAY_PRICE_WITH_TAX == 'ja' && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    $select_str .= ", SUM(tr.tax_rate) as tax_rate ";
}

$from_str = "from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m using(manufacturers_id) left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id";

if (DISPLAY_PRICE_WITH_TAX == 'ja' && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    if (isset($_SESSION['customer_country_id'])) {
        $country_id = $_SESSION['customer_country_id'];
        $zone_id    = $_SESSION['customer_zone_id'];
    } else {
        $country_id = STORE_COUNTRY;
        $zone_id    = STORE_ZONE;
    }
    $from_str .= " left join " . TABLE_TAX_RATES . " tr on p.products_tax_class_id = tr.tax_class_id left join " . TABLE_ZONES_TO_GEO_ZONES . " gz on tr.tax_zone_id = gz.geo_zone_id and (gz.zone_country_id is null or gz.zone_country_id = '0' or gz.zone_country_id = '" . (int)$country_id . "') and (gz.zone_id is null or gz.zone_id = '0' or gz.zone_id = '" . (int)$zone_id . "')";
}

$from_str .= ", " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c";

$where_str = " where p.products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id ";

if (isset($_GET['categories_id']) && tep_not_null($_GET['categories_id'])) {
    if (isset($_GET['inc_subcat']) && $_GET['inc_subcat'] == '1') {
        $subcategories_array = [];
        tep_get_subcategories($subcategories_array, $_GET['categories_id']);

        $where_str .= " and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and (p2c.categories_id = '" . (int)$_GET['categories_id'] . "'";

        for ($i=0, $n=sizeof($subcategories_array); $i<$n; $i++ ) {
            $where_str .= " or p2c.categories_id = '" . (int)$subcategories_array[$i] . "'";
        }

        $where_str .= ")";
    } else {
        $where_str .= " and p2c.products_id = p.products_id and p2c.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p2c.categories_id = '" . (int)$_GET['categories_id'] . "'";
    }
}

if (isset($_GET['manufacturers_id']) && tep_not_null($_GET['manufacturers_id'])) {
    $where_str .= " and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'";
}

if (isset($search_keywords) && (sizeof($search_keywords) > 0)) {
    $where_str .= " and (";
    for ($i=0, $n=sizeof($search_keywords); $i<$n; $i++ ) {
        switch ($search_keywords[$i]) {
            case '(':
            case ')':
            case 'and':
            case 'or':
                $where_str .= " " . $search_keywords[$i] . " ";
            break;
            default:
            $keyword = $search_keywords[$i];
            $where_str .= "(pd.products_name like '%" . tep_db_input($keyword) . "%' or p.products_model like '%" . tep_db_input($keyword) . "%' or m.manufacturers_name like '%" . tep_db_input($keyword) . "%'";
            // Ingo if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1'))
            $where_str .= " or pd.products_description like '%" . tep_db_input($keyword) . "%'";
            $where_str .= ')';
            break;
        }
    }
    $where_str .= " )";
}

if (tep_not_null($dfrom)) {
    $where_str .= " and p.products_date_added >= '" . tep_date_raw($dfrom) . "'";
}

if (tep_not_null($dto)) {
    $where_str .= " and p.products_date_added <= '" . tep_date_raw($dto) . "'";
}

if (tep_not_null($pfrom)) {
    if ($currencies->is_set($currency)) {
        $rate = $currencies->get_value($currency);
        $pfrom = $pfrom / $rate;
    }
}

if (tep_not_null($pto)) {
    if (isset($rate)) {
        $pto = $pto / $rate;
    }
}

if (DISPLAY_PRICE_WITH_TAX == 'ja') {
    if ($pfrom > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) >= " . (double)$pfrom . ")";
    if ($pto > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) * if(gz.geo_zone_id is null, 1, 1 + (tr.tax_rate / 100) ) <= " . (double)$pto . ")";
} else {
    if ($pfrom > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) >= " . (double)$pfrom . ")";
    if ($pto > 0) $where_str .= " and (IF(s.status, s.specials_new_products_price, p.products_price) <= " . (double)$pto . ")";
}

if ( DISPLAY_PRICE_WITH_TAX == 'ja' && (tep_not_null($pfrom) || tep_not_null($pto)) ) {
    $where_str .= " group by p.products_id, tr.tax_priority";
}

if ( (!isset($_GET['sort'])) || (!preg_match('/[1-8ad]/', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) ) {
    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
        if ($column_list[$i] == 'PRODUCT_LIST_NAME') {
            $_GET['sort'] = $i+1 . 'a';
            $order_str = ' order by pd.products_name';
            break;
        }
    }
} else {
    $sort_col = substr($_GET['sort'], 0 , 1);
    $sort_order = substr($_GET['sort'], 1);
    $order_str = ' order by ';
    switch ($column_list[$sort_col-1]) {
        case 'PRODUCT_LIST_MODEL':
            $order_str .= "p.products_model " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
            break;
        case 'PRODUCT_LIST_NAME':
            $order_str .= "pd.products_name " . ($sort_order == 'd' ? "desc" : "");
            break;
        case 'PRODUCT_LIST_MANUFACTURER':
            $order_str .= "m.manufacturers_name " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
            break;
        case 'PRODUCT_LIST_QUANTITY':
            $order_str .= "p.products_quantity " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
            break;
        case 'PRODUCT_LIST_IMAGE':
            $order_str .= "pd.products_name";
            break;
        case 'PRODUCT_LIST_WEIGHT':
            $order_str .= "p.products_weight " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
            break;
        case 'PRODUCT_LIST_PRICE':
            $order_str .= "final_price " . ($sort_order == 'd' ? "desc" : "") . ", pd.products_name";
            break;
    }
}

$listing_sql = $select_str . $from_str . $where_str . $order_str;

if (!isset($search_keywords)) $search_keywords = [];

// Suchtechnologie © 2015 by Ingo, www.strelitzer.de
//
$alter = [];
if (!isset($_GET['page']) && count($search_keywords)>0 && mb_strlen($search_keywords[0], CHARSET)>2) {
    $fuzzy_key = mb_strtolower($search_keywords[0], CHARSET);
    $keylen = mb_strlen($fuzzy_key, CHARSET);
    $matchrating = 60;
    $min_key_len = 3;
    $fuzzy_query = tep_db_query("select concat_ws(',', replace(products_name, ' ', ','), replace(products_description, ' ', ',')) as object from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int)$_SESSION['languages_id'] . "'");
    while ($fuzzy = tep_db_fetch_array($fuzzy_query)) {
        $word_array = explode (",", $fuzzy['object']);
        $x = sizeof($word_array);
        for ($i=0; $i<$x; $i++) {
            $word = trim($word_array[$i]);
            if (mb_strlen($word, CHARSET) < $min_key_len) break;
            while ( !preg_match('/([a-z0-9])/', substr($word,0,1)) ) {
                $word = substr($word, 1);
                if (mb_strlen($word, CHARSET) < $min_key_len) break 2;
            }
            while ( !preg_match('/([a-z0-9])/', substr($word,-1)) ) {
                $word = substr($word,0,-1);
                if (mb_strlen($word, CHARSET) < $min_key_len) break 2;
            }
            if (mb_strlen($word, CHARSET)>=$min_key_len && similar_text(mb_strtolower($word, CHARSET), $fuzzy_key, $diff) && ($diff >= $matchrating) && ($diff<100) && (!in_array($word, $alter)) && (!in_array(mb_strtolower($word, CHARSET), $alter))) {
                $alter[] = $word;
                break;
            }
        }
    }
    sort($alter);
    reset($alter);
    if (count($alter)==0) {
        $check_query = tep_db_query($listing_sql);
        if (!tep_db_num_rows($check_query)) {
            $keywords = urldecode($_GET['keywords']);
            tep_redirect(tep_href_link(FILENAME_CONTACT_US, 'keywords=' . $keywords));
        }
    }
}
//
// Suchtechnologie © 2015 by Ingo, www.strelitzer.de

$page_keywords = $_SESSION['default_keywords'];
$listing_split = new SplitPageResults($listing_sql, MAX_DISPLAY_SEARCH_RESULTS, 'p.products_id');
if ($listing_split->number_of_rows > 0) {
    $listing_query = tep_db_query($listing_split->sql_query);
    while ($listing = tep_db_fetch_array($listing_query)) {
        $page_keywords .= ($page_keywords!=''?',':'') . str_replace('"', "'", strip_tags($listing['products_name']));
    }
    tep_db_data_seek($listing_query);
}

$breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ADVANCED_SEARCH));
$breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, tep_get_all_get_params(), 'NONSSL', true, false));

require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$heading_image = 'table_background_browse.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
if (count($alter)>0) {
?>
      <!-- Suchtechnologie © 2015 by Ingo, www.strelitzer.de -->
      <tr>
        <td class="main">
<?php
    echo '<b>' . DO_YOU_MEAN_ALTER . ':</b><br />';
    $x=sizeof($alter);
    for ($i=0; $i<$x; $i++) echo ($i!=0?', ':'') . '<a href="' . tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords=' . $alter[$i]) . '">' . $alter[$i] . '</a>';
    echo  " <b>?</b>\n";
?>
        </td>
      </tr>
      <!-- Suchtechnologie © 2015 by Ingo, www.strelitzer.de -->
<?php
}
?>
      <tr>
        <td>
<?php
require(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING);
?>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td> 
          <div class="divbox">
            <div style="margin:2px 12px;"><?php echo '<a href="' . tep_href_link(FILENAME_ADVANCED_SEARCH, tep_get_all_get_params(array('sort', 'page')), 'NONSSL', true, false) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
          </div>
        </td>
      </tr>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
