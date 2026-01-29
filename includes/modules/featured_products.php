<?php
/*
  $Id: featured_products.php,v 3.0 by Ingo <http://forums.oscommerce.de/index.php?showuser=36>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License

*/

define('FEATURED_PRODUCTS_MODUL_COLS', 1);

if  (defined('FEATURED_PRODUCTS_MODUL_DISPLAY') && FEATURED_PRODUCTS_MODUL_DISPLAY == 'ja' && $featured_products!='') {

    $contents = [
        [
            [
                'params' => 'width="100%" class="infoBoxHeading"',
                'text' => isset($category['categories_name']) ? sprintf(TABLE_HEADING_FEATURED_PRODUCTS_CATEGORY, $category['categories_name']) : TABLE_HEADING_FEATURED_PRODUCTS
            ]
        ]
    ];
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);
    
    $featured_array = explode(',', $featured_products);
    shuffle($featured_array);
    if (count($featured_array)>MAX_DISPLAY_FEATURED_PRODUCTS) {
        $featured_array = array_slice($featured_array, 0, MAX_DISPLAY_FEATURED_PRODUCTS);
    }
    $featured_products_modul = implode(',', $featured_array);
    $featured_query = tep_db_query("select "
        . "p.products_id, p.products_image, p.products_tax_class_id, p.products_price, "
        . "pd.products_name, pd.products_description, IF(s.status, s.specials_new_products_price, NULL) as specials_price "
        . "from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd "
        . "WHERE p.products_id in (" . tep_db_input($featured_products_modul) . ") "
        . "and pd.products_id = p.products_id and pd.language_id = '" . (int)$GLOBALS['languages_id'] . "'"
    );
?>
<!-- featured_products //-->
<?php
    $row = 0; $col = 0;
    $featured_box_contents = [];
    while ($featured = tep_db_fetch_array($featured_query)) {

        if (in_array($featured['products_id'], $featured_exclude)) continue;
        $featured_exclude[] = $featured['products_id'];

        $featured_mod_tax_rate = tep_get_tax_rate($featured['products_tax_class_id']);
        $featured_mod_preis_zusatz = ingo_price_added($featured_mod_tax_rate);

        if (FEATURED_PRODUCTS_MODUL_COLS == 1) {
            $f_image = '<a href="' . ingo_product_link($featured['products_id'], $featured['products_name']) . '">' . tep_image(DIR_WS_IMAGES . $featured['products_image'], $featured['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a>';
            $f_name  =  '<h2 class="hsmall"><a href="' . ingo_product_link($featured['products_id'], $featured['products_name']) . '">' . $featured['products_name'] . '</a></h2>';
            $f_name .= '<br />' . ingo_cut_description(strip_tags($featured['products_description']), 250, $featured['products_id'], $featured['products_name']);
            if (isset($featured['specials_price'])) {
                $f_button = '<span class="striked">' . $currencies->display_price($featured['products_price'], $featured_mod_tax_rate) . '</span><br /><span class="productSpecialPrice listingprice">' . ingo_make_euro($currencies->display_price($featured['specials_price'], $featured_mod_tax_rate)) . '</span>';
            } else {
                $f_button = '<span class="listingprice">' . ingo_make_euro($currencies->display_price($featured['products_price'], $featured_mod_tax_rate)) . '</span>';
            }
            $f_button .= $featured_mod_preis_zusatz . '<br /><br />';
            if ($spider_flag) {
                $f_button .= '<a href="' . ingo_product_link($featured['products_id'], $featured['products_name']) . '">' . tep_image_button('button_buy_now.gif', IMAGE_BUTTON_BUY_NOW) . '</a>&nbsp;';
            } else {
                $f_button .= '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $featured['products_id']) . '">' . tep_image_button('button_buy_now.gif', IMAGE_BUTTON_BUY_NOW) . '</a>&nbsp;';
            }
            $featured_box_contents[] = [$f_image, $f_name, $f_button];
        } else {
            $featured_box_contents[$row][$col] = [
                'align'  => 'center',
                'params' => 'class="smallText" width="' . intval(100/FEATURED_PRODUCTS_MODUL_COLS) . '%" valign="top"',
                'text'   => '<a href="' . ingo_product_link($featured['products_id'], $featured['products_name']) . '">' . tep_image(DIR_WS_IMAGES . $featured['products_image'], $featured['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br />'
                            . ' <a href="' . ingo_product_link($featured['products_id'], $featured['products_name']) . '">' . $featured['products_name'] . '</a><br />' .
                            (isset($featured['specials_price']) ?
                            '<span class="striked">' . $currencies->display_price($featured['products_price'], $featured_mod_tax_rate) . '</span><br /><span class="productSpecialPrice">' . $currencies->display_price($featured['specials_price'], $featured_mod_tax_rate) . '</span>'
                            :
                            $currencies->display_price($featured['products_price'], $featured_mod_tax_rate)
                            ) . $featured_mod_preis_zusatz
            ];
        }
        $col ++;
        if ($col > (FEATURED_PRODUCTS_MODUL_COLS-1)) {$col = 0; $row ++;}
    }

    if (FEATURED_PRODUCTS_MODUL_COLS == 1) {
?>
                <table width="100%" cellspacing="0" cellpadding="0" class="productListing">
                  <!--tr>
                    <td width="100%" class="infoBoxHeading" style="padding:0"><?php echo $info_box_heading[0]['text']; ?></td>
                  </tr -->
                  <tr>
                    <td><table border="0" width="100%" cellspacing="0" cellpadding="0" class="infoBox">
<?php
        $ci = count($featured_box_contents);
        for ($i=0; $i<$ci; $i++) {
            if (($i/2) == floor($i/2)) {
                $this_tr_style = 'productListing-even';
            } else {
                $this_tr_style = 'productListing-odd';
            }
?>
                      <tr class="<?php echo $this_tr_style; ?>">
                        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
                          <tr>
                            <td width="<?php echo SMALL_IMAGE_WIDTH + 10; ?>" valign="middle" class="main"><?php echo $featured_box_contents[$i][0]; ?></td>
                            <td valign="top" class="main"><?php echo $featured_box_contents[$i][1]; ?></td>
                            <td align="center" valign="bottom" class="main"><?php echo $featured_box_contents[$i][2]; ?></td>
                          </tr>
                        </table></td>
                      </tr>
<?php
        }
?>
                    </table></td>
                  </tr>
                </table>
<?php
    } else {
        $content = new TableBox();
        $content->set_param('cellpadding', 4);
        $content->set_param('parameters', 'class="infoBoxContents"');
        $content_box_contents = [['text' => $content->get_box($featured_box_contents)]];
      
        $content_box = new TableBox();
        $content_box->set_param('cellpadding', 1);
        $content_box->set_param('parameters', 'class="infoBox"');
        $content_box->get_box($content_box_contents, true);
    }
?>
<!-- featured_products_eof //-->
<?php
} else { // if disabled, include the original New Products box
      include (DIR_WS_MODULES . FILENAME_NEW_PRODUCTS);
}
