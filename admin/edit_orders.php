<?php
/* $Id: edit_orders.php,v 1.x 2005/01/01

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License

  Written by Jonathan Hilgeman of SiteCreative.com (osc@sitecreative.com)
  Additional coding by Mathias Fahrig

  Version History
  ---------------------------------------------------------------
  may 2006, Ingo <www.strelitzer.de>
  - any modifications,
  - register_globals,
  - superglobals
  - sanitizing

  18/10/03
  1.2c - Changed category listings for better sub-categories display.
         Some minor code tweaks for performance.
         Some minor bug fixes.

  10/10/03 - Mathias Fahrig
  1.2b - Fixed tax issues with some stores (usually international)
         German language additions. (I, Jonathan, need to learn German.)

  08/08/03
  1.2a - Fixed a query problem on osC 2.1 stores.

  08/08/03
  1.2 - Added more recommendations to the instructions.
        Added "Customer" fields for editing on osC 2.2.
        Corrected "Billing" fields so they update correctly.
        Added Company and Suburb Fields.
        Added optional shipping tax variable.
        First (and hopefully last) fix for currency formatting.

  08/08/03
  1.1 - Added status editing (fixed order status bug from 1.0).
        Added comments editing. (with compatibility for osC 2.1)
        Added customer notifications.
        Added some additional information to the instructions file.
        Fixed bug with product names containing single quotes.

  08/07/03
  1.0 - Original Release.

  To Do in Version 1.3
  ---------------------------------------------------------------
  - Manual order entry.

  Note from the author
  ---------------------------------------------------------------
  This tool was designed and tested on osC 2.2 Milestone 2.2,
  but may work for other versions, as well. Most database changes
  were minor, so getting it to work on other versions may just
  need some tweaking. I hope this helps make your life easier!

  - Jonathan Hilgeman, August 7th, 2003
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

if (isset($_GET['oID']) && $_GET['oID']>0) {
    $oID = $_GET['oID'];
} else {
    tep_redirect(tep_href_link(FILENAME_DEFAULT));
}
$add_product_categories_id = isset($_POST['add_product_categories_id']) ? $_POST['add_product_categories_id'] : 0;
$add_product_products_id = isset($_POST['add_product_products_id']) ? $_POST['add_product_products_id'] : 0;
$add_product_options = isset($_POST['add_product_options']) ? $_POST['add_product_options'] : [];
$add_product_quantity = isset($_POST['add_product_quantity']) ? $_POST['add_product_quantity'] : 1;

$step = isset($_POST['step']) ? $_POST['step'] : 1;

// Optional Tax Rate/Percent
// Ingo: prepare a array of tax_rates
$default_tax = 0;
$tax_rate_query = tep_db_query("select distinct tax_rate from " . TABLE_TAX_RATES . " order by tax_rate desc");
$tax_array = [];
while ($tax_rate_query_array = tep_db_fetch_array($tax_rate_query)) {
    $this_rate = number_format($tax_rate_query_array['tax_rate'], TAX_DECIMAL_PLACES);
    $tax_array[] = [
        'id' => $this_rate,
        'text' => $this_rate
    ];
    if ($default_tax==0) $default_tax = $this_rate;
}
$tax_array[] = ['id' => '0', 'text' => '0'];
$tax_value = [];

$orders_statuses = [];
$orders_status_array = [];
$orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$_SESSION['languages_id'] . "'");
while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array(
        'id' => $orders_status['orders_status_id'],
        'text' => $orders_status['orders_status_name']
    );
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
}

$action = isset($_GET['action']) ? $_GET['action'] : 'edit';

switch ($action) {

    // Update Order
    case 'update_order':

        $order = new Order($oID);

        $status = max(0, intval(xprios_prepare_post('status')));
        $update_totals = xprios_prepare_post('update_totals', true);
        $update_customer = xprios_prepare_post('update_customer');
        $update_billing  = xprios_prepare_post('update_billing');
        $update_delivery  = xprios_prepare_post('update_delivery');
        $update_info = xprios_prepare_post('update_info');
        $sub_total = 0;

      // update order info
        if (!(isset($update_customer['suburb']) && $update_customer['suburb']!='')) {
            $update_customer['suburb'] = 'null';
        }
        if (!(isset($update_billing['suburb']) && $update_billing['suburb']!='')) {
            $update_billing['suburb'] = 'null';
        }
        if (!(isset($update_delivery['suburb']) && $update_delivery['suburb']!='')) {
            $update_delivery['suburb'] = 'null';
        }
        tep_db_perform(TABLE_ORDERS, [
                'customers_name'           => $update_customer['name'],
                'customers_company'        => $update_customer['company'],
                'customers_street_address' => $update_customer['street_address'],
                'customers_suburb'         => $update_customer['suburb'],
                'customers_city'           => $update_customer['city'],
                'customers_state'          => $update_customer['state'],
                'customers_postcode'       => $update_customer['postcode'],
                'customers_country'        => $update_customer['country'],
                'customers_telephone'      => $update_customer['telephone'],
                'customers_email_address'  => $update_customer['email_address'],

                'billing_name'             => $update_billing['name'],
                'billing_company'          => $update_billing['company'],
                'billing_street_address'   => $update_billing['street_address'],
                'billing_suburb'           => $update_billing['suburb'],
                'billing_city'             => $update_billing['city'],
                'billing_state'            => $update_billing['state'],
                'billing_postcode'         => $update_billing['postcode'],
                'billing_country'          => $update_billing['country'],

                'delivery_name'            => $update_delivery['name'],
                'delivery_company'         => $update_delivery['company'],
                'delivery_street_address'  => $update_delivery['street_address'],
                'delivery_suburb'          => $update_delivery['suburb'],
                'delivery_city'            => $update_delivery['city'],
                'delivery_state'           => $update_delivery['state'],
                'delivery_postcode'        => $update_delivery['postcode'],
                'delivery_country'         => $update_delivery['country'],

                'payment_method'           => $update_info['payment_method'],
                'orders_status'            => $status
            ],
            'update',
            'orders_id = ' . intval($oID)
        );
        $order_updated = true;

// Update Status History & Email Customer if Necessary
        if ($order->info['orders_status'] != $status || (isset($_SESSION['create_order']) && $_SESSION['create_order'] == $oID)) {
            unset($_SESSION['create_order']);

            // Notify Customer
            $customer_notified = 0;
            $comments = xprios_prepare_post('comments');
            if (isset($_POST['notify']) && $_POST['notify'] == 1) {
                $customer_notified = 1;

                $email = STORE_NAME . "\n" 
                . EMAIL_SEPARATOR . "\n" 
                . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" 
                . EMAIL_TEXT_INVOICE_URL . ' ' . tep_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . "\n" 
                . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($order->info['date_purchased']) . "\n"
                . "\n";

                if (isset($_POST['notify_comments']) && $_POST['notify_comments'] == 1) {
                    $email .= sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
                }
                
                $email .= sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]);
                tep_mail(
                    $update_customer['name'], 
                    $update_customer['email_address'], 
                    EMAIL_TEXT_SUBJECT, 
                    $email, 
                    STORE_OWNER, 
                    STORE_OWNER_EMAIL_ADDRESS
                );
            }

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, [
                    'orders_id'         => $oID, 
                    'orders_status_id'  => $status, 
                    'date_added'        => 'now()', 
                    'customer_notified' => $customer_notified, 
                    'comments'          => $comments
                ]
            );
        }
        // update products
        //
        // 1. unregistered products
        $unreg_product = xprios_prepare_post('unreg_product');
        $unreg_quantity = 0;
        if (is_array($unreg_product) && sizeof($unreg_product)>0) {
            $unreg_quantity = $unreg_product['qty'];
            $name = $unreg_product['name'];
            if ($unreg_quantity > 0 && $name!='') {
                $model     = $unreg_product['model'];
                $p_tax     = $unreg_product['tax'];
                $price     = floatval(str_replace(',', '.', $unreg_product['price_n']));
                $price_b   = floatval(str_replace(',', '.', $unreg_product['price_b']));
                if ($price_b!=0) {
                    $price = $price_b / (100 + $p_tax) * 100;
                }
                
                tep_db_perform(TABLE_ORDERS_PRODUCTS, [
                        'orders_id'         => $oID,
                        'products_id'       => 0,
                        'products_model'    => $model,
                        'products_name'     => $name,
                        'products_price'    => $price,
                        'final_price'       => $price,
                        'products_tax'      => $p_tax,
                        'products_quantity' => $unreg_quantity
                    ]
                );
                $sub_total = ($price * $unreg_quantity);
                if (!isset($tax_value[(string)$p_tax])) {
                    $tax_value[(string)$p_tax] = 0;
                }
                $tax_value[(string)$p_tax] += ($price * $unreg_quantity * $p_tax / 100);
                if (DISPLAY_PRICE_WITH_TAX == 'ja') {
                    $sub_total += ($price * $unreg_quantity * $p_tax / 100);
                }
            }
        }

        // regular & allready stored products
        $update_products = isset($_POST['update_products']) && is_array($_POST['update_products']) ? xprios_prepare_post('update_products'):[];
        if (count($update_products)>0) {
            foreach ($update_products as $orders_products_id => $products_details) {
            // update orders_products table
                $products_details['final_price'] = floatval(str_replace(',', '.', $products_details['final_price']));
                if ($products_details['qty'] > 0) {
                    tep_db_perform(TABLE_ORDERS_PRODUCTS, [
                            'products_model' => $products_details['model'],
                            'products_name'  => $products_details['name'],
                            'final_price'    => $products_details['final_price'],
                            'products_tax'   => $products_details['tax'],
                            'products_quantity' => $products_details['qty']
                        ],
                        'update',
                        'orders_products_id = ' . (int)$orders_products_id
                    );

                    // update tax and subtotals
                    // DISPLAY_PRICE_WITH_TAX == 'ja' => sub_total & total show with tax
                    $product_total = $products_details['qty'] * $products_details['final_price'];
                    if (!isset($tax_value[(string)$products_details['tax']])) {
                        $tax_value[(string)$products_details['tax']] = 0;
                    }
                    $tax_value[(string)$products_details['tax']] += ($product_total * $products_details['tax'] / 100);
                    $sub_total += $product_total;
                    if (DISPLAY_PRICE_WITH_TAX == 'ja') $sub_total += $product_total * $products_details['tax'] / 100;
                    // Update Any Attributes
                    if (isset($products_details['attributes'])) {
                        foreach ($products_details['attributes'] as $orders_products_attributes_id => $attributes_details) {
                            tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, [
                                    'products_options' => $attributes_details['option'],
                                    'products_options_values' => $attributes_details['value'],
                                    
                                ],
                                'update',
                                'orders_products_attributes_id = ' . (int)$orders_products_attributes_id
                            );
                        }
                    }
                } else {
                    // quantity == 0 => Delete
                    tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_products_id = '" . (int)$orders_products_id . "'");
                    tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_products_id = '" . (int)$orders_products_id . "'");
                    unset($update_products[$orders_products_id]);
                }
            }
        }

        // is the order empty? redirect to orderlisting
        if (count($update_products)==0 && $unreg_quantity == 0) {
            tep_db_query("delete from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
            tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$oID . "'");
            tep_db_query("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$oID . "'");
            tep_redirect(tep_href_link(FILENAME_ORDERS));
        }

        // update order_total
        $total = $sub_total;
        $sort_min = 99;
        for ($i=0; $i<sizeof($update_totals); $i++) {
            $update_totals[$i]['value'] = floatval(str_replace(',', '.', $update_totals[$i]['value']));
            if ($update_totals[$i]['class'] != 'ot_subtotal' && $update_totals[$i]['class'] != 'ot_total' && $update_totals[$i]['class'] != 'ot_tax') {
                if (DISPLAY_PRICE_WITH_TAX == 'ja') {
                    $total += $update_totals[$i]['value'];
                    if ($update_totals[$i]['tax']!=0) {
                        if (!isset($tax_value[(string)$update_totals[$i]['tax']])) {
                            $tax_value[(string)$update_totals[$i]['tax']] = 0;
                        }
                        $tax_value[(string)$update_totals[$i]['tax']] += $update_totals[$i]['value'] / (100 + $update_totals[$i]['tax']) * $update_totals[$i]['tax'];
                    }
                } else {
                    $total += $update_totals[$i]['value'] * (100 + $update_totals[$i]['tax_rate']) / 100;
                    if ($update_totals[$i]['tax']!=0) {
                        if (!isset($tax_value[(string)$update_totals[$i]['tax']])) {
                            $tax_value[(string)$update_totals[$i]['tax']] = 0;
                        }
                        $tax_value[(string)$update_totals[$i]['tax']] += $update_totals[$i]['value'] / 100 * $update_totals[$i]['tax'];
                    }
                }
            }
        }

        include_once(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/order_total/ot_tax.php');
        include_once(DIR_FS_CATALOG_MODULES . '/order_total/ot_tax.php');
        if (tep_class_exists('ot_tax')) {
            $module = new ot_tax;
            $tax_title = $module->title;
            $tax_sort = $module->sort_order;
            $tax_code = $module->code;
        } else {
            $tax_title = TABLE_HEADING_TAX;
            $tax_sort = $tax_sort;
            $tax_code = 'ot_tax';
        }

        $max_sort = 0; 
        $tax_sort = 0;
        for ($i=0; $i<count($update_totals); $i++) {
            if ($update_totals[$i]['sort_order'] > $max_sort) {
                $max_sort = $update_totals[$i]['sort_order']+1;
            }
            if ($update_totals[$i]['class'] != 'ot_tax') {
                if ($update_totals[$i]['class'] == 'ot_subtotal') {
                    $update_totals[$i]['value'] = $sub_total;
                } elseif ($update_totals[$i]['class'] == 'ot_total') {
                    $update_totals[$i]['value'] = $total;
                }
                $update_totals[$i]['text'] = $currencies->display_price($update_totals[$i]['value'],0,1);
                if ($update_totals[$i]['class'] == 'ot_total') {
                    $update_totals[$i]['text'] = '<b>' . $update_totals[$i]['text'] . '</b>';
                }
                if (($update_totals[$i]['title']!='' && $update_totals[$i]['value']!=0) || $update_totals[$i]['class']== 'ot_total') {
                    $sql_data_array = array(
                        'orders_id'  => $oID,
                        'title'      => $update_totals[$i]['title'],
                        'text'       => $update_totals[$i]['text'],
                        'value'      => $update_totals[$i]['value'],
                        'class'      => $update_totals[$i]['class'],
                        'sort_order' => (int)$update_totals[$i]['sort_order']
                    );
                    if ($update_totals[$i]['orders_total_id'] != '0') {
                        tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array, 'update', "orders_total_id = '" . (int)$update_totals[$i]['orders_total_id'] . "'");
                    } else {
                        tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
                    }
                } elseif ($update_totals[$i]['orders_total_id'] != '0') {
                    tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_total_id = '" . $update_totals[$i]['orders_total_id'] . "'");
                }
            } else {
                $tax_sort = $update_totals[$i]['sort_order'];
            }
        }

        if ($tax_sort == 0) {
            $tax_sort = $max_sort;
        }
        tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$oID . "' and class = 'ot_tax'");
        $t = array_keys($tax_value);
        for ($i=0; $i<count($t); $i++) {
            if ($tax_value[(string)$t[$i]]!=0) {
                tep_db_perform(TABLE_ORDERS_TOTAL, [
                        'orders_id' => $oID,
                        'title' => tep_db_input($tax_title . ' ' . $t[$i] . '%'),
                        'text' => tep_db_input($currencies->display_price($tax_value[(string)$t[$i]],0,1)),
                        'value' => tep_db_input($tax_value[$t[$i]]),
                        'class' => tep_db_input($tax_code),
                        'sort_order' => (int)$tax_sort
                    ]
                );
            }
        }
        if ($order_updated) {
            $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
        }

        tep_redirect(tep_href_link(FILENAME_ORDERS, 'oID=' . $oID . '&action=edit'));
        break;

    // Add a Product
    case 'new_product':
        // get order info
        /*
        $option_id = xprios_prepare_post('option_id');
        $option_value_id = xprios_prepare_post('option_value_id');
        */
        $order = new Order($oID);
        $AddedOptionsPrice = 0;

      // Get Product Attribute Info
        if (isset($add_product_options)) {
            foreach ($add_product_options as $option_id => $option_value_id) {
                $result = tep_db_query("SELECT * FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON po.products_options_id=pa.options_id LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON pov.products_options_values_id=pa.options_values_id WHERE products_id='" . (int)$add_product_products_id . "' and options_id='" . (int)$option_id . "' and options_values_id='" . (int)$option_value_id . "'");
                $row = tep_db_fetch_array($result);
                extract($row, EXTR_PREFIX_ALL, "opt");
                $AddedOptionsPrice += $opt_options_values_price;
                $option_value_details[$option_id][$option_value_id] = [
                    "options_values_price" => $opt_options_values_price
                ];
                $option_names[$option_id] = $opt_products_options_name;
                $option_values_names[$option_value_id] = $opt_products_options_values_name;
            }
        }

        // Get Product Info
        $query = "select p.products_model,p.products_price,pd.products_name,p.products_tax_class_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id=p.products_id where p.products_id='" . (int)$add_product_products_id . "' AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
        $result = tep_db_query($query);
        $row = tep_db_fetch_array($result);
        extract($row, EXTR_PREFIX_ALL, "p");

        // Following functions are defined at the bottom of this file
        $CountryID = tep_get_country_id($order->delivery["country"]);
        $ZoneID = tep_get_zone_id($CountryID, $order->delivery["state"]);

        $ProductsTax = tep_get_tax_rate($p_products_tax_class_id, $CountryID, $ZoneID);
        $query = "insert into " . TABLE_ORDERS_PRODUCTS . " set" .
        " orders_id = '" . (int)$oID . "'," .
        " products_id = '" . (int)$add_product_products_id . "'," .
        " products_model = '" . $p_products_model . "'," .
        " products_name = '" . tep_db_input($p_products_name) . "'," .
        " products_price = '" . $p_products_price . "'," .
        " final_price = '" . ($p_products_price + $AddedOptionsPrice) . "'," .
        " products_tax = '" . $ProductsTax . "'," .
        " products_quantity = '" . ($add_product_quantity+0) . "'";
        tep_db_query($query);
        $new_product_id = tep_db_insert_id();

        if (isset($add_product_options)) {
            foreach ($add_product_options as $option_id => $option_value_id) {
                $query = "insert into " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " set" .
            " orders_id = '" . (int)$oID . "'," .
            " orders_products_id = '" . (int)$new_product_id . "'," .
            " products_options = '" . $option_names[$option_id] . "'," .
            " products_options_values = '" . $option_values_names[$option_value_id] . "'," .
            " options_values_price = '" . $option_value_details[$option_id][$option_value_id]["options_values_price"] . "'," .
            " price_prefix = '+'";
                tep_db_query($query);
            }
        }
/*
        // Calculate Tax and Sub-Totals
        $order = new Order($oID);
        $RunningSubTotal = 0;
        $RunningTax = 0;

        for ($i=0; $i<sizeof($order->products); $i++)
        {
//			$RunningSubTotal += ($order->products[$i]['qty'] * $order->products[$i]['final_price']);
              $RunningSubTotal += (($products_details["tax"]/100) * ($products_details["qty"] * $products_details["final_price"]))+(($products_details["qty"] * $products_details["final_price"]));
        $RunningTax += (($order->products[$i]['tax'] / 100) * ($order->products[$i]['qty'] * $order->products[$i]['final_price']));
        }


            // Tax
            $Query = "update " . TABLE_ORDERS_TOTAL . " set
                text = '\$" . number_format($RunningTax, 2, '.', ',') . "',
                value = '" . $RunningTax . "'
                where class='ot_tax' and orders_id=$oID";
            tep_db_query($Query);

            // Sub-Total
            $Query = "update " . TABLE_ORDERS_TOTAL . " set
                text = '\$" . number_format($RunningSubTotal, 2, '.', ',') . "',
                value = '" . $RunningSubTotal . "'
                where class='ot_subtotal' and orders_id=$oID";
            tep_db_query($Query);

            // Total
            $Query = "select sum(value) as total_value from " . TABLE_ORDERS_TOTAL . " where class != 'ot_total' and orders_id=$oID";
            $result = tep_db_query($Query);
            $row = tep_db_fetch_array($result);
            $Total = $row["total_value"];

            $Query = "update " . TABLE_ORDERS_TOTAL . " set
                text = '<b>\$" . number_format($Total, 2, '.', ',') . "</b>',
                value = '" . $Total . "'
                where class='ot_total' and orders_id=$oID";
            tep_db_query($Query);
*/
        tep_redirect(tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=edit'));
    break;
}


if ($action == 'edit') {
    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
    $order_exists = true;
    if (!tep_db_num_rows($orders_query)) {
        $order_exists = false;
        $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
    }
}
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script type="text/javascript" src="includes/general.js"></script>
</head>
<body>
<?php 
require(DIR_WS_INCLUDES . 'header.php'); 
?>

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<?php 
require(DIR_WS_INCLUDES . 'column_left.php'); 
?>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
if ($action == 'edit' && $order_exists == true) {
    $order = new Order($oID);
    //foreach ($order->customer as $key => $value) $order->customer[$key] = stripslashes($order->customer[$key]);
    //foreach ($order->billing as $key => $value) $order->billing[$key] = stripslashes($order->billing[$key]);
    //foreach ($order->delivery as $key => $value) $order->delivery[$key] = stripslashes($order->delivery[$key]);
    //foreach ($order->info as $key => $value) $order->info[$key] = stripslashes($order->info[$key]);
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?> #<?php echo $oID; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="pageHeading" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
          </tr>
          <tr><td colspan="3"><?php echo tep_draw_separator(); ?></td></tr>
        </table></td>
      </tr>

<!-- Begin Addresses Block -->
      <tr><?php echo tep_draw_form('edit_order', FILENAME_EDIT_ORDERS, tep_get_all_get_params(['action','paycc']) . 'action=update_order'); ?>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>

<!-- Customer Info Block -->
            <td valign="top"><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td>&nbsp;</td>
                <td class="main" valign="top"><b><?php echo ENTRY_CUSTOMER; ?></b></td>
              </tr>
              <tr>
                <td class="main" width="150"><b><?php echo ENTRY_NAME; ?></b></td>
                <td><input name="update_customer[name]" size="25" value="<?php echo $order->customer['name']; ?>" /></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_COMPANY; ?></b></td>
                <td><input name="update_customer[company]" size="25" value="<?php echo $order->customer['company']; ?>" /></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_STREET_ADDRESS; ?></b></td>
                <td><input name="update_customer[street_address]" size="25" value="<?php echo $order->customer['street_address']; ?>" /></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_SUBURB; ?></b></td>
                <td><input name="update_customer[surburb]" size="25" value="<?php echo $order->customer['suburb']; ?>" /></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_CITY; ?></b></td>
                <td><input name="update_customer[city]" size="25" value="<?php echo $order->customer['city']; ?>" /></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_STATE; ?></b></td>
                <td><input name="update_customer[state]" size="25" value="<?php echo $order->customer['state']; ?>" /></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_POST_CODE; ?></b></td>
                <td><input name="update_customer[postcode]" size="5" value="<?php echo $order->customer['postcode']; ?>" /></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_COUNTRY; ?></b></td>
                <td><input name="update_customer[country]" size="25" value="<?php echo $order->customer['country']; ?>" /></td>
              </tr>
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_TELEPHONE_NUMBER; ?></b></td>
                <td class="main"><input name="update_customer[telephone]" size="15" value="<?php echo $order->customer['telephone']; ?>" /></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_EMAIL_ADDRESS; ?></b></td>
                <td class="main"><input name="update_customer[email_address]" size="25" value="<?php echo $order->customer['email_address']; ?>" /></td>
              </tr>
            </table></td>

<!-- Billing Address Block -->
            <td>&nbsp;</td>
            <td valign="top"><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><b><?php echo ENTRY_BILLING_ADDRESS; ?></b></td>
              </tr>
              <tr>
                <td class="main"><input name="update_billing[name]" size="25" value="<?php echo $order->billing['name']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_billing[company]" size="25" value="<?php echo $order->billing['company']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_billing[street_address]" size="25" value="<?php echo $order->billing['street_address']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_billing[surburb]" size="25" value="<?php echo $order->billing['suburb']; ?>" /></td>
              </tr
              <tr>
                <td><input name="update_billing[city]" size="25" value="<?php echo $order->billing['city']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_billing[state]" size="25" value="<?php echo $order->billing['state']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_billing[postcode]" size="5" value="<?php echo $order->billing['postcode']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_billing[country]" size="25" value="<?php echo $order->billing['country']; ?>" /></td>
              </tr>
            </table></td>

<!-- Shipping Address Block -->
            <td>&nbsp;</td>
            <td valign="top"><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><b><?php echo ENTRY_SHIPPING_ADDRESS; ?></b></td>
              </tr>
              <tr>
                <td class="main"><input name="update_delivery[name]" size="25" value="<?php echo $order->delivery['name']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_delivery[company]" size="25" value="<?php echo $order->delivery['company']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_delivery[street_address]" size="25" value="<?php echo $order->delivery['street_address']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_delivery[surburb]" size="25" value="<?php echo $order->delivery['suburb']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_delivery[city]" size="25" value="<?php echo $order->delivery['city']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_delivery[state]" size="25" value="<?php echo $order->delivery['state']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_delivery[postcode]" size="5" value="<?php echo $order->delivery['postcode']; ?>" /></td>
              </tr>
              <tr>
                <td><input name="update_delivery[country]" size="25" value="<?php echo $order->delivery['country']; ?>" /></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<!-- End Addresses Block -->

      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>

<!-- Begin Payment Block -->
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main" width="150"><b><?php echo ENTRY_PAYMENT_METHOD; ?></b></td>
            <td class="main"><?php echo tep_draw_input_field('update_info[payment_method]', $order->info['payment_method'], 'size="20"'); ?></td>
          </tr>

<?php
/*
    if ($order->info['cc_type'] || $order->info['cc_owner'] || $order->info['payment_method'] == "Credit Card" || $order->info['cc_number']) {
?>
        <!-- Begin Credit Card Info Block -->
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
            <td class="main"><?php echo tep_draw_input_field('update_info_cc_type', $order->info['cc_type'],  'size="10"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
            <td class="main"><?php echo tep_draw_input_field('update_info_cc_owner', $order->info['cc_owner'],  'size="20"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
            <td class="main"><?php echo tep_draw_input_field('update_info_cc_number', '(Last 4) ' . substr($order->info['cc_number'],-4), 'size="20"'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
            <td class="main"><?php echo tep_draw_input_field('update_info_cc_expires', $order->info['cc_expires'], 'size="4"'); ?></td>
          </tr>
<!-- end credit card info block -->
<?php
    }
*/
?>
        </table></td>
      </tr>
<!-- End Payment Block -->

      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>

<!-- Begin Products Listing Block -->
<!-- Heading -->
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
            <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
          </tr>
          <tr>
<!-- End of heading -->
<!-- Start of Productlisting -->
<?php
// override order.php class's field limitations
    $index = 0;
    $order->products = [];
    $orders_products_query = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$oID . "'");
    while ($orders_products = tep_db_fetch_array($orders_products_query)) {
        $order->products[$index] = [
            'qty' => $orders_products['products_quantity'],
            'name' => str_replace("'", "&#39;", $orders_products['products_name']),
            'model' => $orders_products['products_model'],
            'tax' => $orders_products['products_tax'],
            'price' => $orders_products['products_price'],
            'final_price' => $orders_products['final_price'],
            'orders_products_id' => $orders_products['orders_products_id']
        ];

        $attributes_query_string = "select * from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int)$oID . "' and orders_products_id = '" . (int)$orders_products['orders_products_id'] . "'";
        $attributes_query = tep_db_query($attributes_query_string);

        if (tep_db_num_rows($attributes_query)) {
            $subindex = 0;
            while ($attributes = tep_db_fetch_array($attributes_query)) {
                $order->products[$index]['attributes'][$subindex] = [
                    'option' => $attributes['products_options'],
                    'value' => $attributes['products_options_values'],
                    'prefix' => $attributes['price_prefix'],
                    'price' => $attributes['options_values_price'],
                    'orders_products_attributes_id' => $attributes['orders_products_attributes_id']
                ];
                $subindex++;
            }
        }
        $index++;
    }
?>
<!-- Start of unregistered product (Ingo) -->
          <tr class="dataTableRow">
            <td class="dataTableContent" valign="top" align="right" width="20"><?php echo tep_draw_input_field('unreg_product[qty]', '', 'size="3"'); ?>&nbsp;&times;</td>
            <td class="dataTableContent"><?php echo tep_draw_input_field('unreg_product[name]', '', 'size="50"'); ?></td>
            <td class="dataTableContent"><?php echo tep_draw_input_field('unreg_product[model]','', 'size="12"'); ?></td>
            <td class="dataTableContent" align="right"><?php echo tep_draw_pull_down_menu('unreg_product[tax]', $tax_array); ?>%</td>
            <td class="dataTableContent" align="right"><?php echo tep_draw_input_field('unreg_product[price_n]', '', 'size="10"'); ?></td>
            <td class="dataTableContent" align="right"><?php echo tep_draw_input_field('unreg_product[price_b]', '', 'size="10"'); ?></td>
            <td class="dataTableContent" colspan="2">&nbsp;</td>
          </tr>
<!-- end of unregistered product (Ingo) -->

<!-- ordered products block -->
<?php
    for ($i=0; $i<sizeof($order->products); $i++) {
        $orders_products_id = $order->products[$i]['orders_products_id'];
?>
          <tr class="dataTableRow">
            <td class="dataTableContent" valign="top" align="right" width="20"><?php echo tep_draw_input_field('update_products[' . $orders_products_id. '][qty]', $order->products[$i]['qty'], 'size="3"'); ?>&nbsp;&times;</td>
            <td class="dataTableContent" valign="top"><?php echo tep_draw_input_field('update_products[' . $orders_products_id . '][name]', $order->products[$i]['name'], 'size="50"');

    // Has Attributes?
        if (isset($order->products[$i]['attributes']) && sizeof($order->products[$i]['attributes']) > 0) {
            for ($j=0; $j<sizeof($order->products[$i]['attributes']); $j++) {
                $orders_products_attributes_id = $order->products[$i]['attributes'][$j]['orders_products_attributes_id'];
?>
              <br><span style="white-space:nowrap;"><small>&nbsp;<i> - <?php
                echo '            '
                    . tep_draw_input_field('update_products[' . $orders_products_id . '][attributes][' . $orders_products_attributes_id . '][option]', $order->products[$i]['attributes'][$j]['option'],  'size="6"') . ': ' . "\n"
                    . '            ' 
                    . tep_draw_input_field('update_products[' . $orders_products_id . '][attributes][' . $orders_products_attributes_id . '][value]', $order->products[$i]['attributes'][$j]['value'], 'size="10"') . "\n";
?>
              </i></small></span>
<?php
            }
        }
              ?></td>
            <td class="dataTableContent" valign="top"><?php echo tep_draw_input_field('update_products[' . $orders_products_id . '][model]', $order->products[$i]['model'], 'size="12"'); ?></td>
            <td class="dataTableContent" align="right" valign="top"><?php echo tep_draw_input_field('update_products[' . $orders_products_id . '][tax]', tep_display_tax_value($order->products[$i]['tax']), 'size="4"'); ?> %</td>
            <td class="dataTableContent" align="right" valign="top"><?php echo tep_draw_input_field('update_products[' . $orders_products_id . '][final_price]', number_format($order->products[$i]['final_price'], 6, '.', ''), 'size="10"'); ?></td>
            <td class="dataTableContent" align="right" valign="top"><?php echo $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']); ?></b></td>
            <td class="dataTableContent" align="right" valign="top"><?php echo $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']); ?></td>
            <td class="dataTableContent" align="right" valign="top"><?php echo $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']); ?></b></td>
          </tr>
<?php
    }
?>
<!-- end products listings block -->

          <tr>
            <td colspan="8" width="100%" align="right"><table border="0" cellspacing="0" cellpadding="2" width="100%">
              <tr>
                <td align="center" valign="top">
                  <br><a href="<?php echo tep_href_link(basename($PHP_SELF), 'oID=' . $oID . '&action=add_product&step=1'); ?>"><u><b><font size="3"><?php echo ADDING_TITLE; ?></font></b></u></a>
                </td>
                <td align="right"><table border="0" cellspacing="0" cellpadding="2">

<!-- begin order total block -->
<?php
// override order.php class's field limitations
    $totals_query = tep_db_query("select * from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$oID . "' order by sort_order");
    $field_size_l = $field_size_r = $custom_sort = 0;
    $custom_set = false; 
    $totals = [];
    while ($db_totals = tep_db_fetch_array($totals_query)) { 
        if ($db_totals['class'] == 'ot_total') $custom_sort = $db_totals['sort_order'] - 1;
        $totals[] = [
            'orders_total_id' => $db_totals['orders_total_id'],
            'title' => $db_totals['title'],
            'text' => $db_totals['text'],
            'value' => $db_totals['value'],
            'class' => $db_totals['class'],
            'sort_order' => $db_totals['sort_order'],
            'tax' => 0
        ];

        if ($db_totals['class'] == 'ot_shipping') {
            $totals[] = [
                'orders_total_id' => '0',
                'title' => '',
                'text' => '0.00',
                'value' => 0,
                'class' => 'ot_custom',
                'sort_order' => $db_totals['sort_order'],
                'tax' => 0
            ];
            $custom_set = true;
        }
        if (strlen($db_totals['title']) > $field_size_l) $field_size_l = strlen($db_totals['title']) + 3;
        if (strlen($db_totals['text']) > $field_size_r) $field_size_r = strlen($db_totals['text']) + 3;
    }
    if ($custom_set == false) {
        $totals[] = [
            'orders_total_id' => '0',
            'title' => '',
            'text' => '0.00',
            'value' => 0,
            'class' => 'ot_custom',
            'sort_order' => $custom_sort,
            'tax' => 0
        ];
    }
    for ($i=0; $i<sizeof($totals); $i++) {
        if(($totals[$i]['class'] == 'ot_subtotal') || ($totals[$i]['class'] == 'ot_total') || ($totals[$i]['class'] == 'ot_tax')) {
?>
                  <tr>
                    <td class="main" align="right" colspan="2"><b><?php echo $totals[$i]['title']; ?></b></td>
                    <td class="main" align="right"><b><?php echo $totals[$i]['text']; ?></b><?php 
            echo "\n"
                . '                 ' . tep_draw_hidden_field('update_totals[' . $i . '][orders_total_id]', $totals[$i]['orders_total_id']) . "\n"
                . '                 ' . tep_draw_hidden_field('update_totals[' . $i . '][title]', $totals[$i]['title']) . "\n"
                . '                 ' . tep_draw_hidden_field('update_totals[' . $i . '][text]', $totals[$i]['text']) . "\n"
                . '                 ' . tep_draw_hidden_field('update_totals[' . $i . '][value]', $totals[$i]['value']) . "\n"
                . '                 ' . tep_draw_hidden_field('update_totals[' . $i . '][class]', $totals[$i]['class']) . "\n"
                . '                 ' . tep_draw_hidden_field('update_totals[' . $i . '][sort_order]', $totals[$i]['sort_order']);
?>
                    </td>
                  </tr>
<?php
        } else {
?>
                  <tr>
                    <td align="right" class="smallText"><?php echo tep_draw_input_field('update_totals[' . $i . '][title]', $totals[$i]['title'], 'size="' . $field_size_l . '"'); ?></td>
                    <td class="smallText">&nbsp;<?php echo tep_draw_pull_down_menu('update_totals[' . $i . '][tax]', $tax_array, $default_tax); ?>%</td>
                    <td align="right" class="smallText"><?php echo tep_draw_input_field('update_totals[' . $i . '][value]', $totals[$i]['value'], 'size="' . $field_size_r . '"') .
                        tep_draw_hidden_field('update_totals[' . $i . '][orders_total_id]', $totals[$i]['orders_total_id']) .
                        tep_draw_hidden_field('update_totals[' . $i . '][class]', $totals[$i]['class']) .
                        tep_draw_hidden_field('update_totals[' . $i . '][sort_order]', $totals[$i]['sort_order']); ?></td>
                </tr>
<?php
        }
    }
?>
                </table></td>
              </tr>
            </table></td>
          </tr>
<!-- End Order Total Block -->

        </table></td>
      </tr>

      <tr>
        <td align="center" valign="top"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
      </tr>

      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>

      <tr>
        <td class="main"><table border="1" cellspacing="0" cellpadding="5">
          <tr>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_DATE_ADDED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_STATUS; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
          </tr>
<?php
    $orders_history_query = tep_db_query("select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
    if (tep_db_num_rows($orders_history_query)) {
        while ($orders_history = tep_db_fetch_array($orders_history_query)) {
?>
          <tr>
            <td class="smallText" align="center"><?php echo $orders_history['date_added']; ?></td>
            <td class="smallText" align="center"><?php
            if ($orders_history['customer_notified'] == '1') {
                echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK);
            } else {
                echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS);
            } ?></td>
            <td class="smallText"><?php echo $orders_status_array[$orders_history['orders_status_id']]; ?></td>
            <td class="smallText"><?php echo (isset($orders_history['comments']) ? nl2br($orders_history['comments']) : ''); ?>&nbsp;</td>
          </tr>
<?php
        }
    } else {
?>
          <tr>
            <td class="smallText" colspan="5"><?php echo TEXT_NO_ORDER_HISTORY; ?></td>
          </tr>
<?php
    }
?>
        </table></td>
      </tr>

      <tr>
        <td class="main"><br><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo tep_draw_textarea_field('comments', 'soft', '60', '5'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>

      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo ENTRY_STATUS; ?></b> <?php echo tep_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></td>
          </tr>
          <tr>
            <td class="main"><b><?php echo ENTRY_NOTIFY_CUSTOMER; ?></b> <?php echo tep_draw_checkbox_field('notify', '1', false); ?></td>
          </tr>
          <tr>
            <td class="main"><b><?php echo ENTRY_NOTIFY_COMMENTS; ?></b> <?php echo tep_draw_checkbox_field('notify_comments', '1', false); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td align="center" valign="top"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>
      </tr>
    </table></form>
<?php
} elseif ($action == 'add_product') {
?>
      <tr>
        <td class="pageHeading"><?php echo ADDING_TITLE; ?> #<?php echo $oID; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr>
        <td><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, 'oID=' . $oID) . '">' . tep_image_button('button_back.gif', IMAGE_BACK, 'style="margin-left:100px;"') . '</a>'; ?></td>
      </tr>

<?php

// step 1: choose category
?>
      <tr>
        <td width="100%"><form action="<?php echo basename($PHP_SELF) . '?oID=' . $oID . '&action=' . $action; ?>" method="post"><table border="0" cellspacing="0" cellpadding="0">
          <tr class="dataTableRow">
            <td class="dataTableContent" align="right"><b><?php echo TEXT_STEP; ?> 1: &nbsp;</b></td>
            <td class="dataTableContent" valign="top"><?php echo tep_draw_pull_down_menu('add_product_categories_id', tep_get_category_tree(), $add_product_categories_id, 'onchange="this.form.submit();"'); ?>
            <td class="dataTableContent" align="center"><input type="submit" value=" OK " /><input type="hidden" name="step" value="2" /></td>
          </tr>
          </form>
          <tr>
            <td colspan="3">&nbsp;</td>
          </tr>
        </table></form></td>
      </tr>
<?php

// step 2: choose product
    if ($step > 1 && $add_product_categories_id > 0) {
?>
      <tr>
        <td width="100%"><form action="<?php echo basename($PHP_SELF) . '?oID=' . $oID . '&action=' . $action; ?>" method="post"><table border="0" cellspacing="0" cellpadding="0">
          <tr class="dataTableRow">
            <td class="dataTableContent" align="right"><b><?php echo TEXT_STEP; ?> 2: &nbsp;</b></td>
            <td class="dataTableContent" valign="top"><select name="add_product_products_id" onchange="this.form.submit();">
<?php
        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_model from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$add_product_categories_id . "' and pd.language_id = '" . (int)$_SESSION['languages_id'] . "' order by products_name");
        if (!tep_db_num_rows($products_query)) {
            echo '<option value="0">' . TEXT_NONE . '</option>';
        }
        while ($products = tep_db_fetch_array($products_query)) {
            echo '<option value="' . $products['products_id'] . '"' . ($products['products_id']==$add_product_products_id?' selected="selected"':'') . '>' . $products['products_name'] . (isset($products['products_model'])&&$products['products_model']!=''?' (' . $products['products_model'] . ')':'') . '</option>';
        }
?>
              </select>
            </td>
            <td class="dataTableContent" align="center">
              <input type="submit" value=" OK " />
              <input type="hidden" name="add_product_categories_id" value="<?php echo $add_product_categories_id; ?>" />
              <input type="hidden" name="step" value="3" />
            </td>
          </tr>
          <tr>
            <td colspan="3">&nbsp;</td>
          </tr>
        </table></form></td>
      </tr>
<?php
    }

// step 3: choose options
    if ($step > 2 && $add_product_products_id > 0) {
      // Get Options for Products
        $result = tep_db_query("SELECT * FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa LEFT JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON po.products_options_id=pa.options_id LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON pov.products_options_values_id=pa.options_values_id WHERE products_id='" . (int)$add_product_products_id . "'");

// skip to step 4 if no options
        if (tep_db_num_rows($result) == 0) {
?>
      <tr>
        <td width="100%"><table cellspacing="0" cellpadding="0" border="0">
          <tr class="dataTableRow">
            <td class="dataTableContent" align="right"><b><?php echo TEXT_STEP; ?> 3: &nbsp;</b></td>
            <td class="dataTableContent" valign="top" colspan="2"><i><?php echo TEXT_NO_OPTIONS; ?></i></td>
          </tr>
        </table></td>
      </tr>
<?php
            $step = 4;
        } else {

            while ($row = tep_db_fetch_array($result)) {
                extract($row, EXTR_PREFIX_ALL, "db");
                $options[$db_products_options_id] = $db_products_options_name;
                $ProductOptionValues[$db_products_options_id][$db_products_options_values_id] = $db_products_options_values_name;
            }
?>
      <tr>
        <td width="100%"><form action="<?php echo basename($PHP_SELF) . '?oID=' . $oID . '&action=' . $action; ?>" method="post"><table cellspacing="0" cellpadding="0" border="0">
          <tr class="dataTableRow">
            <td class="dataTableContent" align="right"><b><?php echo TEXT_STEP; ?> 3: &nbsp;</b></td>
            <td class="dataTableContent" valign="top">
<?php
            foreach ($ProductOptionValues as $option_id => $option_values) {
                $option_option = '';
?>
              <b><?php echo $options[$option_id]; ?></b> -
              <select name="add_product_options[<?php echo $option_id; ?>]" onchange="this.form.submit();">
<?php
                foreach ($option_values as $option_values_id => $option_values_name) {
                    echo '<option value="' . $option_values_id . '"' . (isset($add_product_options[$option_id])&&$add_product_options[$option_id]==$option_values_id?' selected="selected"':'') . '>' . $option_values_name . '</option>';
                }
?>
              </select>
<?php
            }
?>
            </td>
            <td class="dataTableContent" align="center">
              <input type="submit" value=" Select These Options " />
              <input type="hidden" name="add_product_categories_id" value="<?php echo $add_product_categories_id; ?>" />
              <input type="hidden" name="add_product_products_id" value="<?php echo $add_product_products_id; ?>" />
              <input type="hidden" name="step" value="4" />
            </td>
          </tr>
        </table></form></td>
      </tr>
<?php
        }
?>
      <tr>
        <td>&nbsp;</td>
      </tr>
<?php
    }

// step 4: confirm
    if ($step > 3) {
?>
      <tr>
        <td width="100%"><?php echo tep_draw_form('step4', FILENAME_EDIT_ORDERS, 'oID=' . $oID . '&action=new_product'); ?><table cellspacing="0" cellpadding="0" border="0">
          <tr class="dataTableRow">
            <td class="dataTableContent" align="right"><b><?php echo TEXT_STEP; ?> 4: &nbsp;</b></td>
            <td class="dataTableContent" valign="top"><?php echo tep_draw_input_field('add_product_quantity', '1', 'size="2"') . ' ' . TEXT_QUANTITY; ?></td>
            <td class="dataTableContent" align="center"><input type="submit" value="<?php echo TEXT_ADD . '!'; ?>" />
<?php
        if (isset($add_product_options)) {
            foreach ($add_product_options as $option_id => $option_value_id) {
                echo '          ' . tep_draw_hidden_field('add_product_options[' . $option_id . ']', $option_value_id) . "\n";
            }
            echo '          ' . tep_draw_hidden_field('add_product_categories_id', $add_product_categories_id) . "\n"
            . '          ' . tep_draw_hidden_field('add_product_products_id', $add_product_products_id) . "\n"
            . '          ' . tep_draw_hidden_field('step', '5') . "\n";
        }
?>
            </td>
          </tr>
        </table></form></td>
      </tr>
    </table>
<?php
    }
}
?>
    </td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<?php 
require(DIR_WS_INCLUDES . 'footer.php'); 
?>
</body>
</html>
<?php
  ////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Function    : tep_get_country_id
  //
  // Arguments   : country_name		country name string
  //
  // Return      : country_id
  //
  // Description : Function to retrieve the country_id based on the country's name
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////
function tep_get_country_id($country_name) {
    $country_id_query = tep_db_query("select countries_id from " . TABLE_COUNTRIES . " where countries_name = '" . tep_db_input($country_name) . "'");
    if (!tep_db_num_rows($country_id_query)) {
        return 0;
    } else {
        $country_id_row = tep_db_fetch_array($country_id_query);
        return $country_id_row['countries_id'];
    }
}

  ////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Function    : tep_get_country_iso_code_2
  //
  // Arguments   : country_id (country id number)
  //
  // Return      : country_iso_code_2
  //
  // Description : Function to retrieve the country_iso_code_2 based on the country's id
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////
function tep_get_country_iso_code_2($country_id) {
    $country_iso_query = tep_db_query("select countries_iso_code_2 from " . TABLE_COUNTRIES . " where countries_id = '" . (int)$country_id . "'");
    if (!tep_db_num_rows($country_iso_query)) {
        return 0;
    } else {
      $country_iso_row = tep_db_fetch_array($country_iso_query);
        return $country_iso_row['countries_iso_code_2'];
    }
}

  ////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Function    : tep_get_zone_id
  //
  // Arguments   : country_id		country id string
  //               zone_name		state/province name
  //
  // Return      : zone_id
  //
  // Description : Function to retrieve the zone_id based on the zone's name
  //
  ////////////////////////////////////////////////////////////////////////////////////////////////
function tep_get_zone_id($country_id, $zone_name) {
    $zone_id_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int)$country_id . "' and zone_name = '" . tep_db_input($zone_name) . "'");
    if (!tep_db_num_rows($zone_id_query)) {
        return 0;
    } else {
      $zone_id_row = tep_db_fetch_array($zone_id_query);
        return $zone_id_row['zone_id'];
    }
}

require(DIR_WS_INCLUDES . 'application_bottom.php');
