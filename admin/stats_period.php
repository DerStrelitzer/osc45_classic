<?php
/*
  $Id: stat_period.php,v 1.00 2006/03/25 by Ingo <www.strelitzer.de>

  xPrioS, Open Source E-Commerce Solutions
  http://www.xprios.de

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

$this_script_version = '1.0';

require('includes/application_top.php');

$first_query = tep_db_query("select date_format(date_purchased, '%Y') as year from " . TABLE_ORDERS . " order by orders_id limit 1");
if (tep_db_num_rows($first_query)) {
    $first_result = tep_db_fetch_array($first_query);
    $first = $first_result['year'];
} else {
    $first = date("Y");
}
$date_year_array = [];
for ($i=$first; $i<=date("Y"); $i++) {
    $date_year_array[] = [
        'id' => $i, 
        'text'=> $i
    ];
}
$date_month_array = [];
for ($i=1; $i<=12; $i++) {
    $date_month_array[] = [
        'id' => $i, 
        'text' => xprios_date_month_name($i)
    ];
}
$order_status_array = [];
$orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$_SESSION['languages_id'] . "' order by orders_status_id");
while ($result = tep_db_fetch_array($orders_status_query)) {
    $order_status_array[] = [
        'id' => $result['orders_status_id'], 
        'text' => $result['orders_status_name']
    ];
}

$status = [];

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action=='show' || $action=='download') {
    $month_from = xprios_prepare_post('month_from');
    $month_to   = xprios_prepare_post('month_to');
    $year_from  = xprios_prepare_post('year_from');
    $year_to    = xprios_prepare_post('year_to');
    if ($year_to<$year_from) $year_to = $year_from;
    if ($month_to<$month_from && $year_to<=$year_from) $month_to = $month_from;
    $status = xprios_prepare_post('status');

    $status_where = '';
    if (is_array($status) && count($status)>0) {
        $status_string = '';
        for ($i=0; $i<count($status); $i++) {
            $status_string .= ($status_string!='' ? ',':'') . (int)$status[$i];
        }
        $status_where = "o.orders_status in (" . $status_string . ") and ";
    }echo '<b>' . $status_where . '</b><br>';

    if (strlen($month_from)<2) $month_from = '0' . $month_from;
    if (strlen($month_to)<2) $month_to = '0' . $month_to;

    $query_create = "create temporary table if not exists statistic (id int auto_increment, name varchar(64), price decimal(15,4), sum_price decimal(15,4), tax decimal(7,4), quantity int, key idx_id(id), key idx_name(name))";
    $query_fields = "select op.products_id as id, op.products_name as name, op.final_price as price, sum(op.final_price) as sum_price, op.products_tax as tax, sum(op.products_quantity) as quantity";
    $query_from = " from " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS . " o";
    $query_where = " where " . $status_where . "date_format(o.date_purchased, '%Y%m') >= '" . $year_from . $month_from . "' and date_format(o.date_purchased, '%Y%m') <= '" . $year_to . $month_to . "' and o.orders_id=op.orders_id " ;
    $query_order = " group by op.products_id, op.products_tax order by op.products_id, op.products_tax";
    $query_prepare = $query_create . $query_fields . $query_from . $query_where . $query_order;
    tep_db_query($query_prepare);

    $statistic_query = tep_db_query("select id, name, price, sum_price, tax, quantity from statistic order by name");
    tep_db_query("drop table if exists statistic");

    if ($action=='download') {
        $from_to = ' ' . $month_from . '/' . $year_from . ' - ' . $month_to . '/' . $year_to;
        $listing_query = tep_db_query($query_fields . ', op.products_price ' . $query_from . $query_where . ' order by op.manufacturers_id, o.date_purchased, op.products_name');
        if (tep_db_num_rows($listing_query)>0) {
            header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
            header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
            header("Content-Type: text/plain");
            header("Content-disposition: attachment; filename=statistik_" . $month_from . "_" . $year_from . "-" . $month_to . "_" . $year_to . ".csv");
            $memory = '';
            while ($entry = tep_db_fetch_array($listing_query)) {
                if ($entry['manufacturers_id']!=$memory) {
                    if ($memory != '') echo "\n";
                    echo str_replace(',', ';', $sub_heading[$entry['manufacturers_id']]) . $from_to . "\n";
                    $memory = $entry['manufacturers_id'];
                }
                echo str_replace(',', ';', $entry['products_name']) . ', '
                . str_replace(',', ';', $entry['products_model']) . ', '
                . str_replace(',', ';', $entry['download']) . ', '
                . $entry['datum'] . ', '
                . str_replace(',', ';', $entry['payment_method']) . ', '
                . number_format($entry['products_price'],2) . ', '
                . number_format($entry['products_price']*(100+$entry['products_tax'])/100,2)
                . "\n";
            }
            exit();
        }
    }
} else {
    $month_from = date('n');
    $month_to   = date('n');
    $year_from = date('Y');
    $year_to = date('Y');
    $manufacturer = '';
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script type="text/javascript"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=100,height=100,screenX=50,screenY=50,top=50,left=50')
}
//--></script>
</head>
<body>
<?php
require(DIR_WS_INCLUDES . 'header.php');
?>

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<?php 
  require(DIR_WS_INCLUDES . 'column_left.php'); 
?>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
<?php
if ($action=='show') {
    if (tep_db_num_rows($statistic_query)) {
?>
      <tr>
        <td class="main"><b><?php echo ''; ?></b></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="headerBar">
                <td class="dataTableHeadingContent" align="center">&nbsp;</td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_QUANTITY; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_SINGLE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_SUM; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_GROSS; ?></td>
                <td class="dataTableHeadingContent" align="center">&nbsp;</td>
              </tr>
<?php
        $sum_quantity =
        $sum_netto = 
        $sum_brutto = 
        $sum_tax = 
        $rows = 0;
        while ($s = tep_db_fetch_array($statistic_query)) {
            $e_netto = round($s['price'], 2);
            $g_netto = round($e_netto * $s['quantity'], 2);
            $g_tax = round($e_netto * $s['quantity']*$s['tax']/100, 2);
            $g_brutto = $g_netto + $g_tax;
            $sum_quantity += $s['quantity'];
            $sum_netto += $g_netto;
            $sum_tax += $g_tax;
            $sum_brutto += $g_brutto;
            $rows++;
            $style_class = $rows/2 == floor($rows/2) ? 'dataTableRow' : 'dataTableRowSelected';
?>
              <tr class="<?php echo $style_class;?>">
                <td class="dataTableHeadingContent" align="center">&nbsp;</td>
                <td class="dataTableContent"><?php echo $s['name']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $s['quantity']; ?></td>
                <td class="dataTableContent" align="right">&nbsp;<?php echo $currencies->display_price($e_netto, 0); ?></td>
                <td class="dataTableContent" align="right">&nbsp;<?php echo $currencies->display_price($g_netto, 0); ?></td>
                <td class="dataTableContent" align="right">&nbsp;<?php echo $currencies->display_price($g_tax, 0); ?></td>
                <td class="dataTableContent" align="right">&nbsp;<?php echo $currencies->display_price($g_brutto, 0); ?></td>
                <td class="dataTableHeadingContent" align="center">&nbsp;</td>
              </tr>
<?php
        }
?>
              <tr class="headerBar">
                <td class="dataTableHeadingContent" align="center">&nbsp;</td>
                <td class="dataTableContent">&nbsp;</td>
                <td class="dataTableContent" align="center"><?php echo $sum_quantity; ?></td>
                <td class="dataTableContent" align="center">&nbsp;</td>
                <td class="dataTableContent" align="right">&nbsp;<?php echo $currencies->display_price($sum_netto, 0); ?></td>
                <td class="dataTableContent" align="right">&nbsp;<?php echo $currencies->display_price($sum_tax, 0); ?></td>
                <td class="dataTableContent" align="right">&nbsp;<?php echo $currencies->display_price($sum_brutto, 0); ?></td>
                <td class="dataTableHeadingContent" align="center">&nbsp;</td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
/*
      <tr>
       <td class="main" align="right">
        <?php echo tep_draw_form('load_stat', basename($PHP_SELF), 'action=download', 'get', 'enctype="multipart/form-data" target="_blank"') . "\n       ";
              echo tep_draw_hidden_field('month_from', $month_from) . tep_draw_hidden_field('year_from', $year_from) . "\n       ";
              echo tep_draw_hidden_field('month_to', $month_to) . tep_draw_hidden_field('year_to', $year_to) . "\n       ";
              echo tep_draw_hidden_field('manufacturer_id', $manufacturer_id) . tep_draw_hidden_field('action', 'download') . "\n       ";
              echo tep_image_submit('button_download.gif', TEXT_DETAIL . ' ' . ICON_FILE_DOWNLOAD) . "</form>\n";
         ?>
       </td>
      </tr>
<?php
*/
    } else {
?>
      <tr>
        <td class="pageHeading"><br><?php echo TEXT_NO_RESULT; if (!count($status)) echo '&nbsp;<span class="errorText">' . ERROR_SELECT_STATUS . '</span>'; ?></td>
      </tr>
      <tr>
        <td><?php  echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
    }
}
?>
      <tr>
        <td><?php echo tep_draw_form('new_stat', basename($PHP_SELF), 'action=show', 'post', 'enctype="multipart/form-data"'); ?><table>
          <tr>
            <td class="main" valign="top">
<?php
echo '              ' . WORD_FROM . ':&nbsp;' . tep_draw_pull_down_menu('month_from', $date_month_array, $month_from, 'size="1"') . " &nbsp; \n"
  . '              ' . tep_draw_pull_down_menu('year_from', $date_year_array, $year_from) . " &nbsp;\n"
  . '              ' . WORD_TILL . ':&nbsp;' . tep_draw_pull_down_menu('month_to', $date_month_array, $month_to) . " &nbsp;\n"
  . '              ' . tep_draw_pull_down_menu('year_to', $date_year_array, $year_to) 
  . ' &nbsp; ' . WORD_STATUSES . ":\n";
?>
            </td>
            <td valign="top" rowspan="2" class="main">
<?php
for ($i=0; $i<count($order_status_array); $i++) {
    $id = $order_status_array[$i]['id'];
    $checked = false;
    if (isset($_POST['status']) && is_array($_POST['status']) && in_array($id, $_POST['status'])) {
        $checked = true;
    }
    echo '              ' 
        . tep_draw_checkbox_field('status[]', $id, $checked) . '&nbsp;' . $order_status_array[$i]['text'] . "<br>\n";
}
?>
            </td>
          </tr>
          <tr>
            <td align="center"><?php echo tep_image_submit('button_search.gif', IMAGE_SEARCH); ?></td>
          </tr>
        </table></form></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td class="smallText" align="center"><?php echo '<b>' . basename($PHP_SELF) . '</b> v:' . $this_script_version . ', statistics-module &copy; ' . date('Y') . ' by <a href="http://forums.oscommerce.de/index.php?showuser=36" target="_blank">Ingo</a>' . tep_draw_separator('pixel_trans.gif', (int)BOX_WIDTH, '1'); ?></td>
  </tr>
</table>
<!-- body_text_eof //-->

<?php 
require(DIR_WS_INCLUDES . 'footer.php'); 
?>
</body>
</html>
<?php 
require(DIR_WS_INCLUDES . 'application_bottom.php'); 
