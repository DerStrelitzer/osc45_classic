<?php
/*
  latest_news.php v1.1.4 (i) by Ingo <www.strelitzer.de>  (by J0J0)

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

if (!function_exists('eval_buffer')) {
    function eval_buffer($string)
    {
        ob_start();
        eval("$string[2];");
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
}
if (!function_exists('eval_print_buffer')) {
    function eval_print_buffer($string)
    {
        ob_start();
        eval("print $string[2];");
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }
}
if (!function_exists('eval_html')) {
    function eval_html($string)
    {
        $string = preg_replace_callback("/(<\?=)(.*?)\?>/si", "eval_print_buffer", $string);
        return preg_replace_callback("/(<\?php|<\?)(.*?)\?>/si", "eval_buffer",$string);
    }
}

$category_depth = '';

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_NEWS));

require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$heading_image = 'table_background_specials.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td>
<!-- latest_news //-->
<?php

$listing_sql = "select news_id, headline, content, date_added from " . TABLE_LATEST_NEWS . " news where status = '1' and language = '" . (int)$_SESSION['languages_id']. "' order by date_added DESC";

$listing_split = new SplitPageResults($listing_sql, MAX_DISPLAY_LATEST_NEWS_PAGE);
$listing_numrows = $listing_split->number_of_rows;
$listing_query = tep_db_query($listing_split->sql_query);

if ( $listing_numrows > 0 && ( PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3') ) {
?>
          <div style="margin:5px;text-align:right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y')));?></div>
<?php
}

while ($listing = tep_db_fetch_array($listing_query)) {
?>
          <div class="divbox" style="margin-top:10px;">
            <div class="divboxheading" style="text-align:left;font-size:11px;padding:1px;">
              <div style="float:left"><?php echo $listing["headline"]; ?></div>
              <div style="text-align:right;font-weight:normal"><?php echo '<i>'.tep_date_long($listing["date_added"]) . '</i>'; ?></div>
              <div style="clear:both"></div>
            </div>
            <div style="margin:3px;"><?php echo nl2br(eval_html($listing["content"])); ?></div>
          </div>

<?php
}

if ( $listing_numrows > 0 && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3')) {
?>
          <div style="margin:5px;text-align:right"><?php echo TEXT_RESULT_PAGE . ' ' . $listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></div>
<?php
}
?>
<!-- latest_news_eof //-->
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <div style="text-align:right;margin:2px 12px"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
          </div>
        </td>
      </tr>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
