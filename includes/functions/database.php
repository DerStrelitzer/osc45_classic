<?php
/*
  $Id: database.php, hpdl,xPrioS Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  http://www.xprios.de

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

function tep_db_connect(
    $server = DB_SERVER, 
    $username = DB_SERVER_USERNAME, 
    $password = DB_SERVER_PASSWORD, 
    $database = DB_DATABASE, 
    $link = 'db_link')
{
    global $$link;

    $$link = mysqli_connect($server, $username, $password, $database);

    if (mysqli_connect_errno()==0) {
        mysqli_set_charset($$link, 'utf8mb4');
        tep_db_query("SET @@session.sql_mode = (SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
    } else {
        $$link = false;
    }

    return $$link;
}

function tep_db_close($link = 'db_link')
{
    global $$link;
    return mysqli_close($$link);
}

function tep_db_error($query, $errno, $error)
{
    die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br /><br />' . $query . '<br /><br /><small><font color="#ff0000">[TEP STOP]</font></small><br /><br /></b></font>');
}

function tep_db_query($query, $link = 'db_link')
{
    global $$link;

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'ja')) {
        error_log(xprios_date_string(STORE_PARSE_DATE_TIME_FORMAT, time()) . ' [QUERY] ' . $query . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }

    $result = mysqli_query($$link, $query) or tep_db_error($query, mysqli_errno($$link), mysqli_error($$link));

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'ja')) {
        $result_error = mysqli_error($$link);
        error_log(xprios_date_string(STORE_PARSE_DATE_TIME_FORMAT, time()) . ' [RESULT] ' . ($result!==false ? 'OK': $result_error) . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
    }

    return $result;
}

function tep_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link')
  {
    if ($action == 'insert') {
      
      $field_list = $value_list = '';
      foreach ($data as $key => $value) {
        $field_list .= ($field_list=='' ? '':', ') . $key;
        $value_list .= $value_list=='' ? '':', ';
        switch ((string)$value) {
          case 'NOW()':
          case 'now()':
            $value_list .= 'now()';
          break;
          case 'NULL':
          case 'null':
            $value_list .= 'null';
          break;
          default:
            $value_list .= '\'' . tep_db_input($value) . '\'';
          break;
        }
      }
      $query = 'insert into ' . $table . ' (' . $field_list . ') values (' . $value_list . ')';
      
    } elseif ($action == 'update') {
    
      $query = 'update ' . $table . ' set ';
      foreach ($data as $columns => $value) {
        switch ((string)$value) {
          case 'NOW()':
          case 'now()':
            $query .= $columns . ' = now(), ';
          break;
          case 'NULL':
          case 'null':
            $query .= $columns .= ' = null, ';
          break;
          default:
            $query .= $columns . ' = \'' . tep_db_input($value) . '\', ';
          break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }

    return tep_db_query($query, $link);
}

function tep_db_fetch_array($db_query)
{
    return mysqli_fetch_array($db_query, MYSQLI_ASSOC);
}

function tep_db_num_rows($db_query)
{
    return mysqli_num_rows($db_query);
}

function tep_db_data_seek($result, $row='0')
{
    $row = max(0, intval($row));
    return mysqli_data_seek($result, $row);
}

function tep_db_insert_id($link = 'db_link')
{
    global $$link;
    return mysqli_insert_id($$link);
}

function tep_db_free_result($db_query)
{
    @mysqli_free_result($db_query);
    return true;
}

function tep_db_fetch_fields($db_query)
{
    return mysqli_fetch_field($db_query);
}

function tep_db_affected_rows($link = 'db_link')
{
    global $$link;
    return mysqli_affected_rows($$link);
}

function tep_db_input($string='', $link = 'db_link')
{
    global $$link;
    if ($string === null) {
        $string = '';
    }
    return mysqli_real_escape_string($$link, $string);
}
