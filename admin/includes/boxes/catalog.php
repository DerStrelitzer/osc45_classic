<?php
/*
  $Id: catalog.php,v 1.21 2003/07/09 01:18:53 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- catalog //-->
          <tr>
            <td>
<?php
  $heading = [];
  $contents = [];

  $heading[] = array(
    'text'  => BOX_HEADING_CATALOG,
    'link'  => tep_href_link(FILENAME_CATEGORIES, 'selected_box=catalog')
  );

  if ($_SESSION['selected_box'] == 'catalog') {
    $contents[] = array(
      'text'  => '<a href="' . tep_href_link(FILENAME_CATEGORIES, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CATALOG_CATEGORIES_PRODUCTS . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CATALOG_CATEGORIES_PRODUCTS_ATTRIBUTES . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_MANUFACTURERS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CATALOG_MANUFACTURERS . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_REVIEWS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CATALOG_REVIEWS . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_SPECIALS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CATALOG_SPECIALS . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_PRODUCTS_EXPECTED, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CATALOG_PRODUCTS_EXPECTED . '</a><br>' .
                 '<a href="' . tep_href_link('imagecheck.php', '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CATALOG_MISSING_IMAGES . '</a>'
    );

    if (defined('LATEST_NEWS_BOX')) {
      $contents[0]['text'] .= '<br><a href="' . tep_href_link(FILENAME_LATEST_NEWS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CATALOG_LATEST_NEWS . '</a>';
    }
    if (defined('FEATURED_PRODUCTS_BOX_DISPLAY')) {
      $contents[sizeof($contents)-1]['text'] .= '<br><a href="' . tep_href_link(FILENAME_FEATURED_PRODUCTS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CATALOG_FEATURED_PRODUCTS . '</a>';
    }
  }
  $box = new Box;
  echo $box->get_menu_box($heading, $contents);
?>
            </td>
          </tr>
<!-- catalog_eof //-->