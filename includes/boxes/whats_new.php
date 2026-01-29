<?php
/*
  $Id: whats_new.php,v 1.31 2003/02/10 22:31:09 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  if ($random_product = tep_random_select("select products_id, products_image, products_tax_class_id, products_price from " . TABLE_PRODUCTS . " where products_status = '1' order by products_date_added desc limit " . MAX_RANDOM_SELECT_NEW)) {
?>
<!-- whats_new //-->
          <tr>
            <td>
<?php
    $random_product['products_name'] = tep_get_products_name($random_product['products_id']);
    $random_product['specials_new_products_price'] = tep_get_products_special_price($random_product['products_id']);
    $whats_new_tax_rate = tep_get_tax_rate($random_product['products_tax_class_id']);

    $contents = array( 
      array(
        array(
          'params' => 'width="100%" class="infoBoxHeading"',
          'text' => '<a href="' . tep_href_link(FILENAME_PRODUCTS_NEW) . '">' . BOX_HEADING_WHATS_NEW . '</a>'
        )
      )
    );
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);

    if (tep_not_null($random_product['specials_new_products_price'])) {
      $whats_new_price = '<span class="striked">' . $currencies->display_price($random_product['products_price'], $whats_new_tax_rate) . '</span><br />';
      $whats_new_price .= '<span class="productSpecialPrice infoboxprice">' . ingo_make_euro($currencies->display_price($random_product['specials_new_products_price'], $whats_new_tax_rate)) . '</span>';
    } else {
      $whats_new_price = '<span class="infoboxprice">' . ingo_make_euro($currencies->display_price($random_product['products_price'], $whats_new_tax_rate)) . '</span>';
    }

    $whats_new_price .= ingo_price_added($whats_new_tax_rate);

    $info_box_contents = [];
    $info_box_contents[] = array('align' => 'center',
                                 'text' => '<a href="' . ingo_product_link($random_product['products_id'], $random_product['products_name']) . '">' . $thumbnail->get(DIR_WS_IMAGES . $random_product['products_image'], $random_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br />' .
                                           '<a href="' . ingo_product_link($random_product['products_id'], $random_product['products_name']) . '">' . $random_product['products_name'] . '</a><br />' . $whats_new_price);

    new InfoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- whats_new_eof //-->
<?php
  }
?>