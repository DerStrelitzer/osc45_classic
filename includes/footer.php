<?php
/*
  $Id: footer.php,v 1.26 2003/02/10 22:30:54 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

?>
  </tr>
  <tr>
    <td class="columncenter bodyfooter" valign="bottom"><br /><?php echo FOOTER_TEXT_BODY; ?></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php
$counter_query = tep_db_query('select startdate, counter from ' . TABLE_COUNTER);
if ($counter = tep_db_fetch_array($counter_query)) {
    $counter_startdate = $counter['startdate'];
    $counter_now = $counter['counter'] + 1;
    tep_db_query("update " . TABLE_COUNTER . " set counter = '" . $counter_now . "'");
} else {
    $counter_startdate = date('Ymd');
    $counter_now = 1;
    tep_db_query('insert into ' . TABLE_COUNTER . ' (startdate, counter) values ("' . $counter_startdate . '", "1")');
}
$counter_startdate_formatted = tep_date_short(substr($counter_startdate, 0, 4) . '-' . substr($counter_startdate, 4, 2) . '-' . substr($counter_startdate, 6, 2) . ' 00:00:00');
?>
<table border="0" width="100%" cellspacing="0" cellpadding="1">
  <tr class="footer">
    <td class="footer">&nbsp;&nbsp;<?php echo tep_date_long(time()); ?>&nbsp;&nbsp;</td>
    <td align="right" class="footer">&nbsp;&nbsp;<?php echo $counter_now . ' ' . FOOTER_TEXT_REQUESTS_SINCE . ' ' . $counter_startdate_formatted; ?>&nbsp;&nbsp;</td>
  </tr>
</table>
<?php
if (tep_banner_exists('dynamic','link_page')) {
?>
<!-- Ingo's link_page -->
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="footer">
  <tr class="main">
      <td align="right" class="main" border="1">&nbsp;&nbsp;<b>Links:</b>&nbsp;</td>
      <td width="100%" class="main"><?php echo "\n";
    $link_query = tep_db_query("SELECT banners_id, banners_title, banners_html_text FROM " . TABLE_BANNERS . " WHERE banners_group = 'link_page' AND status = '1'");
    while ($link = tep_db_fetch_array($link_query)) {
        echo '    <a href="' . tep_href_link('show_links.php','link_id=' . $link['banners_id']) . '" title="' . $link['banners_html_text'] . '"><nobr><b>' . $link['banners_title'] . "</b></nobr></a>&nbsp;\n";
    }
?>
  </td>
 </tr>
</table>
<!-- Ingo's link_page -->
<?php
}

$footer_link_sql = 0;
$footer_link_query = tep_db_query("SELECT banners_title, banners_url, banners_html_text from " . TABLE_BANNERS . " where banners_group = 'footer' AND status = '1' order by rand()");
if (tep_db_num_rows($footer_link_query)) {
    echo '<table border="0" width="100%" cellspacing="0" cellpadding="0" class="footer">' . "\n  <tr>\n" . '    <td class="bodyfooter" align="center">empfohlene Webseiten: ';
    while ($footer_link = tep_db_fetch_array($footer_link_query)) {
        echo (($footer_link_sql != 0) ? ' - ':'');
        if ($footer_link['banners_html_text']!='') {
            echo $footer_link['banners_html_text'];
        } else {
            echo '<a href="' . $footer_link['banners_url'] . '" target="_blank" class="bodyfooter">' . $footer_link['banners_title'] . '</a>';
        }
        $footer_link_sql++;
    }
    echo "\n    </td>\n  </tr>\n</table>\n";
}

if ($banner = tep_banner_exists('dynamic', '468x50')) {
?>
<table border="0" width="100%" cellspacing="0" cellpadding="6" class="footer">
  <tr>
    <td align="center"><?php echo tep_display_banner('static', $banner); ?></td>
  </tr>
</table>
<?php
}
if (isset($xmlhttp_module) && $xmlhttp_module == true) {
    include ('includes/xmlhttp.js.php');
}
?>
<!-- footer_eof //-->
<!-- You are not the only one, John! -->
</div>
</body>
</html>
<?php 
require(DIR_WS_INCLUDES . 'application_bottom.php'); 
