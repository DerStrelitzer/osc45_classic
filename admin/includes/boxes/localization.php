<?php
/*
  $Id: localization.php,v 1.16 2003/07/09 01:18:53 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- localization //-->
          <tr>
            <td>
<?php
  $heading = [];
  $contents = [];

  $heading[] = array(
    'text'  => BOX_HEADING_LOCALIZATION,
    'link'  => tep_href_link(FILENAME_CURRENCIES, 'selected_box=localization')
  );

  if ($_SESSION['selected_box'] == 'localization') {
    $contents[] = array(
      'text'  => '<a href="' . tep_href_link(FILENAME_CURRENCIES, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_LOCALIZATION_CURRENCIES . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_LANGUAGES, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_LOCALIZATION_LANGUAGES . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_ORDERS_STATUS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_LOCALIZATION_ORDERS_STATUS . '</a>'
    );
  }

  $box = new Box;
  echo $box->get_menu_box($heading, $contents);
?>
            </td>
          </tr>
<!-- localization_eof //-->
