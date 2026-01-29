<?php
/*
  $Id: compatibility.php,v 1.10 2003/06/23 01:20:05 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

// $_xxx - VARS are always set on php4
  if (!is_array($_GET)) $_GET = [];
  if (!is_array($_POST)) $_POST = [];
  if (!is_array($_COOKIE)) $_COOKIE = [];

  if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type) {
      if(tep_not_null($host) && tep_not_null($type)) {
        @exec("nslookup -type=$type $host", $output);
        foreach ($output as $k => $line) {
          if (preg_match('/^' . preg_quote($host, '/') . '/', $line)) {
            return true;
          }
        }
      }
      return false;
    }
  }
