<?php
/*
  $Id: modules.php,v 1.47 2003/06/29 22:50:52 hpdl Exp $

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

$set = isset($_GET['set']) ? $_GET['set'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($set) {
    case 'shipping':
        $module_type = 'shipping';
        $module_directory = DIR_FS_CATALOG_MODULES . 'shipping/';
        $module_key = 'MODULE_SHIPPING_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_SHIPPING);
    break;
    
    case 'ordertotal':
        $module_type = 'order_total';
        $module_directory = DIR_FS_CATALOG_MODULES . 'order_total/';
        $module_key = 'MODULE_ORDER_TOTAL_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_ORDER_TOTAL);
    break;
    
    case 'payment':
    default:
        $module_type = 'payment';
        $module_directory = DIR_FS_CATALOG_MODULES . 'payment/';
        $module_key = 'MODULE_PAYMENT_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_PAYMENT);
    break;
}


switch ($action) {
    case 'save':
        if (isset($_POST['configuration']) && is_array($_POST['configuration'])) {
            foreach ($_POST['configuration'] as $key => $value) {
                tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . $value . "' where configuration_key = '" . $key . "'");
            }
        }
        tep_redirect(tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $_GET['module']));
    break;
    
    case 'install':
    case 'remove':
        $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
        $class = basename($_GET['module']);
        if (file_exists($module_directory . $class . $file_extension)) {
            include(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $class . $file_extension);
            include($module_directory . $class . $file_extension);
            $module = new $class;
            if ($action == 'install') {
                $module->install();
            } elseif ($action == 'remove') {
                $module->remove();
            }
        }
        tep_redirect(tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class));
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
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULES; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
$file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
$directory_array = [];
if ($dir = @dir($module_directory)) {
    while ($file = $dir->read()) {
        if (!is_dir($module_directory . $file)) {
            if (substr($file, strrpos($file, '.')) == $file_extension) {
                $directory_array[] = $file;
            }
        }
    }
    sort($directory_array);
    $dir->close();
}

$installed_modules = [];
for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
    $file = $directory_array[$i];

    include_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file);
    include_once($module_directory . $file);

    $class = substr($file, 0, strrpos($file, '.'));
    if (tep_class_exists($class)) {
        $module = new $class;
        if ($module->check() > 0) {
            if ($module->sort_order > 0) {
                $installed_modules[$module->sort_order] = $file;
            } else {
                $installed_modules[] = $file;
            }
        } 

        if ((!isset($_GET['module']) || (isset($_GET['module']) && $_GET['module'] == $class)) && !isset($mInfo)) {
            $module_info = array(
                'code' => $module->code,
                'title' => $module->title,
                'description' => $module->description,
                'status' => $module->check()
            );
            $module_keys = $module->keys();

            $keys_extra = [];
            for ($j=0, $k=sizeof($module_keys); $j<$k; $j++) {
                $key_value_query = tep_db_query("select configuration_title, configuration_value, configuration_description, use_function, set_function from " . TABLE_CONFIGURATION . " where configuration_key = '" . $module_keys[$j] . "'");
                if ($key_value = tep_db_fetch_array($key_value_query)) {

                    $keys_extra[$module_keys[$j]]['title'] = $key_value['configuration_title'];
                    $keys_extra[$module_keys[$j]]['value'] = $key_value['configuration_value'];
                    $keys_extra[$module_keys[$j]]['description'] = $key_value['configuration_description'];
                    $keys_extra[$module_keys[$j]]['use_function'] = $key_value['use_function'];
                    $keys_extra[$module_keys[$j]]['set_function'] = $key_value['set_function'];
                }
            }

            $module_info['keys'] = $keys_extra;

            $mInfo = new ObjectInfo($module_info);
        }

        if (isset($mInfo) && is_object($mInfo) && $class == $mInfo->code) {
            if ($module->check() > 0) {
                echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class . '&action=edit') . '\'">' . "\n";
            } else {
                echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
            }
        } else {
            echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class) . '\'">' . "\n";
        }
?>
                <td class="dataTableContent"><?php echo $module->title; ?></td>
                <td class="dataTableContent" align="right"><?php if (is_numeric($module->sort_order)) echo $module->sort_order; ?></td>
                <td class="dataTableContent" align="right"><?php 
        if (isset($mInfo) && is_object($mInfo) && $class == $mInfo->code) { 
            echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); 
        } else { 
            echo '<a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $class) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; 
        } ?>&nbsp;</td>
              </tr>
<?php
    }
}

ksort($installed_modules);
$check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = '" . $module_key . "'");
if (tep_db_num_rows($check_query)) {
    $check = tep_db_fetch_array($check_query);
    if ($check['configuration_value'] != implode(';', $installed_modules)) {
        tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . implode(';', $installed_modules) . "', last_modified = now() where configuration_key = '" . $module_key . "'");
    }
} else {
    tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Installed Modules', '" . $module_key . "', '" . implode(';', $installed_modules) . "', 'This is automatically updated. No need to edit.', '6', '0', now())");
}
?>
              <tr>
                <td colspan="3" class="smallText"><?php echo TEXT_MODULE_DIRECTORY . ' ' . $module_directory; ?></td>
              </tr>
            </table></td>
<?php
$heading = [];
$contents = [];

switch ($action) {
    case 'edit':
        $keys = '';
        foreach ($mInfo->keys as $key => $value) {
            $keys .= '<b>' . $value['title'] . '</b><br>' . "\n" . $value['description'] . "<br>\n";

            if ($value['set_function']) {
                //$keys .= '#' . $value['set_function'] . '#' . $value['value'] . "#<br>\n";
                eval('$keys .= ' . $value['set_function'] . "'" . $value['value'] . "', '" . $key . "');");
            } else {
                $keys .= tep_draw_input_field('configuration[' . $key . ']', $value['value']);
            }
            $keys .= "\n" . '<br><br>';
        }
        $keys = substr($keys, 0, strrpos($keys, '<br><br>'));

        $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');

        $contents = array('form' => tep_draw_form('modules', FILENAME_MODULES, 'set=' . $set . '&module=' . $_GET['module'] . '&action=save'));
        $contents[] = array('text' => $keys);
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $_GET['module']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;

    default:
        $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');

        if ($mInfo->status == '1') {
            $keys = '';
            foreach ($mInfo->keys as $value) {
                $keys .= '<b>' . $value['title'] . '</b><br>#';
                if ($value['use_function']) {
                    $use_function = $value['use_function'];
                    if (strpos($use_function, '->')!==false) {
                        $class_method = explode('->', $use_function);
                        if (!is_object(${$class_method[0]})) {
                            ${$class_method[0]} = new $class_method[0]();
                        }
                        $keys .= tep_call_function($class_method[1], $value['value'], ${$class_method[0]});
                    } else {
                        $keys .= tep_call_function($use_function, $value['value']);
                    }
                } else {
                    $keys .= $value['value'];
                }
                $keys .= '<br><br>';
            }
            $keys = substr($keys, 0, strrpos($keys, '<br><br>'));

            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=remove') . '">' . tep_image_button('button_module_remove.gif', IMAGE_MODULE_REMOVE) . '</a> <a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . (isset($_GET['module']) ? '&module=' . $_GET['module'] : '') . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>');
            $contents[] = array('text' => '<br>' . $mInfo->description);
            $contents[] = array('text' => '<br>' . $keys);
        } else {
            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_MODULES, 'set=' . $set . '&module=' . $mInfo->code . '&action=install') . '">' . tep_image_button('button_module_install.gif', IMAGE_MODULE_INSTALL) . '</a>');
            $contents[] = array('text' => '<br>' . $mInfo->description);
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
