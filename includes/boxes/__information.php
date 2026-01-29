<?php
/*
  $Id: information.php,v 1.6 2003/02/10 22:31:00 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- information //-->
          <tr>
            <td>
<?php
  $info_box_contents = [];
  $info_box_contents[] = array('text' => BOX_HEADING_INFORMATION);
  new infoBoxHeading ($info_box_contents, false, false);

  $info_box_contents = [];
  $info_box_contents[] = array('text' => '<a href="' . tep_href_link(FILENAME_SHIPPING) . '">' . BOX_INFORMATION_SHIPPING . '</a><br />' .
                                         '<a href="' . tep_href_link(FILENAME_WIDERRUF) . '">' . BOX_WIDERRUF . '</a><br />' .
                                         '<a href="' . tep_href_link(FILENAME_PRIVACY) . '">' . BOX_INFORMATION_PRIVACY . '</a><br />' .
                                         '<a href="' . tep_href_link(FILENAME_CONDITIONS) . '">' . BOX_INFORMATION_CONDITIONS . '</a><br />' .
                                         '<a href="' . tep_href_link(FILENAME_CONTACT_US) . '">' . BOX_INFORMATION_CONTACT . '</a><br />' .

                                         '<a href="' . tep_href_link(FILENAME_IMPRESSUM) . '">' . BOX_INFORMATION_IMPRESSUM . '</a>' );
  $loop = 1;
  while ((defined('BOX_INFORMATION_ABOUT_US_' . $loop)) && (is_file(DIR_FS_CATALOG . 'about_us_' . $loop . '.php')) ) {
    $info_box_contents[0]['text'] .= '<br /><a href="' . tep_href_link('about_us_' . $loop . '.php') . '">' . constant('BOX_INFORMATION_ABOUT_US_' . $loop) . '</a>';
    $loop++;
  }

  new infoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- information_eof //-->