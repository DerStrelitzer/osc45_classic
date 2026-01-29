<?php
/*
  $Id: table.php,v 1.27 2003/02/05 22:41:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class table extends ShippingModules
{
    public 
        $code = 'table';

// class constructor
    public function __construct()
    {
        global $order;

        $this->title = MODULE_SHIPPING_TABLE_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_TABLE_SORT_ORDER') ? MODULE_SHIPPING_TABLE_SORT_ORDER : 0;
        $this->icon = '';
        $this->tax_class = defined('MODULE_SHIPPING_TABLE_TAX_CLASS') ? MODULE_SHIPPING_TABLE_TAX_CLASS : 0;
        $this->enabled = defined('MODULE_SHIPPING_TABLE_STATUS') && MODULE_SHIPPING_TABLE_STATUS == 'ja' ? true : false;

        if ($this->enabled == true && (int)MODULE_SHIPPING_TABLE_ZONE > 0) {
            $check_flag = false;
            $check_query = tep_db_query(
                "select zone_id " 
                . "from " . TABLE_ZONES_TO_GEO_ZONES . " "
                . "where geo_zone_id = '" . MODULE_SHIPPING_TABLE_ZONE . "' "
                . "and zone_country_id = '" . $order->delivery['country']['id'] . "' "
                . "order by zone_id"
            );
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

// class methods
    public function quote($method = '') {
        global $order, $shipping_weight, $shipping_num_boxes;

        $dest_country = $order->delivery['country']['iso_code_2'];

        if (MODULE_SHIPPING_TABLE_MODE == 'price') {
            $order_total = $_SESSION['cart']->show_total();
        } else {
            $order_total = $shipping_weight;
        }

        $table_cost = preg_split('/[:,]/' , MODULE_SHIPPING_TABLE_COST, -1, PREG_SPLIT_NO_EMPTY);
        $size = sizeof($table_cost);
        for ($i=0, $n=$size; $i<$n; $i+=2) {
            if ($order_total <= $table_cost[$i]) {
                $shipping = $table_cost[$i+1];
                break;
            }
        }

        if (MODULE_SHIPPING_TABLE_MODE == 'weight') {
            $shipping = $shipping * $shipping_num_boxes;
            $table_details = ': ' . $shipping_weight . MODULE_SHIPPING_TABLE_TEXT_WEIGHT_UNITS;
        }

        $shipping_cost = $shipping + MODULE_SHIPPING_TABLE_HANDLING;
      
        // eingetragener Wert ist inklusive Steuer, sofern Warenkorb besteuert ist
        $shipping_cost = $shipping_cost/(100+$order->tax_max['rate'])*100;

        $this->quotes = [
            'id'      => $this->code,
            'module'  => MODULE_SHIPPING_TABLE_TEXT_TITLE,
            'methods' => [
                [
                    'id' => $this->code,
                    'title' => MODULE_SHIPPING_TABLE_TEXT_WAY . ' (' . $dest_country . $table_details . ')' ,
                    'cost' => $shipping_cost
                ]
            ]
        ];

        // Steuer nach max.Steuersatz im Warenkorb
        $this->quotes['tax'] = $order->tax_max['rate'];

        if (tep_not_null($this->icon)) {
            $this->quotes['icon'] = tep_image($this->icon, $this->title);
        }

        return $this->quotes;
    }

    public function check() {
        if ($this->_check == null) {
            $check_query = tep_db_query(
                "select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_TABLE_STATUS'"
            );
            if (tep_db_num_rows($check_query)) {
                $this->_check = true;
            } else {
                $this->_check = false;
            }
        }
        return $this->_check;
    }

    public function install() {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Tabellarische Versandkosten', 'MODULE_SHIPPING_TABLE_STATUS', 'ja', 'Werden tabellarische Versandkosten angeboten?', '6', '0', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Kostentabelle', 'MODULE_SHIPPING_TABLE_COST', '2:5.26,5:5.94,10:8.62,30:17.25,1000:129.31', 'Die Versandkosten werden aus folgender Tabelle ermittelt.<br />Beispiel: 25:8.50,50:5.50,etc.<br />Bis 25 kostet 8.50, von dort bis zu 50 kostet 5.50, etc', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Tabellenmethode', 'MODULE_SHIPPING_TABLE_MODE', 'weight', 'Die Basis der Versandkosten ist entweder der Bestellwert oder das Gesamtgewicht.<br /><b>weight</b> = Gewicht<br /><b>price</b> = Bestellwert', '6', '0', 'tep_cfg_select_option(array(\'weight\', \'price\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Grundgebühr', 'MODULE_SHIPPING_TABLE_HANDLING', '0', 'Die Grundgebühr für diese Versandart.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Steuerklasse', 'MODULE_SHIPPING_TABLE_TAX_CLASS', '1', '<b>unbenutzt!</b> Es wird der höchste Steuersatz aus dem Warenkorb benutzt.<!-- Die Versandkosten unterliegen dieser Steuerklasse:-->', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_SHIPPING_TABLE_ZONE', '1', 'Wird hier eine Zone ausgewählt, ist diese Versandart nur für Aufträge aus dieser Zone möglich.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_SHIPPING_TABLE_SORT_ORDER', '3', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '0', now())");
    }

    public function remove() {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
        return [
            'MODULE_SHIPPING_TABLE_STATUS', 
            'MODULE_SHIPPING_TABLE_COST', 
            'MODULE_SHIPPING_TABLE_MODE', 
            'MODULE_SHIPPING_TABLE_HANDLING', 
            'MODULE_SHIPPING_TABLE_TAX_CLASS', 
            'MODULE_SHIPPING_TABLE_ZONE', 
            'MODULE_SHIPPING_TABLE_SORT_ORDER'
        ];
    }
}
