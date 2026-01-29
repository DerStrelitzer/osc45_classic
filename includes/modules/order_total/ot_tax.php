<?php
/*
  $Id: ot_tax.php,v 1.14 2003/02/14 05:58:35 hpdl Exp $
          german by Ingo Malchow, <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class ot_tax extends OrderTotalModules
{
    public
        $code = 'ot_tax';

    public function __construct()
    {
        $this->enabled     = defined('MODULE_ORDER_TOTAL_TAX_STATUS') && MODULE_ORDER_TOTAL_TAX_STATUS == 'true' ? true : false;
        $this->sort_order  = defined('MODULE_ORDER_TOTAL_TAX_SORT_ORDER') ? MODULE_ORDER_TOTAL_TAX_SORT_ORDER : 0;
        $this->title       = MODULE_ORDER_TOTAL_TAX_TITLE;
        $this->description = MODULE_ORDER_TOTAL_TAX_DESCRIPTION;
    }

    function process()
    {
        global $order, $currencies;

        if (isset($order->info['tax_groups']) && is_array($order->info['tax_groups'])) {
            foreach ($order->info['tax_groups'] as $key => $value) {
                if ($value > 0) {
                    $this->output[] = array(
                        'title' => $key . ':',
                        'text' => $currencies->format($value, true, $order->info['currency'], $order->info['currency_value']),
                        'value' => $value
                    );
                }
            }  
        }
    }

    function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TAX_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys()
    {
        return array('MODULE_ORDER_TOTAL_TAX_STATUS', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER');
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Umsatzsteuer', 'MODULE_ORDER_TOTAL_TAX_STATUS', 'true', 'Soll die Umsatzsteuer angezeigt werden?<br /><b>true</b> = ja<br /><b>false</b> = nein', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER', '8', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '2', now())");
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
