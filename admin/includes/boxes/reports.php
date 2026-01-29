<?php
/*
  $Id: reports.php,v 1.5 2003/07/09 01:18:53 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- reports //-->
          <tr>
            <td>
<?php
  $heading = [];
  $contents = [];

  $heading[] = array(
    'text' => BOX_HEADING_REPORTS,
    'link' => tep_href_link(FILENAME_STATS_PRODUCTS_VIEWED, 'selected_box=reports')
  );

  if ($_SESSION['selected_box'] == 'reports') {
    $contents[] = array(
      'text' => '<a href="' . tep_href_link(FILENAME_STATS_PRODUCTS_VIEWED, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_REPORTS_PRODUCTS_VIEWED . '</a><br>' .
                '<a href="' . tep_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_REPORTS_PRODUCTS_PURCHASED . '</a><br>' .
                '<a href="' . tep_href_link(FILENAME_STATS_CUSTOMERS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_REPORTS_ORDERS_TOTAL . '</a><br>' .
                '<a href="' . tep_href_link(FILENAME_STATS_PERIOD, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_REPORTS_ORDERS_PERIOD . '</a>'
    );
  }

  $box = new Box;
  echo $box->get_menu_box($heading, $contents);
?>
            </td>
          </tr>
<!-- reports_eof //-->
