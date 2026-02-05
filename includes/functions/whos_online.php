<?php
/*
  $Id: whos_online.php,v 1.11 2003/06/20 00:12:59 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

function tep_update_whos_online()
{
    global $spider_flag, $user_agent;

    if (isset($_SESSION['customer_id'])) {
        $wo_customer_id = $_SESSION['customer_id'];
        $wo_full_name = $_SESSION['customer_first_name'] . ' ' . $_SESSION['customer_last_name'];
    } else {
        $wo_customer_id = '0';
        $wo_full_name = $spider_flag ? $user_agent : 'Gast';
    }

    $wo_full_name = substr($wo_full_name, 0, 64);
    $wo_session_id = tep_session_id();
    $wo_ip_address = getenv('REMOTE_ADDR');
    $wo_last_page_url = substr(getenv('REQUEST_URI'), 0, 255);

    $current_time = time();
    $xx_mins_ago = ($current_time - WHOS_ONLINE_AGO);

// remove entries that have expired
    tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

    $stored_customer_query = tep_db_query("select count(*) as count from " . TABLE_WHOS_ONLINE . " where session_id = '" . tep_db_input($wo_session_id) . "'");
    $stored_customer = tep_db_fetch_array($stored_customer_query);

    if ($stored_customer['count'] > 0) {
        $update_data = [
            'customer_id'     => (int)$wo_customer_id,
            'full_name'       => $wo_full_name,
            'ip_address'      => $wo_ip_address,
            'time_last_click' => $current_time,
            'last_page_url'   => $wo_last_page_url,
        ];
        tep_db_perform(TABLE_WHOS_ONLINE, $update_data, 'update', "session_id = '" . tep_db_input($wo_session_id) . "'");

    } else {
        $insert_data = [
            'customer_id'     => $wo_customer_id,
            'full_name'       => $wo_full_name,
            'session_id'      => $wo_session_id,
            'ip_address'      => $wo_ip_address,
            'time_entry'      => $current_time,
            'time_last_click' => $current_time,
            'last_page_url'   => $wo_last_page_url
        ];
        tep_db_query("optimize table " . TABLE_WHOS_ONLINE);
        tep_db_perform(TABLE_WHOS_ONLINE, $insert_data);
    }
}
