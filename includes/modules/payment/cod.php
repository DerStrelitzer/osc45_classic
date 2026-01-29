<?php
/*
  $Id: cod.php,v 1.28i by Ingo, http://forums.oscommerce.de/index.php?showuser=36

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/

class cod extends PaymentModules 
{
    public 
      $code = 'cod';

// class constructor
    public function __construct() {
        global $order;

        $this->title = MODULE_PAYMENT_COD_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_COD_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_COD_SORT_ORDER') ? MODULE_PAYMENT_COD_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_COD_STATUS') && MODULE_PAYMENT_COD_STATUS == 'ja' ? true : false;
        if (defined('MODULE_PAYMENT_COD_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_COD_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_COD_ORDER_STATUS_ID;
        }
        if (is_object($order)) {
            $this->update_status();
        }
        $this->confirmation_text = defined('MODULE_PAYMENT_COD_PROVIDER_HAVE_FEE') && MODULE_PAYMENT_COD_PROVIDER_HAVE_FEE=='ja' ? MODULE_PAYMENT_COD_TEXT_PROVIDER_FEE : '';
    }

    public function update_status()
    {
        global $order;
        // disable the module if the order only contains virtual products
        if ($order->content_type == 'virtual') {
            $this->enabled = false;
        }
        // disable the module if country of billingaddress not in the allowed
        if (!in_array($order->billing['country']['iso_code_2'], array(MODULE_PAYMENT_COD_COUNTRIES)) ) {
            $this->enabled = false;
        }
    }

    public function javascript_validation() {
        return false;
    }

    function selection() {
        $selection = [
            'id'     => $this->code,
            'module' => $this->title
        ];
        if ($this->confirmation_text!='') {
            $selection['fields'] = [
                [
                    'title' => $this->confirmation_text, 
                    'field' => ''
                ]
            ];
        }
        return $selection;
    }

    public function pre_confirmation_check()
    {
        return false;
    }

    public function confirmation()
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
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_COD_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Nachnahme', 'MODULE_PAYMENT_COD_STATUS', 'ja', 'Wird die Bezahlung per Nachnahme angeboten?', '6', '1', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Nachnahme', 'MODULE_PAYMENT_COD_PROVIDER_HAVE_FEE', 'ja', 'Kassiert der von Ihnen beauftragte Spediteur (zB. Deutsche Post) vom Empf&auml;nger eine eigene Geb&uuml;hr?', '6', '2', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('L&auml;nder', 'MODULE_PAYMENT_COD_COUNTRIES', 'DE', 'Durch Komma getrennte Liste der Länder (ISO-Code 2) in der Rechnungsadresse, in die per Nachnahme geliefert wird.<br />zB: <div style=\"width:50px;padding:1px;display:inline;border:1px solid black;background:white\">DE,AT,CH</div>', '6', '3', null, null, now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_COD_SORT_ORDER', '2', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '4', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Bestellstatus', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', '0', 'Wird eine Bestellung ausgelöst, so wird dieser, die hier eingetragene Stufe des Bestellstatus zugewiesen.<br /><br />made by Ingo.', '6', '5', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
   }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys()
    {
        return array('MODULE_PAYMENT_COD_STATUS', 'MODULE_PAYMENT_COD_COUNTRIES', 'MODULE_PAYMENT_COD_PROVIDER_HAVE_FEE', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', 'MODULE_PAYMENT_COD_SORT_ORDER');
    }
}
