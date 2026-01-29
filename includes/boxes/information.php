<?php
/*
  $Id: information.php,v 1.6 2003/02/10 22:31:00 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- information //-->
          <tr>
            <td>
<?php

$contents = [ 
    [
        [
            'params' => 'width="100%" class="infoBoxHeading"',
            'text' => BOX_HEADING_INFORMATION
        ]
    ]
];
$box_heading = new TableBox;
$box_heading->set_param('cellpadding', 0);
$box_heading->get_box($contents, true);

$info_box_contents = [ 
    [
        'text' => ''
            . '<a href="' . tep_href_link(FILENAME_IMPRESSUM) . '" class="infoboxcontentlink">' . BOX_INFORMATION_IMPRESSUM . '</a>' . "\n"
            . '<a href="' . tep_href_link(FILENAME_CONDITIONS) . '" class="infoboxcontentlink">' . BOX_INFORMATION_CONDITIONS . '</a>' . "\n"
            . '<a href="' . tep_href_link(FILENAME_CONTACT_US) . '" class="infoboxcontentlink">' . BOX_INFORMATION_CONTACT . '</a>' . "\n"
            . '<a href="' . tep_href_link(FILENAME_SHIPPING) . '" class="infoboxcontentlink">' . BOX_INFORMATION_SHIPPING . '</a>' . "\n"
            . '<a href="' . tep_href_link(FILENAME_PRIVACY) . '" class="infoboxcontentlink">' . BOX_INFORMATION_PRIVACY . '</a>' . "\n"
            . '<a href="' . tep_href_link(FILENAME_ABOUT_US) . '" class="infoboxcontentlink">' . BOX_INFORMATION_ABOUT_US . '</a>' . "\n"
            . ''
    ]
];
new InfoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- information_eof //-->
