<?php
/*
  $Id: remember_moneyorders.php,v 1.0 2006/08/22 by Ingo <http://forums.oscommerce.de/index.php?showuser=36>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

/*
neue Datenfelder in orders:
 - payment_class  zur Identifikation der Vorkasse
 - delivery_country_iso_code_2 ($order->delivery['country']['iso_code_2']) für Landesspezifische Texte
 - remember_status  zum Fortschreiben

alter table orders add remember_status tinyint(1) unsigned default 0
*/

$remember_anzahl_erinnerungen = 3;
$remember_status_deaktiviert = 3;
$remember_frist = 14;
  
$unsold_status = defined('MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID') && MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID>0 ? MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID : DEFAULT_ORDERS_STATUS_ID;
$unsold_query = "select orders_id, remember_status from " . TABLE_ORDERS . " where payment_class = 'moneyorder' and orders_status = '" . intval($unsold_status) . "' and date_add(date_purchased, interval " . intval($remember_frist) . " day)<now() and (isnull(last_modified) or date_add(last_modified, interval " . intval($remember_frist) . " day)<now()) limit 1";
    //echo '<span class="smallText">' . $unsold_query . "</span><br />\n";
$unsold_query = tep_db_query($unsold_query);
if (isset($_SESSION['language']) && tep_db_num_rows($unsold_query)) {
    include (DIR_WS_LANGUAGES . $_SESSION['language'] . '/remember_moneyorders.php');
    $unsold_order = tep_db_fetch_array($unsold_query);
    $unsold_order_id = $unsold_order['orders_id'];
    $remember_status = (isset($unsold_order['remember_status']) && $unsold_order['remember_status']>0)? $unsold_order['remember_status']:0;
    $remember_status++;

    $unsold_order = new Order($unsold_order_id);
    $unsold_country = (isset($unsold_order->delivery['country_iso_code_2']) && $unsold_order->delivery['country_iso_code_2']!='')? $unsold_order->delivery['country_iso_code_2']:'';

    if (ACCOUNT_GENDER == 'true' && $unsold_order->customer['id']>0) {
        $gender = tep_db_query("select customers_gender from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$unsold_order->customer['id'] . "'");
        $gender = tep_db_fetch_array($gender);
        $gender = $gender['customers_gender'];
        if ($gender == 'm') {
            $email_text = sprintf(REMEMBER_EMAIL_GREET_MR, $unsold_order->customer['name']);
        } else {
            $email_text = sprintf(REMEMBER_EMAIL_GREET_MS, $unsold_order->customer['name']);
        }
    } else {
        $email_text = sprintf(REMEMBER_EMAIL_GREET_NONE, $unsold_order->customer['name']);
    }

    $email_text .= sprintf(REMEMBER_EMAIL_OPENER, tep_date_short($unsold_order->info['date_purchased'])) . "\n\n";

    for ($i=0, $n=sizeof($unsold_order->products); $i<$n; $i++) {
        $email_text .= $unsold_order->products[$i]['qty'] . ' x ' . $unsold_order->products[$i]['name'] . '  ' . $currencies->format(tep_add_tax($unsold_order->products[$i]['final_price'], $unsold_order->products[$i]['tax']) * $unsold_order->products[$i]['qty'], true, $unsold_order->info['currency'], $unsold_order->info['currency_value']) . "\n";
        if ( isset($unsold_order->products[$i]['attributes']) && sizeof($unsold_order->products[$i]['attributes']) > 0) {
            for ($j=0, $n2=sizeof($unsold_order->products[$i]['attributes']); $j<$n2; $j++) {
                $email_text .= '-  ' . $unsold_order->products[$i]['attributes'][$j]['option'] . ': ' . $unsold_order->products[$i]['attributes'][$j]['value'] . "\n";
            }
        }
    }
    $email_text .= "\n";

    for ($i=0, $n=sizeof($unsold_order->totals); $i<$n; $i++) {
        $email_text .= $unsold_order->totals[$i]['title'] . ' ' . $unsold_order->totals[$i]['text'] . "\n";
    }
    $email_text .= "\n";

    $email_text .= REMEMBER_EMAIL_PAYMENT_METHOD . ': ' . $unsold_order->info['payment_method'] . "\n\n";

    if ($unsold_order->customer['id']>0) {
        $email_text .= REMEMBER_EMAIL_LOGIN . ":\n" . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'orders_id=' . $unsold_order_id, 'SSL', false) . "\n\n";
    }

    $email_text .= REMEMBER_EMAIL_BOTTOM . ': ' . STORE_OWNER_EMAIL_ADDRESS . "\n\n";

    if (defined('REMEMBER_EMAIL_COMMENT_' . $unsold_country) && constant('REMEMBER_EMAIL_COMMENT_' . $unsold_country)!='') {
        $email_text .= constant('REMEMBER_EMAIL_COMMENT_' . $unsold_country) . "\n\n";
    }

    if ($remember_status < $remember_anzahl_erinnerungen) {
        $email_text .= sprintf(REMEMBER_EMAIL_REMEMBER_COUNTS , $remember_status, $remember_anzahl_erinnerungen) . "\n\n";
        tep_db_query("update " . TABLE_ORDERS . " set last_modified = now(), remember_status = '" . (int)$remember_status . "' where orders_id = '" . (int)$unsold_order_id . "' limit 1");
    } else {
        $email_text .= sprintf(REMEMBER_EMAIL_REMEMBER_LAST , $remember_frist) . "\n\n";
        tep_db_query("update " . TABLE_ORDERS . " set last_modified = now(), remember_status = '" . (int)$remember_status . "', orders_status = '" . (int)$remember_status_deaktiviert . "' where orders_id = '" . (int)$unsold_order_id . "' limit 1");
    }

    $email_text = strip_tags($email_text);
    echo nl2br($email_text) . "<br><br>\n";
  // tep_mail($unsold_order->customer['name'], $unsold_order->customer['email_address'], REMEMBER_EMAIL_TEXT_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
  // tep_mail('Erinnerung an ' . $unsold_order->customer['name'], SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
}
$_SESSION['remember_moneyorder'] = true;
