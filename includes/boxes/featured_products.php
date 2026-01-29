<?php
/*
  $Id: featured_products.php,v 3.0 2006/02/23 by Ingo <http://forums.oscommerce.de/index.php?showuser=36>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License

*/

if (defined('FEATURED_PRODUCTS_BOX_DISPLAY') && FEATURED_PRODUCTS_BOX_DISPLAY == 'ja' && $featured_products!='') {

    if (isset($_GET['products_id']) && $_GET['products_id']>0) {
        $featured_exclude[] = (int)$_GET['products_id'];
    }
    $featured_array = explode(',', $featured_products);
    for ($i=0; $i<count($featured_exclude); $i++) {
        if (($j = array_search($featured_exclude[$i], $featured_array))!==false) {
            unset($featured_array[$j]);
        }
    }
    if (count($featured_array)>0) {
        shuffle($featured_array);
        $featured = $featured_array[0];
        $featured_query = tep_db_query("select p.products_id, p.products_image, p.products_tax_class_id, p.products_price, pd.products_name, IF(s.status, s.specials_new_products_price, NULL) as specials_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd WHERE p.products_id = '" . (int)$featured . "' and pd.products_id = p.products_id AND pd.language_id = '" . (int)$GLOBALS['languages_id'] . "'");
        $featured = tep_db_fetch_array($featured_query);
        $featured_exclude[] = $featured['products_id'];
?>
<!-- featured_products_box //-->
  <tr>
   <td>
<?php

    $contents = [ 
        [
            [
                'params' => 'width="100%" class="infoBoxHeading"',
                'text' => BOX_HEADING_FEATURED_PRODUCTS
            ]
        ]
    ];
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);

    $featured_tax_rate = tep_get_tax_rate($featured['products_tax_class_id']);

    $box_text = '<a href="' . ingo_product_link($featured['products_id'], $featured['products_name']) . '">' . tep_image(DIR_WS_IMAGES . $featured['products_image'], $featured['products_name'], ((SMALL_IMAGE_WIDTH>(BOX_WIDTH-8))?(BOX_WIDTH-8):(BOX_WIDTH-8))) . '</a><br />'
                . '<a href="' . ingo_product_link($featured['products_id'], $featured['products_name']) . '">' . $featured['products_name'] . '</a><br />';
    if (isset($featured['specials_price'])) {
        $box_text .= '<span class="striked">' . $currencies->display_price($featured['products_price'], $featured_tax_rate) . '</span><br />'
                   . '<span class="productSpecialPrice infoboxprice">' . ingo_make_euro($currencies->display_price($featured['specials_price'], $featured_tax_rate)) . '</span>';
    } else {
        $box_text .= '<span class="infoboxprice">' . ingo_make_euro($currencies->display_price($featured['products_price'], $featured_tax_rate)) . '</span>';
    }
    $box_text .= ingo_price_added($featured_tax_rate);

    $info_box_contents = [
        [
            'align'  =>  'center',
            'params' => '',
            'text'   =>  $box_text
        ]
    ];
    new InfoBox($info_box_contents);
?>
   </td>
  </tr>
<!-- featured_products_box_eof //-->
<?php
    }
}
