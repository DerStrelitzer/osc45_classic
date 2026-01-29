<?php
/*
  $Id: ipayment.php,v 1.32 2003/01/29 19:57:14 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class ipayment extends PaymentModules
{
    public 
        $code = 'ipayment';

// class constructor
    public function __construct()
    {
        global $order;

        $this->title = MODULE_PAYMENT_IPAYMENT_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_IPAYMENT_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_IPAYMENT_SORT_ORDER') ? MODULE_PAYMENT_IPAYMENT_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_IPAYMENT_STATUS') && MODULE_PAYMENT_IPAYMENT_STATUS == 'ja' ? true : false;

        if (defined('MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID;
        }

        if (is_object($order)) $this->update_status();

        $this->form_action_url = 'https://ipayment.de/merchant/' . (defined('MODULE_PAYMENT_IPAYMENT_ID') ? MODULE_PAYMENT_IPAYMENT_ID : 'my_ipayment_id') . '/processor.php';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_IPAYMENT_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_IPAYMENT_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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
            '    var cc_owner = document.checkout_payment.ipayment_cc_owner.value;' . "\n" .
            '    var cc_number = document.checkout_payment.ipayment_cc_number.value;' . "\n" .
            '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . MODULE_PAYMENT_IPAYMENT_TEXT_JS_CC_OWNER . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n" .
            '      error_message = error_message + "' . MODULE_PAYMENT_IPAYMENT_TEXT_JS_CC_NUMBER . '";' . "\n" .
            '      error = 1;' . "\n" .
            '    }' . "\n" .
            '  }' . "\n";

      return $js;
    }

    function selection() {
      global $order;

      for ($i=1; $i < 13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => xprios_date_string('%B',mktime(0,0,0,$i,1,2000)));
      }

      $today = getdate();
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $expires_year[] = array('id' => xprios_date_string('%y',mktime(0,0,0,1,1,$i)), 'text' => xprios_date_string('%Y',mktime(0,0,0,1,1,$i)));
      }

      $selection = array('id' => $this->code,
                         'module' => $this->title,
                         'fields' => array(array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_OWNER,
                                                 'field' => tep_draw_input_field('ipayment_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                           array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_NUMBER,
                                                 'field' => tep_draw_input_field('ipayment_cc_number')),
                                           array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_EXPIRES,
                                                 'field' => tep_draw_pull_down_menu('ipayment_cc_expires_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('ipayment_cc_expires_year', $expires_year)),
                                           array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER,
                                                 'field' => tep_draw_input_field('ipayment_cc_checkcode', '', 'size="4" maxlength="3"') . '&nbsp;<small>' . MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER_LOCATION . '</small>')));

      return $selection;
    }

    function pre_confirmation_check() {


      $cc_validation = new CreditCardValidation();
      $result = $cc_validation->validate($_POST['ipayment_cc_number'], $_POST['ipayment_cc_expires_month'], $_POST['ipayment_cc_expires_year']);

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
        $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&ipayment_cc_owner=' . urlencode($_POST['ipayment_cc_owner']) . '&ipayment_cc_expires_month=' . $_POST['ipayment_cc_expires_month'] . '&ipayment_cc_expires_year=' . $_POST['ipayment_cc_expires_year'] . '&ipayment_cc_checkcode=' . $_POST['ipayment_cc_checkcode'];

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
      }

      $this->cc_card_type = $cc_validation->cc_type;
      $this->cc_card_number = $cc_validation->cc_number;
      $this->cc_expiry_month = $cc_validation->cc_expiry_month;
      $this->cc_expiry_year = $cc_validation->cc_expiry_year;
    }

    function confirmation() {

      $confirmation = array('title' => $this->title . ': ' . $this->cc_card_type,
                            'fields' => array(array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => $_POST['ipayment_cc_owner']),
                                              array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)),
                                              array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => xprios_date_string('%B, %Y', mktime(0,0,0,$_POST['ipayment_cc_expires_month'], 1, '20' . $_POST['ipayment_cc_expires_year'])))));

      if (tep_not_null($_POST['ipayment_cc_checkcode'])) {
        $confirmation['fields'][] = array('title' => MODULE_PAYMENT_IPAYMENT_TEXT_CREDIT_CARD_CHECKNUMBER,
                                          'field' => $_POST['ipayment_cc_checkcode']);
      }

      return $confirmation;
    }

    function process_button() {
      global $order, $currencies;

      switch (MODULE_PAYMENT_IPAYMENT_CURRENCY) {
        case 'immer EUR':
          $trx_currency = 'EUR';
          break;
        case 'immer USD':
          $trx_currency = 'USD';
          break;
        case 'Entweder EUR oder USD, sonst EUR':
          if ($GLOBALS['currency'] == 'EUR' || $GLOBALS['currency'] == 'USD') {
            $trx_currency = $GLOBALS['currency'];
          } else {
            $trx_currency = 'EUR';
          }
          break;
        case 'Entweder EUR oder USD, sonst USD':
          if ($GLOBALS['currency'] == 'EUR' || $GLOBALS['currency'] == 'USD') {
            $trx_currency = $GLOBALS['currency'];
          } else {
            $trx_currency = 'USD';
          }
          break;
      }

      $process_button_string = tep_draw_hidden_field('silent', '1') .
                               tep_draw_hidden_field('trx_paymenttyp', 'cc') .
                               tep_draw_hidden_field('trxuser_id', MODULE_PAYMENT_IPAYMENT_USER_ID) .
                               tep_draw_hidden_field('trxpassword', MODULE_PAYMENT_IPAYMENT_PASSWORD) .
                               tep_draw_hidden_field('item_name', STORE_NAME) .
                               tep_draw_hidden_field('trx_currency', $trx_currency) .
                               tep_draw_hidden_field('trx_amount', number_format($order->info['total'] * 100 * $currencies->get_value($trx_currency), 0, '','')) .
                               tep_draw_hidden_field('cc_expdate_month', $_POST['ipayment_cc_expires_month']) .
                               tep_draw_hidden_field('cc_expdate_year', $_POST['ipayment_cc_expires_year']) .
                               tep_draw_hidden_field('cc_number', $_POST['ipayment_cc_number']) .
                               tep_draw_hidden_field('cc_checkcode', $_POST['ipayment_cc_checkcode']) .
                               tep_draw_hidden_field('addr_name', $_POST['ipayment_cc_owner']) .
                               tep_draw_hidden_field('addr_email', $order->customer['email_address']) .
                               tep_draw_hidden_field('redirect_url', tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true)) .
                               tep_draw_hidden_field('silent_error_url', tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&ipayment_cc_owner=' . urlencode($_POST['ipayment_cc_owner']), 'SSL', true));

      return $process_button_string;
    }

    function before_process() {
      return false;
    }

    function after_process() {
      return false;
    }

    function get_error() {
      $error = array('title' => IPAYMENT_ERROR_HEADING,
                     'error' => ((isset($_GET['error'])) ? stripslashes(urldecode($_GET['error'])) : IPAYMENT_ERROR_MESSAGE));
      return $error;
    }

    function check() {
      if ($this->_check == null) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_IPAYMENT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('iPayment Modul ', 'MODULE_PAYMENT_IPAYMENT_STATUS', 'ja', 'Wird die Zahlung durch iPayment angeboten?', '6', '1', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Anmeldenummer', 'MODULE_PAYMENT_IPAYMENT_ID', '99999', 'Hier wird die Anmeldenummer des iPayment Service eingetragen', '6', '2', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Benutzer ID', 'MODULE_PAYMENT_IPAYMENT_USER_ID', '99999', 'Hier wird die Benutzer-ID des iPayment Service eingetragen', '6', '3', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Benutzer Passwort', 'MODULE_PAYMENT_IPAYMENT_PASSWORD', '0', 'Hier wird das Benutzer-Passwort des iPayment Service eingetragen', '6', '4', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Benutzte Währung', 'MODULE_PAYMENT_IPAYMENT_CURRENCY', 'Entweder EUR oder USD, sonst EUR', 'Die benutzte Währung für Kreditkartentransaktionen', '6', '5', 'tep_cfg_select_option(array(\'immer EUR\', \'immer USD\', \'Entweder EUR oder USD, sonst EUR\', \'Entweder EUR oder USD, sonst USD\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_IPAYMENT_SORT_ORDER', '0', 'Anzeigereihenfolge. Niedrigste zuerst.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_PAYMENT_IPAYMENT_ZONE', '0', 'Wird hier eine Zone eingetragen, gilt diese Zahlungsart nur in dieser Zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Bestellstatus', 'MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID', '0', 'Wird mit dieser Zahlungsart ein Auftrag erteilt, wird diesem Auftrag diese Stufe des Bestellstatus zugeteilt.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_IPAYMENT_STATUS', 'MODULE_PAYMENT_IPAYMENT_ID', 'MODULE_PAYMENT_IPAYMENT_USER_ID', 'MODULE_PAYMENT_IPAYMENT_PASSWORD', 'MODULE_PAYMENT_IPAYMENT_CURRENCY', 'MODULE_PAYMENT_IPAYMENT_ZONE', 'MODULE_PAYMENT_IPAYMENT_ORDER_STATUS_ID', 'MODULE_PAYMENT_IPAYMENT_SORT_ORDER');
    }
}
