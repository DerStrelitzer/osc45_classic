<?php
/*
  $Id: letter.php, v2.00 2006/01/01,  Ingo (http://forums.oscommerce.de/index.php?showuser=36)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

class letter extends ShippingModules
{
    public 
      $code = 'letter';

// class constructor
    public function __construct()
    {
        global $order;

        $this->title = MODULE_SHIPPING_LETTER_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_LETTER_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_SHIPPING_LETTER_SORT_ORDER') ? MODULE_SHIPPING_LETTER_SORT_ORDER : 0;
        $this->icon = DIR_WS_ICONS . 'shipping_dp.gif';
        $this->tax_class = defined('MODULE_SHIPPING_LETTER_TAX_CLASS') ? MODULE_SHIPPING_LETTER_TAX_CLASS : 0;
        $this->enabled = defined('MODULE_SHIPPING_LETTER_STATUS') && MODULE_SHIPPING_LETTER_STATUS == 'ja' ? true : false;

        if ( $this->enabled == true && (int)MODULE_SHIPPING_LETTER_ZONE > 0) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_LETTER_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }
            if ($check_flag == false) $this->enabled = false;
        }
        if (isset($_SESSION['cart']) && $_SESSION['cart']->total > MODULE_SHIPPING_LETTER_VALUE_MAX) $this->enabled = false;
    }

    public function quote($method = '') {
        global $order, $shipping_weight, $shipping_num_boxes;

        $dest_country = $order->delivery['country']['iso_code_2'];
        $dest_zone = 0;
        $error = false;
        $i = 1;
        while (defined('MODULE_SHIPPING_LETTER_COUNTRIES_' . $i)) {
            $country_zones = preg_split('/[,]/', constant('MODULE_SHIPPING_LETTER_COUNTRIES_' . $i), -1, PREG_SPLIT_NO_EMPTY);
            if (in_array($dest_country, $country_zones)) {
                $dest_zone = $i;
                if ($dest_zone>2) {
                    $this->title = MODULE_SHIPPING_LETTER_TEXT_TITLE_INT;
                }
                break;
            }
            ++$i;
        }

        if ($dest_zone == 0)  {
            $error = true;
        } else {
            $shipping = -1;
            $dp_cost = constant('MODULE_SHIPPING_LETTER_COST_' . $dest_zone);

            $dp_table = preg_split('/[:,]/', $dp_cost);
            for ($i=0; $i<sizeof($dp_table); $i+=2) {
                if ($shipping_weight <= $dp_table[$i]) {
                    $shipping = $dp_table[$i+1];
                    $shipping_method = MODULE_SHIPPING_LETTER_TEXT_WAY . ' ' . $dest_country . ': ';
                    break;
                }
            }

            if ($shipping == -1) {
                $shipping_cost = 0;
                $error = true;
                $shipping_method = MODULE_SHIPPING_LETTER_UNDEFINED_RATE;
            } else {
                $shipping_cost = ($shipping + MODULE_SHIPPING_LETTER_HANDLING);
            }
        }

        // eingetragener Wert ist inklusive Steuer, sofern Warenkorb besteuert ist
        $shipping_cost = $shipping_cost/(100+$order->tax_max['rate'])*100;

        $this->quotes = [
            'id' => $this->code,
            'module' => $this->title,
            'methods' => [
                [
                    'id' => $this->code,
                    'title' => $shipping_method . '<span style="white-space:nowrap">' . $shipping_weight . ' ' . MODULE_SHIPPING_LETTER_TEXT_UNITS . '</span>',
                    'cost' => $shipping_cost * $shipping_num_boxes
                ]
            ]
        ];

        // Steuer nach max.Steuersatz im Warenkorb
        $this->quotes['tax'] = $order->tax_max['rate'];

        if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);

        if ($error == true || $shipping_weigth > constant('MODULE_SHIPPING_LETTER_MAX_' . $dest_zone)) $this->quotes['error'] = MODULE_SHIPPING_LETTER_INVALID_ZONE;

        return $this->quotes;
    }

    public function check() {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_LETTER_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    public function install() {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES ('Deutsche Post Briefsendung', 'MODULE_SHIPPING_LETTER_STATUS', 'ja', 'Wollen Sie den Versand als Briefsendung anbieten?', '6', '0', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Bearbeitungsgebühr', 'MODULE_SHIPPING_LETTER_HANDLING', '0', 'Bearbeitungsgebühr für diese Versandart in Euro', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Steuersatz', 'MODULE_SHIPPING_LETTER_TAX_CLASS', '1', '<b>unbenutzt!</b> Es wird der höchste Steuersatz aus dem Warenkorb benutzt.<!-- Die Versandkosten unterliegen dieser Steuerklasse:-->', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Versand Zone', 'MODULE_SHIPPING_LETTER_ZONE', '1', 'Wird hier eine Zone ausgewählt, ist diese Versandart nur für Aufträge aus dieser Zone möglich.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_SHIPPING_LETTER_SORT_ORDER', '3', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Auftragswert', 'MODULE_SHIPPING_LETTER_VALUE_MAX', '30', 'Maximal erlaubter Auftragswert im Warenkorb.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr noshade size=\"3\" />Briefsendung<br /><br />Zone 1 Land', 'MODULE_SHIPPING_LETTER_COUNTRIES_1', 'DE', 'Tragen Sie hier den Code des Heimatlandes (DE) ein. <br />(Versandart=<b>Briefsendung</b>)', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 1 Kostentabelle', 'MODULE_SHIPPING_LETTER_COST_1', '350:2.2414', 'Versandkosten für Ziele in diese Zone, basierend auf einen Breich des Gewichts. Beispiel: 5:16.50,10:20.50,... Gewicht 0 bis 5 kostet 16.50 für Ziele in Zone 1.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 1 max. Gewicht', 'MODULE_SHIPPING_LETTER_MAX_1', '350', 'Maximal zulässiges Gewicht als Briefsendung in diese Zone.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr />Zone 2 Länder', 'MODULE_SHIPPING_LETTER_COUNTRIES_2', '', 'Durch Komma getrennte Liste von Ländern (ISO 2 Zeichen), für die <b>auch \'Briefsendung</b>\' gilt.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 2 Kostentabelle', 'MODULE_SHIPPING_LETTER_COST_2', '1000:2.00', 'Versandkosten für Ziele in diese Zone, basierend auf einen Breich des Gewichts. Beispiel: 5:25.00,10:35.000,... Gewicht 0 bis 5 kostet 25.00 für Ziele in Zone 2.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 2 max. Gewicht', 'MODULE_SHIPPING_LETTER_MAX_2', '5000', 'Maximal zulässiges Gewicht als Breifsendung in diese Zone.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr size=\"3\">Briefsendung international:<br /><br />Zone 3 Länder', 'MODULE_SHIPPING_LETTER_COUNTRIES_3', 'AD,BE,DK,FO,FI,FR,GR,GL,GB,IE,IT,LI,LU,MC,NL,AT,PT,SM,SE,ES,VA', 'Durch Komma getrennte Liste von Ländern (ISO 2 Zeichen), die Teil der Zone 3 sind. <br />(<b>Ab Zone 3: \'Briefsendung international\'</b>.)', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 3 Kostentabelle', 'MODULE_SHIPPING_LETTER_COST_3', '1000:4.50,1500:6.50,2000:9.00,3000:13.00,4000:17.00,5000:22.00', 'Versandkosten für Ziele in diese Zone, basierend auf einen Breich des Gewichts. Beispiel: 5:29.00,10:39.00,... Gewicht 0 bis 5 kostet 29.00 für Ziele in Zone 3.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 3 max. Gewicht', 'MODULE_SHIPPING_LETTER_MAX_3', '5000', 'Maximal zulässiges Gewicht als Briefsendung in diese Zone.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr />Zone 4 Länder', 'MODULE_SHIPPING_LETTER_COUNTRIES_4', 'CH,PL,CZ,SK', 'Durch Komma getrennte Liste von Ländern (ISO 2 Zeichen), die Teil der Zone 4 sind.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 4 Kostentabelle', 'MODULE_SHIPPING_LETTER_COST_4', '1000:4.50,1500:6.50,2000:9.00,3000:13.00,4000:17.00,5000:22.00', 'Versandkosten für Ziele in diese Zone, basierend auf einen Breich des Gewichts. Beispiel: 5:35.00,10:50.00,... Gewicht 0 bis 5 kostet 35.00 für Ziele in Zone 4.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 4 max. Gewicht', 'MODULE_SHIPPING_LETTER_MAX_4', '5000', 'Maximal zulässiges Gewicht als Briefsendung in diese Zone.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr />Zone 5 Länder', 'MODULE_SHIPPING_LETTER_COUNTRIES_5', 'AL,AM,AZ,BY,BA,BG,EE,GE,GI,IS,YU,KZ,HR,LV,LT,MK,MT,MD,NO,RO,RU,SI,TR,UA,HU,CY', 'Durch Komma getrennte Liste von Ländern (ISO 2 Zeichen), die Teil der Zone 5 sind.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 5 Kostentabelle', 'MODULE_SHIPPING_LETTER_COST_5', '1000:4.50,1500:6.50,2000:9.00,3000:13.00,4000:17.00,5000:22.00', 'Versandkosten für Ziele in diese Zone, basierend auf einen Breich des Gewichts. Beispiel: 5:35.00,10:50.00,... Gewicht 0 bis 5 kostet 35.00 für Ziele in Zone 5.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 5 max. Gewicht', 'MODULE_SHIPPING_LETTER_MAX_5', '', 'Maximal zulässiges Gewicht als Briefsendung in diese Zone.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('<hr />Zone 6 Länder', 'MODULE_SHIPPING_LETTER_COUNTRIES_6', '', 'Durch Komma getrennte Liste von Ländern (ISO 2 Zeichen), die Teil der Zone 6 sind.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 6 Kostentabelle', 'MODULE_SHIPPING_LETTER_COST_6', '', 'Versandkosten für Ziele in diese Zone, basierend auf einen Breich des Gewichts. Beispiel: 5:6.70,10:9.70,... Gewicht 0 bis 5 kostet 6.70 für Ziele in Zone 6.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Zone 6 max. Gewicht', 'MODULE_SHIPPING_LETTER_MAX_6', '', 'Maximal zulässiges Gewicht als Briefsendung in diese Zone.', '6', '0', now())");
    }

    public function remove() {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
        $keys = array('MODULE_SHIPPING_LETTER_STATUS', 'MODULE_SHIPPING_LETTER_HANDLING', 'MODULE_SHIPPING_LETTER_TAX_CLASS', 'MODULE_SHIPPING_LETTER_ZONE', 'MODULE_SHIPPING_LETTER_SORT_ORDER', 'MODULE_SHIPPING_LETTER_VALUE_MAX');

        for ($i = 1; $i <= 6; $i ++) {
            $keys[] = 'MODULE_SHIPPING_LETTER_COUNTRIES_' . $i;
            $keys[] = 'MODULE_SHIPPING_LETTER_COST_' . $i;
            $keys[] = 'MODULE_SHIPPING_LETTER_MAX_' . $i;
        }

        return $keys;
    }
}
