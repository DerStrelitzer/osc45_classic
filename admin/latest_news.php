<?php
/*
  $Id: latest_news.php,v 1.1.1.1 2002/11/11 06:15:14 will Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2002 Will Mays

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'setflag': //set the status of a news item.
      if (isset($_GET['flag']) && ($_GET['flag'] == '0' || $_GET['flag'] == '1') ) {
        if (isset($_GET['latest_news_id']) && $_GET['latest_news_id']>0) {
          tep_db_query("update " . TABLE_LATEST_NEWS . " set status = '" . $_GET['flag'] . "' where news_id = '" . intval($_GET['latest_news_id']) . "'");
        }
      }

      tep_redirect(tep_href_link(FILENAME_LATEST_NEWS));
    break;

    case 'delete_latest_news_confirm': //user has confirmed deletion of news article.
      if (isset($_POST['latest_news_id']) && $_POST['latest_news_id']>0) {
        $latest_news_id = intval(xprios_prepare_post('latest_news_id'));
        tep_db_query("delete from " . TABLE_LATEST_NEWS . " where news_id = '" . $latest_news_id . "'");
      }

      tep_redirect(tep_href_link(FILENAME_LATEST_NEWS));
    break;

    case 'insert_latest_news': //insert a new news article.
        if (isset($_POST['headline']) && $_POST['headline']!='') {
            $sql_data_array = array(
                'headline'   => xprios_prepare_post('headline'),
                'content'    => xprios_prepare_post('content'),
                'date_added' => 'now()',
                'language'   => intval(xprios_prepare_post('item_language')),
                'status'     => 1
            );

            tep_db_perform(TABLE_LATEST_NEWS, $sql_data_array);
            $news_id = tep_db_insert_id();
        }

        tep_redirect(tep_href_link(FILENAME_LATEST_NEWS));
    break;

    case 'update_latest_news': //user wants to modify a news article.
        if($_GET['latest_news_id']) {
            $sql_data_array = array(
                'headline'   => xprios_prepare_post('headline'),
                'content'    => xprios_prepare_post('content'),
                'date_added' => xprios_prepare_post('date_added'),
                'language'   => intval(xprios_prepare_post('item_language'))
            );

            tep_db_perform(TABLE_LATEST_NEWS, $sql_data_array, 'update', "news_id = '" . intval(xprios_prepare_get('latest_news_id')) . "'");
        }

      tep_redirect(tep_href_link(FILENAME_LATEST_NEWS));
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
<?php
if ($action == 'new_latest_news') { //insert or edit a news item
    if ( isset($_GET['latest_news_id']) ) { //editing exsiting news item
      $latest_news_query = tep_db_query("select news_id, headline, language, date_added, content from " . TABLE_LATEST_NEWS . " where news_id = '" . $_GET['latest_news_id'] . "'");
      $latest_news = tep_db_fetch_array($latest_news_query);
    } else { //adding new news item
      $latest_news = [];
      $latest_news = array(
        'language'  => $_SESSION['languages_id'],
        'headline'  => '',
        'content'   => ''
      );
    }
?>
      <tr>
        <td class="pageHeading"><?php echo HEADING_TITLE ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr><?php echo tep_draw_form('new_latest_news', FILENAME_LATEST_NEWS, isset($_GET['latest_news_id']) ? 'latest_news_id=' . $_GET['latest_news_id'] . '&action=update_latest_news' : 'action=insert_latest_news', 'post', 'enctype="multipart/form-data"'); ?>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_LATEST_NEWS_HEADLINE; ?>:</td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('headline', $latest_news['headline'], '', true); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_LATEST_NEWS_CONTENT; ?>:</td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_textarea_field('content', 'soft', '70', '15', stripslashes($latest_news['content'])); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>

<?php
    if ( isset($_GET['latest_news_id']) ) {
?>
          <tr>
            <td class="main"><?php echo TEXT_LATEST_NEWS_DATE; ?>:</td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' .  tep_draw_input_field('date_added', $latest_news['date_added'], '', true); ?></td>
          </tr>

          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>

<?php
    }
?>
          <tr>
            <td class="main"><?php echo TEXT_LATEST_NEWS_LANGUAGE; ?>:</td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;';


            if (!isset($lng) || (isset($lng) && !is_object($lng))) {
              $lng = new Language;
            }
            $nr = 0;
            foreach ($lng->catalog_languages as $key => $value) {
              $nr++;

              if (!isset($latest_news['language']) AND $nr == 1) {
                $latest_news['language'] = $value['id'];
              }

              if($latest_news['language'] == $value['id']) {
                echo tep_draw_radio_field('item_language', $value['id'], true, "");
              } else {
                echo tep_draw_radio_field('item_language', $value['id'], false, ""); }
                echo tep_image(DIR_WS_CATALOG_LANGUAGES .  $value['directory'] . '/images/'. $value['image'], $value['name']).tep_draw_separator('pixel_trans.gif', '20', '15');
              }
            ?></td>
          </tr>


        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="center">
          <?php
            isset($_GET['latest_news_id']) ? $cancel_button = '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_LATEST_NEWS, 'latest_news_id=' . $_GET['latest_news_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>' : $cancel_button = '';
            echo tep_image_submit('button_insert.gif', IMAGE_INSERT) . $cancel_button;
          ?>
        </td>
      </form></tr>
<?php

} else {
?>
      <tr>
        <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
              <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            </tr>
          </table>
        </td>
      </tr>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent">#</td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LATEST_NEWS_HEADLINE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TEXT_LATEST_NEWS_DATE; ?></td>
                <td class="dataTableHeadingContent"><?php echo TEXT_LATEST_NEWS_LANGUAGE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LATEST_NEWS_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_LATEST_NEWS_ACTION; ?>&nbsp;</td>
              </tr>
<?php

    if (!isset($lng) || (isset($lng) && !is_object($lng))) {
      $lng = new Language;
    }

    foreach ($lng->catalog_languages as $key => $value) {
      $languages_ids[$value['id']] = $key;
    }
    
    $selected_item = false;
    $rows = 0;
    $latest_news_count = 0;
    $latest_news_query = tep_db_query('select news_id, headline, content, date_added, language, status from ' . TABLE_LATEST_NEWS . ' order by date_added desc');

    while ($latest_news = tep_db_fetch_array($latest_news_query)) {
      $latest_news_count++;
      $rows++;

      if ( (!isset($_GET['latest_news_id']) || (isset($_GET['latest_news_id']) && $_GET['latest_news_id'] == $latest_news['news_id'])) && $selected_item==false && substr($action, 0, 4) != 'new_' ) {
        $selected_item = $latest_news;
      }
      if ( (is_array($selected_item)) && ($latest_news['news_id'] == $selected_item['news_id']) ) {
        echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_LATEST_NEWS, 'latest_news_id=' . $latest_news['news_id']) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . tep_href_link(FILENAME_LATEST_NEWS, 'latest_news_id=' . $latest_news['news_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '&nbsp;' . $latest_news['news_id']; ?></td>
                <td class="dataTableContent"><?php echo $latest_news['headline']; ?></td>
                <td class="dataTableContent"><?php echo tep_date_short($latest_news["date_added"]); ?></td>
                <td class="dataTableContent"><?php


                $value = $lng->catalog_languages[ $languages_ids[$latest_news['language']]  ];
                echo tep_image(DIR_WS_CATALOG_LANGUAGES .  $value['directory'] . '/images/'. $value['image'], $value['name']).tep_draw_separator('pixel_trans.gif', '20', '15');

                #echo var_dump($lng->catalog_languages);


                 ?></td>
                <td class="dataTableContent" align="center">
<?php
      if ($latest_news['status'] == '1') {
        echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_LATEST_NEWS, 'action=setflag&flag=0&latest_news_id=' . $latest_news['news_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . tep_href_link(FILENAME_LATEST_NEWS, 'action=setflag&flag=1&latest_news_id=' . $latest_news['news_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?></td>
                <td class="dataTableContent" align="right"><?php 
      if ((isset($_GET['latest_news_id']) && $latest_news['news_id'] == $_GET['latest_news_id']) || $selected_item['news_id'] == $latest_news['news_id']) { 
        echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); 
      } else { 
        echo '<a href="' . tep_href_link(FILENAME_LATEST_NEWS, 'latest_news_id=' . $latest_news['news_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; 
      } ?>&nbsp;</td>
              </tr>
<?php
    }

?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText"><?php echo '<br>' . TEXT_NEWS_ITEMS . '&nbsp;' . $latest_news_count; ?></td>
                    <td align="right" class="smallText"><?php echo '&nbsp;<a href="' . tep_href_link(FILENAME_LATEST_NEWS, 'action=new_latest_news') . '">' . tep_image_button('button_new_news_item.gif', IMAGE_NEW_NEWS_ITEM) . '</a>'; ?>&nbsp;</td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
    $heading = [];
    $contents = [];
    switch ($action) {
      case 'delete_latest_news': //generate box for confirming a news article deletion
        $heading[] = array('text'   => '<b>' . TEXT_INFO_HEADING_DELETE_ITEM . '</b>');

        $contents = array('form'    => tep_draw_form('news', FILENAME_LATEST_NEWS, 'action=delete_latest_news_confirm') . tep_draw_hidden_field('latest_news_id', $_GET['latest_news_id']));
        $contents[] = array('text'  => TEXT_DELETE_ITEM_INTRO);
        $contents[] = array('text'  => '<br><b>' . $selected_item['headline'] . '</b>');

        $contents[] = array('align' => 'center',
                            'text'  => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_LATEST_NEWS, 'latest_news_id=' . $selected_item['news_id']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;

      default:
        if ($rows > 0) {
          if (is_array($selected_item)) { //an item is selected, so make the side box
            $heading[] = array('text' => '<b>' . $selected_item['headline'] . '</b>');

            $contents[] = array('align' => 'center',
                                'text' => '<a href="' . tep_href_link(FILENAME_LATEST_NEWS, 'latest_news_id=' . $selected_item['news_id'] . '&action=new_latest_news') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_LATEST_NEWS, 'latest_news_id=' . $selected_item['news_id'] . '&action=delete_latest_news') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
            $contents[] = array('text' => '<br>' . $selected_item['content']);
          }
        } else { // create category/product info
          $heading[] = array('text' => '<b>' . EMPTY_CATEGORY . '</b>');
          $contents[] = array('text' => sprintf(TEXT_NO_CHILD_CATEGORIES_OR_PRODUCTS, $parent_categories_name));
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
<?php
}
?>
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
