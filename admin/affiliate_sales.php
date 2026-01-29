<?php
/*
  $Id: affiliate_sales.php,v 1.6 2003/02/19 15:00:52 simarilius Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

$acID = intval(xprios_prepare_get('acID'));
  
if ($acID > 0) {

    $affiliate_sales_raw = "
      select asale.*, os.orders_status_name as orders_status, a.affiliate_firstname, a.affiliate_lastname from " . TABLE_AFFILIATE_SALES . " asale
      left join " . TABLE_ORDERS . " o on (asale.affiliate_orders_id = o.orders_id)
      left join " . TABLE_ORDERS_STATUS . " os on (o.orders_status = os.orders_status_id and language_id = " . (int)$_SESSION['languages_id'] . ")
      left join " . TABLE_AFFILIATE . " a on (a.affiliate_id = asale.affiliate_id)
      where asale.affiliate_id = '" . $acID . "'
      order by affiliate_date desc
      ";
      
} else {

    $affiliate_sales_raw = "
      select asale.*, os.orders_status_name as orders_status, a.affiliate_firstname, a.affiliate_lastname from " . TABLE_AFFILIATE_SALES . " asale
      left join " . TABLE_ORDERS . " o on (asale.affiliate_orders_id = o.orders_id)
      left join " . TABLE_ORDERS_STATUS . " os on (o.orders_status = os.orders_status_id and language_id = " . (int)$_SESSION['languages_id'] . ")
      left join " . TABLE_AFFILIATE . " a  on (a.affiliate_id = asale.affiliate_id)
      order by affiliate_date desc
      ";
}
$page = max(1, isset($_GET['page']) ? $_GET['page'] : '1');
$affiliate_sales_split = new SplitPageResults($page, MAX_DISPLAY_SEARCH_RESULTS, $affiliate_sales_raw, $affiliate_sales_numrows);
  
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 10, 1); ?></td>
<?php
  if ($acID > 0) {
?>
            <td class="pageHeading" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE_STATISTICS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
<?php
  } else {
?>
            <td class="pageHeading" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE_SUMMARY, '') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
<?php
  }
?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="4">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_AFFILIATE; ?></td>
            <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_DATE; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_ID; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_VALUE; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PERCENTAGE; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_SALES; ?></td>
            <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
          </tr>
<?php
  if ($affiliate_sales_numrows > 0) {
    $affiliate_sales_values = tep_db_query($affiliate_sales_raw);
    $number_of_sales = '0';
    while ($affiliate_sales = tep_db_fetch_array($affiliate_sales_values)) {
      $number_of_sales++;
      if (($number_of_sales / 2) == floor($number_of_sales / 2)) {
        echo '          <tr class="dataTableRowSelected">';
      } else {
        echo '          <tr class="dataTableRow">';
      }

      $link_to = '<a href="orders.php?action=edit&oID=' . $affiliate_sales['affiliate_orders_id'] . '">' . $affiliate_sales['affiliate_orders_id'] . '</a>';
?>
            <td class="dataTableContent"><?php echo $affiliate_sales['affiliate_firstname'] . " ". $affiliate_sales['affiliate_lastname']; ?></td>
            <td class="dataTableContent" align="center"><?php echo tep_date_short($affiliate_sales['affiliate_date']); ?></td>
            <td class="dataTableContent" align="right"><?php echo $link_to; ?></td>
            <td class="dataTableContent" align="right">&nbsp;&nbsp;<?php echo $currencies->display_price($affiliate_sales['affiliate_value'], ''); ?></td>
            <td class="dataTableContent" align="right"><?php echo $affiliate_sales['affiliate_percent'] . "%" ; ?></td>
            <td class="dataTableContent" align="right">&nbsp;&nbsp;<?php echo $currencies->display_price($affiliate_sales['affiliate_payment'], ''); ?></td>
            <td class="dataTableContent" align="center"><?php if ($affiliate_sales['orders_status']) echo $affiliate_sales['orders_status']; else echo TEXT_DELETED_ORDER_BY_ADMIN; ?></td>
<?php
    }
  } else {
?>
          <tr class="dataTableRowSelected">
            <td colspan="7" class="smallText"><?php echo TEXT_NO_SALES; ?></td>
          </tr>
<?php
  }
  if ($affiliate_sales_numrows > 0 && (PREV_NEXT_BAR_LOCATION == '2' || PREV_NEXT_BAR_LOCATION == '3')) {
?>
          <tr>
            <td colspan="7"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText" valign="top"><?php echo $affiliate_sales_split->display_count($affiliate_sales_numrows, MAX_DISPLAY_SEARCH_RESULTS, $page, TEXT_DISPLAY_NUMBER_OF_SALES); ?></td>
                <td class="smallText" align="right"><?php echo $affiliate_sales_split->display_links($affiliate_sales_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $page, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
              </tr>
            </table></td>
          </tr>
<?php
  }
?>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<?php 
require(DIR_WS_INCLUDES . 'footer.php'); 
?>
</body>
</html>
<?php 
require(DIR_WS_INCLUDES . 'application_bottom.php'); 
