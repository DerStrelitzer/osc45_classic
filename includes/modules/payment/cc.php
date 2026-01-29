<?php
/*
  $Id: cc.php,v 1.53 2003/02/04 09:55:01 project3000 Exp $
           german by Ingo Malchow, (www.strelitzer.de)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class cc extends PaymentModules
{
    public 
      $code = 'cc',
      $title = 'Credit Card, not for production use!';

// class constructor
    public function __construct() {
        global $order;

        $this->title = MODULE_PAYMENT_CC_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_CC_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_CC_SORT_ORDER') ? MODULE_PAYMENT_CC_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_CC_STATUS') && MODULE_PAYMENT_CC_STATUS == 'ja' ? true : false;

        if (defined('MODULE_PAYMENT_CC_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_CC_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_CC_ORDER_STATUS_ID;
        }

        if (is_object($order)) $this->update_status();
    }

// class methods
    public function update_status()
    {
        global $order;

        if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_CC_ZONE > 0) ) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_CC_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

    public function javascript_validation()
    {
        $js = '  if (payment_value == "' . $this->code . '") {' . "\n"
            . '    var cc_owner = document.checkout_payment.cc_owner.value;' . "\n"
            . '    var cc_number = document.checkout_payment.cc_number.value;' . "\n"
            . '    if (cc_owner == "" || cc_owner.length < ' . CC_OWNER_MIN_LENGTH . ') {' . "\n"
            . '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_OWNER . '";' . "\n"
            . '      error = 1;' . "\n"
            . '    }' . "\n"
            . '    if (cc_number == "" || cc_number.length < ' . CC_NUMBER_MIN_LENGTH . ') {' . "\n"
            . '      error_message = error_message + "' . MODULE_PAYMENT_CC_TEXT_JS_CC_NUMBER . '";' . "\n"
            . '      error = 1;' . "\n"
            . '    }' . "\n"
            . '  }' . "\n";

        return $js;
    }

    public function selection() {
        global $order;

        for ($i=1; $i<13; $i++) {
            $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => xprios_date_string('%B', mktime(0,0,0,$i,1,2000)));
        }

        $today = getdate();
        for ($i=$today['year']; $i < $today['year']+10; $i++) {
            $expires_year[] = array('id' => xprios_date_string('%y',mktime(0,0,0,1,1,$i)), 'text' => xprios_date_string('%Y', mktime(0,0,0,1,1,$i)));
        }

        $selection = [
            'id' => $this->code,
            'module' => $this->title,
            'fields' => [
                [
                    'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
                    'field' => tep_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])
                ],
                [
                    'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
                    'field' => tep_draw_input_field('cc_number')
                ],
                [
                    'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
                    'field' => tep_draw_pull_down_menu('cc_expires_month', $expires_month) . '&nbsp;' 
                               . tep_draw_pull_down_menu('cc_expires_year', $expires_year)
                ]
            ]
        ];

        return $selection;
    }

    public function pre_confirmation_check() {

        $cc_validation = new CreditCardValidation();
        $result = $cc_validation->validate($_POST['cc_number'], $_POST['cc_expires_month'], $_POST['cc_expires_year']);

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
            $payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error) . '&cc_owner=' . urlencode($_POST['cc_owner']) . '&cc_expires_month=' . $_POST['cc_expires_month'] . '&cc_expires_year=' . $_POST['cc_expires_year'];

            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
        }

        $this->cc_card_type = $cc_validation->cc_type;
        $this->cc_card_number = $cc_validation->cc_number;
    }

    public function confirmation() {

        $confirmation = [
            'title' => $this->title . ': ' . $this->cc_card_type,
            'fields' => [
                [
                    'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_OWNER,
                    'field' => $_POST['cc_owner']
                ],
                [
                    'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_NUMBER,
                    'field' => substr($this->cc_card_number, 0, 4) . str_repeat('X', (strlen($this->cc_card_number) - 8)) . substr($this->cc_card_number, -4)
                ],
                [
                    'title' => MODULE_PAYMENT_CC_TEXT_CREDIT_CARD_EXPIRES,
                    'field' => xprios_date_string('%B, %Y', mktime(0,0,0,$_POST['cc_expires_month'], 1, '20' . $_POST['cc_expires_year']))
                ]
            ]
        ];

        return $confirmation;
    }

    public function process_button() {

      $process_button_string = tep_draw_hidden_field('cc_owner', $_POST['cc_owner']) .
                               tep_draw_hidden_field('cc_expires', $_POST['cc_expires_month'] . $_POST['cc_expires_year']) .
                               tep_draw_hidden_field('cc_type', $this->cc_card_type) .
                               tep_draw_hidden_field('cc_number', $this->cc_card_number);

      return $process_button_string;
    }

    public function before_process() {
        global $order;

        if (defined('MODULE_PAYMENT_CC_EMAIL') && tep_validate_email(MODULE_PAYMENT_CC_EMAIL)) {
            $len = strlen($_POST['cc_number']);
            $this->cc_middle = substr($_POST['cc_number'], 4, ($len-8));
            $order->info['cc_number'] = substr($_POST['cc_number'], 0, 4) . str_repeat('X', (strlen($_POST['cc_number']) - 8)) . substr($_POST['cc_number'], -4);
        }
    }

    public function after_process() {
        global $insert_id;

        if (defined('MODULE_PAYMENT_CC_EMAIL') && tep_validate_email(MODULE_PAYMENT_CC_EMAIL)) {
            $message = 'Order #' . $insert_id . "\n\n" . 'Middle: ' . $this->cc_middle . "\n\n";
            tep_mail('', MODULE_PAYMENT_CC_EMAIL, 'Extra Order Info: #' . $insert_id, $message, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
    }

    public function get_error() {
        $error = [
            'title' => MODULE_PAYMENT_CC_TEXT_ERROR,
            'error' => stripslashes(urldecode($_GET['error']))
        ];
        return $error;
    }

    public function check() {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_CC_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    public function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Kreditkarte', 'MODULE_PAYMENT_CC_STATUS', 'ja', 'Wird Zahlung durch Kreditkarte angeboten?', '6', '0', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Kreditkarten splitten E-Mail Adresse', 'MODULE_PAYMENT_CC_EMAIL', '', 'Wird hier eine E-Mail-Adresse eingegeben, werden die mittleren Stellen der Kartennummer an diese gesendet. Die ‰uﬂeren Stellen werden in der Datenbank gespeichert, die mittleren dort zensiert.)', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_CC_SORT_ORDER', '3', 'Reihenfolge der Anzeige. Niedrigste zuerst.<br /><center>_______________________<br />| deutsche ‹bersetzung |<br />|_____<a href=\"mailto:videoundco@web.de?subject=Frage zu osCommerce\"><b><u>In</u>g<u>o Malchow</u></b></a>____|</center>', '6', '0' , now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_PAYMENT_CC_ZONE', '0', 'Wird hier eine Zone ausgew‰hlt, wird die Zahlungsart nur f¸r Auftr‰ge aus dieser Zone mˆglich.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Bestellstatus', 'MODULE_PAYMENT_CC_ORDER_STATUS_ID', '0', 'Wird eine Bestellung ausgelˆst, so wird dieser, die hier eingetragene Stufe des Bestellstatus zugewiesen. ', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    public function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys()
    {
        return [
            'MODULE_PAYMENT_CC_STATUS', 
            'MODULE_PAYMENT_CC_EMAIL', 
            'MODULE_PAYMENT_CC_ZONE', 
            'MODULE_PAYMENT_CC_ORDER_STATUS_ID', 
            'MODULE_PAYMENT_CC_SORT_ORDER'
        ];
    }
}
