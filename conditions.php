<?php
/*
  $Id: conditions.php,v 1.22 2003/06/05 23:26:22 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_CONDITIONS));

if (defined('READ_INFO_TEXTE_FROM_DATABASE') && READ_INFO_TEXTE_FROM_DATABASE=='ja') {
    $info_text = '';
    $text_query = tep_db_query('SELECT text FROM ' . TABLE_INFO_TEXTE . ' WHERE code = "conditions" AND languages_id = ' . (int)$_SESSION['languages_id']);
    if ($text_result = tep_db_fetch_array($text_query)) {
        $info_text = trim($text_result['text']);
    }
} else {
    $info_text = TEXT_INFORMATION;
}

require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$heading_image = 'table_background_specials.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><div class="boxText"><?php echo $info_text; ?></div></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" colspan="2"><?php echo '<a href="' . tep_href_link(DIR_WS_LANGUAGES . $language . '/conditions.pdf') . '" target="_blank">' . TEXT_DOWNLOAD_CONDITIONS_PDF . '</a>'; ?></td>
          </tr>
          <tr>
             <td align="left" class="main"><?php echo TEXT_DOWNLOAD_ACROBAT; ?></td>
            <td align="right" width="100"><a href="http://www.adobe.de/products/acrobat/readstep2.html" target="_blank"><?php echo tep_image(DIR_WS_IMAGES . 'desk/getacrobat.gif'); ?></a></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
