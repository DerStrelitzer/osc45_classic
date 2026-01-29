<?php
/*
  $Id: iclear.php,v 1.02 2003/02/26 02:54:00 harley_vb Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

************************************************************************
  Copyright (C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers
       http://www.themedia.at & http://www.oscommerce.at

                    All rights reserved.

  This program is free software licensed under the GNU General Public License (GPL).

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  USA

*************************************************************************/

class iclear extends PaymentModules
{
    public 
      $code = 'iclear';

// class constructor
    public function __construct()
    {
        global $order;
    
        $this->title = MODULE_PAYMENT_ICLEAR_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_ICLEAR_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_ICLEAR_SORT_ORDER') ? MODULE_PAYMENT_ICLEAR_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_ICLEAR_STATUS') && MODULE_PAYMENT_ICLEAR_STATUS == 'ja' ? true : false;

        if (defined('MODULE_PAYMENT_ICLEAR_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_ICLEAR_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_ICLEAR_ORDER_STATUS_ID;
        }

        if (isset($order) && is_object($order)) $this->update_status();

        $this->form_action_url = 'https://www.iclear.de/servlets/GenBuyTool';
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ICLEAR_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ICLEAR_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

      $process_button_string = tep_draw_hidden_field('ShopID', MODULE_PAYMENT_ICLEAR_ID) .
                               tep_draw_hidden_field('BasketID', tep_create_random_value(5, 'digits')) .
                               tep_draw_hidden_field('Currency', $_SESSION['currency']);

      $process_products_string = '';

      for ($i=0; $i<sizeof($order->products); $i++) {
        $process_products_string .= $order->products[$i]['name'] . '::' . $order->products[$i]['model'] . '::' . $order->products[$i]['qty'] . '::' . $order->products[$i]['final_price'] . '::' . number_format($order->products[$i]['final_price'] * (1 + ($order->products[$i]['tax']/100)), 2) . '::' . number_format($order->products[$i]['tax'], 2) . ':::';
      }

      if ($order->info['shipping_method']) {
        $process_products_string .= 'Versandkosten::Versand::1::' . number_format($_SESSION['shipping']['cost'], 2) . '::' . number_format($order->info['shipping_cost'], 2) . '::' . number_format(($order->info['shipping_cost'] / $_SESSION['shipping']['cost'] - 1) * 100, 2) . ':::';
      }

      $process_button_string .= tep_draw_hidden_field('ProductIndex', $i) .
                                tep_draw_hidden_field('Products', $process_products_string) .
                                tep_draw_hidden_field('User_Def', '&' . tep_session_name() . '=' . tep_session_id());

      return $process_button_string;
    }

    function before_process() {
      global $order, $currencies;

      if ($_GET['StatusExist'] == 'failed') {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(MODULE_PAYMENT_ICLEAR_TEXT_ERROR_MESSAGE), 'SSL', true, false));
      }

      if ($_GET['StatusExist'] == 'accepted') {

        $order_total_modules = new Ordertotal;

        $order_totals = $order_total_modules->process();

        $basket_id = $_GET['BasketID'];
        $customer_id_iclear = $_GET['Kundennummer'];
        $billing_string = explode("::", $_GET['Kundenadresse']);
        $delivery_string = explode("::", $_GET['Lieferadresse']);

        $billing_firstname = $billing_string[0];
        $billing_lastname = $billing_string[1];
        $billing_company = $billing_string[2];
        $billing_company1 = $billing_string[3];
        $billing_street = $billing_string[4];
        $billing_postcode = $billing_string[5];
        $billing_city = $billing_string[6];
        $billing_country = $billing_string[7];

        $delivery_firstname = $delivery_string[0];
        $delivery_lastname = $delivery_string[1];
        $delivery_company = $delivery_string[2];
        $delivery_company1 = $delivery_string[3];
        $delivery_street = $delivery_string[4];
        $delivery_postcode = $delivery_string[5];
        $delivery_city = $delivery_string[6];
        $delivery_country = $delivery_string[7];
        $delivery_telephone = $delivery_string[8];
        $delivery_email_address = $delivery_string[9];

        $sql_data_array = [
            'customers_id' => $_SESSION['customer_id'],
            'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
            'customers_company' => $order->customer['company'],
            'customers_street_address' => $order->customer['street_address'],
            'customers_suburb' => isset($order->customer['suburb']) && (string)$order->customer['suburb']!= '' ? $order->customer['suburb'] : 'null',
            'customers_city' => $order->customer['city'],
            'customers_postcode' => $order->customer['postcode'],
            'customers_state' => $order->customer['state'],
            'customers_country' => $order->customer['country']['title'],
            'customers_telephone' => $delivery_telephone,
            'customers_email_address' => $delivery_email_address,
            'customers_address_format_id' => $order->customer['format_id'],
            'delivery_name' => $delivery_firstname . ' ' . $delivery_lastname,
            'delivery_company' => $delivery_company,
            'delivery_street_address' => $delivery_street,
            'delivery_suburb' => 'null',
            'delivery_city' => $delivery_city,
            'delivery_postcode' => $delivery_postcode,
            'delivery_state' => '',
            'delivery_country' => $delivery_country,
            'delivery_address_format_id' => $order->customer['format_id'],
            'billing_name' => $billing_firstname . ' ' . $billing_lastname,
            'billing_company' => $billing_company,
            'billing_street_address' => $billing_street,
            'billing_suburb' => 'null',
            'billing_city' => $billing_city,
            'billing_postcode' => $billing_postcode,
            'billing_state' => '',
            'billing_country' => $billing_country,
            'billing_address_format_id' => $order->customer['format_id'],
            'payment_method' => $order->info['payment_method'],
            'orders_status' => $order->info['order_status'],
            'currency' => $order->info['currency'],
            'currency_value' => $order->info['currency_value']
        ];
        tep_db_perform(TABLE_ORDERS, $sql_data_array);
        $insert_id = tep_db_insert_id();
        for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
            $sql_data_array = [
                'orders_id' => $insert_id,
                'title' => $order_totals[$i]['title'],
                'text' => $order_totals[$i]['text'],
                'value' => $order_totals[$i]['value'],
                'class' => $order_totals[$i]['code'],
                'sort_order' => $order_totals[$i]['sort_order']
            ];
            tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
        }

        $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
        $sql_data_array = array('orders_id' => $insert_id,
                                'orders_status_id' => $order->info['order_status'],
                                'date_added' => 'now()',
                                'customer_notified' => $customer_notification,
                                'comments' => 'BasketID: ' . $basket_id . '<br />iclear Kunden-Nr.: ' . $customer_id_iclear . '<br />' . $order->info['comments']);
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

// initialized for the email confirmation
        $products_ordered = '';
        $subtotal = 0;
        $total_tax = 0;

        for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
// Stock Update - Joao Correia
          if (STOCK_LIMITED == 'true') {
            if (DOWNLOAD_ENABLED == 'true') {
              $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                  FROM " . TABLE_PRODUCTS . " p
                                  LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                   ON p.products_id=pa.products_id
                                  LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                   ON pa.products_attributes_id=pad.products_attributes_id
                                  WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
              $products_attributes = $order->products[$i]['attributes'];
              if (is_array($products_attributes)) {
                $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
              }
              $stock_query = tep_db_query($stock_query_raw);
            } else {
              $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            }
            if (tep_db_num_rows($stock_query) > 0) {
              $stock_values = tep_db_fetch_array($stock_query);
// do not decrement quantities if products_attributes_filename exists
              if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
                $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
              } else {
                $stock_left = $stock_values['products_quantity'];
              }
              tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
              if ($stock_left < 1) {
                tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
              }
            }
          }

// Update products_ordered (for bestsellers list)
          tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

          $sql_data_array = array('orders_id' => $insert_id,
                                  'products_id' => tep_get_prid($order->products[$i]['id']),
                                  'products_model' => $order->products[$i]['model'],
                                  'products_name' => $order->products[$i]['name'],
                                  'products_price' => $order->products[$i]['price'],
                                  'final_price' => $order->products[$i]['final_price'],
                                  'products_tax' => $order->products[$i]['tax'],
                                  'products_quantity' => $order->products[$i]['qty']);
          tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
          $order_products_id = tep_db_insert_id();

//------insert customer choosen option to order--------
          $attributes_exist = '0';
          $products_ordered_attributes = '';
          if (isset($order->products[$i]['attributes'])) {
            $attributes_exist = '1';
            for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
              if (DOWNLOAD_ENABLED == 'true') {
                $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                     from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                     left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                      on pa.products_attributes_id=pad.products_attributes_id
                                     where pa.products_id = '" . $order->products[$i]['id'] . "'
                                      and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                      and pa.options_id = popt.products_options_id
                                      and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                      and pa.options_values_id = poval.products_options_values_id
                                      and popt.language_id = '" . (int)$GLOBALS['languages_id'] . "'
                                      and poval.language_id = '" . $GLOBALS['languages_id'] . "'";
                $attributes = tep_db_query($attributes_query);
              } else {
                $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int)$GLOBALS['languages_id'] . "' and poval.language_id = '" . (int)$GLOBALS['languages_id'] . "'");
              }
              $attributes_values = tep_db_fetch_array($attributes);

              $sql_data_array = array('orders_id' => $insert_id,
                                      'orders_products_id' => $order_products_id,
                                      'products_options' => $attributes_values['products_options_name'],
                                      'products_options_values' => $attributes_values['products_options_values_name'],
                                      'options_values_price' => $attributes_values['options_values_price'],
                                      'price_prefix' => $attributes_values['price_prefix']);
              tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

              if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                $sql_data_array = array('orders_id' => $insert_id,
                                        'orders_products_id' => $order_products_id,
                                        'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                        'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                        'download_count' => $attributes_values['products_attributes_maxcount']);
                tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
              }
              $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
            }
          }
//------insert customer choosen option eof ----
          $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
          $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
          $total_cost += $total_products_price;

          $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
        }

// lets start with the email confirmation
        $email_order = STORE_NAME . "\n" .
                       EMAIL_SEPARATOR . "\n" .
                       EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n" .
                       EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . "\n" .
                       EMAIL_TEXT_DATE_ORDERED . ' ' . xprios_date_string(DATE_FORMAT_LONG, time()) . "\n\n";
        if ($order->info['comments']) {
          $email_order .= tep_db_output($order->info['comments']) . "\n\n";
        }
        $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        $products_ordered .
                        EMAIL_SEPARATOR . "\n";

        for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
          $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
        }

        if ($order->content_type != 'virtual') {
          $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                          EMAIL_SEPARATOR . "\n" .
                          $delivery_company . "\n" .
                          $delivery_firstname . ' ' . $delivery_lastname . "\n" .
                          $delivery_street . "\n" .
                          $delivery_country . '-' . $delivery_postcode . ' ' . $delivery_city . "\n";
        }

        $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        $billing_company . "\n" .
                        $billing_firstname . ' ' . $billing_lastname . "\n" .
                        $billing_street . "\n" .
                        $billing_postcode . ' ' . $billing_city . "\n" .
                        $billing_country . "\n\n";
        if (is_object($$payment)) {
          $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                          EMAIL_SEPARATOR . "\n";
          $payment_class = $$payment;
          $email_order .= $payment_class->title . "\n\n";
          if ($payment_class->email_footer) {
            $email_order .= $payment_class->email_footer . "\n\n";
          }
        }
        tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $delivery_email_address, EMAIL_TEXT_SUBJECT, nl2br($email_order), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '');

// send emails to other people
        if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
          tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, nl2br($email_order), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, '');
        }

        $_SESSION['cart']->reset(true);

// unregister session variables used during checkout
        unset(
          $_SESSION['sendto'],
          $_SESSION['billto'],
          $_SESSION['shipping'],
          $_SESSION['payment'],
          $_SESSION['comments']
        );

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
      }
    }

    function after_process() {
	  return false;
    }

    function output_error() {
      return false;
    }

    function check() {
      if ($this->_check == null) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ICLEAR_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Zahlung durch iclear', 'MODULE_PAYMENT_ICLEAR_STATUS', 'ja', 'Wird die Zahlung durch iclear Rechnungskauf angeboten?', '6', '0', 'tep_cfg_select_option(array(\'ja\', \'nein\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handels ID', 'MODULE_PAYMENT_ICLEAR_ID', 'unsershop', 'Hier wird die Handels-ID eingetragen, die bei der Zahlung durch EuroCoin benutzt wird.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_PAYMENT_ICLEAR_SORT_ORDER', '3', 'Reihenfolge der Anzeige. Niedrigste zuerst.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Zone', 'MODULE_PAYMENT_ICLEAR_ZONE', '0', 'Wird hier eine Zone ausgewählt, wird die Zahlungsart nur für Aufträge aus dieser Zone möglich.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Bestellstatus', 'MODULE_PAYMENT_ICLEAR_ORDER_STATUS_ID', '0', 'Wird eine Bestellung ausgelöst, so wird dieser, die hier eingetragene Stufe des Bestellstatus zugewiesen.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_ICLEAR_STATUS', 'MODULE_PAYMENT_ICLEAR_ID', 'MODULE_PAYMENT_ICLEAR_ZONE', 'MODULE_PAYMENT_ICLEAR_ORDER_STATUS_ID', 'MODULE_PAYMENT_ICLEAR_SORT_ORDER');
    }
}
