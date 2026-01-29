<?php
/*
  $Id: new_products.php,v 1.34 2003/06/09 22:49:58 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>

<!-- new_products //-->
<?php

$dateformatter = new IntlDateFormatter(
    DATE_FORMATTER_LOCALE,
    IntlDateFormatter::FULL,
    IntlDateFormatter::FULL,
    date_default_timezone_get(),
    IntlDateFormatter::GREGORIAN,
    'MMMM' 
);
$contents = [ 
    [
        [
            'params' => 'width="100%" class="infoBoxHeading"',
            'text' => '<a href="' . tep_href_link(FILENAME_PRODUCTS_NEW) . '">' . sprintf(TABLE_HEADING_NEW_PRODUCTS, $dateformatter->format(time())) . '</a>'
        ]
    ]
];
$box_heading = new TableBox;
$box_heading->set_param('cellpadding', 0);
$box_heading->get_box($contents, true);

if (!isset($new_products_category_id) || $new_products_category_id == '0') {
    $new_products_query = tep_db_query("select p.products_id, p.products_image, p.products_tax_class_id, if(s.status, s.specials_new_products_price, p.products_price) as products_price, p.products_date_added from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id where products_status = '1' order by p.products_date_added desc limit " . MAX_DISPLAY_NEW_PRODUCTS);
} else {
    $new_products_query = tep_db_query("select distinct p.products_id, p.products_image, p.products_tax_class_id, if(s.status, s.specials_new_products_price, p.products_price) as products_price, p.products_date_added from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.parent_id = '" . (int)$new_products_category_id . "' and p.products_status = '1' order by p.products_date_added desc limit " . MAX_DISPLAY_NEW_PRODUCTS);
}

$row = 0;
$col = 0;
$products_box_contents = [];
while ($new_products = tep_db_fetch_array($new_products_query)) {
    $new_products_tax_rate = tep_get_tax_rate($new_products['products_tax_class_id']);

    $new_products['products_name'] = tep_get_products_name($new_products['products_id']);
    $products_box_contents[$row][$col] = [
        'align'  => 'center',
        'params' => 'class="smallText" width="33%" valign="top"',
        'text'   => '<a href="' . ingo_product_link($new_products['products_id'], $new_products['products_name']) . '">' . $thumbnail->get(DIR_WS_IMAGES . $new_products['products_image'], $new_products['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br />'
                    . '<a href="' . ingo_product_link($new_products['products_id'], $new_products['products_name']) . '">' . $new_products['products_name'] . '</a><br />'
                    . $currencies->display_price($new_products['products_price'], $new_products_tax_rate) . ingo_price_added($new_products_tax_rate, false)
    ];

    $col ++;
    if ($col > 2) {
        $col = 0;
        $row ++;
    }
}
  
$products_box_contents[$row][$col] = [
    'align'  => 'center',
    'params' => 'class="smallText" colspan="3" width="100%" valign="top"',
    'text'   => ingo_price_added()
];
  
$content = new TableBox();
$content->set_param('cellpadding', 4);
$content->set_param('parameters', 'class="infoBoxContents"');
$content_box_contents = [['text' => $content->get_box($products_box_contents)]];
    
$content_box = new TableBox();
$content_box->set_param('cellpadding', 1);
$content_box->set_param('parameters', 'class="infoBox"');
$content_box->get_box($content_box_contents, true);

?>
<!-- new_products_eof //-->
