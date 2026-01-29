<?php
/*
  $Id: file_manager.php,v 1.42i 2003/06/29 22:50:52 hpdl Exp $

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

if (!isset($_SESSION['current_path'])) {
    $_SESSION['current_path'] = DIR_FS_DOCUMENT_ROOT;
}

if (isset($_GET['goto'])) {
    $_SESSION['current_path'] = $_GET['goto'];
    tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
}

if (strstr($_SESSION['current_path'], '..')) $_SESSION['current_path'] = DIR_FS_DOCUMENT_ROOT;

if (!is_dir($_SESSION['current_path'])) $_SESSION['current_path'] = DIR_FS_DOCUMENT_ROOT;

if (!preg_match('/^' . preg_quote(DIR_FS_DOCUMENT_ROOT, '/') . '/', $_SESSION['current_path'])) $_SESSION['current_path'] = DIR_FS_DOCUMENT_ROOT;

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'reset':
      unset($_SESSION['current_path']);
      tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
    break;
    
    case 'deleteconfirm':
      if (strstr($_GET['info'], '..')) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));

      tep_remove($_SESSION['current_path'] . '/' . $_GET['info']);
      if (!$tep_remove_error) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
    break;
    
    case 'insert':
      if (mkdir($_SESSION['current_path'] . '/' . $_POST['folder_name'], 0777)) {
        tep_redirect(tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($_POST['folder_name'])));
      }
    break;
    
    case 'save':
      if ($fp = fopen($_SESSION['current_path'] . '/' . $_POST['filename'], 'w+')) {
        fputs($fp, stripslashes($_POST['file_contents']));
        fclose($fp);
        tep_redirect(tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($_POST['filename'])));
      }
    break;
    
    case 'processuploads':
        for ($i=1; $i<6; $i++) {
            if (isset($GLOBALS['file_' . $i]) && tep_not_null($GLOBALS['file_' . $i])) {
                $new_file = new Upload('file_' . $i, $_SESSION['current_path']);
                $new_file->parse();
                $new_file->save();
            }
        }

      tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
    break;
    
    case 'download':
      header('Content-type: application/x-octet-stream');
      header('Content-disposition: attachment; filename=' . urldecode($_GET['filename']));
      readfile($_SESSION['current_path'] . '/' . urldecode($_GET['filename']));
      exit;
    break;
    
    case 'upload':
    case 'new_folder':
    case 'new_file':
      $directory_writeable = true;
      if (!is_writeable($_SESSION['current_path'])) {
        $directory_writeable = false;
        $messageStack->add(sprintf(ERROR_DIRECTORY_NOT_WRITEABLE, $_SESSION['current_path']), 'error');
      }
    break;
    
    case 'edit':
      if (strstr($_GET['info'], '..')) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));

      $file_writeable = true;
      if (!is_writeable($_SESSION['current_path'] . '/' . $_GET['info'])) {
        $file_writeable = false;
        $messageStack->add(sprintf(ERROR_FILE_NOT_WRITEABLE, $_SESSION['current_path'] . '/' . $_GET['info']), 'error');
      }
    break;
    
    case 'delete':
      if (strstr($_GET['info'], '..')) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));
    break;
}

$in_directory = substr(substr(DIR_FS_DOCUMENT_ROOT, strrpos(DIR_FS_DOCUMENT_ROOT, '/')), 1);
$current_path_array = explode('/', $_SESSION['current_path']);
$document_root_array = explode('/', DIR_FS_DOCUMENT_ROOT);
$goto_array = array(array('id' => DIR_FS_DOCUMENT_ROOT, 'text' => $in_directory));
for ($i=0, $n=sizeof($current_path_array); $i<$n; $i++) {
    if ((isset($document_root_array[$i]) && ($current_path_array[$i] != $document_root_array[$i])) || !isset($document_root_array[$i])) {
        $goto_array[] = array('id' => implode('/', array_slice($current_path_array, 0, $i+1)), 'text' => $current_path_array[$i]);
    }
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script type="text/javascript" src="includes/general.js"></script>
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
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo tep_draw_form('goto', FILENAME_FILE_MANAGER, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE . '<br><span class="smallText">' . $_SESSION['current_path'] . '</span>'; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', '1', HEADING_IMAGE_HEIGHT); ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_pull_down_menu('goto', $goto_array, $_SESSION['current_path'], 'onchange="this.form.submit();"'); ?></td>
          </form></tr>
        </table></td>
      </tr>
<?php
  if ( (($action == 'new_file') && ($directory_writeable == true)) || ($action == 'edit') ) {
    if (isset($_GET['info']) && strstr($_GET['info'], '..')) tep_redirect(tep_href_link(FILENAME_FILE_MANAGER));

    if (!isset($file_writeable)) $file_writeable = true;
    $file_contents = '';
    if ($action == 'new_file') {
      $filename_input_field = tep_draw_input_field('filename');
    } elseif ($action == 'edit') {
      if ($file_array = file($_SESSION['current_path'] . '/' . $_GET['info'])) {
        $file_contents = addslashes(implode('', $file_array));
      }
      $filename_input_field = $_GET['info'] . tep_draw_hidden_field('filename', $_GET['info']);
    }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr><?php echo tep_draw_form('new_file', FILENAME_FILE_MANAGER, 'action=save'); ?>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_FILE_NAME; ?></td>
            <td class="main"><?php echo $filename_input_field; ?></td>
          </tr>
          <tr>
            <td class="main" valign="top"><?php echo TEXT_FILE_CONTENTS; ?></td>
            <td class="main"><?php echo tep_draw_textarea_field('file_contents', 'soft', '80', '20', $file_contents, (($file_writeable) ? '' : 'readonly')); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td align="right" class="main" colspan="2"><?php if ($file_writeable == true) echo tep_image_submit('button_save.gif', IMAGE_SAVE) . '&nbsp;'; echo '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($_GET['info']) ? 'info=' . urlencode($_GET['info']) : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } else {
    $showuser = (function_exists('posix_getpwuid') ? true : false);
    $contents = [];
    $dir = dir($_SESSION['current_path']);
    while ($file = $dir->read()) {
      if ( ($file != '.') && ($file != 'CVS') && ( ($file != '..') || ($_SESSION['current_path'] != DIR_FS_DOCUMENT_ROOT) ) ) {
        $file_size = number_format(filesize($_SESSION['current_path'] . '/' . $file)) . ' bytes';

        $permissions = tep_get_file_permissions(fileperms($_SESSION['current_path'] . '/' . $file));
        if ($showuser) {
          $user = @posix_getpwuid(fileowner($_SESSION['current_path'] . '/' . $file));
          $group = @posix_getgrgid(filegroup($_SESSION['current_path'] . '/' . $file));
        } else {
          $user = $group = array('name' => '-');
        }

        $contents[] = array(
          'name' => $file,
          'is_dir' => is_dir($_SESSION['current_path'] . '/' . $file),
          'last_modified' => date(DATE_TIME_FORMAT, filemtime($_SESSION['current_path'] . '/' . $file)),
          'size' => $file_size,
          'permissions' => $permissions,
          'user' => $user['name'],
          'group' => $group['name']
        );
      }
    }

    function tep_cmp($a, $b) {
      return strcmp( ($a['is_dir'] ? 'D' : 'F') . $a['name'], ($b['is_dir'] ? 'D' : 'F') . $b['name']);
    }
    usort($contents, 'tep_cmp');
?>

      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_FILENAME; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_SIZE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PERMISSIONS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_USER; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_LAST_MODIFIED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
    if ((!isset($_GET['info']) || (isset($_GET['info']) && ($_GET['info'] == $contents[$i]['name']))) && !isset($fInfo) && ($action != 'upload') && ($action != 'new_folder')) {
      $fInfo = new ObjectInfo($contents[$i]);
    }

    if ($contents[$i]['name'] == '..') {
      $goto_link = substr($_SESSION['current_path'], 0, strrpos($_SESSION['current_path'], '/'));
    } else {
      $goto_link = $_SESSION['current_path'] . '/' . $contents[$i]['name'];
    }

    if (isset($fInfo) && is_object($fInfo) && ($contents[$i]['name'] == $fInfo->name)) {
      if ($fInfo->is_dir) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
        $onclick_link = 'goto=' . $goto_link;
      } else {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
        $onclick_link = 'info=' . urlencode($fInfo->name) . '&action=edit';
      }
    } else {
      echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
      $onclick_link = 'info=' . urlencode($contents[$i]['name']);
    }

    if ($contents[$i]['is_dir']) {
      if ($contents[$i]['name'] == '..') {
        $icon = tep_image(DIR_WS_ICONS . 'previous_level.gif', ICON_PREVIOUS_LEVEL);
      } else {
        $icon = (isset($fInfo) && is_object($fInfo) && ($contents[$i]['name'] == $fInfo->name) ? tep_image(DIR_WS_ICONS . 'current_folder.gif', ICON_CURRENT_FOLDER) : tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER));
      }
      $link = tep_href_link(FILENAME_FILE_MANAGER, 'goto=' . preg_replace('#/+#', '/', $goto_link));
    } else {
      $icon = tep_image(DIR_WS_ICONS . 'file_download.gif', ICON_FILE_DOWNLOAD);
      $link = tep_href_link(FILENAME_FILE_MANAGER, 'action=download&filename=' . urlencode($contents[$i]['name']));
    }
?>
                <td class="dataTableContent" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo '<a href="' . $link . '">' . $icon . '</a>&nbsp;' . $contents[$i]['name']; ?></td>
                <td class="dataTableContent" align="right" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo ($contents[$i]['is_dir'] ? '&nbsp;' : $contents[$i]['size']); ?></td>
                <td class="dataTableContent" align="center" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><tt><?php echo $contents[$i]['permissions']; ?></tt></td>
                <td class="dataTableContent" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo $contents[$i]['user']; ?></td>
                <td class="dataTableContent" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo $contents[$i]['group']; ?></td>
                <td class="dataTableContent" align="center" onclick="document.location.href='<?php echo tep_href_link(FILENAME_FILE_MANAGER, $onclick_link); ?>'"><?php echo $contents[$i]['last_modified']; ?></td>
                <td class="dataTableContent" align="right"><?php 
    if ($contents[$i]['name'] != '..') {
      echo '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($contents[$i]['name']) . '&action=delete') . '">' . tep_image(DIR_WS_ICONS . 'delete.gif', ICON_DELETE) . '</a>&nbsp;'; 
    }
    if (isset($fInfo) && is_object($fInfo) && ($fInfo->name == $contents[$i]['name'])) { 
      echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); 
    } else { 
      echo '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($contents[$i]['name'])) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; 
    } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="7"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr valign="top">
                    <td class="smallText"><?php echo '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, 'action=reset') . '">' . tep_image_button('button_reset.gif', IMAGE_RESET) . '</a>'; ?></td>
                    <td class="smallText" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($_GET['info']) ? 'info=' . urlencode($_GET['info']) . '&' : '') . 'action=upload') . '">' . tep_image_button('button_upload.gif', IMAGE_UPLOAD) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($_GET['info']) ? 'info=' . urlencode($_GET['info']) . '&' : '') . 'action=new_file') . '">' . tep_image_button('button_new_file.gif', IMAGE_NEW_FILE) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($_GET['info']) ? 'info=' . urlencode($_GET['info']) . '&' : '') . 'action=new_folder') . '">' . tep_image_button('button_new_folder.gif', IMAGE_NEW_FOLDER) . '</a>'; ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
    $heading = [];
    $contents = [];

    switch ($action) {
      case 'delete':
        $heading[] = array('text' => '<b>' . $fInfo->name . '</b>');

        $contents = array('form' => tep_draw_form('file', FILENAME_FILE_MANAGER, 'info=' . urlencode($fInfo->name) . '&action=deleteconfirm'));
        $contents[] = array('text' => TEXT_DELETE_INTRO);
        $contents[] = array('text' => '<br><b>' . $fInfo->name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_FILE_MANAGER, (tep_not_null($fInfo->name) ? 'info=' . urlencode($fInfo->name) : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'new_folder':
        $heading[] = array('text' => '<b>' . TEXT_NEW_FOLDER . '</b>');

        $contents = array('form' => tep_draw_form('folder', FILENAME_FILE_MANAGER, 'action=insert'));
        $contents[] = array('text' => TEXT_NEW_FOLDER_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_FILE_NAME . '<br>' . tep_draw_input_field('folder_name'));
        $contents[] = array('align' => 'center', 'text' => '<br>' . (($directory_writeable == true) ? tep_image_submit('button_save.gif', IMAGE_SAVE) : '') . ' <a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($_GET['info']) ? 'info=' . urlencode($_GET['info']) : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'upload':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_UPLOAD . '</b>');

        $contents = array('form' => tep_draw_form('file', FILENAME_FILE_MANAGER, 'action=processuploads', 'post', 'enctype="multipart/form-data"'));
        $contents[] = array('text' => TEXT_UPLOAD_INTRO);

        $file_upload = '';
        for ($i=1; $i<6; $i++) $file_upload .= tep_draw_file_field('file_' . $i) . '<br>';

        $contents[] = array('text' => '<br>' . $file_upload);
        $contents[] = array('align' => 'center', 'text' => '<br>' . (($directory_writeable == true) ? tep_image_submit('button_upload.gif', IMAGE_UPLOAD) : '') . ' <a href="' . tep_href_link(FILENAME_FILE_MANAGER, (isset($_GET['info']) ? 'info=' . urlencode($_GET['info']) : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if (isset($fInfo) && is_object($fInfo)) {
          $heading[] = array('text' => '<b>' . $fInfo->name . '</b>');

          if (!$fInfo->is_dir) $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_FILE_MANAGER, 'info=' . urlencode($fInfo->name) . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
          $contents[] = array('text' => '<br>' . TEXT_FILE_NAME . ' <b>' . $fInfo->name . '</b>');
          if (!$fInfo->is_dir) $contents[] = array('text' => '<br>' . TEXT_FILE_SIZE . ' <b>' . $fInfo->size . '</b>');
          $contents[] = array('text' => '<br>' . TEXT_LAST_MODIFIED . ' ' . $fInfo->last_modified .
                        ((in_array(substr($fInfo->name,-4), array('.jpg', '.gif', '.png')))?
                        '<br>' . tep_info_image($fInfo->name, $fInfo->name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT):''));
        }
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
