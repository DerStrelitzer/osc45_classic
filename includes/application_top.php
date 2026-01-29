<?php
/*
  $Id: application_top.php $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

spl_autoload_register(function ($class) {
    if (is_file(DIR_FS_CATALOG . DIR_WS_CLASSES . $class . '.php')) {
        require_once DIR_FS_CATALOG . DIR_WS_CLASSES . $class . '.php';
    }
});

// charset for all purposes
define('CHARSET', 'utf-8');

// start the timer for the page parse time log
define('PAGE_PARSE_START_TIME', microtime());

// disable use_trans_sid as tep_href_link() does this manually
//  if (function_exists('ini_set')) @ini_set('session.use_trans_sid', 0);


// set the level of error reporting
//  error_reporting(E_ALL & ~E_NOTICE);
error_reporting(E_ALL);

// Set the local configuration parameters - mainly for developers
if (file_exists('includes/local/configure.php')) include('includes/local/configure.php');

// include server parameters
require('includes/configure.php');
define('DIR_FS_CACHE', DIR_FS_CATALOG . 'cache/');
define('SESSION_WRITE_DIRECTORY', DIR_FS_CATALOG . 'session');
define('STORE_PAGE_PARSE_TIME_LOG', DIR_FS_CATALOG . 'parselog/page_parse_time.log');

if (strlen(DB_SERVER) < 1) {
    if (is_dir('install')) {
      header('Location: install/index.php');
    }
}

// define the project version
define('PROJECT_VERSION', 'osc45@2016');

// set the type of request (secure or not)
$request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
if (!isset($PHP_SELF) || $PHP_SELF=='') {
    if (isset($_SERVER['PHP_SELF'])&& $_SERVER['PHP_SELF']!='') $PHP_SELF = $_SERVER['PHP_SELF'];
    elseif (isset($_SERVER['SCRIPT_NAME'])&& $_SERVER['SCRIPT_NAME']!='') $PHP_SELF = $_SERVER['SCRIPT_NAME'];
}

if ($request_type == 'NONSSL') {
    define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
} else {
    define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
}

// include the list of project filenames
require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
require(DIR_WS_INCLUDES . 'database_tables.php');

// customization for the design layout
//  define('BOX_WIDTH', 125); // how wide the boxes should be in pixels (default: 125)

// include the database functions
require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
tep_db_connect() or die('Unable to connect to database server!');

// set the application parameters
$extract = $extract_where ='';
/*
$configuration_group_query = tep_db_query(
    "select configuration_group_id from " . TABLE_CONFIGURATION_GROUP . " where visible = '0' and configuration_group_id != '6'"
);
while ($extract_groups = tep_db_fetch_array($configuration_group_query)) {
    $extract .= ($extract!='' ? ',':'') . $extract_groups['configuration_group_id'];
}
$extract_where = $extract!='' ? ' where find_in_set(configuration_group_id, \'' . $extract . '\') = 0': '';
*/
$configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION . $extract_where);
while ($configuration = tep_db_fetch_array($configuration_query)) {
    define($configuration['cfgKey'], $configuration['cfgValue']);
}
tep_db_free_result($configuration_query);

// if gzip_compression is enabled, start to buffer the output
if (GZIP_COMPRESSION == 'true' && ($ext_zlib_loaded = extension_loaded('zlib'))) {
    if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
        ob_start('ob_gzhandler');
    } else {
        ini_set('zlib.output_compression_level', GZIP_LEVEL);
    }
}

// set the HTTP GET parameters manually if search_engine_friendly_urls is enabled
if (SEARCH_ENGINE_FRIENDLY_URLS == 'true') {
    if (strlen(getenv('PATH_INFO')) > 1) {
        $GET_array = [];
        $PHP_SELF = str_replace(getenv('PATH_INFO'), '', $PHP_SELF);
        $vars = explode('/', substr(getenv('PATH_INFO'), 1));
        for ($i=0, $n=sizeof($vars); $i<$n; $i++) {
            if (strpos($vars[$i], '[]')) {
                $GET_array[substr($vars[$i], 0, -2)][] = $vars[$i+1];
            } else {
                $_GET[$vars[$i]] = $vars[$i+1];
            }
            $i++;
        }

        if (sizeof($GET_array) > 0) {
            foreach ($GET_array as $key => $value) {
                $_GET[$key] = $value;
            }
        }
    }
}

// Ingo:  muss hinter dem Abschnitt zur Rekonstuktion der GET-Variablen stehen!
if (isset($_GET['products_id']) && strpos($_GET['products_id'], "-")!==false ) {
    $get_array = preg_split('/\\-/', $_GET['products_id'], -1, PREG_SPLIT_NO_EMPTY);
    if (sizeof($get_array)>1) $_GET['products_id'] = $get_array[sizeof($get_array)-1];
}
if (isset($_GET['cPath'])) {
    $get_array = preg_split('/\\-/', $_GET['cPath'], -1, PREG_SPLIT_NO_EMPTY);
    if (count($get_array)>1) {
        $_GET['cPath'] = $get_array[sizeof($get_array)-1];
    }
    $c_get = preg_split('/_/', $_GET['cPath'], -1, PREG_SPLIT_NO_EMPTY);
    for ($c=0, $cc=count($c_get); $c<$cc; $c++) {
        if (!is_numeric($c_get[$c])) {
            unset($_GET['cPath']);
        }
    }
}
  // include ingo's special functions
require (DIR_WS_INCLUDES . 'ingo_function.php');
// Ingo Ende

// define general functions used application-wide
require(DIR_WS_FUNCTIONS . 'general.php');
require(DIR_WS_FUNCTIONS . 'html_output.php');

// set the cookie domain
if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']=='localhost') {
    $cookie_domain = '';
} else {
    $cookie_domain = $request_type == 'NONSSL' ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN;
}
$cookie_path = $request_type == 'NONSSL' ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH;

// include cache functions if enabled
if (USE_CACHE == 'ja') include(DIR_WS_FUNCTIONS . 'cache.php');

// some code to solve compatibility issues
require(DIR_WS_FUNCTIONS . 'compatibility.php');

// define how the session functions will be used
require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
tep_session_name('iosCid');
  //tep_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
session_set_cookie_params(0, $cookie_path, $cookie_domain);

// set the session ID if it exists
if (isset($_POST[tep_session_name()])) {
     tep_session_id($_POST[tep_session_name()]);
} elseif ($request_type == 'SSL' && isset($_GET[tep_session_name()])) {
     tep_session_id($_GET[tep_session_name()]);
}

// start the session
$session_started = false;
if (SESSION_FORCE_COOKIE_USE == 'True') {
    tep_setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30, $cookie_path, $cookie_domain);

    if (isset($_COOKIE['cookie_test'])) {
        tep_session_start();
        $session_started = true;
    }
} elseif (SESSION_BLOCK_SPIDERS == 'True') {
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

// Ingo security !!!
    if ($spider_flag == false) {
        $session_name = tep_session_name();
        if (STORE_SESSIONS == '') {
            if (isset($_GET[$session_name]) && !is_file(SESSION_WRITE_DIRECTORY . '/sess_'. $_GET[$session_name])) {
                $spider_flag = true;
            }
            if (isset($_POST[$session_name]) && !is_file(SESSION_WRITE_DIRECTORY . '/sess_'. $_POST[$session_name])) {
                $spider_flag = true;
            }
        } else {
            if (isset($_GET[$session_name]) && $_GET[$session_name] != '') {
                $query = tep_db_query("select count(*) as total from " . TABLE_SESSIONS . " where sesskey = '" .tep_db_input($_GET[$session_name]) . "'");
                $result = tep_db_fetch_array($query);
                if ($result['total']!=1) {
                    tep_db_query("delete from " . TABLE_SESSIONS . " where sesskey = '" .tep_db_input($_GET[$session_name]) . "'");
                    $spider_flag = true;
                }
                tep_db_free_result($query);
            }
            if (isset($_POST[$session_name]) && $_POST[$session_name] != '') {
                $query = tep_db_query("select count(*) as total from " . TABLE_SESSIONS . " where sesskey = '" .tep_db_input($_POST[$session_name]) . "'");
                $result = tep_db_fetch_array($query);
                if ($result['total']!=1) {
                    tep_db_query("delete from " . TABLE_SESSIONS . " where sesskey = '" .tep_db_input($_POST[$session_name]) . "'");
                    $spider_flag = true;
                }
                tep_db_free_result($query);
            }
        }
    }

    if ($spider_flag == false) {
        tep_session_start();
        $session_started = true;
    }
} else {
    tep_session_start();
    $session_started = true;
}

// set SID once, even if empty
  $SID = (defined('SID') ? SID : '');

// prevent bad POSTs
if ($session_started == true && $_SERVER['REQUEST_METHOD']=='POST') {
    if (isset($_POST['hidden_trigger']) && isset($_SESSION['trigger']) && $_POST['hidden_trigger'] == $_SESSION['trigger']) {
        // do nothing this time!
    } else {
        tep_redirect(FILENAME_DEFAULT, '', 'NONSSL', false);
    }
}
if ($session_started == true && substr(basename($PHP_SELF),0,5) != 'popup') {
    $_SESSION['trigger'] = md5(uniqid(ceil(time()/rand(2,7))));
}

// verify the ssl_session_id if the feature is enabled
if ($request_type == 'SSL' && SESSION_CHECK_SSL_SESSION_ID == 'True' && ENABLE_SSL == true && $session_started == true) {
    $ssl_session_id = getenv('SSL_SESSION_ID');
    if (!isset($_SESSION['SSL_SESSION_ID'])) {
        $_SESSION['SESSION_SSL_ID'] = $ssl_session_id;
    }

    if ($_SESSION['SESSION_SSL_ID'] != $ssl_session_id) {
        tep_session_destroy();
        tep_redirect(tep_href_link(FILENAME_SSL_CHECK));
    }
}

// verify the browser user agent if the feature is enabled
if ($session_started == true && SESSION_CHECK_USER_AGENT == 'True') {
    $http_user_agent = getenv('HTTP_USER_AGENT');
    if (!isset($_SESSION['SESSION_USER_AGENT'])) {
        $_SESSION['SESSION_USER_AGENT'] = $http_user_agent;
    }

    if ($_SESSION['SESSION_USER_AGENT'] != $http_user_agent) {
        tep_session_destroy();
        tep_redirect(tep_href_link(FILENAME_LOGIN));
    }
}

// verify the IP address if the feature is enabled
if ($session_started == true && SESSION_CHECK_IP_ADDRESS == 'True') {
    $ip_address = tep_get_ip_address();
    if (!isset($_SESSION['SESSION_IP_ADDRESS'])) {
        $_SESSION['SESSION_IP_ADDRESS'] = $ip_address;
    }

    if ($_SESSION['SESSION_IP_ADDRESS'] != $ip_address) {
        tep_session_destroy();
        tep_redirect(tep_href_link(FILENAME_LOGIN));
    }
}

// create the shopping cart if necesary
if ($session_started == true) {
    if (!(isset($_SESSION['cart']) && is_object($_SESSION['cart']))) {
        $_SESSION['cart'] = new ShoppingCart;
    }
    $cart = &$_SESSION['cart'];
} else {
    $cart = new ShoppingCart;
}


// set the language
if ($session_started == true && isset($_SESSION['language'])) {
    $language     = &$_SESSION['language'];
    $languages_id = &$_SESSION['languages_id'];
}

if (!isset($language) || (isset($_GET['language']) && $_GET['language']!='' && strlen($_GET['language'])==2)) {

    $lng = new Language;
    if (isset($_GET['language']) && tep_not_null($_GET['language'])) {
        $lng->set_language($_GET['language']);
    } else {
        $lng->get_browser_language();
    }

    $language_data = xprios_get_language_data();
    $language     = $language_data['directory'];
    $languages_id = $language_data['id'];
    
    $language_code = $language_data['code'];
    $default_title = $language_data['page_title'];
    $default_keywords = $language_data['page_keywords'];
    $default_description = $language_data['page_description'];

} else {
    $language_data = xprios_get_language_data($language);

    $language_code = $language_data['code'];
    $default_title = $language_data['page_title'];
    $default_keywords = $language_data['page_keywords'];
    $default_description = $language_data['page_description'];
}
if ($session_started == true) {
    $_SESSION['language'] = $language;
    $_SESSION['languages_id'] = $languages_id;
}

// include the language translations
require(DIR_WS_LANGUAGES . $language . '.php');
if (defined('CURRENT_PAGE') && file_exists(DIR_WS_LANGUAGES . $language . '/' .  CURRENT_PAGE)) {
    include(DIR_WS_LANGUAGES . $language . '/' .  CURRENT_PAGE);
}

// Ingo
ingo_categories_info();

// currency
// currencies create 
$currencies = new Currencies;

if ($session_started == true) {
    $currency = &$_SESSION['currency'];
}
if (!isset($currency) || isset($_GET['currency']) || (USE_DEFAULT_LANGUAGE_CURRENCY == 'true' && isset($currency) && LANGUAGE_CURRENCY != $currency)) {
    if (isset($_GET['currency'])) {
        if (!$currency = tep_currency_exists($_GET['currency'])) {
            $currency = USE_DEFAULT_LANGUAGE_CURRENCY == 'true' ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
        }
    } else {
        $currency = USE_DEFAULT_LANGUAGE_CURRENCY == 'true' ? LANGUAGE_CURRENCY : DEFAULT_CURRENCY;
    }
}
if ($session_started == true) {
    $_SESSION['currency'] = $currency;
}


// navigation history
if ($session_started == true) {
    if (!isset($_SESSION['navigation'])) {
        $_SESSION['navigation'] = new NavigationHistory;
    }
    $_SESSION['navigation']->add_current_page();
}

// Shopping cart actions
if (isset($_GET['action'])) {
// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled
    if ($session_started == false) {
        tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
    }

    if (DISPLAY_CART == 'true') {
        $goto =  FILENAME_SHOPPING_CART;
        $parameters = array('action', 'cPath', 'products_id', 'pid');
    } else {
        $goto = basename($PHP_SELF);
        if ($_GET['action'] == 'buy_now') {
            $parameters = array('action', 'pid', 'products_id');
        } else {
            $parameters = array('action', 'pid');
        }
    }
    switch ($_GET['action']) {
      // customer wants to update the product quantity in their shopping cart
        case 'update_product' : 
        for ($i=0, $n=sizeof($_POST['products_id']); $i<$n; $i++) {
            if (in_array($_POST['products_id'][$i], (is_array($_POST['cart_delete']) ? $_POST['cart_delete'] : []))) {
                $_SESSION['cart']->remove($_POST['products_id'][$i]);
            } else {
                $attributes = isset($_POST['id'][$_POST['products_id'][$i]]) ? $_POST['id'][$_POST['products_id'][$i]] : '';
                $_SESSION['cart']->add_cart($_POST['products_id'][$i], $_POST['cart_quantity'][$i], $attributes, false);
            }
        }
        tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
        break;

        // customer adds a product from the products page
        case 'add_product':
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        if (isset($_POST['products_id']) && is_numeric($_POST['products_id'])) {
            $_SESSION['cart']->add_cart(
                $_POST['products_id'], 
                $_SESSION['cart']->get_quantity(tep_get_uprid($_POST['products_id'], $id))+1, 
                $id
            );
        }
        tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
        break;

        // performed by the 'buy now' button in product listings and review page
        case 'buy_now' :        
        if (isset($_GET['products_id'])) {
            if (tep_has_product_attributes($_GET['products_id'])) {
                tep_redirect(ingo_product_link($_GET['products_id']));
            } else {
                $_SESSION['cart']->add_cart($_GET['products_id'], $_SESSION['cart']->get_quantity($_GET['products_id'])+1);
            }
        }
        tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
        break;

        case 'notify' :
        if (isset($_SESSION['customer_id']) && $_SESSION['customer_id']>0) {
            if (isset($_GET['products_id'])) {
                $notify = $_GET['products_id'];
            } elseif (isset($_GET['notify'])) {
                $notify = $_GET['notify'];
            } elseif (isset($_POST['notify'])) {
                $notify = $_POST['notify'];
            } else {
                tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'notify'))));
            }
            if (!is_array($notify)) {
                $notify = [$notify];
            }
            for ($i=0, $n=sizeof($notify); $i<$n; $i++) {
                if ($notify[$i]>0) {
                    $check_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . $notify[$i] . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");
                    $check = tep_db_fetch_array($check_query);
                    if ($check['count'] < 1) {
                        tep_db_query("insert into " . TABLE_PRODUCTS_NOTIFICATIONS . " (products_id, customers_id, date_added) values ('" . $notify[$i] . "', '" . (int)$_SESSION['customer_id'] . "', now())");
                    }
                    tep_db_free_result($check_query);
                }
            }
            tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action', 'notify'))));
        } else {
            $_SESSION['navigation']->set_snapshot();
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }
        break;

        case 'notify_remove' :  
        if (isset($_SESSION['customer_id']) && $_SESSION['customer_id']>0 && isset($_GET['products_id'])) {
            $check_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . $_GET['products_id'] . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");
            $check = tep_db_fetch_array($check_query);
            if ($check['count'] > 0) {
                tep_db_query("delete from " . TABLE_PRODUCTS_NOTIFICATIONS . " where products_id = '" . $_GET['products_id'] . "' and customers_id = '" . (int)$_SESSION['customer_id'] . "'");
            }
            tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action'))));
        } else {
            $_SESSION['navigation']->set_snapshot();
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }
        break;

        case 'cust_order' :    
        if (isset($_SESSION['customer_id']) && $_SESSION['customer_id'] >0 && isset($_GET['pid'])) {
            if (tep_has_product_attributes($_GET['pid'])) {
                tep_redirect(tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $_GET['pid']));
            } else {
                $_SESSION['cart']->add_cart($_GET['pid'], $_SESSION['cart']->get_quantity($_GET['pid'])+1);
            }
        }
        tep_redirect(tep_href_link($goto, tep_get_all_get_params($parameters)));
        break;
    }
}

// include the who's online functions
require(DIR_WS_FUNCTIONS . 'whos_online.php');
tep_update_whos_online();

// include the password crypto functions
require(DIR_WS_FUNCTIONS . 'password_funcs.php');

// include validation functions (right now only email address)
require(DIR_WS_FUNCTIONS . 'validations.php');

// auto activate and expire banners
require(DIR_WS_FUNCTIONS . 'banner.php');
tep_activate_banners();
tep_expire_banners();

// auto expire special products
require(DIR_WS_FUNCTIONS . 'specials.php');
tep_expire_specials();

// calculate category path
if (isset($_GET['cPath'])) {
    $cPath = $_GET['cPath'];
} elseif (isset($_GET['products_id']) && !isset($_GET['manufacturers_id'])) {
    $cPath = tep_get_product_path($_GET['products_id']);
    if ($cPath==0) $cPath='';
} else {
    $cPath = '';
}

if (tep_not_null($cPath)) {
    $cPath_array = tep_parse_category_path($cPath);
    $cPath = implode('_', $cPath_array);
    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
} else {
    $current_category_id = 0;
}

// start the breadcrumb trail
$breadcrumb = new Breadcrumb;

$breadcrumb->add(HEADER_TITLE_TOP, HTTP_SERVER);
if (DIR_WS_HTTP_CATALOG!='/') {
  $breadcrumb->add(HEADER_TITLE_CATALOG, tep_href_link(FILENAME_DEFAULT));
}

// add category names or the manufacturer name to the breadcrumb trail
$title_path = '';
if (isset($cPath_array)) {
    for ($i=0, $n=sizeof($cPath_array); $i<$n; $i++) {
//    $categories_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$cPath_array[$i] . "' and language_id = '" . (int)$_SESSION['languages_id'] . "'");
//    if (tep_db_num_rows($categories_query) > 0) {
//      $categories = tep_db_fetch_array($categories_query);
        $breadcrumb->add($categories_info_array[$cPath_array[$i]]['name'], ingo_category_link(implode('_', array_slice($cPath_array, 0, ($i+1)))));
//      $breadcrumb->add($categories['categories_name'], tep_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
//      $title_path .= $categories['categories_name'] . ' '; // ingo
        $title_path .= ($title_path!=''? ', ':'') . $categories_info_array[$cPath_array[$i]]['name'];
/*      } else {
        break;
      }
*/
    }
    $current_category_name = $categories_info_array[$cPath_array[$i-1]]['name'];
} elseif (isset($_GET['manufacturers_id'])) {
    $manufacturers_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'");
    if (tep_db_num_rows($manufacturers_query)) {
        $manufacturers = tep_db_fetch_array($manufacturers_query);
        $breadcrumb->add($manufacturers['manufacturers_name'], tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $_GET['manufacturers_id']));
    }
    tep_db_free_result($manufacturers_query);
}

// add the products name to the breadcrumb trail
if (isset($_GET['products_id']) && basename($PHP_SELF)!=FILENAME_PRODUCT_INFO){
    $this_products_name = tep_get_products_name($_GET['products_id'], $languages_id);
    $breadcrumb->add($this_products_name , tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . ingo_make_link($_GET['products_id'], 'p', $this_products_name)));
}

// message stack for output messages
$messageStack = new MessageStack;

// set which precautions should be checked
define('WARN_INSTALL_EXISTENCE', 'true');
define('WARN_CONFIG_WRITEABLE', 'true');
define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
define('WARN_SESSION_AUTO_START', 'true');
define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');

// Include OSC-AFFILIATE if enabled
if (AFFILIATE_ENABLED == 'ja') include (DIR_WS_INCLUDES . 'affiliate_application_top.php');

// make links for friendly shops
// if (!isset($_SESSION['my_links_serv'])) include (DIR_WS_INCLUDES . 'make_links.php');

// include featured_products
require (DIR_WS_INCLUDES . 'application_featured.php');

// Ingo PWA
if (isset($_SESSION['customer_id']) && $_SESSION['customer_id'] == 0 && substr(basename($PHP_SELF),0,7)=='account') {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
}

// unbezahlte Vorkassebestellungen
if ($session_started == true && !isset($_SESSION['remember_moneyorder'])) {
   // require ('includes/remember_moneyorders.php');
}

// Ingo Vorschaubilder von "normalen" Produkten
$thumbnails_width = defined('THUMBNAIL_IMAGE_WIDTH') && THUMBNAIL_IMAGE_WIDTH>0? THUMBNAIL_IMAGE_WIDTH:(defined('SMALL_IMAGE_WIDTH')&&SMALL_IMAGE_WIDTH>0?SMALL_IMAGE_WIDTH:0);
$thumbnails_height = defined('THUMBNAIL_IMAGE_HEIGHT') && THUMBNAIL_IMAGE_HEIGHT>0? THUMBNAIL_IMAGE_HEIGHT:(defined('SMALL_IMAGE_HEIGHT')&&SMALL_IMAGE_HEIGHT>0?SMALL_IMAGE_HEIGHT:0);
$thumbnail = new Thumbnail(DIR_FS_CATALOG, DIR_WS_IMAGES, 'thumbs', 1, $thumbnails_width, $thumbnails_height);

if (STORE_PAGE_PARSE_TIME == 'ja') {
    error_log(xprios_date_string(STORE_PARSE_DATE_TIME_FORMAT, time()) . ' [END: application_top.php] ' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
}

// Ingo, besuchte Produkte
if ($session_started == true && (!(isset($_SESSION['viewed_products']) && is_object($_SESSION['viewed_products']))) {
    $_SESSION['viewed_products'] = new viewed_products;
}

// crypt mailto-link into entities
$store_owner_email_address = '';
for ($i=0, $j=strlen(STORE_OWNER_EMAIL_ADDRESS); $i<$j; $i++) $store_owner_email_address .= '&#' . ord(substr(STORE_OWNER_EMAIL_ADDRESS, $i, 1)) . ';';
$email_address_crypted_mailto = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;';
