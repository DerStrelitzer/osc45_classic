<?php
/*
  $Id: application_top.php $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

spl_autoload_register(function ($class) {
    if (is_file(DIR_FS_ADMIN . DIR_WS_CLASSES . $class . '.php')) {
        require DIR_FS_ADMIN . DIR_WS_CLASSES . $class . '.php';
    } elseif (is_file(DIR_FS_CATALOG . DIR_WS_CLASSES . $class . '.php')) {
        require DIR_FS_CATALOG . DIR_WS_CLASSES . $class . '.php';
    }
});

// Start the clock for the page parse time log
define('PAGE_PARSE_START_TIME', microtime());

// Set the level of error reporting
//error_reporting(E_ALL & ~(E_NOTICE|E_USER_NOTICE));
error_reporting(E_ALL); // & ~E_NOTICE);

// charset for all purposes
define('CHARSET', 'utf-8');

// Set the local configuration parameters - mainly for developers
if (file_exists('includes/local/configure.php')) include('includes/local/configure.php');

// Include application configuration parameters
require('includes/configure.php');
define('DIR_FS_CACHE', DIR_FS_CATALOG . 'cache/');
define('SESSION_WRITE_DIRECTORY', DIR_FS_CATALOG . 'session');
define('STORE_PAGE_PARSE_TIME_LOG', DIR_FS_CATALOG . 'parselog/page_parse_time.log');

// Define the project version
define('PROJECT_VERSION', 'osc45');

// set php_self in the local scope
$PHP_SELF = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];

// Used in the "Backup Manager" to compress backups
define('LOCAL_EXE_GZIP', '/usr/bin/gzip');
define('LOCAL_EXE_GUNZIP', '/usr/bin/gunzip');
define('LOCAL_EXE_ZIP', '/usr/local/bin/zip');
define('LOCAL_EXE_UNZIP', '/usr/local/bin/unzip');

// include the list of project filenames
require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
require(DIR_WS_INCLUDES . 'database_tables.php');

// customization for the design layout
  //define('BOX_WIDTH', 125); // how wide the boxes should be in pixels (default: 125)

// Define how do we update currency exchange rates
// Possible values are 'oanda' 'xe' or ''
define('CURRENCY_SERVER_PRIMARY', 'oanda');
define('CURRENCY_SERVER_BACKUP', 'xe');

// include the database functions
require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
  tep_db_connect() or die('Unable to connect to database server!');

// set application wide parameters
$configuration_group_query = tep_db_query("select configuration_group_id from " . TABLE_CONFIGURATION_GROUP . " where visible = '0' and configuration_group_id != '6'");
$extract = '';
while ($extract_groups = tep_db_fetch_array($configuration_group_query)) {
    $extract .= ($extract!='' ? ',':'') . $extract_groups['configuration_group_id'];
}
$extract_where = $extract!='' ? ' where find_in_set(configuration_group_id, \'' . $extract . '\') = 0': '';
$configuration_query = tep_db_query(
    'select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION . $extract_where
);
while ($configuration = tep_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
}

// define our general functions used application-wide
require(DIR_WS_FUNCTIONS . 'general.php');
require(DIR_WS_FUNCTIONS . 'html_output.php');

// some code to solve compatibility issues
require(DIR_WS_FUNCTIONS . 'compatibility.php');

// define how the session functions will be used
require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
tep_session_name('osCAdminID');
tep_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
session_set_cookie_params(0, DIR_WS_ADMIN);

// lets start our session
tep_session_start();

// set the language
if (!isset($_SESSION['language']) || isset($_GET['language'])) {

    $lng = new Language;

    if (isset($_GET['language']) && tep_not_null($_GET['language'])) {
      $lng->set_language($_GET['language']);
    } else {
      $lng->get_browser_language();
    }

    $_SESSION['language'] = $lng->language['directory'];
    $_SESSION['languages_id'] = $lng->language['id'];
}
$language = $_SESSION['language']; 

// include the language translations
require(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');
$current_page = basename($PHP_SELF);
if (file_exists(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $current_page)) {
    include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $current_page);
}

// define our localization functions
require(DIR_WS_FUNCTIONS . 'localization.php');

// Include validation functions (right now only email address)
require(DIR_WS_FUNCTIONS . 'validations.php');

// message stack for output messages
$messageStack = new MessageStack;
  
$currencies = new Currencies;
  
// calculate category path
if (isset($_GET['cPath'])) {
    $cPath = $_GET['cPath'];
} else {
    $cPath = '';
}

if (tep_not_null($cPath)) {
    $cPath_array = tep_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
} else {
    $cPath_array = [];
    $current_category_id = 0;
}

// default open navigation box
if (!isset($_SESSION['selected_box'])) {
    $_SESSION['selected_box'] = 'configuration';
}

if (isset($_GET['selected_box'])) {
    $_SESSION['selected_box'] = $_GET['selected_box'];
}

// the following cache blocks are used in the Tools->Cache section
// ('language' in the filename is automatically replaced by available languages)
$cache_blocks = array(
    array('title' => TEXT_CACHE_CATEGORIES, 'code' => 'categories', 'file' => 'categories_box-language.cache', 'multiple' => true),
    array('title' => TEXT_CACHE_MANUFACTURERS, 'code' => 'manufacturers', 'file' => 'manufacturers_box-language.cache', 'multiple' => true),
    array('title' => TEXT_CACHE_ALSO_PURCHASED, 'code' => 'also_purchased', 'file' => 'also_purchased-language.cache', 'multiple' => true)
);

// check if a default currency is set
  if (!defined('DEFAULT_CURRENCY')) {
    $messageStack->add(ERROR_NO_DEFAULT_CURRENCY_DEFINED, 'error');
  }

// check if a default language is set
  if (!defined('DEFAULT_LANGUAGE')) {
    $messageStack->add(ERROR_NO_DEFAULT_LANGUAGE_DEFINED, 'error');
  }

  if (function_exists('ini_get') && ((bool)ini_get('file_uploads') == false) ) {
    $messageStack->add(WARNING_FILE_UPLOADS_DISABLED, 'warning');
  }

// check if the 'install' directory exists, and warn of its existence
  if (file_exists(dirname(DIR_FS_CATALOG) . '/install')) {
    $messageStack->add('header', WARNING_INSTALL_DIRECTORY_EXISTS, 'warning');
  }

// check if the configure.php file is writeable
  if ( file_exists(dirname(DIR_FS_CATALOG) . '/includes/configure.php') && is_writeable(dirname(DIR_FS_CATALOG) . '/includes/configure.php')) {
    $messageStack->add('header', WARNING_CONFIG_FILE_WRITEABLE, 'warning');
  }

// Include OSC-AFFILIATE
  require ('includes/affiliate_application_top.php');

// create invoice_number if required
  include ('includes/application_invoice.php');

?>