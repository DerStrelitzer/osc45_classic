<?php
/*
  $Id: dp.php,v 1.36 2003/03/09 02:14:35 harley_vb Exp $
       mod 2006 Ingo, (http://forums.oscommerce.de/index.php?showuser=36)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class dpdhl extends ShippingModules
{
    public 
        $code = 'dpdhl';

    public function __construct()
    {
        global $order;

        $this->title = MODULE_SHIPPING_DPDHL_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_DPDHL_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_DPDHL_SORT_ORDER') ? MODULE_SHIPPING_DPDHL_SORT_ORDER : 0;
        $this->icon = DIR_WS_ICONS . 'shipping_dpdhl.gif';
        $this->tax_class = defined('MODULE_SHIPPING_DPDHL_TAX_CLASS') ? MODULE_SHIPPING_DPDHL_TAX_CLASS : 0;
        $this->enabled = defined('MODULE_SHIPPING_DPDHL_STATUS') && MODULE_SHIPPING_DPDHL_STATUS == 'ja' ? true : false;
        
        $module_zone = intval(defined('MODULE_SHIPPING_DPDHL_ZONE') ? MODULE_SHIPPING_DPDHL_ZONE : '0');

        if ($this->enabled == true && $module_zone > 0) {
            $check_flag = false;
            $check_query = tep_db_query('select '
            . 'zone_id from ' 
            . TABLE_ZONES_TO_GEO_ZONES . ' '
            . 'where geo_zone_id = ' . $module_zone . ' '
            . 'and zone_country_id = ' . (int)$order->delivery['country']['id'] . ' '
            . 'order by zone_id'
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

    public function quote($method = '')
    {
        global $order, $shipping_weight, $shipping_num_boxes;

        $dest_country = $order->delivery['country']['iso_code_2'];
        $dest_zone = 0;
        $error = false;
        $i=1;
        while (defined('MODULE_SHIPPING_DPDHL_COUNTRIES_' . $i)) {
            $country_zones = preg_split('/[,]/', constant('MODULE_SHIPPING_DPDHL_COUNTRIES_' . $i), -1, PREG_SPLIT_NO_EMPTY);
            if (in_array($dest_country, $country_zones)) {
                $dest_zone = $i;
                break;
            }
            ++$i;
        }

        // nicht gefundene Länder nach Zone 6
        if ($dest_zone == 0) {$dest_zone = 6; $i =6; }

        if ($dest_zone == 0) {
            $error = true;
        } else {
            $shipping = -1;
            $dp_table = preg_split('/[:,]/' , constant('MODULE_SHIPPING_DPDHL_COST_' . $i), -1, PREG_SPLIT_NO_EMPTY);
            for ($i=0; $i<sizeof($dp_table); $i+=2) {
                if ($shipping_weight <= $dp_table[$i]) {
                    $shipping = $dp_table[$i+1];
                    $shipping_method = MODULE_SHIPPING_DPDHL_TEXT_WAY . ' ' . $dest_country . ': ';
                    break;
                }
            }

            if ($shipping == -1) {
                $shipping_cost = 0;
                $shipping_method = MODULE_SHIPPING_DPDHL_UNDEFINED_RATE;
            } else {
                $shipping_cost = $shipping + MODULE_SHIPPING_DPDHL_HANDLING;
            }
        }

        // eingetragener Wert ist inklusive Steuer, sofern Warenkorb besteuert ist
        $shipping_cost = $shipping_cost / (100+$order->tax_max['rate']) * 100;

        $this->quotes = [
            'id' => $this->code,
            'module' => $this->title,
            'methods' => [
                [
                    'id' => $this->code,
                    'title' => $shipping_method . ' (' . $shipping_num_boxes . ' x ' . $shipping_weight . ' ' . MODULE_SHIPPING_DPDHL_TEXT_UNITS .')',
                    'cost' => $shipping_cost * $shipping_num_boxes
                ]
            ]
        ];

        // Steuer nach max.Steuersatz im Warenkorb
        $this->quotes['tax'] = $order->tax_max['rate'];

        if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

        if ($error == true) $this->quotes['error'] = MODULE_SHIPPING_DPDHL_INVALID_ZONE;

        return $this->quotes;
    }

    public function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_DPDHL_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    public function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Deutsche Post WorldNet', 'MODULE_SHIPPING_DPDHL_STATUS', 'ja', 'Wollen Sie den Versand als Postpaket anbieten?', '6', '0', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Bearbeitungsgebühr', 'MODULE_SHIPPING_DPDHL_HANDLING', '1.5', 'Bearbeitungsgebühr für diese Versandart in Euro', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Steuersatz', 'MODULE_SHIPPING_DPDHL_TAX_CLASS', '1', '<b>unbenutzt!</b> Es wird der höchste Steuersatz aus dem Warenkorb benutzt.<!-- Die Versandkosten unterliegen dieser Steuerklasse: -->', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Versand Zone', 'MODULE_SHIPPING_DPDHL_ZONE', '0', 'Wird hier eine Zone ausgewählt, ist diese Versandart nur für Aufträge aus Ländern dieser Zone möglich.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihemfolge', 'MODULE_SHIPPING_DPDHL_SORT_ORDER', '1', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr /><b>Deutschland</b>', 'MODULE_SHIPPING_DPDHL_COUNTRIES_1', 'DE', 'Deutschland', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Kostentabelle', 'MODULE_SHIPPING_DPDHL_COST_1', '5:7,10:10.50,20:14', 'Versandkosten für Deutschland, basierend auf einen Bereich des Gewichts. Beispiel: 5:16.50,10:20.50,... Gewicht 0 bis 5 kostet 16.50 für Ziele in Zone 1.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr /><b>DP Zone 1 Länder</b>', 'MODULE_SHIPPING_DPDHL_COUNTRIES_2', 'BE,DK,FI,FR,GR,GB,IE,IT,LU,NL,AT,PL,PT,SE,CH,SK,ES,CZ,AD,FO,GL,LI,MC,SM,SK,VA', 'Durch Komma getrennte Liste von Ländern (ISO 2 Zeichen), die Teil der Zone 1 sind.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('DP Zone 1 Kostentabelle', 'MODULE_SHIPPING_DPDHL_COST_2', '5:17,10:22,20:32', 'Versandkosten für Ziele in Zone 1, basierend auf einen Bereich des Gewichts. Beispiel: 5:25.00,10:35.000,... Gewicht 0 bis 5 kostet 25.00 für Ziele in Zone 2.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr /><b>DP Zone 2 Länder</b>', 'MODULE_SHIPPING_DPDHL_COUNTRIES_3', 'AL,AM,AZ,BY,BA,BG,EE,GE,GI,IS,KZ,HR,LT,LV,MT,MK,MD,NO,RO,RU,YU,SI,TR,UA,HU,BY,CY', 'Durch Komma getrennte Liste von Ländern (ISO 2 Zeichen), die Teil der Zone 2 sind.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('DP Zone 2 Kostentabelle', 'MODULE_SHIPPING_DPDHL_COST_3', '5:30,10:35,20:45', 'Versandkosten für Ziele in Zone 2, basierend auf einen Bereich des Gewichts. Beispiel: 5:29.00,10:39.00,... Gewicht 0 bis 5 kostet 29.00 für Ziele in Zone 3.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr /><b>DP Zone 3 Länder</b>', 'MODULE_SHIPPING_DPDHL_COUNTRIES_4', 'EG,DZ,BH,IR,IQ,IL,YE,JO,CA,KW,LB,LY,MA,OM,SA,SY,TN,US,AE', 'Durch Komma getrennte Liste von Ländern (ISO 2 Zeichen), die Teil der Zone 3 sind.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('DP Zone 3 Kostentabelle', 'MODULE_SHIPPING_DPDHL_COST_4', '5:32,10:42,20:62', 'Versandkosten für Ziele in Zone 3, basierend auf einen Bereich des Gewichts. Beispiel: 5:35.00,10:50.00,... Gewicht 0 bis 5 kostet 35.00 für Ziele in Zone 4.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr /><b>DP Zone 4 Länder</b>', 'MODULE_SHIPPING_DPDHL_COUNTRIES_5', '', 'Durch Komma getrennte Liste von Ländern (ISO 2 Zeichen), die Teil der Zone 4 sind.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('DP Zone 4 Kostentabelle', 'MODULE_SHIPPING_DPDHL_COST_5', '', 'Versandkosten für Ziele in Zone 4, basierend auf einen Bereich des Gewichts. Beispiel: 5:35.00,10:50.00,... Gewicht 0 bis 5 kostet 35.00 für Ziele in Zone 5.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr /><b>Rest!</b>', 'MODULE_SHIPPING_DPDHL_COUNTRIES_6', '', 'Hier <b>keine</b> Länder eintragen. Alle nicht aufgeführten fallen hier hinein.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Rest Kostentabelle', 'MODULE_SHIPPING_DPDHL_COST_6', '5:37,10:52,20:82', 'Versandkosten für diese Ziele, basierend auf einen Bereich des Gewichts. Beispiel: 5:6.70,10:9.70,... Gewicht 0 bis 5 kostet 6.70 für Ziele in Zone 6.', '6', '0', now())");
    }

    public function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys()
    {
        $keys = [
            'MODULE_SHIPPING_DPDHL_STATUS', 
            'MODULE_SHIPPING_DPDHL_HANDLING', 
            'MODULE_SHIPPING_DPDHL_TAX_CLASS', 
            'MODULE_SHIPPING_DPDHL_ZONE', 
            'MODULE_SHIPPING_DPDHL_SORT_ORDER'
        ];
        $i = 1;
        while (defined('MODULE_SHIPPING_DPDHL_COUNTRIES_' . $i)) {
            $keys[] = 'MODULE_SHIPPING_DPDHL_COUNTRIES_' . $i;
            $keys[] = 'MODULE_SHIPPING_DPDHL_COST_' . $i;
            ++$i;
        }
        return $keys;
    }
}
