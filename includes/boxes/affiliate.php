<?php
/*
  $Id: affiliate.php,v 1.6 2003/02/22 01:20:39 harley_vb Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- affiliate_system //-->
          <tr>
            <td>
<?php

$contents = [ 
    [
        [
            'params' => 'width="100%" class="infoBoxHeading"',
            'text' => BOX_HEADING_AFFILIATE
        ]
    ]
];
$box_heading = new TableBox;
$box_heading->set_param('cellpadding', 0);
$box_heading->get_box($contents, true);

if (isset($_SESSION['affiliate_id'])) {
    $info_box_contents = [
        [
            'text' => '<a href="' . tep_href_link(FILENAME_AFFILIATE_SUMMARY, '', 'SSL') . '">' . BOX_AFFILIATE_SUMMARY . '</a><br />'
                . '<a href="' . tep_href_link(FILENAME_AFFILIATE_ACCOUNT, '', 'SSL'). '">' . BOX_AFFILIATE_ACCOUNT . '</a><br />'
                . '<a href="' . tep_href_link(FILENAME_AFFILIATE_PAYMENT, '', 'SSL'). '">' . BOX_AFFILIATE_PAYMENT . '</a><br />'
                . '<a href="' . tep_href_link(FILENAME_AFFILIATE_CLICKS, '', 'SSL'). '">' . BOX_AFFILIATE_CLICKRATE . '</a><br />'
                . '<a href="' . tep_href_link(FILENAME_AFFILIATE_SALES, '', 'SSL'). '">' . BOX_AFFILIATE_SALES . '</a><br />'
                . '<a href="' . tep_href_link(FILENAME_AFFILIATE_BANNERS). '">' . BOX_AFFILIATE_BANNERS . '</a><br />'
                . '<a href="' . tep_href_link(FILENAME_AFFILIATE_CONTACT). '">' . BOX_AFFILIATE_CONTACT . '</a><br />'
                . '<a href="' . tep_href_link(FILENAME_AFFILIATE_FAQ). '">' . BOX_AFFILIATE_FAQ . '</a><br />'
                . '<a href="' . tep_href_link(FILENAME_AFFILIATE_LOGOUT). '">' . BOX_AFFILIATE_LOGOUT . '</a>'
        ]
    ];
} else {
    $info_box_contents = [ 
        [
            'text' => '<a href="' . tep_href_link(FILENAME_AFFILIATE_INFO). '">' . BOX_AFFILIATE_INFO . '</a><br />'
                . '<a href="' . tep_href_link(FILENAME_AFFILIATE, '', 'SSL') . '">' . BOX_AFFILIATE_LOGIN . '</a>'
        ]
    ];
}

new InfoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- affiliate_system_eof //-->
