<?php
/*
  $Id: order.php,v 1.33 2003/06/09 22:25:35 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class Order 
{
    public 
        $info = [], 
        $totals = [], 
        $products = [], 
        $customer = [], 
        $delivery = [], 
        $billing = [], 
        $content_type = '', 
        $tax_max = [];

    public function __construct($order_id = '')
    {
        if (tep_not_null($order_id)) {
            $this->query($order_id);
        } else {
            $this->cart();
        }
    }

    private function query($order_id)
    {

        $order_id = intval($order_id);

        $order_query = tep_db_query(
            "select customers_id, customers_name, customers_company, customers_street_address, customers_suburb, customers_city, "
            . "customers_postcode, customers_state, customers_country, customers_telephone, customers_email_address, customers_address_format_id, "
            . "delivery_name, delivery_firstname, delivery_lastname, delivery_company, delivery_street_address, delivery_suburb, delivery_city, "
            . "delivery_postcode, delivery_state, delivery_country, delivery_country_iso_code_2, delivery_address_format_id, "
            . "billing_name, billing_firstname, billing_lastname, billing_company, billing_street_address, billing_suburb, "
            . "billing_city, billing_postcode, billing_state, billing_country, billing_country_iso_code_2, billing_address_format_id, "
            . "payment_method, payment_class, shipping_method, shipping_class, currency, "
            . "currency_value, date_purchased, orders_status, last_modified from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'"
        );
        $order = tep_db_fetch_array($order_query);

        $totals_query = tep_db_query("select title, text, value, class from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' order by sort_order");
        while ($totals = tep_db_fetch_array($totals_query)) {
            $this->totals[] = array(
                'title' => $totals['title'],
                'text'  => $totals['text'],
                'value' => $totals['value'],
                'class' => $totals['class']
            );
        }

        $order_total_query = tep_db_query("select text from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' and class = 'ot_total'");
        $order_total = tep_db_fetch_array($order_total_query);

        $shipping_method_query = tep_db_query("select title from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "' and class = 'ot_shipping'");
        $shipping_method = tep_db_fetch_array($shipping_method_query);

        $order_status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . $order['orders_status'] . "' and language_id = '" . (int)$GLOBALS['languages_id'] . "'");
        $order_status = tep_db_fetch_array($order_status_query);

        $this->info = [
            'currency' => $order['currency'],
            'currency_value' => $order['currency_value'],
            'payment_method' => $order['payment_method'],
            'payment_class' => $order['payment_class'],
            'shipping_class' => $order['shipping_class'],
            'date_purchased' => $order['date_purchased'],
            'orders_status' => $order_status['orders_status_name'],
            'last_modified' => $order['last_modified'],
            'total' => strip_tags($order_total['text']),
            'shipping_method' => substr($shipping_method['title'], -1) == ':' ? substr(strip_tags($shipping_method['title']), 0, -1) : strip_tags($shipping_method['title'])
        ];

        $this->customer = [
            'id' => $order['customers_id'],
            'name' => $order['customers_name'],
            'company' => $order['customers_company'],
            'street_address' => $order['customers_street_address'],
            'suburb' => isset($order['customers_suburb']) ? $order['customers_suburb'] : null,
            'city' => $order['customers_city'],
            'postcode' => $order['customers_postcode'],
            'state' => $order['customers_state'],
            'country' => $order['customers_country'],
            'format_id' => $order['customers_address_format_id'],
            'telephone' => $order['customers_telephone'],
            'email_address' => $order['customers_email_address']
        ];

        if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
            $this->delivery = false;
        } else {
            $this->delivery = [
                'name' => $order['delivery_name'],
                'company' => $order['delivery_company'],
                'street_address' => $order['delivery_street_address'],
                'suburb' => isset($order['delivery_suburb']) ? $order['delivery_suburb'] : null,
                'city' => $order['delivery_city'],
                'postcode' => $order['delivery_postcode'],
                'state' => $order['delivery_state'],
                'country' => $order['delivery_country'],
                'format_id' => $order['delivery_address_format_id']
            ];
        }

        $this->billing = [
            'name' => $order['billing_name'],
            'company' => $order['billing_company'],
            'street_address' => $order['billing_street_address'],
            'suburb' => isset($order['billing_suburb']) ? $order['billing_suburb'] : null,
            'city' => $order['billing_city'],
            'postcode' => $order['billing_postcode'],
            'state' => $order['billing_state'],
            'country' => $order['billing_country'],
            'format_id' => $order['billing_address_format_id']
        ];

        $index = 0;
        $orders_products_query = tep_db_query("select orders_products_id, products_id, products_name, products_model, products_price, products_tax, products_quantity, final_price from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
        while ($orders_products = tep_db_fetch_array($orders_products_query)) {
            $this->products[$index] = [
                'qty' => $orders_products['products_quantity'],
                'id' => $orders_products['products_id'],
                'name' => $orders_products['products_name'],
                'model' => $orders_products['products_model'],
                'tax' => $orders_products['products_tax'],
                'price' => $orders_products['products_price'],
                'final_price' => $orders_products['final_price']
            ];

            $subindex = 0;
            $attributes_query = tep_db_query("select products_options, products_options_values, options_values_price, price_prefix from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int)$order_id . "' and orders_products_id = '" . (int)$orders_products['orders_products_id'] . "'");
            if (tep_db_num_rows($attributes_query)) {
                while ($attributes = tep_db_fetch_array($attributes_query)) {
                    $this->products[$index]['attributes'][$subindex] = [
                        'option' => $attributes['products_options'],
                        'value' => $attributes['products_options_values'],
                        'prefix' => $attributes['price_prefix'],
                        'price' => $attributes['options_values_price']
                    ];
                    $subindex++;
                }
            }

            $this->info['tax_groups']["{$this->products[$index]['tax']}"] = '1';

            $index++;
        }
    }

    private function cart()
    {
        global $currencies;

        $this->content_type = $_SESSION['cart']->get_content_type();
        
        if (!isset($_SESSION['customer_id'])) {
            return false;
        }

// Ingo PWA Beginn
        if ($_SESSION['customer_id'] == 0) {
            $country_query = tep_db_query("select c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, z.zone_name from " . TABLE_COUNTRIES . " c left join " . TABLE_ZONES . " z on z.zone_id = '" . (isset($_SESSION['pwa_array_address']['entry_zone_id'])?intval($_SESSION['pwa_array_address']['entry_zone_id']):0) . "' where countries_id = '" . intval($_SESSION['pwa_array_address']['entry_country_id']) . "'");
            $country = tep_db_fetch_array($country_query);
            $address = array_merge(
                $country,
                array(
                    'customers_firstname' => $_SESSION['pwa_array_customer']['customers_firstname'],
                    'customers_lastname'  => $_SESSION['pwa_array_customer']['customers_lastname'],
                    'entry_firstname' => $_SESSION['pwa_array_customer']['customers_firstname'],
                    'entry_lastname'  => $_SESSION['pwa_array_customer']['customers_lastname'],
                    'customers_telephone' => $_SESSION['pwa_array_customer']['customers_telephone'],
                    'customers_email_address' => $_SESSION['pwa_array_customer']['customers_email_address'],
                    'entry_company' => isset($_SESSION['pwa_array_address']['entry_company']) ? $_SESSION['pwa_array_address']['entry_company']:'',
                    'entry_street_address' => $_SESSION['pwa_array_address']['entry_street_address'],
                    'entry_suburb' => isset($_SESSION['pwa_array_address']['entry_suburb']) ? $_SESSION['pwa_array_address']['entry_suburb'] : null,
                    'entry_postcode' => $_SESSION['pwa_array_address']['entry_postcode'],
                    'entry_city' => $_SESSION['pwa_array_address']['entry_city'],
                    'entry_zone_id' => isset($_SESSION['pwa_array_address']['entry_zone_id'])?$_SESSION['pwa_array_address']['entry_zone_id']:'',
                    'countries_id' => $_SESSION['pwa_array_address']['entry_country_id'],
                    'entry_country_id' => $_SESSION['pwa_array_address']['entry_country_id'],
                    'entry_state' => isset($_SESSION['pwa_array_address']['entry_state'])?$_SESSION['pwa_array_address']['entry_state']:''
                )
            );


            $customer_address = $shipping_address = $billing_address = $address;

            if (isset($_SESSION['pwa_array_shipping']) && is_array($_SESSION['pwa_array_shipping']) && count($_SESSION['pwa_array_shipping'])) {
        // separately shipping address
                $country_query = tep_db_query("select c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, z.zone_name from " . TABLE_COUNTRIES . " c left join " . TABLE_ZONES . " z on z.zone_id = '" . intval($_SESSION['pwa_array_shipping']['entry_zone_id']) . "' where countries_id = '" . intval($_SESSION['pwa_array_shipping']['entry_country_id']) . "'");
                $country = tep_db_fetch_array($country_query);
                $shipping_address = array_merge(
                    $country,
                    [
                        'customers_firstname' => $_SESSION['pwa_array_shipping']['entry_firstname'],
                        'customers_lastname'  => $_SESSION['pwa_array_shipping']['entry_lastname'],
                        'entry_firstname' => $_SESSION['pwa_array_shipping']['entry_firstname'],
                        'entry_lastname'  => $_SESSION['pwa_array_shipping']['entry_lastname'],
                        'customers_telephone' => isset($_SESSION['pwa_array_shipping']['customers_telephone'])? $_SESSION['pwa_array_shipping']['customers_telephone']:'',
                        'customers_email_address' => isset($_SESSION['pwa_array_shipping']['customers_email_address'])? $_SESSION['pwa_array_shipping']['customers_email_address']:'',
                        'entry_company' => (isset($_SESSION['pwa_array_shipping']['entry_company'])? $_SESSION['pwa_array_shipping']['entry_company']:''),
                        'entry_street_address' => $_SESSION['pwa_array_shipping']['entry_street_address'],
                        'entry_suburb' => isset($_SESSION['pwa_array_shipping']['entry_suburb']) ? $_SESSION['pwa_array_shipping']['entry_suburb'] : null,
                        'entry_postcode' => $_SESSION['pwa_array_shipping']['entry_postcode'],
                        'entry_city' => $_SESSION['pwa_array_shipping']['entry_city'],
                        'entry_zone_id' => $_SESSION['pwa_array_shipping']['entry_zone_id'],
                        'countries_id' => $_SESSION['pwa_array_shipping']['entry_country_id'],
                        'entry_country_id' => $_SESSION['pwa_array_shipping']['entry_country_id'],
                        'entry_state' => $_SESSION['pwa_array_shipping']['entry_state']
                    ]
                );
            }

            $tax_address = [
                'entry_country_id' => $_SESSION['pwa_array_address']['entry_country_id'], 
                'entry_zone_id' => isset($_SESSION['pwa_array_address']['entry_zone_id'])? $_SESSION['pwa_array_address']['entry_zone_id']:0
            ];

      // address label #0
            $this->pwa_label_customer = [
                'firstname' => $customer_address['customers_firstname'],
                'lastname'  => $customer_address['customers_lastname'],
                'company' => $customer_address['entry_company'],
                'street_address' => $customer_address['entry_street_address'],
                'suburb' => $customer_address['entry_suburb'],
                'city' => $customer_address['entry_city'],
                'postcode' => $customer_address['entry_postcode'],
                'state' => $customer_address['entry_state'],
                'zone_id' => $customer_address['entry_zone_id'],
                'country_id' => $customer_address['entry_country_id']
            ];
      // address label #1
            $this->pwa_label_shipping = array(
                'firstname' => $shipping_address['customers_firstname'],
                'lastname'  => $shipping_address['customers_lastname'],
                'company' => $shipping_address['entry_company'],
                'street_address' => $shipping_address['entry_street_address'],
                'suburb' => $shipping_address['entry_suburb'],
                'city' => $shipping_address['entry_city'],
                'postcode' => $shipping_address['entry_postcode'],
                'state' => $shipping_address['entry_state'],
                'zone_id' => $shipping_address['entry_zone_id'],
                'country_id' => $shipping_address['entry_country_id']
            );

        } else {

            $customer_address_query = tep_db_query("select c.customers_firstname, c.customers_lastname, c.customers_telephone, c.customers_email_address, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, co.countries_id, co.countries_name, co.countries_iso_code_2, co.countries_iso_code_3, co.address_format_id, ab.entry_state from " . TABLE_CUSTOMERS . " c, " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " co on (ab.entry_country_id = co.countries_id) where c.customers_id = '" . (int)$_SESSION['customer_id'] . "' and ab.customers_id = '" . (int)$_SESSION['customer_id'] . "' and c.customers_default_address_id = ab.address_book_id");
            $customer_address = tep_db_fetch_array($customer_address_query);

            $shipping_address_query = tep_db_query("select ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " c on (ab.entry_country_id = c.countries_id) where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "' and ab.address_book_id = '" . (int)$_SESSION['sendto'] . "'");
            $shipping_address = tep_db_fetch_array($shipping_address_query);

            $billing_address_query = tep_db_query("select ab.entry_firstname, ab.entry_lastname, ab.entry_company, ab.entry_street_address, ab.entry_suburb, ab.entry_postcode, ab.entry_city, ab.entry_zone_id, z.zone_name, ab.entry_country_id, c.countries_id, c.countries_name, c.countries_iso_code_2, c.countries_iso_code_3, c.address_format_id, ab.entry_state from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) left join " . TABLE_COUNTRIES . " c on (ab.entry_country_id = c.countries_id) where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "' and ab.address_book_id = '" . (isset($_SESSION['billto'])?(int)$_SESSION['billto']:0) . "'");
            $billing_address = tep_db_fetch_array($billing_address_query);

            $tax_address_query = tep_db_query("select ab.entry_country_id, ab.entry_zone_id from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) where ab.customers_id = '" . (int)$_SESSION['customer_id'] . "' and ab.address_book_id = '" . (int)($this->content_type == 'virtual' ? $_SESSION['billto'] : $_SESSION['sendto']) . "'");
            $tax_address = tep_db_fetch_array($tax_address_query);

        }  // Ingo PWA

        $this->info = array(
            'order_status' => DEFAULT_ORDERS_STATUS_ID,
            'currency' => $GLOBALS['currency'],
            'currency_value' => $currencies->currencies[$GLOBALS['currency']]['value'],
            'payment_method' => isset($_SESSION['payment']) ? $_SESSION['payment']:'',
            'payment_class' => isset($_SESSION['payment']) ? $_SESSION['payment']:'',
            'shipping_method' => isset($_SESSION['shipping']['title'])? $_SESSION['shipping']['title']:'',
            'shipping_class' => isset($_SESSION['shipping']['id']) ? ( (strpos($_SESSION['shipping']['id'],'_') > 0) ?  substr( strrev( strchr(strrev($_SESSION['shipping']['id']),'_') ),0,-1) : $_SESSION['shipping']['id'] ) : '',
            'shipping_cost' => isset($_SESSION['shipping']['cost']) ? $_SESSION['shipping']['cost'] : '0',
            'subtotal' => 0,
            'tax' => 0,
            'tax_groups' => [],
            'comments' => isset($_SESSION['comments']) ? $_SESSION['comments'] : ''
        );

        if (isset($_SESSION['payment']) && isset($GLOBALS[$_SESSION['payment']]) && is_object($GLOBALS[$_SESSION['payment']])) {
            $this->info['payment_method'] = $GLOBALS[$_SESSION['payment']]->title;
            $this->info['payment_class'] = $GLOBALS[$_SESSION['payment']]->code;

            if ( isset($GLOBALS[$_SESSION['payment']]->order_status) && is_numeric($GLOBALS[$_SESSION['payment']]->order_status) && ($GLOBALS[$_SESSION['payment']]->order_status > 0) ) {
                $this->info['order_status'] = $GLOBALS[$_SESSION['payment']]->order_status;
            }
        }

        $this->customer = array(
            'firstname' => $customer_address['customers_firstname'],
            'lastname' => $customer_address['customers_lastname'],
            'company' => $customer_address['entry_company'],
            'street_address' => $customer_address['entry_street_address'],
            'suburb' => $customer_address['entry_suburb'],
            'city' => $customer_address['entry_city'],
            'postcode' => $customer_address['entry_postcode'],
            'state' => ((tep_not_null($customer_address['entry_state'])) ? $customer_address['entry_state'] : $customer_address['zone_name']),
            'zone_id' => $customer_address['entry_zone_id'],
            'country' => array(
                'id' => $customer_address['countries_id'], 
                'title' => $customer_address['countries_name'], 
                'iso_code_2' => $customer_address['countries_iso_code_2'], 
                'iso_code_3' => $customer_address['countries_iso_code_3']
            ),
            'format_id' => $customer_address['address_format_id'],
            'telephone' => $customer_address['customers_telephone'],
            'email_address' => $customer_address['customers_email_address']
        );
        
        $this->delivery = array(
            'firstname' => $shipping_address['entry_firstname'],
            'lastname' => $shipping_address['entry_lastname'],
            'company' => $shipping_address['entry_company'],
            'street_address' => $shipping_address['entry_street_address'],
            'suburb' => $shipping_address['entry_suburb'],
            'city' => $shipping_address['entry_city'],
            'postcode' => $shipping_address['entry_postcode'],
            'state' => tep_not_null($shipping_address['entry_state']) ? $shipping_address['entry_state'] : $shipping_address['zone_name'],
            'zone_id' => $shipping_address['entry_zone_id'],
            'country' => array(
                'id' => $shipping_address['countries_id'], 
                'title' => $shipping_address['countries_name'], 
                'iso_code_2' => $shipping_address['countries_iso_code_2'], 
                'iso_code_3' => $shipping_address['countries_iso_code_3']
            ),
            'country_id' => $shipping_address['entry_country_id'],
            'format_id' => $shipping_address['address_format_id']
        );

        if (!is_null($billing_address)) {
            $this->billing = array(
                'firstname' => $billing_address['entry_firstname'],
                'lastname' => $billing_address['entry_lastname'],
                'company' => $billing_address['entry_company'],
                'street_address' => $billing_address['entry_street_address'],
                'suburb' => $billing_address['entry_suburb'],
                'city' => $billing_address['entry_city'],
                'postcode' => $billing_address['entry_postcode'],
                'state' => ((tep_not_null($billing_address['entry_state'])) ? $billing_address['entry_state'] : $billing_address['zone_name']),
                'zone_id' => $billing_address['entry_zone_id'],
                'country' => array(
                    'id' => $billing_address['countries_id'], 
                    'title' => $billing_address['countries_name'], 
                    'iso_code_2' => $billing_address['countries_iso_code_2'], 
                    'iso_code_3' => $billing_address['countries_iso_code_3']
                ),
                'country_id' => $billing_address['entry_country_id'],
                'format_id' => $billing_address['address_format_id']
            );
        }

        $index = 0;
        $products = $_SESSION['cart']->get_products();
        $this->tax_max['rate'] = 0;
        for ($i=0, $n=sizeof($products); $i<$n; $i++) {
            $this->products[$index] = array(
                'qty' => $products[$i]['quantity'],
                'name' => $products[$i]['name'],
                'image' => $products[$i]['image'],
                'model' => $products[$i]['model'],
                'tax' => tep_get_tax_rate($products[$i]['tax_class_id'], $tax_address['entry_country_id'], $tax_address['entry_zone_id']),
                'tax_description' => tep_get_tax_description($products[$i]['tax_class_id'], $tax_address['entry_country_id'], $tax_address['entry_zone_id']),
                'price' => $products[$i]['price'],
                'final_price' => $products[$i]['price'] + $_SESSION['cart']->attributes_price($products[$i]['id']),
                'weight' => $products[$i]['weight'],
                'id' => $products[$i]['id']
            );

            if ($products[$i]['attributes']) {
                $subindex = 0;
                foreach ($products[$i]['attributes'] as $option => $value) {
                    $attributes_query = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int)$products[$i]['id'] . "' and pa.options_id = '" . (int)$option . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int)$value . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int)$GLOBALS['languages_id'] . "' and poval.language_id = '" . (int)$GLOBALS['languages_id'] . "'");
                    $attributes = tep_db_fetch_array($attributes_query);

                    $this->products[$index]['attributes'][$subindex] = array(
                        'option' => $attributes['products_options_name'],
                        'value' => $attributes['products_options_values_name'],
                        'option_id' => $option,
                        'value_id' => $value,
                        'prefix' => $attributes['price_prefix'],
                        'price' => $attributes['options_values_price']
                    );

                    $subindex++;
                }
            }

            $shown_price = tep_add_tax($this->products[$index]['final_price'], $this->products[$index]['tax']) * $this->products[$index]['qty'];
            $this->info['subtotal'] += $shown_price;

            $products_tax = $this->products[$index]['tax'];
            $products_tax_description = $this->products[$index]['tax_description'];

            if ($products_tax > $this->tax_max['rate']) {
                $this->tax_max['rate'] = $products_tax;
                $this->tax_max['description'] = $products_tax_description;
            }

            if (DISPLAY_PRICE_WITH_TAX == 'ja') {
                $this->info['tax'] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
                if (isset($this->info['tax_groups']["$products_tax_description"])) {
                    $this->info['tax_groups']["$products_tax_description"] += $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
                } else {
                    $this->info['tax_groups']["$products_tax_description"] = $shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax)));
                }
            } else {
                $this->info['tax'] += ($products_tax / 100) * $shown_price;
                if (isset($this->info['tax_groups']["$products_tax_description"])) {
                    $this->info['tax_groups']["$products_tax_description"] += ($products_tax / 100) * $shown_price;
                } else {
                    $this->info['tax_groups']["$products_tax_description"] = ($products_tax / 100) * $shown_price;
                }
            }

            $index++;
        }

        if (DISPLAY_PRICE_WITH_TAX == 'ja') {
            $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
        } else {
            $this->info['total'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
        }
    }
}
