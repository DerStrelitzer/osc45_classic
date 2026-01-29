<?php
/*
  $Id: moneyorder.php,v 1.10 2003/01/29 19:57:14 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class moneyorder extends PaymentModules
{
    public 
        $code = 'moneyorder'; 

    public function __construct()
    {
        global $order;

        $this->title = MODULE_PAYMENT_MONEYORDER_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_PAYMENT_MONEYORDER_SORT_ORDER;
        $this->enabled = ((MODULE_PAYMENT_MONEYORDER_STATUS == 'ja') ? true : false);

        if ((int)MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID;
        }

        if (is_object($order)) {
            $this->update_status();
        }

        $this->email_footer = MODULE_PAYMENT_MONEYORDER_TEXT_EMAIL_FOOTER;
    }

    public function update_status()
    {
        global $order;

        if ($this->enabled == true && (int)MODULE_PAYMENT_MONEYORDER_ZONE > 0) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MONEYORDER_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    public function javascript_validation()
    {
        return false;
    }

    public function selection()
    {
        return [
            'id' => $this->code,
            'module' => $this->title,
            'fields' => [
                [
                    'title' => MODULE_PAYMENT_MONEYORDER_TEXT_SUBTITLE, 
                    'field' => ''
                ]
            ]
        ];
    }

    public function pre_confirmation_check()
    {
        return false;
    }

    public function confirmation()
    {
        return ['title' => MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION];
    }

    public function process_button()
    {
        return false;
    }

    public function before_process()
    {
        return false;
    }

    public function after_process()
    {
        return false;
    }

    public function get_error()
    {
        return false;
    }

    public function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_MONEYORDER_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    public function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Vorkasse', 'MODULE_PAYMENT_MONEYORDER_STATUS', 'ja', 'Wird Vorkasse angeboten?', '6', '1', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now());");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_MONEYORDER_SORT_ORDER', '1', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_PAYMENT_MONEYORDER_ZONE', '1', 'Wird hier eine Zone ausgewählt, wird die Zahlungsart nur für Aufträge aus dieser Zone möglich.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Bestellstatus', 'MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID', '0', 'Wird eine Bestellung ausgelöst, so wird dieser, die hier eingetragene Stufe des Bestellstatus zugewiesen.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    public function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys()
    {
        return [
            'MODULE_PAYMENT_MONEYORDER_STATUS', 
            'MODULE_PAYMENT_MONEYORDER_ZONE', 
            'MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID', 
            'MODULE_PAYMENT_MONEYORDER_SORT_ORDER'
        ];
    }
}