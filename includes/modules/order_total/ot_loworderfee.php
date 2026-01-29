<?php
/*
  $Id: ot_loworderfee.php,v 1.11 2003/02/14 06:03:32 hpdl Exp $
          german by Ingo Malchow, <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class ot_loworderfee extends OrderTotalModules
{
    public
        $code = 'ot_loworderfee';

    public function __construct()
    {
        $this->enabled = defined('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS') && MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS == 'true' ? true : false;
        $this->sort_order = defined('MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER') ? MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER : 0;
        $this->title = MODULE_ORDER_TOTAL_LOWORDERFEE_TITLE;
        $this->description = MODULE_ORDER_TOTAL_LOWORDERFEE_DESCRIPTION;
    }

    function process()
    {
        global $order, $currencies;

        if (MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE == 'true') {
            switch (MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION) {
                case 'national':
                    if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; 
                    break;
                case 'international':
                    if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; 
                    break;
                case 'both':
                    $pass = true; 
                    break;
                default:
                $pass = false; 
            }

            if ($pass == true && $order->info['subtotal'] < MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER ) {
                $tax = $order->tax_max['rate'];
                $tax_description = $order->tax_max['description'];
                //$tax = tep_get_tax_rate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
                //$tax_description = tep_get_tax_description(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $order->delivery['country']['id'], $order->delivery['zone_id']);
                if ($tax>0) {
                    $order->info['tax'] += tep_calculate_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax);
                    $order->info['tax_groups']["$tax_description"] += tep_calculate_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax);
                }
                $order->info['total'] += MODULE_ORDER_TOTAL_LOWORDERFEE_FEE + tep_calculate_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax);

                $this->output[] = array(
                    'title' => $this->title . ':',
                    'text'  => $currencies->format(tep_add_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax), true, $order->info['currency'], $order->info['currency_value']),
                    'value' => tep_add_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax)
                );
            }
        }
    }

    function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys()
    {
        return array(
            'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 
            'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER', 
            'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE', 
            'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER', 
            'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE', 
            'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION', 
            'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS'
        );
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Kleinmengengebühr anzeigen', 'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 'true', 'Soll die Kleinmengengebühr angezeigt werden?<br /><b>true</b> = ja<br /><b>false</b> = nein', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER', '3', 'Reihenfolge der Anzeige. Niedrigste zuerst.<center>_______________________<br />| deutsche Übersetzung |<br />|_____<a href=\"http://www.strelitzer.de\"><b><u>In</u>g<u>o Malchow</u></b></a>____|</center> ', '6', '2', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Kleinmengengebühr', 'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE', 'true', 'Soll die Kleinmengengebühr berechnet werden?<br /><b>true</b> = ja<br /><b>false</b> = nein ', '6', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Kleinmengengebühr unter', 'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER', '40', 'Die Kleinmengengebühr wird bei Aufträgen unter diesem Wert berechnet:', '6', '4', 'currencies->format', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Kleinmengengebühr Höhe', 'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE', '2.1551', 'Die Kleinmengengebühr beträgt:', '6', '5', 'currencies->format', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Kleinmengengebühr Ziel', 'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION', 'both', 'Die Kleinmengengebühr wird für den Versand in folgende Ziele berechnet: <br /><b>national, international, beide</b>', '6', '6', 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Steuerklasse', 'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS', '1', '<b>unbenutzt!</b> Es wird der höchste Steuersatz aus dem Warenkorb benutzt. <!-- Die Kleinmengengebühr unterliegt dieser Steuerklasse. -->', '6', '7', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
