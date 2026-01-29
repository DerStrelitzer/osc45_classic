<?php
/*
  $Id: ot_shipping.php,v 1.15 2003/02/07 22:01:57 dgw_ Exp $
          german by Ingo Malchow, <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class ot_shipping extends OrderTotalModules
{
    public
        $code = 'ot_shipping';

    public function __construct()
    {
        $this->enabled = defined('MODULE_ORDER_TOTAL_SHIPPING_STATUS') && MODULE_ORDER_TOTAL_SHIPPING_STATUS == 'true' ? true : false;
        $this->sort_order = defined('MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER') ? MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER : 0;
        $this->title = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
        $this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
    }

    function process()
    {
        global $order, $currencies;

        if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
            $pass = false;
            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
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
                    $pass = false; break;
            }

            if ($pass == true && ($order->info['total'] - $order->info['shipping_cost'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                $order->info['shipping_method'] = $this->title;
                $order->info['total'] -= $order->info['shipping_cost'];
                $order->info['shipping_cost'] = 0;
            }
        }

        //$module = substr($_SESSION['shipping']['id'], 0, strpos($_SESSION['shipping']['id'], '_'));

        if (tep_not_null($order->info['shipping_method'])) {
            //if ($GLOBALS[$module]->tax_class > 0) {
            if ($order->tax_max['rate']>0) {
                $shipping_tax = $order->tax_max['rate'];
                $shipping_tax_description = $order->tax_max['description'];
                //$shipping_tax = tep_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                //$shipping_tax_description = tep_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);

                $order->info['tax'] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);
                $order->info['tax_groups']["$shipping_tax_description"] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);
                $order->info['total'] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);

                if (DISPLAY_PRICE_WITH_TAX == 'ja') $order->info['shipping_cost'] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);
            }

            $this->output[] = array(
                'title' => $order->info['shipping_method'] . ':',
                'text'  => $currencies->format($order->info['shipping_cost'], true, $order->info['currency'], $order->info['currency_value']),
                'value' => $order->info['shipping_cost']
            );
        }
    }

    function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_SHIPPING_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys()
    {
        return array(
            'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 
            'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', 
            'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 
            'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', 
            'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION'
        );
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Anzeige Versandkosten', 'MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 'Sollen die Versandkosten angezeigt werden?<br /><b>true</b> = ja<br /><b>false</b> = nein ', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', '5', 'Reihenfolge der Anzeige. Niedrigste zuerst.<center>_______________________<br />| deutsche Übersetzung |<br />|_____<a href=\"mailto:videoundco@web.de?subject=Frage zu osCommerce\"><b><u>In</u>g<u>o Malchow</u></b></a>____|</center> ', '6', '2', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Versandkostenfrei möglich', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'false', 'Werden versandkostenfreie Lieferungen angeboten?<br /><b>true</b> = ja<br /><b>false</b> = nein ', '6', '3', 'tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, date_added) values ('Versandkostenfrei über', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', '150', 'Versandkostenfreie Lieferungen erfolgen über dieser Auftragssumme:', '6', '4', 'currencies->format', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Versandkostenfreie Ziele', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION', 'national', 'Versandkostenfreie lieferung ist möglich bei Lieferungen<br /><b>national, international </b>oder <b>beide</b>', '6', '5', 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ', now())");
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
