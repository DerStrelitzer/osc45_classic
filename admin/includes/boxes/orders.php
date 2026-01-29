<?php
/*
  $Id: customers.php,v 1.16 2003/07/09 01:18:53 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- customers //-->
          <tr>
            <td>
<?php
  $heading = [];
  $contents = [];

  $heading[] = array(
    'text'  => BOX_HEADING_ORDERS,
    'link'  => tep_href_link(FILENAME_ORDERS, 'selected_box=orders')
  );

  if ($_SESSION['selected_box'] == 'orders') {
    $contents[0] = array(
      'text' => '<a href="' . tep_href_link(FILENAME_ORDERS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CUSTOMERS_ORDERS . '</a><br>' .
                '<a href="' . tep_href_link(FILENAME_CUSTOMERS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CUSTOMERS_CUSTOMERS . '</a><br>'
    );

    if (defined('FILENAME_CREATE_ORDER')) {
      $contents[0]['text'] .= '<a href="' . tep_href_link(FILENAME_CREATE_ORDER, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CUSTOMERS_CREATE_ORDER . '</a><br>';
      if (isset($_GET['oID'])) {
        $contents[0]['text'] .= '<a href="' . tep_href_link(FILENAME_EDIT_ORDERS, 'oID=' . $_GET['oID'], 'NONSSL') . '" class="menuBoxContentLink">' . BOX_CUSTOMERS_EDIT_ORDER . '</a><br>';
      }
    }
  }

  $box = new Box;
  echo $box->get_menu_box($heading, $contents);
?>
            </td>
          </tr>
<!-- customers_eof //-->
