<?php
/*
  $Id: ot_subtotal.php,v 1.7 2003/02/13 00:12:04 hpdl Exp $
          german by Ingo Malchow, <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class ot_subtotal extends OrderTotalModules
{
    public
        $code = 'ot_subtotal';
        
    public function __construct()
    {
        $this->enabled = defined('MODULE_ORDER_TOTAL_SUBTOTAL_STATUS') && MODULE_ORDER_TOTAL_SUBTOTAL_STATUS == 'true' ? true : false;
        $this->sort_order = defined('MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER') ? MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER : 0;
        $this->title = MODULE_ORDER_TOTAL_SUBTOTAL_TITLE;
        $this->description = MODULE_ORDER_TOTAL_SUBTOTAL_DESCRIPTION;
    }

    function process()
    {
        global $order, $currencies;
        $this->output[] = [
            'title' => $this->title . ':',
            'text'  => $currencies->format($order->info['subtotal'], true, $order->info['currency'], $order->info['currency_value']),
            'value' => $order->info['subtotal']
        ];
    }

    function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys()
    {
        return array('MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER');
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Zwischensumme', 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'true', 'Soll die Zwischensumme angezeigt werden?<br /><b>true</b> = ja<br /><b>false</b> = nein ', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER', '1', 'Reihenfolge der Anzeige. Niedrigste zuerst.<center>_______________________<br />| deutsche Übersetzung |<br />|_____<a href=\"mailto:videoundco@web.de?subject=Frage zu osCommerce\"><b><u>In</u>g<u>o Malchow</u></b></a>____|</center> ', '6', '2', now())");
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
