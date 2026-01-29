<?php
/*
  $Id: product_listing.php,v 1.44 2003/06/09 22:49:59 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if ($listing_split->number_of_rows > 0 && (PREV_NEXT_BAR_LOCATION == '1' || PREV_NEXT_BAR_LOCATION == '3')) {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td class="smallText"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
    <td class="smallText" align="right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
  </tr>
</table>
<?php
}

$list_box_contents = [];

for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {
    switch ($column_list[$col]) {
      case 'PRODUCT_LIST_MODEL':
        $lc_text = TABLE_HEADING_MODEL;
        $lc_align = '';
      break;
      case 'PRODUCT_LIST_NAME':
        $lc_text = TABLE_HEADING_PRODUCTS;
        $lc_align = '';
      break;
      case 'PRODUCT_LIST_MANUFACTURER':
        $lc_text = TABLE_HEADING_MANUFACTURER;
        $lc_align = '';
      break;
      case 'PRODUCT_LIST_PRICE':
        $lc_text = TABLE_HEADING_PRICE;
        $lc_align = 'right';
      break;
      case 'PRODUCT_LIST_QUANTITY':
        $lc_text = TABLE_HEADING_QUANTITY;
        $lc_align = 'right';
      break;
      case 'PRODUCT_LIST_WEIGHT':
        $lc_text = TABLE_HEADING_WEIGHT;
        $lc_align = 'right';
      break;
      case 'PRODUCT_LIST_IMAGE':
        $lc_text = TABLE_HEADING_IMAGE;
        $lc_align = 'center';
      break;
      case 'PRODUCT_LIST_BUY_NOW':
        $lc_text = TABLE_HEADING_BUY_NOW;
        $lc_align = 'center';
      break;
    }

    if ( ($column_list[$col] != 'PRODUCT_LIST_BUY_NOW') && ($column_list[$col] != 'PRODUCT_LIST_IMAGE') ) {
        $lc_text = tep_create_sort_heading($_GET['sort'], $col+1, $lc_text);
    }

    $list_box_contents[0][] = [
        'align'  => $lc_align,
        'params' => 'class="productListing-heading"',
        'text'   => '&nbsp;' . $lc_text . '&nbsp;'
    ];
}

if ($listing_split->number_of_rows > 0) {
    $rows = 0;
    // Ingo
    //$listing_query = tep_db_query($listing_split->sql_query);
    $next_page = (-1);
    while ($listing = tep_db_fetch_array($listing_query)) {

      $next_page = $next_page * (-1);
      $rows++;

      if (($rows/2) == floor($rows/2)) {
        $list_box_contents[] = array('params' => 'class="productListing-even"');
      } else {
        $list_box_contents[] = array('params' => 'class="productListing-odd"');
      }

      $cur_row = sizeof($list_box_contents) - 1;

      for ($col=0, $n=sizeof($column_list); $col<$n; $col++) {

        $lc_align = '';
        $lc_params = '';

        switch ($column_list[$col]) {
          case 'PRODUCT_LIST_MODEL':
            $lc_text = '&nbsp;' . $listing['products_model'] . '&nbsp;';
          break;
          case 'PRODUCT_LIST_NAME':
            $lc_params = 'valign="top"';
            $products_description = '';
            if (basename($PHP_SELF)==FILENAME_ADVANCED_SEARCH_RESULT && count($search_keywords)>0) {
              for ($i=0; $i<count($search_keywords); $i++) {
                $listing['products_name'] = preg_replace('/' . preg_quote($search_keywords[$i], '/') . '/i', '<span class="marksearchresults">\\0</span>', $listing['products_name']);
                $saetze = preg_split('/[\\.\\!\\?]/', $listing['products_description'], -1, PREG_SPLIT_NO_EMPTY);
                $products_description = '';
                for ($j=0; $j<sizeof($saetze); $j++) {
                  if (preg_match_all('/' . $search_keywords[$i] . '/i', $saetze[$j], $regs)) {
                    $products_description .= (($products_description!='')? ' ... ':'') . strip_tags($saetze[$j]);
                  }
                  if (strlen($products_description)>200) break;
                }
                $p = strlen($products_description);
                if ($p<100) {
                  $products_description = ($p>0? '( ...' . preg_replace('/' . preg_quote($search_keywords[$i], '/') . '/i', '<span class="marksearchresults">\\0</span>', $products_description) . ' ...)<br />':'') . ingo_cut_description(strip_tags($listing['products_description']), 200, $listing['products_id'], $listing['products_name']);
                } else {
                  $products_description = preg_replace('/' . preg_quote($search_keywords[$i], '/') . '/i', '<span class="marksearchresults">\\0</span>', $products_description);
                }
              }
            } else {
              $products_description = ingo_cut_description(strip_tags($listing['products_description']), 250, $listing['products_id'], $listing['products_name']);
            }
            $lc_text = '<h2 class="listingname"><a href="' . ingo_product_link($listing['products_id'], $listing['products_name']) . '">' . $listing['products_name'] . '</a></h2><br /><br />';
/*
            $lc_break = false;
            if (isset($listing['products_model'])&&$listing['products_model']!='') {
              $lc_text .= TABLE_HEADING_MODEL . ': ' . $listing['products_model'] . '<br />';
              $lc_break = true;
            }
            if (isset($listing['manufacturers_name'])&&$listing['manufacturers_name']!='') {
              $lc_text .= TABLE_HEADING_MANUFACTURER . ': ' . $listing['manufacturers_name'] . '<br />';
              $lc_break = true;
            }
            if ($lc_break) $lc_text .= '<br />';
*/
// Ingo Einfügung Produktbeschreibung
            $lc_text .= $products_description;
          break;
          case 'PRODUCT_LIST_MANUFACTURER':
            $lc_text = '&nbsp;<a href="' . tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $listing['manufacturers_id']) . '">' . $listing['manufacturers_name'] . '</a>&nbsp;';
          break;
          case 'PRODUCT_LIST_PRICE':
            $lc_align = 'right';
            $listing_tax_rate = tep_get_tax_rate($listing['products_tax_class_id']);
            if (tep_not_null($listing['specials_new_products_price'])) {
              $lc_text = '&nbsp;<span class="smallText">' . TEXT_SPECIAL_PRICE_OLD . ': '. ingo_make_euro($currencies->display_price($listing['products_price'], $listing_tax_rate)) . '</span>&nbsp; ' . TEXT_SPECIAL_PRICE_NOW . ': <span class="productSpecialPrice listingprice">' . ingo_make_euro($currencies->display_price($listing['specials_new_products_price'], $listing_tax_rate)) . '</span>&nbsp;';
            } else {
              $lc_text = '&nbsp;<span class="listingprice">' . ingo_make_euro($currencies->display_price($listing['products_price'], $listing_tax_rate)) . '</span>&nbsp;';
            }
            $lc_text .= ingo_price_added($listing_tax_rate);
// Ingo Kauf-Button unter dem Preis zeigen (1 Zeile):
            if (!$spider_flag) {
              $lc_text .= '<br /><a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing['products_id']) . '" onclick="javascript:cart_action(' . $listing['products_id'] . ',1);return false;">' . tep_image_button('button_buy_now.gif', IMAGE_BUTTON_BUY_NOW, 'style="margin:1px 3px;"') . '</a>';
            } else {
              $lc_text .= '<br /><a href="' . tep_href_link(basename($PHP_SELF), 'cPath=' . ingo_make_link($_GET['cPath'], 'c', (isset($current_category['categories_name'])? $current_category['categories_name']:'')) . '&page=' . ($listing_split->current_page_number+$next_page) ) . '">' . tep_image_button('button_buy_now.gif', IMAGE_BUTTON_BUY_NOW, 'style="margin:1px 3px;"') . '</a>';
            }
          break;
          case 'PRODUCT_LIST_QUANTITY':
            $lc_align = 'right';
            $lc_text = '&nbsp;' . $listing['products_quantity'] . '&nbsp;';
          break;
          case 'PRODUCT_LIST_WEIGHT':
            $lc_align = 'right';
            $lc_text = '&nbsp;' . $listing['products_weight'] . '&nbsp;';
            break;
          case 'PRODUCT_LIST_IMAGE':
            $lc_align = 'center';
            if (isset($_GET['manufacturers_id'])) {
              $lc_text = '<a href="' . ingo_product_link($listing['products_id'], $listing['products_name']) . '">' . $thumbnail->get(DIR_WS_IMAGES . $listing['products_image'], $listing['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'style="margin:1px 3px;"') . '</a>';
            } else {
              $lc_text = '<a href="' . ingo_product_link($listing['products_id'], $listing['products_name']) . '">' . $thumbnail->get(DIR_WS_IMAGES . $listing['products_image'], $listing['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'style="margin:1px 3px;"') . '</a>';
            }
          break;
          case 'PRODUCT_LIST_BUY_NOW':
            $lc_align = 'center';
            $lc_text = '<a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing['products_id']) . '">' . tep_image_button('button_buy_now.gif', IMAGE_BUTTON_BUY_NOW, 'style="margin:1px 3px;"') . '</a>';
          break;
        }

        $list_box_contents[$cur_row][] = array(
          'align'  => $lc_align,
          'params' => 'class="productListing-data"' . ($lc_params!=''? ' ' . $lc_params:''),
          'text'   => $lc_text
        );
      }
    }
?><table width="100%" cellpadding="3" cellspacing="0" border="0"><tr><td><?php
    $product_listing = new TableBox;
    $product_listing->set_param('parameters', 'class="productListing"');
    $product_listing->get_box($list_box_contents, true);
    
    $xmlhttp_module = true;
?></td></tr></table><?php

} else {
    $list_box_contents = [];

    $list_box_contents[0] = ['params' => 'class="productListing-odd"'];
    $list_box_contents[0][] = [
        'params' => 'class="productListing-data"',
        'text'   => TEXT_NO_PRODUCTS
    ];
?><table width="100%" cellpadding="3" cellspacing="0" border="0"><tr><td><?php
    $product_listing = new TableBox;
    $product_listing->set_param('parameters', 'class="productListing"');
    $product_listing->get_box($list_box_contents, true);
?></td></tr></table><?php
}

if ($listing_split->number_of_rows > 0 && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3')) {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td class="smallText"><?php echo $listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
    <td class="smallText" align="right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
  </tr>
</table>
<?php
}
