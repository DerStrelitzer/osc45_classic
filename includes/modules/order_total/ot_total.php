<?php
/*
  $Id: ot_total.php,v 1.7 2003/02/13 00:12:04 hpdl Exp $
          german by Ingo Malchow, <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class ot_total extends OrderTotalModules
{
    public
        $code = 'ot_total';

    public function __construct()
    {
        $this->enabled = defined('MODULE_ORDER_TOTAL_TOTAL_STATUS') && MODULE_ORDER_TOTAL_TOTAL_STATUS == 'true' ? true : false;
        $this->sort_order = defined('MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER') ? MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER : 0;
        $this->title = MODULE_ORDER_TOTAL_TOTAL_TITLE;
        $this->description = MODULE_ORDER_TOTAL_TOTAL_DESCRIPTION;
    }

    function process() {
        global $order, $currencies;
        $this->output[] = array(
            'title' => $this->title . ':',
            'text'  => '<b>' . $currencies->format($order->info['total'], true, $order->info['currency'], $order->info['currency_value']) . '</b>',
            'value' => $order->info['total']
        );
    }

    function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TOTAL_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys()
    {
        return array('MODULE_ORDER_TOTAL_TOTAL_STATUS', 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER');
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Gesamtsumme', 'MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', 'Soll die Gesamtsumme angezeigt werden?<br /><b>true</b> = ja<br /><b>false</b> = nein<br><br><span style=\"padding:5px;background:#fff;color:#f33\"><b>Achtung</b> Dieses Modul muss stets aktiviert sein!</span>', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER', '7', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '2', now())");
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
