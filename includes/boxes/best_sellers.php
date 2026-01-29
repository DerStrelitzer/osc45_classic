<?php
/*
  $Id: best_sellers.php,v 1.21 2003/06/09 22:07:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if (isset($current_category_id) && ($current_category_id > 0)) {
    $best_sellers_query = tep_db_query("select distinct p.products_id, p.products_ordered, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c where p.products_status = '1' and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = '" . (int)$GLOBALS['languages_id'] . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and '" . (int)$current_category_id . "' in (c.categories_id, c.parent_id) order by p.products_ordered desc, pd.products_name limit " . MAX_DISPLAY_BESTSELLERS);
} else {
    $best_sellers_query = tep_db_query("select distinct p.products_id, p.products_ordered, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = '" . (int)$GLOBALS['languages_id'] . "' order by p.products_ordered desc, pd.products_name limit " . MAX_DISPLAY_BESTSELLERS);
}

if (tep_db_num_rows($best_sellers_query) >= MIN_DISPLAY_BESTSELLERS) {
?>
<!-- best_sellers //-->
          <tr>
            <td>
<?php
    $contents = [ 
        [
            [
                'params' => 'width="100%" class="infoBoxHeading"',
                'text' => BOX_HEADING_BESTSELLERS
            ]
        ]
    ];
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);

    $rows = 0;
    $bestsellers_list = '<table border="0" width="100%" cellspacing="0" cellpadding="1">' . "\n";
    while ($best_sellers = tep_db_fetch_array($best_sellers_query)) {
        $rows++;
        $bestsellers_list .= '  <tr>' . "\n"
        . '    <td class="boxText" valign="top">' . tep_row_number_format($rows) . '.</td>'
        . '    <td class="boxText"><a href="' . ingo_product_link($best_sellers['products_id'], $best_sellers['products_name']) . '" class="infoboxcontentlink">' . $best_sellers['products_name'] . '</a></td>'
        . '  </tr>' . "\n";
    }
    $bestsellers_list .= '</table>' . "\n";

    $info_box_contents = [
        [
            'text' => $bestsellers_list
        ]
    ];
    new InfoBox($info_box_contents);

?>
            </td>
          </tr>
<!-- best_sellers_eof //-->
<?php
}
