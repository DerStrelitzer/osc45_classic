<?php
/*
  $Id: advanced_search.php,v 1.50 2003/06/05 23:25:46 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

$breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ADVANCED_SEARCH));

$this_head_include = "<script type=\"text/javascript\" language=\"javascript\" src=\"includes/general.js\"></script>
<script type=\"text/javascript\" language=\"javascript\"><!--
function check_form() {
  var error_message = '" . JS_ERROR . "';
  var error_found = false;
  var error_field;
  var keywords = document.advanced_search.keywords.value;
  var dfrom = document.advanced_search.dfrom.value;
  var dto = document.advanced_search.dto.value;
  var pfrom = document.advanced_search.pfrom.value;
  var pto = document.advanced_search.pto.value;
  var pfrom_float;
  var pto_float;

  if ( ((keywords == '') || (keywords.length < 1)) && ((dfrom == '') || (dfrom == '" . DOB_FORMAT_STRING . "') || (dfrom.length < 1)) && ((dto == '') || (dto == '" . DOB_FORMAT_STRING . "') || (dto.length < 1)) && ((pfrom == '') || (pfrom.length < 1)) && ((pto == '') || (pto.length < 1)) ) {
    error_message = error_message + '* " . ERROR_AT_LEAST_ONE_INPUT . '\n' . "';
    error_field = document.advanced_search.keywords;
    error_found = true;
  }
  if ((dfrom.length > 0) && (dfrom != '" . DOB_FORMAT_STRING . "')) {
    if (!IsValidDate(dfrom, '" . DOB_FORMAT_STRING . "')) {
      error_message = error_message + '* " . ERROR_INVALID_FROM_DATE . '\n' . "';
      error_field = document.advanced_search.dfrom;
      error_found = true;
    }
  }
  if ((dto.length > 0) && (dto != '" . DOB_FORMAT_STRING . "')) {
    if (!IsValidDate(dto, '" . DOB_FORMAT_STRING . "')) {
      error_message = error_message + '* " . ERROR_INVALID_TO_DATE . '\n' . "';
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }
  if ((dfrom.length > 0) && (dfrom != '" . DOB_FORMAT_STRING . "') && (IsValidDate(dfrom, '" . DOB_FORMAT_STRING . "')) && (dto.length > 0) && (dto != '" . DOB_FORMAT_STRING . "') && (IsValidDate(dto, '" . DOB_FORMAT_STRING . "'))) {
    if (!CheckDateRange(document.advanced_search.dfrom, document.advanced_search.dto)) {
      error_message = error_message + '* " . ERROR_TO_DATE_LESS_THAN_FROM_DATE . '\n' ."';
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }
  if (pfrom.length > 0) {
    pfrom_float = parseFloat(pfrom);
    if (isNaN(pfrom_float)) {
      error_message = error_message + '* " . ERROR_PRICE_FROM_MUST_BE_NUM . '\n' . "';
      error_field = document.advanced_search.pfrom;
      error_found = true;
    }
  } else {
    pfrom_float = 0;
  }
  if (pto.length > 0) {
    pto_float = parseFloat(pto);
    if (isNaN(pto_float)) {
      error_message = error_message + '* " . ERROR_PRICE_TO_MUST_BE_NUM . '\n' . "';
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  } else {
    pto_float = 0;
  }
  if ( (pfrom.length > 0) && (pto.length > 0) ) {
    if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
      error_message = error_message + '* " . ERROR_PRICE_TO_LESS_THAN_PRICE_FROM . '\n' . "';
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  }
  if (error_found == true) {
    alert(error_message);
    error_field.focus();
    return false;
  } else {
    RemoveFormatString(document.advanced_search.dfrom, '" . DOB_FORMAT_STRING . "');
    RemoveFormatString(document.advanced_search.dto, '" . DOB_FORMAT_STRING . "');
    return true;
  }
}
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
//--></script>";
require(DIR_WS_INCLUDES . 'meta.php');
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
$this_content_form = tep_draw_form('advanced_search', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get', 'onsubmit="return check_form(this);"') . tep_hide_session_id();
$heading_image = 'table_background_browse.gif';
require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
if ($messageStack->size('search') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('search'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td>
          <div class="divbox">
            <div class="divboxheading"><?php echo HEADING_SEARCH_CRITERIA; ?></div>
            <div class="main" style="margin:10px"><?php
echo tep_draw_input_field('keywords', '', 'style="width: 100%"');
echo tep_draw_checkbox_field('search_in_description', '1') . ' ' . TEXT_SEARCH_IN_DESCRIPTION; ?></div>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText"><?php echo '<a href="javascript:popupWindow(\'' . tep_href_link(FILENAME_POPUP_SEARCH_HELP) . '\')" style="margin-left:10px;">' . TEXT_SEARCH_HELP_LINK . '</a>'; ?></td>
                <td class="smallText" align="right"><?php echo tep_image_submit('button_search.gif', IMAGE_BUTTON_SEARCH, 'style="margin-right:10px;"'); ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
          <div class="divbox">
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="fieldKey"><?php echo ENTRY_CATEGORIES; ?></td>
                <td class="fieldValue"><?php echo tep_draw_pull_down_menu('categories_id', tep_get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES)))); ?></td>
              </tr>
              <tr>
                <td class="fieldKey">&nbsp;</td>
                <td class="smallText"><?php echo tep_draw_checkbox_field('inc_subcat', '1', true) . ' ' . ENTRY_INCLUDE_SUBCATEGORIES; ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_MANUFACTURERS; ?></td>
                <td class="fieldValue"><?php echo tep_draw_pull_down_menu('manufacturers_id', tep_get_manufacturers(array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS)))); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_PRICE_FROM; ?></td>
                <td class="fieldValue"><?php echo tep_draw_input_field('pfrom'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_PRICE_TO; ?></td>
                <td class="fieldValue"><?php echo tep_draw_input_field('pto'); ?></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_DATE_FROM; ?></td>
                <td class="fieldValue"><?php echo tep_draw_input_field('dfrom', DOB_FORMAT_STRING, 'onfocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"'); ?></td>
              </tr>
              <tr>
                <td class="fieldKey"><?php echo ENTRY_DATE_TO; ?></td>
                <td class="fieldValue"><?php echo tep_draw_input_field('dto', DOB_FORMAT_STRING, 'onfocus="RemoveFormatString(this, \'' . DOB_FORMAT_STRING . '\')"'); ?></td>
              </tr>
            </table>
          </div>
        </td>
      </tr>
    </table></form></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
