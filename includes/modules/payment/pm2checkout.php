<?php
/*
  $Id: pm2checkout.php,v 1.19 2003/01/29 19:57:15 hpdl Exp $
          german by Ingo Malchow, (www.strelitzer.de)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class pm2checkout extends PaymentModules
{
    public 
      $code = 'pm2checkout';

// class constructor
    public function __construct()
    {
        global $order;

        $this->title = MODULE_PAYMENT_2CHECKOUT_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_2CHECKOUT_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_2CHECKOUT_SORT_ORDER') ? MODULE_PAYMENT_2CHECKOUT_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_2CHECKOUT_STATUS') && MODULE_PAYMENT_2CHECKOUT_STATUS == 'ja' ? true : false;

        if (defined('MODULE_PAYMENT_2CHECKOUT_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_2CHECKOUT_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_2CHECKOUT_ORDER_STATUS_ID;
        }

        if (is_object($order)) $this->update_status();

        $this->form_action_url = 'https://www.2checkout.com/cgi-bin/Abuyers/purchase.2c';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_2CHECKOUT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_2CHECKOUT_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
      $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
            '    var cc_number = document.checkout_payment.pm_2checkout_cc_number.value;' . "\n" .
            '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . MODULE_PAYMENT_2CHECKOUT_TEXT_JS_CC_NUMBER . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '  }' . "\n";

      return $js;
    }

    function selection()
    {
        global $order;

        for ($i=1; $i < 13; $i++) {
            $expires_month[] = [
                'id' => sprintf('%02d', $i), 
                'text' => sprintf('%02d', $i),
            ];
        }

        $today = getdate();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
            $expires_year[] = [
                'id' => date('y', mktime(0,0,0,1,1,$i)), 
                'text' => date('Y', mktime(0,0,0,1,1,$i))
            ];
        }

        $selection = [
            'id' => $this->code,
            'module' => $this->title,
            'fields' => [
                [
                    'title' => MODULE_PAYMENT_2CHECKOUT_TEXT_CREDIT_CARD_OWNER_FIRST_NAME,
                    'field' => tep_draw_input_field('pm_2checkout_cc_owner_firstname', $order->billing['firstname'])
                ],
                [
                    'title' => MODULE_PAYMENT_2CHECKOUT_TEXT_CREDIT_CARD_OWNER_LAST_NAME,
                    'field' => tep_draw_input_field('pm_2checkout_cc_owner_lastname', $order->billing['lastname'])
                ],
                [
                    'title' => MODULE_PAYMENT_2CHECKOUT_TEXT_CREDIT_CARD_NUMBER,
                    'field' => tep_draw_input_field('pm_2checkout_cc_number')
                ],
                [
                    'title' => MODULE_PAYMENT_2CHECKOUT_TEXT_CREDIT_CARD_EXPIRES,
                    'field' => tep_draw_pull_down_menu('pm_2checkout_cc_expires_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('pm_2checkout_cc_expires_year', $expires_year)
                ],
                [
                    'title' => MODULE_PAYMENT_2CHECKOUT_TEXT_CREDIT_CARD_CHECKNUMBER,
                    'field' => tep_draw_input_field('pm_2checkout_cc_cvv', '', 'size="4" maxlength="3"') . '&nbsp;<small>' . MODULE_PAYMENT_2CHECKOUT_TEXT_CREDIT_CARD_CHECKNUMBER_LOCATION . '</small>'
                ]
            ]
        ];

      return $selection;
    }

    function pre_confirmation_check() {

      $cc_validation = new CreditCardValidation();
      $result = $cc_validation->validate($_POST['pm_2checkout_cc_number'], $_POST['pm_2checkout_cc_expires_month'], $_POST['pm_2checkout_cc_expires_year']);

      $error = '';
      switch ($result) {
        case -1:
          $error = sprintf(TEXT_CCVAL_ERROR_UNKNOWN_CARD, substr($cc_validation->cc_number, 0, 4));
          break;
        case -2:
        case -3:
        case -4:
          $error = TEXT_CCVAL_ERROR_INVALID_DATE;
          break;
        case false:
          $error = TEXT_CCVAL_ERROR_INVALID_NUMBER;
          break;
      }

      if ( ($result == false) || ($result < 1) ) {
        $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&pm_2checkout_cc_owner_firstname=' . urlencode($_POST['pm_2checkout_cc_owner_firstname']) . '&pm_2checkout_cc_owner_lastname=' . urlencode($_POST['pm_2checkout_cc_owner_lastname']) . '&pm_2checkout_cc_expires_month=' . $_POST['pm_2checkout_cc_expires_month'] . '&pm_2checkout_cc_expires_year=' . $_POST['pm_2checkout_cc_expires_year'];

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
      }

      $this->cc_card_type = $cc_validation->cc_type;
      $this->cc_card_number = $cc_validation->cc_number;
      $this->cc_expiry_month = $cc_validation->cc_expiry_month;
      $this->cc_expiry_year = $cc_validation->cc_expiry_year;
    }

    function confirmation() {

      $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                            'fields' => array(array('title' => MODULE_PAYMENT_2CHECKOUT_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => $_POST['pm_2checkout_cc_owner_firstname'] . ' ' . $_POST['pm_2checkout_cc_owner_lastname']),
                                              array('title' => MODULE_PAYMENT_2CHECKOUT_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                              array('title' => MODULE_PAYMENT_2CHECKOUT_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => xprios_date_string('%B, %Y', mktime(0,0,0,$_POST['pm_2checkout_cc_expires_month'], 1, '20' . $_POST['pm_2checkout_cc_expires_year'])))));

      return $confirmation;
    }

    function process_button() {
      global $order;

      $process_button_string = tep_draw_hidden_field('x_login', MODULE_PAYMENT_2CHECKOUT_LOGIN) .
                               tep_draw_hidden_field('x_amount', number_format($order->info['total'], 2)) .
                               tep_draw_hidden_field('x_invoice_num', date('YmdHis')) .
                               tep_draw_hidden_field('x_test_request', ((MODULE_PAYMENT_2CHECKOUT_TESTMODE == 'Test') ? 'Y' : 'N')) .
                               tep_draw_hidden_field('x_card_num', $this->cc_card_number) .
                               tep_draw_hidden_field('cvv', $_POST['pm_2checkout_cc_cvv']) .
                               tep_draw_hidden_field('x_exp_date', $this->cc_expiry_month . substr($this->cc_expiry_year, -2)) .
                               tep_draw_hidden_field('x_first_name', $_POST['pm_2checkout_cc_owner_firstname']) .
                               tep_draw_hidden_field('x_last_name', $_POST['pm_2checkout_cc_owner_lastname']) .
                               tep_draw_hidden_field('x_address', $order->customer['street_address']) .
                               tep_draw_hidden_field('x_city', $order->customer['city']) .
                               tep_draw_hidden_field('x_state', $order->customer['state']) .
                               tep_draw_hidden_field('x_zip', $order->customer['postcode']) .
                               tep_draw_hidden_field('x_country', $order->customer['country']['title']) .
                               tep_draw_hidden_field('x_email', $order->customer['email_address']) .
                               tep_draw_hidden_field('x_phone', $order->customer['telephone']) .
                               tep_draw_hidden_field('x_ship_to_first_name', $order->delivery['firstname']) .
                               tep_draw_hidden_field('x_ship_to_last_name', $order->delivery['lastname']) .
                               tep_draw_hidden_field('x_ship_to_address', $order->delivery['street_address']) .
                               tep_draw_hidden_field('x_ship_to_city', $order->delivery['city']) .
                               tep_draw_hidden_field('x_ship_to_state', $order->delivery['state']) .
                               tep_draw_hidden_field('x_ship_to_zip', $order->delivery['postcode']) .
                               tep_draw_hidden_field('x_ship_to_country', $order->delivery['country']['title']) .
                               tep_draw_hidden_field('x_receipt_link_url', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL')) .
                               tep_draw_hidden_field('x_email_merchant', ((MODULE_PAYMENT_2CHECKOUT_EMAIL_MERCHANT == 'True') ? 'TRUE' : 'FALSE'));

      return $process_button_string;
    }

    function before_process() {
      if ($_POST['x_response_code'] != '1') {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_2CHECKOUT_TEXT_ERROR_MESSAGE), 'SSL', true, false));
      }
    }

    function after_process() {
      return false;
    }

    function get_error() {
      $error = array('title' => MODULE_PAYMENT_2CHECKOUT_TEXT_ERROR,
                     'error' => stripslashes(urldecode($_GET['error'])));
      return $error;
    }

    function check() {
      if ($this->_check == null) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_2CHECKOUT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('2CheckOut', 'MODULE_PAYMENT_2CHECKOUT_STATUS', 'ja', 'Wird die Zahlung durch \'2CheckOut\' Service angeboten?', '6', '0', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Login/Shop Nummer', 'MODULE_PAYMENT_2CHECKOUT_LOGIN', '18157', 'Login/Shop Nummer die beim \'2CheckOut\' Service benutzt wird.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaktionsmodus', 'MODULE_PAYMENT_2CHECKOUT_TESTMODE', 'Test', 'Transaktionsmodus, der mit dem \'2Checkout\' Service benutzt wird.<br /><b>test</b> = zum testen<br /><b>production</b> = zum Einsatz', '6', '0', 'tep_cfg_select_option(array(\'Test\', \'Production\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Handelshinweis', 'MODULE_PAYMENT_2CHECKOUT_EMAIL_MERCHANT', 'True', 'Soll <i>2CheckOut</i> per E-Mail eine Quittung an den Shopbetreiber schicken?<br /><b>true</b> = ja<br /><b>false</b> = nein', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_2CHECKOUT_SORT_ORDER', '0', 'Reihenfolge der Anzeige. Niedrigste zuerst.<center>_______________________<br />| deutsche Übersetzung |<br />|_____<a href=\"mailto:videoundco@web.de?subject=Frage zu osCommerce\"><b><u>In</u>g<u>o Malchow</u></b></a>____|</center> ', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_PAYMENT_2CHECKOUT_ZONE', '0', 'Wird hier eine Zone ausgewählt, wird die Zahlungsart nur für Aufträge aus dieser Zone möglich.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Bestellstatus', 'MODULE_PAYMENT_2CHECKOUT_ORDER_STATUS_ID', '0', 'Wird eine Bestellung ausgelöst, so wird dieser, die hier eingetragene Stufe des Bestellstatus zugewiesen. ', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_2CHECKOUT_STATUS', 'MODULE_PAYMENT_2CHECKOUT_LOGIN', 'MODULE_PAYMENT_2CHECKOUT_TESTMODE', 'MODULE_PAYMENT_2CHECKOUT_EMAIL_MERCHANT', 'MODULE_PAYMENT_2CHECKOUT_ZONE', 'MODULE_PAYMENT_2CHECKOUT_ORDER_STATUS_ID', 'MODULE_PAYMENT_2CHECKOUT_SORT_ORDER');
    }
}
