<?php
/*
  $Id: invoice.php,v 1.25 2003/02/19 02:14:00 harley_vb Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class invoice extends PaymentModules
{
    public 
        $code = 'invoice';

// class constructor
    public function __construct() {
        global $order;

        $this->code = 'invoice';
        $this->title = MODULE_PAYMENT_INVOICE_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_INVOICE_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_INVOICE_SORT_ORDER') ? MODULE_PAYMENT_INVOICE_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_INVOICE_STATUS') && MODULE_PAYMENT_INVOICE_STATUS == 'ja' ? true : false;

        if (defined('MODULE_PAYMENT_INVOICE_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_INVOICE_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_INVOICE_ORDER_STATUS_ID;
        }

        if (is_object($order)) $this->update_status();
    }

// class methods
    function update_status()
    {
        global $order;

// Rechnung erst ab x-ten Auftrag
        $test_query = tep_db_query("select count(*) as total from " . TABLE_ORDERS . " where customers_id = '" . (int)$_SESSION['customer_id'] . "' AND orders_status = '" . MODULE_PAYMENT_INVOICE_STATUS_MUST_BE . "'");
        $result = tep_db_fetch_array($test_query);
        $total = $result['total'];
        if ($total+1 < MODULE_PAYMENT_INVOICE_FROM_ORDER) {
            $this->enabled = false;
        }

// disable the module if the order only contains virtual products
      /*
      if ($this->enabled == true) {
        if ($order->content_type == 'virtual') {
          $this->enabled = false;
        }
      }
      */
    }


// class methods
    function javascript_validation()
    {
        return false;
    }

    function selection() {
        return [
            'id'     => $this->code,
            'module' => $this->title
        ];
    }

    function pre_confirmation_check()
    {
        return false;
    }

    function confirmation()
    {
        return false;
    }

    function process_button()
    {
        return false;
    }

    function before_process()
    {
        return false;
    }

    function after_process()
    {
        return false;
    }

    function get_error()
    {
        return false;
    }

    function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_INVOICE_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Rechnung', 'MODULE_PAYMENT_INVOICE_STATUS', 'ja', 'Wird die Zahlungen per Rechnung angeboten?', '6', '1', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now());");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('L&auml;nder', 'MODULE_PAYMENT_INVOICE_COUNTRIES', 'DE', 'Durch Komma getrennte Liste der Länder (ISO-Code 2) in der Rechnungsadresse, in die auf Rechnung geliefert wird.<br />zB: <div style=\"width:50px;padding:1px;display:inline;border:1px solid black;background:white\">DE,AT,CH</div>', '6', '2', null, null, now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_INVOICE_SORT_ORDER', '4', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '3', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Bestellstatus', 'MODULE_PAYMENT_INVOICE_ORDER_STATUS_ID', '0', 'Wird eine Bestellung ausgelöst, so wird dieser, die hier eingetragene Stufe des Bestellstatus zugewiesen.', '6', '4', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        // start änderung
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Stammkunden', 'MODULE_PAYMENT_INVOICE_FROM_ORDER', '4', '&quot;&lt;1&quot; = Funktion deaktiviert.<br>Rechnung ab Bestellung <b>x</b> möglich, wenn <b>(x-1)</b> Bestellungen davor, den eingestellten Mindeststatus haben.', '6', '5', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Mindeststatus', 'MODULE_PAYMENT_INVOICE_STATUS_MUST_BE', '3', 'Auftragsstatus, der gezählt wird, um Zahlung auf Rechnung zu erlauben.', '6', '6', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
        // ende änderung
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys()
    {
        return array('MODULE_PAYMENT_INVOICE_STATUS', 'MODULE_PAYMENT_INVOICE_COUNTRIES', 'MODULE_PAYMENT_INVOICE_ORDER_STATUS_ID', 'MODULE_PAYMENT_INVOICE_SORT_ORDER', 'MODULE_PAYMENT_INVOICE_STATUS_MUST_BE', 'MODULE_PAYMENT_INVOICE_FROM_ORDER');
    }
}
