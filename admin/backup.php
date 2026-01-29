<?php
/*
  $Id: backup.php,v 1.60 2003/06/29 22:50:51 hpdl Exp $

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

$action = (isset($_GET['action']) ? $_GET['action'] : '');

$maximumQuerySize = 1048574;
$maximumRows = 1000;

if (tep_not_null($action)) {
    switch ($action) {
        case 'forget':
            tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'DB_LAST_RESTORE'");
            $messageStack->add_session(SUCCESS_LAST_RESTORE_CLEARED, 'success');
            tep_redirect(tep_href_link(FILENAME_BACKUP));
        break;
      
        case 'backupnow':
            @tep_set_time_limit(0);
            $backup_file = 'db_' . DB_DATABASE . '-' . date('Ymd-His') . '.sql';
            $fp = fopen(DIR_FS_BACKUP . $backup_file, 'w');

            fputs(
                $fp,
                '# xPrioS, Open Source E-Commerce Solutions' . "\n"
                . '# http://www.xprios.de' . "\n"
                . '#' . "\n"
                . '# database backup for:' . "\n"
                . '# ' . STORE_NAME . "\n"
                . '# ' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . "\n"
                . '# ' . "\n"
                . '# data copyright (c) ' . date('Y') . ' ' . STORE_OWNER . "\n"
                . '#' . "\n"
                . '# database: ' . DB_DATABASE . "\n"
                . '# database server: ' . DB_SERVER . "\n"
                . '#' . "\n"
                . '# backup date: ' . date(PHP_DATE_TIME_FORMAT) . "\n\n"
            );

            $backupTimeStart = explode(' ', microtime());

            $tables_query = tep_db_query('show table status');
            while ($table = tep_db_fetch_array($tables_query)) {

                $table_list      = [];
                $table_numerics  = [];
                $fields_query    = tep_db_query('show fields from ' . $table['Name']);
                $fieldDefinition = '';
                while ($fields = tep_db_fetch_array($fields_query)) {
                    $table_list[] = $fields['Field'];

                    $table_numerics[$fields['Field']] =
                        substr($fields['Type'],0,3)=='int' ||
                        substr($fields['Type'],0,7)=='tinyint' ||
                        substr($fields['Type'],0,8)=='smallint' ||
                        substr($fields['Type'],0,9)=='mediumint' ||
                        substr($fields['Type'],0,6)=='bigint' ||
                        substr($fields['Type'],0,5)=='float' ||
                        substr($fields['Type'],0,7)=='decimal' ||
                        substr($fields['Type'],0,6)=='double'
                    ? true : false;

                    $fieldDefinition .= ($fieldDefinition == '' ? '':",\n") . '  ' . $fields['Field'] . ' ' . $fields['Type'];
                    if (isset($fields['Default']) && strlen($fields['Default']) > 0) {
                        $fieldDefinition .= ' default \'' . $fields['Default'] . '\'';
                    }
                    if ($fields['Null'] != 'YES') {
                        $fieldDefinition .= ' not null';
                    }
                    if (isset($fields['Extra']) && $fields['Extra']!='') {
                        $fieldDefinition .= ' ' . $fields['Extra'];
                    }
                } 

// add the keys
                $index = [];
                $keys_query = tep_db_query("show keys from " . $table['Name']);
                while ($keys = tep_db_fetch_array($keys_query)) {
                    $kname = $keys['Key_name'];

                    if (!isset($index[$kname])) {
                        $index[$kname] = [
                            'unique'   => $keys['Non_unique']==1 ? false:true,
                            'fulltext' => $keys['Index_type'] == 'FULLTEXT' ? true : false,
                            'columns'  => []
                        ];
                    }
                    $index[$kname]['columns'][] = $keys['Column_name'];
                }

                $keyDefinition = '';
                foreach ($index as $kname => $info) {
                    $columns = implode(', ', $info['columns']);
                    $keyDefinition .= $keyDefinition=='' ? '':",\n";
                    if ($kname == 'PRIMARY') {
                        $keyDefinition .= '  PRIMARY KEY (' . $columns . ')';
                    } elseif ($info['fulltext'] == true ) {
                        $keyDefinition .= '  FULLTEXT' . ($kname!='' ? ' ' . $kname : '') . ' (' . $columns . ')';
                    } elseif ($info['unique'] == true) {
                        $keyDefinition .= '  UNIQUE' . ($kname!='' ? ' ' . $kname : '') . ' (' . $columns . ')';
                    } else {
                        $keyDefinition .= '  KEY' . ($kname!='' ? ' ' . $kname : '') . ' (' . $columns . ')';
                    }
                }

                $schema = 'drop table if exists ' . $table['Name'] . ';' . "\n"
                  . 'create table ' . $table['Name'] . ' (' . "\n"
                  . $fieldDefinition;
                if ($keyDefinition!='') {
                    $schema .= ",\n" . $keyDefinition ;
                }
          
                $schema .= "\n" . ')';
                if (isset($table['Engine']) && $table['Engine']!='') {
                    $schema .= ' ENGINE=' . $table['Engine'];
                }
                if (isset($table['Collation']) && $table['Collation']!='') {
                    $schema .= ' COLLATE=' . $table['Collation'];
                }            
                $schema .= ';' . "\n\n";
                fputs($fp, $schema);

// dump the data
                if ( $table['Name'] != TABLE_SESSIONS && $table['Name'] != TABLE_WHOS_ONLINE && $table['Rows'] > 0) {
                    $rows_query = tep_db_query('select ' . implode(',', $table_list) . ' from ' . $table['Name']);

                    if (tep_db_num_rows($rows_query)) {
                        fputs($fp, 'alter table ' . $table['Name'] . ' disable keys;' . "\n");
                        $insertHeader = 'insert into ' . $table['Name'] . ' (' . implode(',', $table_list) . ') values' . "\n";
                        $rowCounter = 0;
                        $rowLength  = 0;
                        $buffer = '';
                        while ($rows = tep_db_fetch_array($rows_query)) {
                            //$schema = 'insert into ' . $table['Name'] . ' (' . implode(', ', $table_list) . ') values (';
                            $dataRow = '';
                            foreach ($table_list as $i) {
                                $dataRow .= $dataRow == '' ? '(':',';
                                if (!isset($rows[$i])) {
                                    $dataRow .= 'NULL';
                                } elseif (tep_not_null($rows[$i])) {
                                    if ($table_numerics[$i]==true) {
                                        $dataRow .= $rows[$i];
                                    } else {
                                        $dataRow .= "'" . preg_replace("/\n#/", "\n".'\#', addslashes($rows[$i])) . "'";
                                    }
                                } else {
                                    $dataRow .= "''";
                                }
                            }
                            $dataRow .= ')';
                            $newLength = $rowLength + strlen($dataRow);
                            if ($newLength > $maximumQuerySize || $rowCounter > $maximumRows) {
                                fputs($fp, $insertHeader . $buffer . ";\n");
                                $buffer = '';
                                $rowCounter = 0;
                            }
                            ++$rowCounter;
                            $buffer .= ($buffer == '' ? '':',' . "\n") . $dataRow;
                            $rowLength = strlen($buffer);
                            //$schema .= $dataRow . ');' . "\n";
                            //fputs($fp, $schema);
                        }
                        if ($buffer!='') {
                            fputs($fp, $insertHeader . $buffer . ";\n");
                        }
                        fputs($fp, 'alter table ' . $table['Name'] . ' enable keys;' . "\n");
                    }
                    fputs($fp, "\n");

                } else {
                    fputs($fp, '# no data for ' . $table['Name'] . "\n\n");
                }
            }

            $backupTimeEnd = explode(' ', microtime());
            $backupTimeTotal = number_format(($backupTimeEnd[1] + $backupTimeEnd[0] - ($backupTimeStart[1] + $backupTimeStart[0])), 3);
            fputs($fp, '# timer: ' . $backupTimeTotal . ' seconds' . "\n");
            fclose($fp);

            if (isset($_POST['download']) && ($_POST['download'] == 'yes')) {
                switch ($_POST['compress']) {
                    case 'gzip':
                    exec(LOCAL_EXE_GZIP . ' ' . DIR_FS_BACKUP . $backup_file);
                    $backup_file .= '.gz';
                    break;

                    case 'zip':
                    exec(LOCAL_EXE_ZIP . ' -j ' . DIR_FS_BACKUP . $backup_file . '.zip ' . DIR_FS_BACKUP . $backup_file);
                    unlink(DIR_FS_BACKUP . $backup_file);
                    $backup_file .= '.zip';
                }
                header('Content-type: application/x-octet-stream');
                header('Content-disposition: attachment; filename=' . $backup_file);

                readfile(DIR_FS_BACKUP . $backup_file);
                unlink(DIR_FS_BACKUP . $backup_file);

                exit;
            } else {
                switch ($_POST['compress']) {
                    case 'gzip':
                    exec(LOCAL_EXE_GZIP . ' ' . DIR_FS_BACKUP . $backup_file);
                    break;

                    case 'zip':
                    exec(LOCAL_EXE_ZIP . ' -j ' . DIR_FS_BACKUP . $backup_file . '.zip ' . DIR_FS_BACKUP . $backup_file);
                    unlink(DIR_FS_BACKUP . $backup_file);
                }

                $messageStack->add_session(SUCCESS_DATABASE_SAVED, 'success');
            }

            tep_redirect(tep_href_link(FILENAME_BACKUP));
        break;
      
        case 'restorenow':
        case 'restorelocalnow':
            tep_set_time_limit(0);
            $restore_query = '';

            if ($action == 'restorenow') {
                $read_from = $_GET['file'];

                if (file_exists(DIR_FS_BACKUP . $_GET['file'])) {
                    $restore_file = DIR_FS_BACKUP . $_GET['file'];
                    $extension = substr($_GET['file'], -3);

                    if ( ($extension == 'sql') || ($extension == '.gz') || ($extension == 'zip') ) {
                        switch ($extension) {
                            case 'sql':
                            $restore_from = $restore_file;
                            $remove_raw = false;
                            break;

                            case '.gz':
                            $restore_from = substr($restore_file, 0, -3);
                            exec(LOCAL_EXE_GUNZIP . ' ' . $restore_file . ' -c > ' . $restore_from);
                            $remove_raw = true;
                            break;

                            case 'zip':
                            $restore_from = substr($restore_file, 0, -4);
                            exec(LOCAL_EXE_UNZIP . ' ' . $restore_file . ' -d ' . DIR_FS_BACKUP);
                            $remove_raw = true;
                        }

                        if (isset($restore_from) && file_exists($restore_from) && (filesize($restore_from) > 15000 || 1==1)) {
                            $fd = fopen($restore_from, 'rb');
                            $restore_query = fread($fd, filesize($restore_from));
                            fclose($fd);
                        }
                    }
                }
            } elseif ($action == 'restorelocalnow') {
                $sql_file = new Upload('sql_file');

                if ($sql_file->parse() == true) {
                    $restore_query = fread(fopen($sql_file->tmp_filename, 'r'), filesize($sql_file->tmp_filename));
                    $read_from = $sql_file->filename;
                }
            }

            if ($restore_query!='') {
                $sql_array = [];
                $drop_table_names = [];
                $sql_length = strlen($restore_query);
                $pos = strpos($restore_query, ';');
                for ($i=$pos; $i<$sql_length; $i++) {
                    if ($restore_query[0] == '#') {
                        $restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
                        $sql_length = strlen($restore_query);
                        $i = strpos($restore_query, ';')-1;
                        continue;
                    }
                    if (isset($restore_query[($i+1)]) && $restore_query[($i+1)] == "\n") {
                        for ($j=($i+2); $j<$sql_length; $j++) {
                            if (trim($restore_query[$j]) != '') {
                                $next = substr($restore_query, $j, 6);
                                if ($next[0] == '#') {
// find out where the break position is so we can remove this line (#comment line)
                                    for ($k=$j; $k<$sql_length; $k++) {
                                        if ($restore_query[$k] == "\n") break;
                                    }
                                    $query = substr($restore_query, 0, $i+1);
                                    $restore_query = substr($restore_query, $k);
// join the query before the comment appeared, with the rest of the dump
                                    $restore_query = $query . $restore_query;
                                    $sql_length = strlen($restore_query);
                                    $i = strpos($restore_query, ';')-1;
                                    continue 2;
                                }
                                break;
                            }
                        }
                        if ($next == '') { // get the last insert query
                            $next = 'insert';
                        }
                        $next = strtolower(trim($next));
                        if ( in_array($next, array('create', 'insert', 'drop t', 'alter')) ) {
                            $query = substr($restore_query, 0, $i);
                            $next = '';

                            $restore_query = ltrim(substr($restore_query, $i+1));
                            $sql_length = strlen($restore_query);
                            $i = strpos($restore_query, ';')-1;

                            if (preg_match('/^create .*/i', $query)) {
                                $table_name = trim(substr($query, stripos($query, 'table ')+6));
                                $table_name = substr($table_name, 0, strpos($table_name, ' '));
                                $drop_table_names[] = $table_name;
                            }
                            if (!preg_match('/^drop table .*/i', $query)) {
                                $sql_array[] = $query;
                            }
                        }
                    }
                }
                if (strlen($restore_query)>0) {
                    $sql_array[] = $restore_query;
                }

                tep_session_close();
                tep_db_query("delete from " . TABLE_WHOS_ONLINE);
                tep_db_query("delete from " . TABLE_SESSIONS);
                tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'DB_LAST_RESTORE'");
                tep_db_query("insert into " . TABLE_CONFIGURATION . " values (null, 'Last Database Restore', 'DB_LAST_RESTORE', '" . $read_from . "', 'Last database restore file', '6', '0', null, now(), '', '')");

                if (isset($remove_raw) && ($remove_raw == true)) {
                    unlink($restore_from);
                }

                $messageStack->add_session(SUCCESS_DATABASE_RESTORED, 'success');
            }

            tep_redirect(tep_href_link(FILENAME_BACKUP));
        break;
      
        case 'download':
            $extension = substr($_GET['file'], -3);

            if ( ($extension == 'zip') || ($extension == '.gz') || ($extension == 'sql') ) {
                if ($fp = fopen(DIR_FS_BACKUP . $_GET['file'], 'rb')) {
                    $buffer = fread($fp, filesize(DIR_FS_BACKUP . $_GET['file']));
                    fclose($fp);

                    header('Content-type: application/x-octet-stream');
                    header('Content-disposition: attachment; filename=' . $_GET['file']);

                    echo $buffer;

                    exit;
                }
            } else {
                $messageStack->add(ERROR_DOWNLOAD_LINK_NOT_ACCEPTABLE, 'error');
            }
        break;
      
        case 'deleteconfirm':
            if (strstr($_GET['file'], '..')) tep_redirect(tep_href_link(FILENAME_BACKUP));

            tep_remove(DIR_FS_BACKUP . '/' . $_GET['file']);

            if (!$tep_remove_error) {
                $messageStack->add_session(SUCCESS_BACKUP_DELETED, 'success');
                tep_redirect(tep_href_link(FILENAME_BACKUP));
            }
        break;
    }
}

// check if the backup directory exists
$dir_ok = false;
if (is_dir(DIR_FS_BACKUP)) {
    if (is_writeable(DIR_FS_BACKUP)) {
        $dir_ok = true;
    } else {
        $messageStack->add(ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE, 'error');
    }
} else {
    $messageStack->add(ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST, 'error');
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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_TITLE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_FILE_DATE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_FILE_SIZE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
if ($dir_ok == true) {
    $dir = dir(DIR_FS_BACKUP);
    $contents = [];
    while ($file = $dir->read()) {
        if (!is_dir(DIR_FS_BACKUP . $file)) {
            $contents[] = $file;
        }
    }
    sort($contents);

    for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
        $entry = $contents[$i];

        $check = 0;

        if ((!isset($_GET['file']) || (isset($_GET['file']) && ($_GET['file'] == $entry))) && !isset($buInfo) && ($action != 'backup') && ($action != 'restorelocal')) {
            $file_array['file'] = $entry;
            $file_array['date'] = date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry));
            $file_array['size'] = number_format(filesize(DIR_FS_BACKUP . $entry)) . ' bytes';
            switch (substr($entry, -3)) {
                case 'zip': $file_array['compression'] = 'ZIP'; break;
                case '.gz': $file_array['compression'] = 'GZIP'; break;
                default: $file_array['compression'] = TEXT_NO_EXTENSION; break;
            }

            $buInfo = new ObjectInfo($file_array);
        }

        if (isset($buInfo) && is_object($buInfo) && ($entry == $buInfo->file)) {
            echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
            $onclick_link = 'file=' . $buInfo->file . '&action=restore';
        } else {
            echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
            $onclick_link = 'file=' . $entry;
        }
?>
                <td class="dataTableContent" onclick="document.location.href='<?php echo tep_href_link(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo '<a href="' . tep_href_link(FILENAME_BACKUP, 'action=download&file=' . $entry) . '">' . tep_image(DIR_WS_ICONS . 'file_download.gif', ICON_FILE_DOWNLOAD) . '</a>&nbsp;' . $entry; ?></td>
                <td class="dataTableContent" align="center" onclick="document.location.href='<?php echo tep_href_link(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry)); ?></td>
                <td class="dataTableContent" align="right" onclick="document.location.href='<?php echo tep_href_link(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo number_format(filesize(DIR_FS_BACKUP . $entry)); ?> bytes</td>
                <td class="dataTableContent" align="right"><?php if (isset($buInfo) && is_object($buInfo) && ($entry == $buInfo->file)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $entry) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
    $dir->close();
}
?>
              <tr>
                <td class="smallText" colspan="3"><?php echo TEXT_BACKUP_DIRECTORY . ' ' . DIR_FS_BACKUP; ?></td>
                <td align="right" class="smallText"><?php if ( ($action != 'backup') && (isset($dir)) ) echo '<a href="' . tep_href_link(FILENAME_BACKUP, 'action=backup') . '">' . tep_image_button('button_backup.gif', IMAGE_BACKUP) . '</a>'; if ( ($action != 'restorelocal') && isset($dir) ) echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_BACKUP, 'action=restorelocal') . '">' . tep_image_button('button_restore.gif', IMAGE_RESTORE) . '</a>'; ?></td>
              </tr>
<?php
if (defined('DB_LAST_RESTORE')) {
?>
              <tr>
                <td class="smallText" colspan="4"><?php echo TEXT_LAST_RESTORATION . ' ' . DB_LAST_RESTORE . ' <a href="' . tep_href_link(FILENAME_BACKUP, 'action=forget') . '">' . TEXT_FORGET . '</a>'; ?></td>
              </tr>
<?php
}
?>
            </table></td>
<?php
$heading = [];
$contents = [];

switch ($action) {
    case 'backup':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_BACKUP . '</b>');

      $contents = array('form' => tep_draw_form('backup', FILENAME_BACKUP, 'action=backupnow'));
      $contents[] = array('text' => TEXT_INFO_NEW_BACKUP);

      $contents[] = array('text' => '<br>' . tep_draw_radio_field('compress', 'no', true) . ' ' . TEXT_INFO_USE_NO_COMPRESSION);
      if (file_exists(LOCAL_EXE_GZIP)) $contents[] = array('text' => '<br>' . tep_draw_radio_field('compress', 'gzip') . ' ' . TEXT_INFO_USE_GZIP);
      if (file_exists(LOCAL_EXE_ZIP)) $contents[] = array('text' => tep_draw_radio_field('compress', 'zip') . ' ' . TEXT_INFO_USE_ZIP);

      if ($dir_ok == true) {
        $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('download', 'yes') . ' ' . TEXT_INFO_DOWNLOAD_ONLY . '*<br><br>*' . TEXT_INFO_BEST_THROUGH_HTTPS);
      } else {
        $contents[] = array('text' => '<br>' . tep_draw_radio_field('download', 'yes', true) . ' ' . TEXT_INFO_DOWNLOAD_ONLY . '*<br><br>*' . TEXT_INFO_BEST_THROUGH_HTTPS);
      }

      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_backup.gif', IMAGE_BACKUP) . '&nbsp;<a href="' . tep_href_link(FILENAME_BACKUP) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'restore':
      $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

      $contents[] = array('text' => tep_break_string(sprintf(TEXT_INFO_RESTORE, DIR_FS_BACKUP . (($buInfo->compression != TEXT_NO_EXTENSION) ? substr($buInfo->file, 0, strrpos($buInfo->file, '.')) : $buInfo->file), ($buInfo->compression != TEXT_NO_EXTENSION) ? TEXT_INFO_UNPACK : ''), 35, ' '));
      $contents[] = array('align' => 'center', 'text' => '<br><a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=restorenow') . '">' . tep_image_button('button_restore.gif', IMAGE_RESTORE) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'restorelocal':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_RESTORE_LOCAL . '</b>');

      $contents = array('form' => tep_draw_form('restore', FILENAME_BACKUP, 'action=restorelocalnow', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL . '<br><br>' . TEXT_INFO_BEST_THROUGH_HTTPS);
      $contents[] = array('text' => '<br>' . tep_draw_file_field('sql_file'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL_RAW_FILE);
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_restore.gif', IMAGE_RESTORE) . '&nbsp;<a href="' . tep_href_link(FILENAME_BACKUP) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

      $contents = array('form' => tep_draw_form('delete', FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $buInfo->file . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($buInfo) && is_object($buInfo)) {
        $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=restore') . '">' . tep_image_button('button_restore.gif', IMAGE_RESTORE) . '</a> <a href="' . tep_href_link(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE . ' ' . $buInfo->date);
        $contents[] = array('text' => TEXT_INFO_SIZE . ' ' . $buInfo->size);
        $contents[] = array('text' => '<br>' . TEXT_INFO_COMPRESSION . ' ' . $buInfo->compression);
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
