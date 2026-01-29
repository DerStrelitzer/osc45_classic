<?php
/*
  $Id: show_links.php,v 1.00 2003/07/29 by Ingo (www.strelitzer.de)

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

if  (!isset($_GET['link_id'])) {
    $ersatz_query = tep_db_query("SELECT banners_id FROM " . TABLE_BANNERS . " WHERE banners_group = 'link_page' AND status = '1' order by banners_id");
    $ersatz =tep_db_fetch_array($ersatz_query);
    if ($ersatz['banners_id']==0) tep_redirect(tep_href_link(FILENAME_DEFAULT, 'error_message='.urlencode(ICON_ERROR . ': ID')));
    $_GET['link_id']=$ersatz['banners_id'];
}

$breadcrumb->add(HEADING_TITLE);

$link_query = tep_db_query("SELECT banners_title, banners_url, banners_html_text FROM " . TABLE_BANNERS . " WHERE banners_group = 'link_page' AND banners_id = '". (int)$_GET['link_id'] . "'");
$link_page = tep_db_fetch_array($link_query);

if ($link_page['banners_url']=='') tep_redirect(tep_href_link(FILENAME_DEFAULT,'error_message='.urlencode(ICON_ERROR . ': URL')));

// bei fehlermeldung, folgende 4 Zeilen auskommentieren und
//  1. weiter unten die Zeile 'echo "\n" . $link_text . "\n"; ' auch
//  2. die Zeile"include($link_page...." aktivieren
//
$link_text = '';
$link_file = fopen($link_page['banners_url'] . '/my_links.txt' , 'r');
while (!feof($link_file)) {
    $link_text .= fgets($link_file);
}
fclose ($link_file);

if ($link_text=='') tep_redirect(tep_href_link(FILENAME_DEFAULT,'error_message='.urlencode(ICON_ERROR.': HTML')));

$page_title = $link_page['banners_title'] . ' - ' . HEADING_TITLE;


require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$heading_image = 'table_background_browse.gif';
define('HEADING_TITLE', '<a href="' . $link_page['banners_url'] . '" target="_blank"><h1 class="hbig">' .  $link_page['banners_title'] . '</h1></a>');
require(DIR_WS_INCLUDES . 'column_center.php');
?>
    <tr>
      <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
    </tr>
     <tr>
      <td class="main" cellpadding="0">
        <div align="center">
          <b><span class="greetUser"><?php echo LINK_TEXT_DISCLAIMER; ?></b></span><br />
          <h2><?php echo $link_page['banners_html_text']; ?></h2></div></td>
    </tr>
    <tr>
      <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '5'); ?></td>
    </tr>
   <tr>
      <td>
<?php
echo "\n" . $link_text . "\n";
  // include($link_page['banners_url']. '/my_links.txt' );
?>
      </td>
    </tr>
    <tr>
      <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
    </tr>
    <tr>
       <td align="right" class="main"><a href="<?php echo tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></a></td>
     </tr>
     <tr>
      <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
     </tr>
    </table>
   </td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
