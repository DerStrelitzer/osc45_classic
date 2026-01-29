<?php
/*
  $Id: all_products.php,v 3.02 2005/12/27 by Ingo (www.strelitzer.de)

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

$breadcrumb->add(HEADING_TITLE, tep_href_link(FILENAME_ALL_PRODUCTS, '', 'NONSSL'));

require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
$current_category_id = 0;
$this_language_code = (isset($_GET['language']) && tep_not_null($_GET['language'])) ? $_GET['language'] : DEFAULT_LANGUAGE;

$included_categories_query = tep_db_query("SELECT c.categories_id, c.parent_id, cd.categories_name, if(c.categories_id='" . $current_category_id . "', cd.categories_description, '') as description FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd WHERE c.categories_id = cd.categories_id AND cd.language_id = " . (int)$_SESSION['languages_id']);

$inc_cat = [];
while ($included_categories = tep_db_fetch_array($included_categories_query)) {
    $inc_cat[] = array (
          'id' => $included_categories['categories_id'],
      'parent' => $included_categories['parent_id'],
        'name' => $included_categories['categories_name']);
    if ($included_categories['categories_id']==$current_category_id) {
?>
      <tr>
       <td class="main" style="padding-left: 3px;"><?php echo $included_categories['description']; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
      </tr>
<?php
    }
}

$cat_info = [];
for ($i=0; $i<sizeof($inc_cat); $i++) {
    $cat_info[$inc_cat[$i]['id']] = [
        'parent'=> $inc_cat[$i]['parent'],
        'name'  => $inc_cat[$i]['name'],
        'path'  => $inc_cat[$i]['id'],
        'link'  => '',
        'sons'  => $inc_cat[$i]['id']
    ];
}
for ($i=0; $i<sizeof($inc_cat); $i++) {
    $cat_id = $inc_cat[$i]['id'];
    while ($cat_info[$cat_id]['parent'] != 0) {
        $cat_info[$cat_info[$cat_id]['parent']]['sons'] .= ( (strpos($cat_info[$cat_info[$cat_id]['parent']]['sons'],$cat_id)===false) ?     (($cat_info[$cat_info[$cat_id]['parent']]['sons']!='')?',':'')               . $cat_id : '');
        $cat_info[$inc_cat[$i]['id']]['path'] = $cat_info[$cat_id]['parent'] . '_' . $cat_info[$inc_cat[$i]['id']]['path'];
        $cat_id = $cat_info[$cat_id]['parent'];
    }
    $link_array = preg_split('/_/', $cat_info[$inc_cat[$i]['id']]['path'], -1, PREG_SPLIT_NO_EMPTY);
    for ($j=0; $j<sizeof($link_array); $j++) {
        $cat_info[$inc_cat[$i]['id']]['link'] .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . ingo_make_link( $cat_info[$link_array[$j]]['path'], 'c', $cat_info[$link_array[$j]]['name'])) . '">' . $cat_info[$link_array[$j]]['name'] . '</a> &raquo; ';
    }
}

$category_where = ($current_category_id != 0 ? "and find_in_set(pc.categories_id,'" . $cat_info[$current_category_id]['sons'] . "') > 0" : '') . (($spider_flag)? ' limit 300': ' ORDER BY pc.categories_id, pd.products_name');
$products_query = tep_db_query("SELECT p.products_id, pd.products_name, pc.categories_id FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " pc WHERE p.products_id = pd.products_id AND p.products_id = pc.products_id AND p.products_status = 1 AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "' " . $category_where);
?>
      <tr>
        <td><table width="100%" cellspacing="0" cellpadding="0" border="0">
<?php


$memory = '';
while($products = tep_db_fetch_array($products_query)) {
    echo
"          <tr>\n" .
'           <td width="33%" class="pageheading" style="padding-left: 3px;">' . ($memory == $products['categories_id'] ? ' ': '<h1 class="hsmall">' . $cat_info[$products['categories_id']]['link'] . '</h1>') . "</td>\n" .
'           <td class="pageheading"><h1 class="hsmall"><a href="' . ingo_product_link($products['products_id'], $products['products_name'], $this_language_code == DEFAULT_LANGUAGE ? '' : 'language=' . $this_language_code) . '">' . $products['products_name'] . "</a></h1></td>\n" .
"          </tr>\n";
    $memory = $products['categories_id'];
}

?>
         </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td> 
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE, 'style="margin-right:10px;"') . '</a>'; ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
