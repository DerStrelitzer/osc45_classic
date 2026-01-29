<?php
/*
  $Id: ot_giftwrap.php,v 1.2i 2005/01/01 Ingo <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/

class ot_giftwrap extends OrderTotalModules 
{
    public
          $code = 'ot_giftwrap';

    public function __construct()
    {
        $this->enabled = defined('MODULE_ORDER_TOTAL_GIFTWRAP_STATUS') && MODULE_ORDER_TOTAL_GIFTWRAP_STATUS == 'true' ? true : false;
        $this->sort_order = defined('MODULE_ORDER_TOTAL_GIFTWRAP_SORT_ORDER') ? MODULE_ORDER_TOTAL_GIFTWRAP_SORT_ORDER : 0;
        $this->title = MODULE_ORDER_TOTAL_GIFTWRAP_TITLE;
        $this->description = MODULE_ORDER_TOTAL_GIFTWRAP_DESCRIPTION;
    }

    function process()
    {
        global $order, $currencies;

        if (tep_not_null($order->info['giftwrap_method'])  && tep_not_null($order->info['shipping_cost']) ) {
            if ($order->tax_max['rate'] > 0) {
                $giftwrap_tax = $order->tax_max['rate'];
                $giftwrap_tax_description = $order->tax_max['description'];
                //$giftwrap_tax = tep_get_tax_rate(MODULE_ORDER_TOTAL_GIFTWRAP_TAX_CLASS);
                //$giftwrap_tax_description = tep_get_tax_description(MODULE_ORDER_TOTAL_GIFTWRAP_TAX_CLASS, $order->billing['country']['id'], $order->billing['zone_id']);

                $order->info['tax'] += tep_calculate_tax($order->info['giftwrap_cost'], $giftwrap_tax);
                $order->info['tax_groups'][$giftwrap_tax_description] += tep_calculate_tax($order->info['giftwrap_cost'], $giftwrap_tax);
                $order->info['total'] += tep_calculate_tax($order->info['giftwrap_cost'], $giftwrap_tax);

                if (DISPLAY_PRICE_WITH_TAX == 'ja') $order->info['giftwrap_cost'] += tep_calculate_tax($order->info['giftwrap_cost'], $giftwrap_tax);
            }

            $this->output[] = array(
                'title' => $order->info['giftwrap_method'] . ':',
                'text'  => $currencies->format($order->info['giftwrap_cost'], true, $order->info['currency'], $order->info['currency_value']),
                'value' => $order->info['giftwrap_cost']
            );
        }
    }

    function check()
    {
        if (!isset($this->_check)) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_GIFTWRAP_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys()
    {
        return array('MODULE_ORDER_TOTAL_GIFTWRAP_STATUS', 'MODULE_ORDER_TOTAL_GIFTWRAP_SORT_ORDER', 'MODULE_ORDER_TOTAL_GIFTWRAP_TAX_CLASS');
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Geschenkverpackung anzeigen', 'MODULE_ORDER_TOTAL_GIFTWRAP_STATUS', 'true', 'Sollen die Kosten für die Geschenkverpackung angezeigt werden?<br /><b>true</b> = ja<br /><b>false</b> = nein  ', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_ORDER_TOTAL_GIFTWRAP_SORT_ORDER', '4', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '2', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Steuerklasse', 'MODULE_ORDER_TOTAL_GIFTWRAP_TAX_CLASS', '1', '<b>unbenutzt!</b> Es wird der höchste Steuersatz aus dem Warenkorb benutzt. <!-- Die Verpackungskosten unterliegen dieser Steuerklasse. -->', '6', '6', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
