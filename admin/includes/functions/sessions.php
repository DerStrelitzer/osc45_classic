<?php
/*
  $Id: sessions.php,v 1.9 2003/06/23 01:20:05 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  if (STORE_SESSIONS == 'mysql') {
    if (!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime')) {
      $SESS_LIFE = 1440;
    }

    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {

      $key = preg_replace('/[\\-,a-z0-9]/i', '', $key);
      if (!tep_db_is_connected(tep_db_link())) {
        tep_db_connect();
      }
      $query = tep_db_query("select value from " . TABLE_SESSIONS . " where sesskey = '" . tep_db_input($key) . "' and expiry > '" . time() . "'");

      $result = tep_db_fetch_array($query);
      if (isset($result['value'])) {
        return $result['value'];
      }

      return false;
    }

    function _sess_write($key, $val) {
      global $SESS_LIFE;

      $expiry = intval(time() + $SESS_LIFE);
      $value  = $val;
        $key    = preg_replace('/[\\-,a-z0-9]/i', '', $key);
        
        if (!tep_db_is_connected(tep_db_link())) {
            tep_db_connect();
        }

      $query = tep_db_query("select count(*) as total from " . TABLE_SESSIONS . " where sesskey = '" . $key . "'");
      $total = tep_db_fetch_array($query);

      if ($total['total'] > 0) {
        return tep_db_query('update ' . TABLE_SESSIONS . ' set expiry = ' . $expiry . ', value = "' . tep_db_input($value) . '" where sesskey = "' . $key . '"');
      } else {
        return tep_db_query('insert into ' . TABLE_SESSIONS . ' values ("' . $key . '", ' . $expiry . ', "' . tep_db_input($value) . '")');
      }
    }

    function _sess_destroy($key) {
			$key = preg_replace('/[\\-,a-z0-9]/i', '', $key);
			if (!tep_db_is_connected(tep_db_link())) {
				tep_db_connect();
			}
      return tep_db_query('delete from ' . TABLE_SESSIONS . ' where sesskey = "' . $key . '"');
    }

    function _sess_gc($maxlifetime) {
        global $SESS_LIFE;
        
        if (!tep_db_is_connected(tep_db_link())) {
            tep_db_connect();
        }
        tep_db_query('delete from ' . TABLE_SESSIONS . ' where expiry < ' . intval(time()-$SESS_LIFE));

        return true;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
  }

  function tep_session_start() {
    return session_start();
  }

  function tep_session_id($sessid = '') {
    if ($sessid != '') {
      return session_id($sessid);
    } else {
      return session_id();
    }
  }

  function tep_session_name($name = '') {
    if ($name != '') {
      return session_name($name);
    } else {
      return session_name();
    }
  }

  function tep_session_close() {
    if (function_exists('session_close')) {
      return session_close();
    }
  }

  function tep_session_destroy() {
    return session_destroy();
  }

  function tep_session_save_path($path = '') {
    if ($path != '') {
      return session_save_path($path);
    } else {
      return session_save_path();
    }
  }
