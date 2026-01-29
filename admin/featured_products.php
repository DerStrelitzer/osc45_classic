<?php
/*
  $Id: featured_products.php,v 2.0 2004/03/20 by Ingo <www.strelitzer.de>

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

function tep_set_featured_status($featured_id, $status)
{
    if ($status == '1') {
        return tep_db_query("update " . TABLE_FEATURED_PRODUCTS . " set status = '1', expires_date = NULL, date_status_change = NULL where featured_id = '" . $featured_id . "'");
    } elseif ($status == '0') {
        return tep_db_query("update " . TABLE_FEATURED_PRODUCTS . " set status = '0', date_status_change = now() where featured_id = '" . $featured_id . "'");
    } else {
        return -1;
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if (isset($_GET['f_cID']) && $_GET['f_cID']!='') {
    $action = 'new';
}

switch ($action) {
    case 'setflag':
        tep_set_featured_status($_GET['id'], $_GET['flag']);
        tep_redirect(tep_href_link(FILENAME_FEATURED_PRODUCTS, tep_get_all_get_params(array('action', 'flag'))));
    break;
    
    case 'insert':
        $expires_date = 'null';
        if ($_POST['day'] && $_POST['month'] && $_POST['year']) {
            $expires_date = $_POST['year'];
            $expires_date .= (strlen($_POST['month']) == 1) ? '0' . $_POST['month'] : $_POST['month'];
            $expires_date .= (strlen($_POST['day']) == 1) ? '0' . $_POST['day'] : $_POST['day'];
            $expires_date = "'" . tep_db_input($expires_date) . "'";
        }

        tep_db_query("insert into " . TABLE_FEATURED_PRODUCTS . " (products_id, featured_date_added, expires_date, status) values ('" . (int)$_POST['products_id'] . "', now(), " . $expires_date . ", '1')");
        tep_redirect(tep_href_link(FILENAME_FEATURED_PRODUCTS, tep_get_all_get_params(array('action'))));
    break;
    
    case 'update':
        $expires_date = 'null';
        if ($_POST['day'] && $_POST['month'] && $_POST['year']) {
            $expires_date = $_POST['year'];
            $expires_date .= (strlen($_POST['month']) == 1) ? '0' . $_POST['month'] : $_POST['month'];
            $expires_date .= (strlen($_POST['day']) == 1) ? '0' . $_POST['day'] : $_POST['day'];
            $expires_date = "'" . tep_db_input($expires_date) . "'";
        }

        tep_db_query("update " . TABLE_FEATURED_PRODUCTS . " set expires_date = " . $expires_date . " where featured_id = '" . (int)$_POST['featured_id'] . "'");
        tep_redirect(tep_href_link(FILENAME_FEATURED_PRODUCTS, tep_get_all_get_params(array('action'))));
    break;
    
    case 'deleteconfirm':
        $featured_id = intval(xprios_prepare_get('sID'));

        tep_db_query("delete from " . TABLE_FEATURED_PRODUCTS . " where featured_id = '" . $featured_id . "'");

        tep_redirect(tep_href_link(FILENAME_FEATURED_PRODUCTS, tep_get_all_get_params(array('action', 'sID'))));
    break;
}
  
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script type="text/javascript" language="javascript" src="includes/general.js"></script>
<?php
  if ($action == 'new' || $action == 'edit') {
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/calendar.css">
<script type="text/javascript" language="javascript" src="includes/javascript/calendarcode.js"></script>
<?php
  }
?>
</head>
<body onload="SetFocus();">
<div id="popupcalendar" class="text"></div>
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
        <td width="100%" class="pageHeading"><?php echo HEADING_TITLE; ?></td>
      </tr>
<?php
if ($action == 'new' || $action == 'edit') {
    $form_action = 'insert';
    if ($action == 'edit' && isset($_GET['sID']) ) {
        $form_action = 'update';

        $product_query = tep_db_query("select p.products_id, pd.products_name, s.expires_date from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_FEATURED_PRODUCTS . " s WHERE p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p.products_id = s.products_id and s.featured_id = '" . $_GET['sID'] . "' order by pd.products_name");
        $product = tep_db_fetch_array($product_query);

        $sInfo = new ObjectInfo($product);
    } else {
        $sInfo = new ObjectInfo([]);

// create an array of featured products, which will be excluded from the pull down menu of products
// (when creating a new featured product)
        $featured_array = [];
        $featured_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_FEATURED_PRODUCTS . " f, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c WHERE f.products_id = p.products_id OR (p2c.products_id = p.products_id AND p2c.categories_id != '" . ((isset($_GET['f_cID']))?(int)$_GET['f_cID']:'0') . "')" );
        while ($featured = tep_db_fetch_array($featured_query)) {
            $featured_array[] = $featured['products_id'];
        }
    }
?>
      <tr>
       <td><br><table border="0" cellspacing="0" cellpadding="2">

<?php
    if ($action == 'new') {
?>
          <tr>
           <td class="main"><?php echo TEXT_FEATURED_CATEGORY . '&nbsp;'; ?></td>
           <td>
<?php
        echo tep_draw_form('goto', FILENAME_FEATURED_PRODUCTS, '', 'get');
        echo tep_draw_pull_down_menu('f_cID', tep_get_category_tree(), $current_category_id, 'onchange="this.form.submit();"');
        echo '</form>';
?>
           </td>
          </tr>
<?php
    }
?>
        <form name="new_feature" action="<?php echo tep_href_link(FILENAME_FEATURED_PRODUCTS, tep_get_all_get_params(array('action', 'info', 'sID', 'f_cID')) . 'action=' . $form_action, 'NONSSL'); ?>" method="post">
        <?php if ($form_action == 'update') echo tep_draw_hidden_field('featured_id', $_GET['sID']); ?>
          <tr>
            <td class="main"><?php echo TEXT_FEATURED_PRODUCT; ?>&nbsp;</td>
            <td class="main"><?php echo ($sInfo->products_name) ? $sInfo->products_name : tep_draw_products_pull_down('products_id', 'style="font-size:10px; size:60"', $featured_array); echo tep_draw_hidden_field('products_price', $sInfo->products_price); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_FEATURED_EXPIRES_DATE; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_input_field('day', substr($sInfo->expires_date, 8, 2), 'size="2" maxlength="2" class="cal-TextBox"') . tep_draw_input_field('month', substr($sInfo->expires_date, 5, 2), 'size="2" maxlength="2" class="cal-TextBox"') . tep_draw_input_field('year', substr($sInfo->expires_date, 0, 4), 'size="4" maxlength="4" class="cal-TextBox"'); ?><a class="so-BtnLink" href="javascript:calClick();return false;" onmouseover="calSwapImg('BTN_date', 'img_Date_OVER',true);" onmouseout="calSwapImg('BTN_date', 'img_Date_UP',true);" onclick="calSwapImg('BTN_date', 'img_Date_DOWN');showCalendar('new_feature','dteWhen','BTN_date');return false;"><?php echo tep_image(DIR_WS_IMAGES . 'cal_date_up.gif', 'Calendar', '22', '17', 'align="absmiddle" name="BTN_date"'); ?></a></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" align="right" valign="top"><br><?php echo (($form_action == 'insert') ? tep_image_submit('button_insert.gif', IMAGE_INSERT) : tep_image_submit('button_update.gif', IMAGE_UPDATE)). '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'page=' . $page . '&sID=' . $_GET['sID']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
         </form>
        </table></td>
      </tr>
<?php
} else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="right">&nbsp;</td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $page = max(1, isset($_GET['page']) ? $_GET['page'] : '1');
    $featured_query_raw = "select p.products_id, pd.products_name, s.featured_id, s.featured_date_added, s.featured_last_modified, s.expires_date, s.date_status_change, s.status from " . TABLE_PRODUCTS . " p, " . TABLE_FEATURED_PRODUCTS . " s, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' and p.products_id = s.products_id order by pd.products_name";
    $featured_split = new SplitPageResults($page, MAX_DISPLAY_SEARCH_RESULTS, $featured_query_raw, $featured_query_numrows);
    $featured_query = tep_db_query($featured_query_raw);
    while ($featured = tep_db_fetch_array($featured_query)) {
      if ( ((!$_GET['sID']) || ($_GET['sID'] == $featured['featured_id'])) && (!$sInfo) ) {

        $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . $featured['products_id'] . "'");
        $products = tep_db_fetch_array($products_query);
        $sInfo_array = array_merge($featured, $products);
        $sInfo = new ObjectInfo($sInfo_array);
      }

      if ( (is_object($sInfo)) && ($featured['featured_id'] == $sInfo->featured_id) ) {
        echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'page=' . $page . '&sID=' . $sInfo->featured_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'page=' . $page . '&sID=' . $featured['featured_id']) . '\'">' . "\n";
      }
?>
                <td  class="dataTableContent"><?php echo $featured['products_name']; ?></td>
                <td  class="dataTableContent" align="right">&nbsp;</td>
                <td  class="dataTableContent" align="right">
<?php
      if ($featured['status'] == '1') {
        echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'action=setflag&flag=0&id=' . $featured['featured_id'], 'NONSSL') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'action=setflag&flag=1&id=' . $featured['featured_id'], 'NONSSL') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?></td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($sInfo)) && ($featured['featured_id'] == $sInfo->featured_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'page=' . $page . '&sID=' . $featured['featured_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
      </tr>
<?php
    }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $featured_split->display_count($featured_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $page, TEXT_DISPLAY_NUMBER_OF_FEATURED); ?></td>
                    <td class="smallText" align="right"><?php echo $featured_split->display_links($featured_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $page); ?></td>
                  </tr>
<?php
  if (!isset($_GET['action'])) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'page=' . $page . '&action=new') . '">' . tep_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>'; ?></td>
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
        case 'delete':
            $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_FEATURED . '</b>');

            $contents = array('form' => tep_draw_form('featured', FILENAME_FEATURED_PRODUCTS, 'page=' . $page . '&sID=' . $sInfo->featured_id . '&action=deleteconfirm'));
            $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
            $contents[] = array('text' => '<br><b>' . $sInfo->products_name . '</b>');
            $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'page=' . $page . '&sID=' . $sInfo->featured_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
        default:
        if (isset($sInfo) && is_object($sInfo)) {
            $heading[] = array('text' => '<b>' . $sInfo->products_name . '</b>');

            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'page=' . $page . '&sID=' . $sInfo->featured_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_FEATURED_PRODUCTS, 'page=' . $page . '&sID=' . $sInfo->featured_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
            $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . tep_date_short($sInfo->featured_date_added));
            $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . tep_date_short($sInfo->featured_last_modified));
            $contents[] = array('align' => 'center', 'text' => '<br>' . tep_info_image($sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));

            $contents[] = array('text' => '<br>' . TEXT_INFO_EXPIRES_DATE . ' <b>' . tep_date_short($sInfo->expires_date) . '</b>');
            $contents[] = array('text' => '' . TEXT_INFO_STATUS_CHANGE . ' ' . tep_date_short($sInfo->date_status_change));
        }
        break;
    }
    if (tep_not_null($heading) && tep_not_null($contents)) {
        echo '            <td width="25%" valign="top">' . "\n";
        $box = new Box;
        echo $box->get_info_box($heading, $contents);
        echo '            </td>' . "\n";
    }
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
