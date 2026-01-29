<?php
/*
  $Id: tax_rates.php,v 1.30 2003/06/29 22:50:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'insert':
        $tax_zone_id  = intval(xprios_prepare_post('tax_zone_id'));
        $tax_class_id = intval(xprios_prepare_post('tax_class_id'));
        $tax_rate     = max(0, floatval(xprios_prepare_post('tax_rate')));
        $tax_description = xprios_prepare_post('tax_description');
        $tax_priority = max(1, intval(xprios_prepare_post('tax_priority')));
        $sql_data = [
            'tax_zone_id'     => $tax_zone_id,
            'tax_class_id'    => $tax_class_id,
            'tax_rate'        => $tax_rate,
            'tax_description' => $tax_description,
            'tax_priority'    => $tax_priority
        ];
        tep_db_perform(TABLE_TAX_RATES, $sql_data);
        tep_redirect(tep_href_link(FILENAME_TAX_RATES, tep_get_all_get_params(['action'])));
    break;

    case 'save':
        $tax_rates_id = intval(xprios_prepare_get('tID'));
        $tax_zone_id  = intval(xprios_prepare_post('tax_zone_id'));
        $tax_class_id = intval(xprios_prepare_post('tax_class_id'));
        $tax_rate     = max(0, floatval(xprios_prepare_post('tax_rate')));
        $tax_description = xprios_prepare_post('tax_description');
        $tax_priority = max(1, intval(xprios_prepare_post('tax_priority')));
        $sql_data = [
            'tax_zone_id'     => $tax_zone_id,
            'tax_class_id'    => $tax_class_id,
            'tax_rate'        => $tax_rate,
            'tax_description' => $tax_description,
            'tax_priority'    => $tax_priority
        ];
        tep_db_perform(TABLE_TAX_RATES, $sql_data, 'update', 'tax_rates_id = ' . $tax_rates_id);
        tep_redirect(tep_href_link(FILENAME_TAX_RATES, tep_get_all_get_params(['action'])));
    break;

    case 'deleteconfirm':
        $tax_rates_id = intval(xprios_prepare_get('tID'));

        tep_db_query("delete from " . TABLE_TAX_RATES . " where tax_rates_id = '" . $tax_rates_id . "'");

        tep_redirect(tep_href_link(FILENAME_TAX_RATES, tep_get_all_get_params(['action'])));
    break;
  }

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script type="text/javascript" src="includes/general.js"></script>
</head>
<body onload="SetFocus();">
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
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_RATE_PRIORITY; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_CLASS_TITLE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_ZONE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TAX_RATE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
$page = max(1, isset($_GET['page']) ? $_GET['page'] : '1');
$rates_query_raw = "select r.tax_rates_id, z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified from " . TABLE_TAX_CLASS . " tc, " . TABLE_TAX_RATES . " r left join " . TABLE_GEO_ZONES . " z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id";
$rates_split = new SplitPageResults($page, MAX_DISPLAY_SEARCH_RESULTS, $rates_query_raw, $rates_query_numrows);
$rates_query = tep_db_query($rates_query_raw);
while ($rates = tep_db_fetch_array($rates_query)) {
    if ((!isset($_GET['tID']) || (isset($_GET['tID']) && ($_GET['tID'] == $rates['tax_rates_id']))) && !isset($trInfo) && (substr($action, 0, 3) != 'new')) {
        $trInfo = new ObjectInfo($rates);
    }

    if (isset($trInfo) && is_object($trInfo) && ($rates['tax_rates_id'] == $trInfo->tax_rates_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $page . '&tID=' . $trInfo->tax_rates_id . '&action=edit') . '\'">' . "\n";
    } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $page . '&tID=' . $rates['tax_rates_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $rates['tax_priority']; ?></td>
                <td class="dataTableContent"><?php echo $rates['tax_class_title']; ?></td>
                <td class="dataTableContent"><?php echo $rates['geo_zone_name']; ?></td>
                <td class="dataTableContent"><?php echo tep_display_tax_value($rates['tax_rate']); ?>%</td>
                <td class="dataTableContent" align="right"><?php if (isset($trInfo) && is_object($trInfo) && ($rates['tax_rates_id'] == $trInfo->tax_rates_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $page . '&tID=' . $rates['tax_rates_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $rates_split->display_count($rates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $page, TEXT_DISPLAY_NUMBER_OF_TAX_RATES); ?></td>
                    <td class="smallText" align="right"><?php echo $rates_split->display_links($rates_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $page); ?></td>
                  </tr>
<?php
if ($action == '') {
?>
                  <tr>
                    <td colspan="5" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $page . '&action=new') . '">' . tep_image_button('button_new_tax_rate.gif', IMAGE_NEW_TAX_RATE) . '</a>'; ?></td>
                  </tr>
<?php
}
?>
                </table></td>
              </tr>
            </table></td>
<?php
$heading = [];
$contents = [];

switch ($action) {
    case 'new':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_TAX_RATE . '</b>');

        $contents = array('form' => tep_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&action=insert'));
        $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_TITLE . '<br>' . tep_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . tep_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE . '<br>' . tep_draw_input_field('tax_rate'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_RATE_DESCRIPTION . '<br>' . tep_draw_input_field('tax_description'));
        $contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE_PRIORITY . '<br>' . tep_draw_input_field('tax_priority'));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . '&nbsp;<a href="' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
    break;

    case 'edit':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_TAX_RATE . '</b>');

        $contents = array('form' => tep_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=save'));
        $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CLASS_TITLE . '<br>' . tep_tax_classes_pull_down('name="tax_class_id" style="font-size:10px"', $trInfo->tax_class_id));
        $contents[] = array('text' => '<br>' . TEXT_INFO_ZONE_NAME . '<br>' . tep_geo_zones_pull_down('name="tax_zone_id" style="font-size:10px"', $trInfo->geo_zone_id));
        $contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE . '<br>' . tep_draw_input_field('tax_rate', $trInfo->tax_rate));
        $contents[] = array('text' => '<br>' . TEXT_INFO_RATE_DESCRIPTION . '<br>' . tep_draw_input_field('tax_description', $trInfo->tax_description));
        $contents[] = array('text' => '<br>' . TEXT_INFO_TAX_RATE_PRIORITY . '<br>' . tep_draw_input_field('tax_priority', $trInfo->tax_priority));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
    break;

    case 'delete':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_TAX_RATE . '</b>');

        $contents = array('form' => tep_draw_form('rates', FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id  . '&action=deleteconfirm'));
        $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
        $contents[] = array('text' => '<br><b>' . $trInfo->tax_class_title . ' ' . number_format($trInfo->tax_rate, TAX_DECIMAL_PLACES) . '%</b>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $_GET['page'] . '&tID=' . $trInfo->tax_rates_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
    break;

    default:
        if (is_object($trInfo)) {
            $heading[] = array('text' => '<b>' . $trInfo->tax_class_title . '</b>');
            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $page . '&tID=' . $trInfo->tax_rates_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_TAX_RATES, 'page=' . $page . '&tID=' . $trInfo->tax_rates_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
            $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($trInfo->date_added));
            $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($trInfo->last_modified));
            $contents[] = array('text' => '<br>' . TEXT_INFO_RATE_DESCRIPTION . '<br>' . $trInfo->tax_description);
        }
    break;
}

if (tep_not_null($heading) && tep_not_null($contents)) {
    echo '            <td width="25%" valign="top">' . "\n";
    $box = new Box;
    echo $box->get_info_box($heading, $contents);
    echo '            </td>' . "\n";
}
?>
          </tr>
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
