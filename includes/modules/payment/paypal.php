<?php
/*
  $Id: paypal.php,v 1.39 2003/01/29 19:57:15 hpdl Exp $
          german by Ingo Malchow, (www.strelitzer.de)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class paypal extends PaymentModules
{
    public 
        $code = 'paypal',
        $title = 'Paypal Website Payment Standard';

// class constructor
    public function __construct() {
        global $order;

        $this->title = MODULE_PAYMENT_PAYPAL_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_PAYPAL_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_PAYPAL_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_PAYPAL_STATUS') && MODULE_PAYMENT_PAYPAL_STATUS == 'ja' ? true : false;

        if (defined('MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID;
        }

        if (is_object($order)) $this->update_status();

        $this->form_action_url = 'https://secure.paypal.com/cgi-bin/webscr';
    }

// class methods
    public function update_status() {
        global $order;

        if ($this->enabled == true && (int)MODULE_PAYMENT_PAYPAL_ZONE > 0) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

    public function javascript_validation() {
        return false;
    }

    public function selection() {
        return ['id' => $this->code, 'module' => $this->title];
    }

    public function pre_confirmation_check() {
        return false;
    }

    public function confirmation() {
        return false;
    }

    public function process_button() {
        global $order, $currencies;

        if (MODULE_PAYMENT_PAYPAL_CURRENCY == 'Selected Currency') {
            $my_currency = $GLOBALS['currency'];
        } else {
            $my_currency = substr(MODULE_PAYMENT_PAYPAL_CURRENCY, 5);
        }
        if (!in_array($my_currency, ['CAD', 'EUR', 'GBP', 'JPY', 'USD'])) {
            $my_currency = 'USD';
        }
        $process_button_string = tep_draw_hidden_field('cmd', '_xclick')
            . tep_draw_hidden_field('business', MODULE_PAYMENT_PAYPAL_ID)
            . tep_draw_hidden_field('item_name', STORE_NAME)
            . tep_draw_hidden_field('amount', number_format(($order->info['total'] - $order->info['shipping_cost']) * $currencies->get_value($my_currency), $currencies->get_decimal_places($my_currency)))
            . tep_draw_hidden_field('shipping', number_format($order->info['shipping_cost'] * $currencies->get_value($my_currency), $currencies->get_decimal_places($my_currency)))
            . tep_draw_hidden_field('currency_code', $my_currency)
            . tep_draw_hidden_field('return', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'))
            . tep_draw_hidden_field('cancel_return', tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));

        return $process_button_string;
    }

    public function before_process() {
        return false;
    }

    public function after_process() {
        return false;
    }

    public function output_error() {
        return false;
    }

    public function check() {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYPAL_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    public function install() {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('PayPal Modul', 'MODULE_PAYMENT_PAYPAL_STATUS', 'ja', 'Wird die Zahlung mit PayPal angeboten?', '6', '3', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('E-Mail Adresse', 'MODULE_PAYMENT_PAYPAL_ID', 'info@shop.de', 'Die E-Mail Adresse zur Benutzung mit dem PayPal Service', '6', '4', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaktionswährung', 'MODULE_PAYMENT_PAYPAL_CURRENCY', 'Selected Currency', 'Die Währung, die mit diesem Kreditkartenservice benutzt wird.', '6', '6', 'tep_cfg_select_option(array(\'Selected Currency\',\'Only USD\',\'Only CAD\',\'Only EUR\',\'Only GBP\',\'Only JPY\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_PAYPAL_SORT_ORDER', '0', 'Reihenfolge der Anzeige. Niedrigste zuerst.<center>_______________________<br />| deutsche Übersetzung |<br />|_____<a href=\"mailto:videoundco@web.de?subject=Frage zu osCommerce\"><b><u>In</u>g<u>o Malchow</u></b></a>____|</center> ', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_PAYMENT_PAYPAL_ZONE', '0', 'Wird hier eine Zone ausgewählt, wird die Zahlungsart nur für Aufträge aus dieser Zone möglich. ', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Bestellstatus', 'MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID', '2', 'Wird eine Bestellung ausgelöst, so wird dieser, die hier eingetragene Stufe des Bestellstatus zugewiesen. ', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    public function remove() {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys() {
        return [
            'MODULE_PAYMENT_PAYPAL_STATUS', 
            'MODULE_PAYMENT_PAYPAL_ID', 
            'MODULE_PAYMENT_PAYPAL_CURRENCY', 
            'MODULE_PAYMENT_PAYPAL_ZONE', 
            'MODULE_PAYMENT_PAYPAL_ORDER_STATUS_ID', 
            'MODULE_PAYMENT_PAYPAL_SORT_ORDER'
        ];
    }
}
