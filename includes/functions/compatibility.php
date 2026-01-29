<?php
/*
  $Id: compatibility.php,v 1.19 2003/04/09 16:12:54 project3000 Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

  Modified by Marco Canini, <m.canini@libero.it>
  - Fixed a bug with arrays in $_xxx vars
*/

// $_xxx - VARS are always set since php4
if (!is_array($_GET)) $_GET = [];
if (!is_array($_POST)) $_POST = [];
if (!is_array($_COOKIE)) $_COOKIE = [];

if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type) {
        if(tep_not_null($host) && tep_not_null($type)) {
            @exec("nslookup -type=$type $host", $output);
            foreach ($output as $k => $line) {
                if (preg_match('/^' . preg_quote($host, '/') . '/i', $line)) {
                    return true;
                }
            }
        }
        return false;
    }
}

// only for compatibility with contributions!
// please check the key in $_SESSION
function tep_session_is_registered ($var='') : bool
{
    $return = false;
    if ($var != '' && isset($_SESSION[$var])) {
        $return = true;
    }
    return $return;
}
