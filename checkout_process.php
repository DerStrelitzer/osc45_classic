<?php
/*
  $Id: checkout_process.php,v 1.128 2003/05/28 18:00:29 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

include('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
if (!isset($_SESSION['customer_id'])) {
    xprios_set_snapshot(['mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT]);
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}

if (!isset($_SESSION['sendto'])) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

if (tep_not_null(MODULE_PAYMENT_INSTALLED) && !isset($_SESSION['payment'])) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
}

// avoid hack attempts during the checkout procedure by checking the internal cartID
if (isset($_SESSION['cart']->cartID) && isset($_SESSION['cartID'])) {
    if ($_SESSION['cart']->cartID != $_SESSION['cartID']) {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
}


// load the selected shipping module
$shipping_modules = new Shipping($_SESSION['shipping']);

$order = new Order;

// load selected payment module
$payment_modules = new payment($_SESSION['payment']);
// load the before_process function from the payment modules
$payment_modules->before_process();

$order_total_modules = new OrderTotal;
$order_totals = $order_total_modules->process();

tep_db_perform(TABLE_ORDERS, 
    [
        'customers_id' => $_SESSION['customer_id'],
        'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
        'customers_company' => $order->customer['company'],
        'customers_street_address' => $order->customer['street_address'],
        'customers_suburb' => (string)$order->customer['suburb'] != '' ? $order->customer['suburb'] : 'null',
        'customers_city' => $order->customer['city'],
        'customers_postcode' => $order->customer['postcode'],
        'customers_state' => $order->customer['state'],
        'customers_country' => $order->customer['country']['title'],
        'customers_telephone' => $order->customer['telephone'],
        'customers_email_address' => $order->customer['email_address'],
        'customers_address_format_id' => $order->customer['format_id'],
        'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
        'delivery_firstname' => $order->delivery['firstname'],
        'delivery_lastname' => $order->delivery['lastname'],
        'delivery_company' => $order->delivery['company'],
        'delivery_street_address' => $order->delivery['street_address'],
        'delivery_suburb' => (string)$order->delivery['suburb'] != '' ? $order->delivery['suburb'] : 'null',
        'delivery_city' => $order->delivery['city'],
        'delivery_postcode' => $order->delivery['postcode'],
        'delivery_state' => $order->delivery['state'],
        'delivery_country' => $order->delivery['country']['title'],
        'delivery_country_iso_code_2' => $order->delivery['country']['iso_code_2'],
        'delivery_address_format_id' => $order->delivery['format_id'],
        'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
        'billing_firstname' => $order->billing['firstname'],
        'billing_lastname' => $order->billing['lastname'],
        'billing_company' => $order->billing['company'],
        'billing_street_address' => $order->billing['street_address'],
        'billing_suburb' => (string)$order->billing['suburb'] != '' ? $order->billing['suburb'] : 'null',
        'billing_city' => $order->billing['city'],
        'billing_postcode' => $order->billing['postcode'],
        'billing_state' => $order->billing['state'],
        'billing_country' => $order->billing['country']['title'],
        'billing_country_iso_code_2' => $order->billing['country']['iso_code_2'],
        'billing_address_format_id' => $order->billing['format_id'],
        'payment_method' => substr($order->info['payment_method'], 0, 64),
        'payment_class' => $order->info['payment_class'],
        'shipping_method' => substr($order->info['shipping_method'], 0, 64),
        'shipping_class' => $order->info['shipping_class'],
        'orders_status' => $order->info['order_status'],
        'currency' => $order->info['currency'],
        'currency_value' => $order->info['currency_value']
    ]
);
$insert_id = tep_db_insert_id();
  
foreach ($order_totals as $total) {
    tep_db_perform(TABLE_ORDERS_TOTAL, 
        [
            'orders_id'   => $insert_id,
            'title'       => $total['title'],
            'text'        => $total['text'],
            'value'       => $total['value'],
            'class'       => $total['code'],
            'sort_order'  => $total['sort_order']
        ]
    );
}

$customer_notification = SEND_EMAILS == 'true' ? '1' : '0';
tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, 
    [
        'orders_id'         => $insert_id,
        'orders_status_id'  => $order->info['order_status'],
        'date_added'        => 'now()',
        'customer_notified' => $customer_notification,
        'comments'          => $order->info['comments']
    ]
);

// initialized for the email confirmation
$products_ordered = '';
$subtotal     = 
$total_weight = 0;

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
            if (isset($order->products[$i]['attributes']) && is_array($order->products[$i]['attributes'])) {
                $attributes = $order->products[$i]['attributes'];
                if (is_array($products_attributes)) {
                    $stock_query_raw .= " AND pa.options_id = '" . $attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $attributes[0]['value_id'] . "'";
                }
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
            if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
                tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            }
        }
    }

// Update products_ordered (for bestsellers list)
    tep_db_query("update " 
        . TABLE_PRODUCTS 
        . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) 
        . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'"
    );

    tep_db_perform(TABLE_ORDERS_PRODUCTS, 
        [
            'orders_id' => $insert_id,
            'products_id' => tep_get_prid($order->products[$i]['id']),
            'products_model' => $order->products[$i]['model'],
            'products_name' => $order->products[$i]['name'],
            'products_price' => $order->products[$i]['price'],
            'final_price' => $order->products[$i]['final_price'],
            'products_tax' => $order->products[$i]['tax'],
            'products_quantity' => $order->products[$i]['qty']
        ]
    );
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
                                and popt.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                and poval.language_id = '" . (int)$_SESSION['languages_id'] . "'";
                $attributes = tep_db_query($attributes_query);
            } else {
                $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int)$_SESSION['languages_id'] . "' and poval.language_id = '" . (int)$_SESSION['languages_id'] . "'");
            }
            $attributes_values = tep_db_fetch_array($attributes);

            tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, 
                [
                    'orders_id' => $insert_id,
                    'orders_products_id' => $order_products_id,
                    'products_options' => $attributes_values['products_options_name'],
                    'products_options_values' => $attributes_values['products_options_values_name'],
                    'options_values_price' => $attributes_values['options_values_price'],
                    'price_prefix' => $attributes_values['price_prefix']
                ]
            );

            if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, 
                    [
                        'orders_id' => $insert_id,
                        'orders_products_id' => $order_products_id,
                        'orders_products_filename' => $attributes_values['products_attributes_filename'],
                        'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                        'download_count' => $attributes_values['products_attributes_maxcount']
                    ]
                );
            }
            $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
        }
    }
//------insert customer choosen option eof ----
    $total_weight += $order->products[$i]['qty'] * $order->products[$i]['weight'];
    $products_ordered .= 
        $order->products[$i]['qty'] 
        . ' x ' 
        . $order->products[$i]['name'] 
        . ' (' . $order->products[$i]['model'] . ') = ' 
        . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) 
        . $products_ordered_attributes 
        . "\n";
}

// lets start with the email confirmation
$email_order = STORE_NAME . "\n"
    . EMAIL_SEPARATOR . "\n"
    . EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n"
    . EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . "\n"
    . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long(date('Y-m-d H:i:s')) 
    . "\n\n";

// Ingo PWA Beginn
if ($_SESSION['customer_id'] == 0) {
    $email_order .= EMAIL_WARNING . "\n\n";
}

if ($order->info['comments']!='') {
    $email_order .= htmlspecialchars($order->info['comments'], ENT_QUOTES, CHARSET, false) . "\n\n";
}
$email_order .= EMAIL_TEXT_PRODUCTS . "\n"
    . EMAIL_SEPARATOR . "\n"
    . $products_ordered
    . EMAIL_SEPARATOR 
    . "\n";

for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
    $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
}

if ($order->content_type != 'virtual') {
    $email_order .= "\n" 
    . EMAIL_TEXT_DELIVERY_ADDRESS . "\n"
    . EMAIL_SEPARATOR . "\n"
    . tep_address_label($_SESSION['customer_id'], $_SESSION['sendto'], 0, '', "\n") 
    . "\n";
}

$email_order .= "\n" 
    . EMAIL_TEXT_BILLING_ADDRESS . "\n"
    . EMAIL_SEPARATOR . "\n"
    . tep_address_label($_SESSION['customer_id'], $_SESSION['billto'], 0, '', "\n") 
    . "\n\n";

if (is_object(${$_SESSION['payment']})) {
    $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n"
    . EMAIL_SEPARATOR . "\n"
    . ${$_SESSION['payment']}->title 
    . "\n\n";
    if (isset(${$_SESSION['payment']}->email_footer) && ${$_SESSION['payment']} != '') {
        $email_order .= ${$_SESSION['payment']}->email_footer . ' ' . $insert_id . "\n\n";
    }
}

tep_mail(
    $order->customer['firstname'] . ' ' . $order->customer['lastname'], 
    $order->customer['email_address'], 
    EMAIL_TEXT_SUBJECT, 
    $email_order, 
    STORE_OWNER, 
    STORE_OWNER_EMAIL_ADDRESS
);

// send emails to other people
if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
    tep_mail(
        '', 
        SEND_EXTRA_ORDER_EMAILS_TO, 
        EMAIL_TEXT_SUBJECT, 
        $email_order, 
        STORE_OWNER, 
        STORE_OWNER_EMAIL_ADDRESS
    );
}

// Include OSC-AFFILIATE if enabled
if (AFFILIATE_ENABLED == 'ja') require (DIR_WS_INCLUDES . 'affiliate_checkout_process.php');

// load the after_process function from the payment modules
$payment_modules->after_process();

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

require(DIR_WS_INCLUDES . 'application_bottom.php');
