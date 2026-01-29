<?php
/*
  $Id: affiliate_summary.php,v 1.12 2003/03/13 17:13:22 simarilius Exp $

  OSC-Affiliate

  Contribution based on:

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

if (!isset($_SESSION['affiliate_id'])) {
    xprios_set_snapshot();
    tep_redirect(tep_href_link(FILENAME_AFFILIATE, '', 'SSL'));
}

$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_AFFILIATE_SUMMARY));

$affiliate_banner_history_raw = 'select sum(affiliate_banners_shown) as count from ' . TABLE_AFFILIATE_BANNERS_HISTORY .  ' where affiliate_banners_affiliate_id  = ' . intval($_SESSION['affiliate_id']);
$affiliate_banner_history_query = tep_db_query($affiliate_banner_history_raw);
$affiliate_banner_history = tep_db_fetch_array($affiliate_banner_history_query);
$affiliate_impressions = $affiliate_banner_history['count'];
if ($affiliate_impressions == 0) $affiliate_impressions="n/a";

$affiliate_clickthroughs_raw = 'select count(*) as count from ' . TABLE_AFFILIATE_CLICKTHROUGHS . ' where affiliate_id = ' . intval($_SESSION['affiliate_id']);
$affiliate_clickthroughs_query = tep_db_query($affiliate_clickthroughs_raw);
$affiliate_clickthroughs = tep_db_fetch_array($affiliate_clickthroughs_query);
$affiliate_clickthroughs =$affiliate_clickthroughs['count'];

$affiliate_sales_raw = 
	    'select count(*) as count, sum(affiliate_value) as total, sum(affiliate_payment) as payment from ' . TABLE_AFFILIATE_SALES . ' a
			left join ' . TABLE_ORDERS . ' o on (a.affiliate_orders_id=o.orders_id)
			where a.affiliate_id = ' . intval($_SESSION['affiliate_id']) . ' and o.orders_status >= ' . intval(AFFILIATE_PAYMENT_ORDER_MIN_STATUS);
$affiliate_sales_query = tep_db_query($affiliate_sales_raw);
$affiliate_sales = tep_db_fetch_array($affiliate_sales_query);

$affiliate_transactions=$affiliate_sales['count'];
if ($affiliate_clickthroughs > 0) {
    $affiliate_conversions = tep_round(($affiliate_transactions / $affiliate_clickthroughs)*100, 2) . "%";
} else {
    $affiliate_conversions = "n/a";
}
$affiliate_amount = $affiliate_sales['total'];
if ($affiliate_transactions>0) {
    $affiliate_average = tep_round($affiliate_amount / $affiliate_transactions, 2);
} else {
    $affiliate_average = "n/a";
}
$affiliate_commission = $affiliate_sales['payment'];

$affiliate_values = tep_db_query('select * from ' . TABLE_AFFILIATE . ' where affiliate_id = ' . intval($_SESSION['affiliate_id']));
$affiliate = tep_db_fetch_array($affiliate_values);
$affiliate_percent = 0;
$affiliate_percent = $affiliate['affiliate_commission_percent'];
if ($affiliate_percent < AFFILIATE_PERCENT) $affiliate_percent = AFFILIATE_PERCENT;

$this_head_include = "
<script type=\"text/javascript\" src=\"includes/general.js\"></script>
<script type=\"text/javascript\"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=450,height=150,screenX=150,screenY=150,top=150,left=150')
}
//--></script>";
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_GREETING . $affiliate['affiliate_firstname'] . ' ' . $affiliate['affiliate_lastname'] . '<br />' . TEXT_AFFILIATE_ID . ' ' . $_SESSION['affiliate_id']; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="infoboxheading"><?php echo TEXT_SUMMARY_TITLE; ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0" cellpadding="4" cellspacing="2">
              <center>
                <tr>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_IMPRESSIONS; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP, 'id=1') . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $affiliate_impressions; ?></td>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_VISITS; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP, 'id=2') . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $affiliate_clickthroughs; ?></td>
                </tr>
                <tr>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_TRANSACTIONS; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP, 'id=3') . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $affiliate_transactions; ?></td>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_CONVERSION; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP, 'id=4') . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $affiliate_conversions;?></td>
                </tr>
                <tr>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_AMOUNT; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP, 'id=5') . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $currencies->display_price($affiliate_amount, ''); ?></td>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_AVERAGE; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP, 'id=6') . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $currencies->display_price($affiliate_average, ''); ?></td>
                </tr>
                <tr>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_COMMISSION_RATE; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP, 'id=7') . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo tep_round($affiliate_percent, 2). '%'; ?></td>
                  <td width="35%" align="right" class="boxtext"><?php echo TEXT_COMMISSION; ?><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_AFFILIATE_HELP, 'id=8') . '\')">' . TEXT_SUMMARY_HELP . '</a>'; ?></td>
                  <td width="15%" class="boxtext"><?php echo $currencies->display_price($affiliate_commission, ''); ?></td>
                </tr>
                <tr>
                  <td colspan="4"><?php echo tep_draw_separator(); ?></td>
                </tr>
                 <tr>
                  <td align="center" class="boxtext" colspan="4"><b><?php echo TEXT_SUMMARY; ?><b></td>
                </tr>
                <tr>
                  <td colspan="4"><?php echo tep_draw_separator(); ?></td>
                </tr>
                <tr>
                  <td align="right" colspan="4"><?php echo '<a href="' . tep_href_link(FILENAME_AFFILIATE_BANNERS, '') . '">' . tep_image_button('button_affiliate_banners.gif', IMAGE_BANNERS) . '</a> <a href="' . tep_href_link(FILENAME_AFFILIATE_CLICKS, '') . '">' . tep_image_button('button_affiliate_clickthroughs.gif', IMAGE_CLICKTHROUGHS) . '</a> <a href="' . tep_href_link(FILENAME_AFFILIATE_SALES, '','SSL') . '">' . tep_image_button('button_affiliate_sales.gif', IMAGE_SALES) . '</a>'; ?></td>
                </tr>
              </center>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
