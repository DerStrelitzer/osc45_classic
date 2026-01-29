<?php
/*
  $Id: affiliate.php,v 1.2 2003/02/12 00:15:01 harley_vb Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- affiliates //-->
          <tr>
            <td>
<?php
  $heading = [];
  $contents = [];

  $heading[] = array(
    'text'  => BOX_HEADING_AFFILIATE,
    'link'  => tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('selected_box')) . 'selected_box=affiliate')
  );

  if ($_SESSION['selected_box'] == 'affiliate') {
    $contents[] = array(
      'text'  => '<a href="' . tep_href_link(FILENAME_AFFILIATE_SUMMARY, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_AFFILIATE_SUMMARY . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_AFFILIATE, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_AFFILIATE . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_AFFILIATE_PAYMENT, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_AFFILIATE_PAYMENT . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_AFFILIATE_SALES, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_AFFILIATE_SALES . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_AFFILIATE_CLICKS, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_AFFILIATE_CLICKS . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_AFFILIATE_BANNER_MANAGER, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_AFFILIATE_BANNERS . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_AFFILIATE_CONTACT, '', 'NONSSL') . '" class="menuBoxContentLink">' . BOX_AFFILIATE_CONTACT . '</a>'
     );
  }

  $box = new Box;
  echo $box->get_menu_box($heading, $contents);
?>
            </td>
          </tr>
<!-- affiliates_eof //-->