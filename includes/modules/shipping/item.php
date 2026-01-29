<?php
/*
  $Id: item.php,v 1.39 2003/02/05 22:41:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class item extends ShippingModules
{
    public 
      $code = 'item';

    public function __construct()
    {
        global $order;

        $this->title = MODULE_SHIPPING_ITEM_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_ITEM_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_ITEM_SORT_ORDER') ? MODULE_SHIPPING_ITEM_SORT_ORDER : 0;
        $this->icon = '';
        $this->tax_class = defined('MODULE_SHIPPING_ITEM_TAX_CLASS') ? MODULE_SHIPPING_ITEM_TAX_CLASS : 0;
        $this->enabled = defined('MODULE_SHIPPING_ITEM_STATUS') && MODULE_SHIPPING_ITEM_STATUS == 'ja' ? true : false;

        if ($this->enabled == true && (int)MODULE_SHIPPING_ITEM_ZONE > 0) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_ITEM_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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
        global $order, $total_count;

        $shipping_cost = (float)MODULE_SHIPPING_ITEM_COST * $total_count + (float)MODULE_SHIPPING_ITEM_HANDLING;

        // eingetragener Wert ist inklusive Steuer, sofern Warenkorb besteuert ist
        $shipping_cost = $shipping_cost/(100+$order->tax_max['rate'])*100;

        $this->quotes = [
            'id'      => $this->code,
            'module'  => MODULE_SHIPPING_ITEM_TEXT_TITLE,
            'methods' => [
                [
                    'id'    => $this->code,
                    'title' => $total_count .  ' ' . MODULE_SHIPPING_ITEM_TEXT_WAY ,
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
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ITEM_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    public function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Versandkosten pro Artikel', 'MODULE_SHIPPING_ITEM_STATUS', 'ja', 'Werden die Versandkosten \'pro Artikel\' angeboten?', '6', '0', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Versandkosten', 'MODULE_SHIPPING_ITEM_COST', '0.86207', 'Die Versandkosten ermitteln sich hier aus der Anzahl der bestellten Artikel, multipliziert mit diesem Betrag:', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Grundgebühr', 'MODULE_SHIPPING_ITEM_HANDLING', '4.31035', 'Die Grundgebühr für diese Versandart beträgt:', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Steuerklasse', 'MODULE_SHIPPING_ITEM_TAX_CLASS', '1', '<b>unbenutzt!</b> Es wird der höchste Steuersatz aus dem Warenkorb benutzt.<!-- Die Versandkosten unterliegen dieser Steuerklasse:-->', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_SHIPPING_ITEM_ZONE', '1', 'Wird hier eine Zone ausgewählt, ist diese Versandart nur für Aufträge aus dieser Zone möglich.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_SHIPPING_ITEM_SORT_ORDER', '2', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '0', now())");
    }

    public function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
      return array('MODULE_SHIPPING_ITEM_STATUS', 'MODULE_SHIPPING_ITEM_COST', 'MODULE_SHIPPING_ITEM_HANDLING', 'MODULE_SHIPPING_ITEM_TAX_CLASS', 'MODULE_SHIPPING_ITEM_ZONE', 'MODULE_SHIPPING_ITEM_SORT_ORDER');
    }
}
