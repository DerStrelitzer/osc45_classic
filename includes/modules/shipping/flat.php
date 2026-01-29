<?php
/*
  $Id: flat.php,v 1.40 2003/02/05 22:41:52 hpdl Exp $
       mod 2006 Ingo, (http://forums.oscommerce.de/index.php?showuser=36)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class flat extends ShippingModules
{
    public 
        $code = 'flat';

    public function __construct()
    {
        global $order;
      
        $this->title = MODULE_SHIPPING_FLAT_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_FLAT_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_FLAT_SORT_ORDER') ? MODULE_SHIPPING_FLAT_SORT_ORDER : 0;
        $this->icon = '';
        $this->tax_class = defined('MODULE_SHIPPING_FLAT_TAX_CLASS') ? MODULE_SHIPPING_FLAT_TAX_CLASS : 0;
        $this->enabled = defined('MODULE_SHIPPING_FLAT_STATUS') && MODULE_SHIPPING_FLAT_STATUS == 'ja' ? true : false;

        if (isset($order) && is_object($order) && $this->enabled == true && (int)MODULE_SHIPPING_FLAT_ZONE > 0 ) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_FLAT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    public function quote($method = '')
    {
        global $order;

        // eingetragener Wert ist inklusive Steuer, sofern Warenkorb besteuert ist
        $shipping_cost = MODULE_SHIPPING_FLAT_COST / (100+$order->tax_max['rate'])*100;

        $this->quotes = [
            'id'      => $this->code,
            'module'  => MODULE_SHIPPING_FLAT_TEXT_TITLE,
            'methods' => [
                [
                    'id'    => $this->code,
                    'title' => MODULE_SHIPPING_FLAT_TEXT_WAY,
                    'cost'  => $shipping_cost
                ]
            ]
        ];

        // Steuer nach max.Steuersatz im Warenkorb
        $this->quotes['tax'] = $order->tax_max['rate'];

        if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

        return $this->quotes;
    }

    public function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_FLAT_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    public function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Pauschale Versandkosten', 'MODULE_SHIPPING_FLAT_STATUS', 'ja', 'Werden pauschale Versandkosten angeboten?', '6', '0', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Versandkosten', 'MODULE_SHIPPING_FLAT_COST', '5.00', 'Die Versandkosten für alle Bestellungen betragen hier:', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Steuerklasse', 'MODULE_SHIPPING_FLAT_TAX_CLASS', '1', '<b>unbenutzt!</b> Es wird der höchste Steuersatz aus dem Warenkorb benutzt.<!-- Die Versandkosten unterliegen dieser Steuerklasse:-->', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_SHIPPING_FLAT_ZONE', '1', 'Wird hier eine Zone ausgewählt, ist diese Versandart nur für Aufträge aus dieser Zone möglich ', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_SHIPPING_FLAT_SORT_ORDER', '1', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '0', now())");
    }

    public function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys()
    {
        return [
            'MODULE_SHIPPING_FLAT_STATUS', 
            'MODULE_SHIPPING_FLAT_COST', 
            'MODULE_SHIPPING_FLAT_TAX_CLASS', 
            'MODULE_SHIPPING_FLAT_ZONE', 
            'MODULE_SHIPPING_FLAT_SORT_ORDER'
        ];
    }
}
