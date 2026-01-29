<?php
/*
  $Id: application_top.php,v 1.280 2003/07/12 09:38:07 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

spl_autoload_register(function ($class) {
    if (is_file(DIR_FS_CATALOG . DIR_WS_CLASSES . $class . '.php')) {
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . $class . '.php';
    }
});

// charset for all purposes
define('CHARSET', 'utf-8');

// set the level of error reporting
//  error_reporting(E_ALL & ~E_NOTICE);
error_reporting(E_ALL);

// disable use_trans_sid as tep_href_link() does this manually
if (function_exists('ini_set')) {
    @ini_set('session.use_trans_sid', 0);
    @ini_set('url_rewriter.tags ', '');
}

// include server parameters
require('includes/configure.php');
require('includes/filenames.php');
require('includes/database_tables.php');
require('includes/functions/general.php');
require('includes/functions/sessions.php');
require('includes/functions/html_output.php');
define('SESSION_WRITE_DIRECTORY', DIR_FS_CATALOG . 'session/');

// set the type of request (secure or not)
$request_type = getenv('HTTPS') == 'on' ? 'SSL' : 'NONSSL';

// set the cookie domain
$cookie_domain = $request_type == 'NONSSL' ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN;
$cookie_path = $request_type == 'NONSSL' ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH;

// set php_self in the local scope
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

// include the database functions
require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
tep_db_connect() or die('Unable to connect to database server!');

// set the application parameters
$configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
while ($configuration = tep_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
}

// currencies class create an instance
$currencies = new Currencies;

// set the session name and save path
session_save_path(SESSION_WRITE_DIRECTORY);
session_name($_POST['session']);
session_set_cookie_params(0, $cookie_path, $cookie_domain);
  
if (isset($_POST['session']) && $_POST['session']!='' && isset($_COOKIE[$_POST['session']]) && $_COOKIE[$_POST['session']]!='') {
    session_id($_COOKIE[$_POST['session']]);
    $SID = '';
} elseif (isset($_POST[$_POST['session']]) && $_POST[$_POST['session']]!='') {
    session_id($_POST[$_POST['session']]);
    $SID = $_POST['session'] . '=' . $_POST[$_POST['session']];
}

// start the session
session_start();
$session_started = true;
$spider_flag = false;

$user_agent = strtolower(getenv('HTTP_USER_AGENT'));
$spider_flag = false;
if (tep_not_null($user_agent)) {
    $spiders = file(DIR_WS_INCLUDES . 'spiders.txt');
    for ($i=0, $n=sizeof($spiders); $i<$n; $i++) {
        if (tep_not_null($spiders[$i])) {
            if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
                $spider_flag = true;
                break;
            }
        }
    }
}

// set the language
// $_SESSION['language'] = $lng->language['directory'];
// $_SESSION['languages_id'] = $lng->language['id'];

require(DIR_WS_LANGUAGES . $_SESSION['language'] . '.php');

// set SID once, even if empty
//  $SID = (defined('SID') ? SID : '');

// include ingo's special functions
require (DIR_WS_INCLUDES . 'ingo_function.php');

// prevent bad POSTs
if ($_SERVER['REQUEST_METHOD']=='POST') {

}

// create the shopping cart & fix the cart if necesary
if (!isset($_SESSION['cart']) || !is_object($_SESSION['cart'])) {
    $_SESSION['cart'] = new ShoppingCart;
}

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        // performed by the 'buy now' button in product listings and review page
        case 'buy_now' :
            if (isset($_POST['id']) && $_POST['id']>0 && isset($_POST['qty']) && $_POST['qty']>0) {
                if (!tep_has_product_attributes($_POST['id'])) {
                    $_SESSION['cart']->add_cart($_POST['id'], $_SESSION['cart']->get_quantity($_POST['id'])+$_POST['qty']);
                }
            }
        break;
    }
}
