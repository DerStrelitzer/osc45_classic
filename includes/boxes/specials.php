<?php
/*
  $Id: specials.php,v 1.31 2003/06/09 22:21:03 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if ($random_product = tep_random_select("select p.products_id, pd.products_name, p.products_price, p.products_tax_class_id, p.products_image, s.specials_new_products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_status = '1' and p.products_id = s.products_id and pd.products_id = s.products_id and pd.language_id = '" . (int)$GLOBALS['languages_id'] . "' and s.status = '1' order by s.specials_date_added desc limit " . MAX_RANDOM_SELECT_SPECIALS)) {
?>
<!-- specials //-->
          <tr>
            <td>
<?php
    $contents = [
        [
            [
                'params' => 'width="100%" class="infoBoxHeading"',
                'text' => '<a href="' . tep_href_link(FILENAME_SPECIALS) . '">' . BOX_HEADING_SPECIALS . '</a>'
            ]
        ]
    ];
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);

    $specials_tax_rate = tep_get_tax_rate($random_product['products_tax_class_id']);
    $info_box_contents = [
        [
            'align' => 'center',
            'text' => '<a href="' . ingo_product_link($random_product["products_id"], $random_product['products_name']) . '">' . $thumbnail->get(DIR_WS_IMAGES . $random_product['products_image'], $random_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br />' .
                  '<a href="' . ingo_product_link($random_product["products_id"], $random_product['products_name']) . '">' . $random_product['products_name'] . '</a><br />' .
                  '<span class="striked">' . $currencies->display_price($random_product['products_price'], $specials_tax_rate) . '</span><br />' .
                  '<span class="productSpecialPrice infoboxprice">' . ingo_make_euro($currencies->display_price($random_product['specials_new_products_price'], $specials_tax_rate)) . '</span>' .
                  ingo_price_added($specials_tax_rate)
        ]
    ];

    new InfoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- specials_eof //-->
<?php
}
