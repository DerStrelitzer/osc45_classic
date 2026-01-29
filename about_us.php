<?php
/*
  $Id: about_us.php, 2015/04/01 Ingo <www.strelitzer.de>

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

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(CURRENT_PAGE));

if (defined('READ_INFO_TEXTE_FROM_DATABASE') && READ_INFO_TEXTE_FROM_DATABASE=='ja') {
    $info_text = '';
    $text_query = tep_db_query('SELECT text FROM ' . TABLE_INFO_TEXTE . ' WHERE code = "about_us" AND languages_id = ' . (int)$_SESSION['languages_id']);
    if ($text_result = tep_db_fetch_array($text_query)) {
      $info_text = trim($text_result['text']);
    }
} else {
    $info_text = TEXT_ABOUT_US;
}
$page_keywords = $_SESSION['default_keywords'] . ', ' . HEADING_TITLE;

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
        <td class="main"><?php echo $info_text; ?></td>
      </tr>
<?php 
if (isset($_SESSION['navigation']) && is_object($_SESSION['navigation']) && isset($_SESSION['navigation']->referer[0])) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td> 
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td align="right"><?php echo '<a href="' . $_SESSION['navigation']->referer[0] . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK, 'style="margin-left:10px;"') . '</a>'; ?></td>
              </tr>
            </table> 
          </div>
        </td>
      </tr>
<?php
}
?>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
