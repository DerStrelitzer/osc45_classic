<?php
/*
  $Id: downloads.php,v 1.31 2003/06/09 22:49:58 hpdl Exp $
       controller included by Ingo (www.strelitzer.de)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- downloads, controlling by Ingo (www.strelitzer.de) //-->
<?php
if (!strstr($PHP_SELF, FILENAME_ACCOUNT_HISTORY_INFO)) {
// Get last order id for checkout_success
    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "' order by orders_id desc limit 1");
    $orders = tep_db_fetch_array($orders_query);
    $last_order = $orders['orders_id'];
} else {
    $last_order = $_GET['order_id'];
}

// DOWNLOAD_AVAILABLE_DATE & DOWNLOAD_MIN_STATUS im Admin setzen
$my_downloads_date = 'date_purchased';    // Download ab Einkauf
if (DOWNLOAD_AVAILABLE_DATE == '2') $my_downloads_date = 'last_modified';    // Download ab Bearbeitung der Bestellung (Statusänderung)
// debug:
// echo '<tr><td class="main"><br /><b>Debug</b></td></tr><tr><td><br />Mindeststatus: ' . $my_download_min_status . ' -- Downloaddatum:  ' . $my_downloads_date .  '<br />&nbsp;</td></tr>';

// Now get all downloadable products in that order
  $downloads_query = tep_db_query("select date_format($my_downloads_date, '%Y-%m-%d') as date_purchased_day, o.orders_status, opd.download_maxdays, op.products_name, opd.orders_products_download_id, opd.orders_products_filename, opd.download_count, opd.download_maxdays
      from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd
      where o.customers_id = '" . (int)$_SESSION['customer_id'] . "'
      and o.orders_id = '" . (int)$last_order . "'
      and o.orders_id = op.orders_id
      and op.orders_products_id = opd.orders_products_id
      and opd.orders_products_filename != ''");

  if (tep_db_num_rows($downloads_query) > 0) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><b><?php echo HEADING_DOWNLOAD; ?></b></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
<!-- list of products -->
<?php
    while ($downloads = tep_db_fetch_array($downloads_query)) {
// MySQL 3.22 does not have INTERVAL
      list($dt_year, $dt_month, $dt_day) = explode('-', $downloads['date_purchased_day']);
      $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads['download_maxdays'], $dt_year);
      $download_expiry = date('Y-m-d H:i:s', $download_timestamp);

// Der Link erscheint nur, wenn:
// - erforderliche Bestellstatus erreicht ist UND
// - erlaubte Downloads  > 0, UND
// - die bezeichnete Datei im DOWNLOADverzeichnis existiert, UND ENTWEDER
//   - Kein Verfallszeitraum angegeben (maxdays == 0), ODER
//   - Verfallszeitraum nicht erreicht
//
// debug:
// echo '<td>Debug:<br />Orderstatus: ' . $downloads['orders_status'] . ' Min.status: ' . $my_download_min_status . '<br /></td></tr><tr>';
      echo   '          <tr>' . "\n";

      if ( ($downloads['orders_status'] >= DOWNLOAD_MIN_STATUS) && ($downloads['download_count'] > 0) && (file_exists(DIR_FS_DOWNLOAD . $downloads['orders_products_filename'])) && ( ($downloads['download_maxdays'] == 0) || ($download_timestamp >= time())) ) {
        echo '            <td class="main"><a href="' . tep_href_link(FILENAME_DOWNLOAD, 'order=' . $last_order . '&id=' . $downloads['orders_products_download_id']) . '">' . $downloads['products_name'] . '</a></td>' . "\n" .
             '            <td class="main">' . TABLE_HEADING_DOWNLOAD_DATE . ' ' . tep_date_long($download_expiry) . '</td>' . "\n";
      } else {
        echo '            <td class="main">' . $downloads['products_name'] . '</td>' . "\n" .
             '            <td class="main">' . TABLE_HEADING_DOWNLOAD_DATE . '<span class="errortext"> '. DOWNLOAD_NOT_FREE . '</span></td>' . "\n";
      }

      echo   '            <td class="main" align="right">' . $downloads['download_count'] . ' ' . TABLE_HEADING_DOWNLOAD_COUNT . '</td>' . "\n" .
             '          </tr>' . "\n";

    }
?>
            </table>
          </div>
        </td>
      </tr>
<?php
    if (!strstr($PHP_SELF, FILENAME_ACCOUNT_HISTORY_INFO)) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="smalltext" colspan="4"><p><?php printf(FOOTER_DOWNLOAD, '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . HEADER_TITLE_MY_ACCOUNT . '</a>'); ?></p></td>
      </tr>
<?php
    }
  }
?>
<!-- downloads_eof //-->