<?php
/*
  $Id: nochex.php,v 1.12 2003/01/29 19:57:15 hpdl Exp $
          german by Ingo Malchow, (www.strelitzer.de)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class nochex extends PaymentModules
{
    public 
        $code = 'nochex';

// class constructor
    public function __construct()
    {
        global $order;

        $this->title = MODULE_PAYMENT_NOCHEX_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_NOCHEX_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_NOCHEX_SORT') ? ORDERMODULE_PAYMENT_NOCHEX_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_NOCHEX_STATUS') && MODULE_PAYMENT_NOCHEX_STATUS == 'ja' ? true : false;

        if (defined('MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID;
        }

        if (is_object($order)) $this->update_status();

        $this->form_action_url = 'https://www.nochex.com/nochex.dll/checkout';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_NOCHEX_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_NOCHEX_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      global $order, $currencies;

      $process_button_string = tep_draw_hidden_field('cmd', '_xclick') .
                               tep_draw_hidden_field('email', MODULE_PAYMENT_NOCHEX_ID) .
                               tep_draw_hidden_field('amount', number_format($order->info['total'] * $currencies->currencies['GBP']['value'], $currencies->currencies['GBP']['decimal_places'])) .
                               tep_draw_hidden_field('ordernumber', $_SESSION['customer_id'] . '-' . date('Ymdhis')) .
                               tep_draw_hidden_field('returnurl', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')) .
                               tep_draw_hidden_field('cancel_return', tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

      return $process_button_string;
    }

    function before_process() {
      return false;
    }

    function after_process() {
      return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      if ($this->_check == null) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_NOCHEX_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('NOCHEX Module', 'MODULE_PAYMENT_NOCHEX_STATUS', 'ja', ' Wird die Zahlung durch NOCHEX angeboten?', '6', '3', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('E-Mail Adresse', 'MODULE_PAYMENT_NOCHEX_ID', 'you@yourbuisness.com', 'Die E-Mail Adresse, zur benutzung für den NOCHEX Service', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_NOCHEX_SORT_ORDER', '0', 'Reihenfolge der Anzeige. Niedrigste zuerst. ', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_PAYMENT_NOCHEX_ZONE', '0', 'Ist hier eine Zone ausgewählt, ist die Zahlungsart nur für Aufträge aus dieser Zone möglich.<center>_______________________<br />| deutsche Übersetzung |<br />|_____<a href=\"mailto:videoundco@web.de\"><b><u>In</u>g<u>o Malchow</u></b></a>____|</center>', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Bestellstatus', 'MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID', '0', 'Wird eine Bestellung ausgelöst, so wird dieser, die hier eingetragene Stufe des Bestellstatus zugewiesen. ', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_NOCHEX_STATUS', 'MODULE_PAYMENT_NOCHEX_ID', 'MODULE_PAYMENT_NOCHEX_ZONE', 'MODULE_PAYMENT_NOCHEX_ORDER_STATUS_ID', 'MODULE_PAYMENT_NOCHEX_SORT_ORDER');
    }
}
