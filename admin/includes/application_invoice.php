<?php
/*
  $Id: application_invoice.php,v 1.0 2005/01/01 by Ingo <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/
if ((defined('INVOICE_NUMBER_LENGTH')) && (isset($_GET['oID'])) && ($_GET['oID'] != '')){
  define ('WARNING_INVOICE_NUMBER_EXISTS', 'Rechnungsnummer bereits erstellt');
  define ('SUCCESS_INVOICE_NUMBER_CREATE', 'Rechnungsnummer jetzt erstellt');
  define ('ERROR_INVOICE_NUMBER_CREATE',   'Rechnungsnummer konnte nicht erstellt werden');
  define ('ERROR_INVOICE_ORDER_NUMBER',    'unzulässige Bestellnummer');
  $invoice_order_query = tep_db_query("SELECT orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = '" . (int)$_GET['oID'] . "'");
  if (tep_db_num_rows($invoice_order_query)!=1) {
    $messageStack->add(ERROR_INVOICE_ORDER_NUMBER, 'error');
  } else {
    $invoice_order_status = tep_db_fetch_array($invoice_order_query);
    if (($invoice_order_status['orders_status'] == INVOICE_NUMBER_AT_STATUS) || (INVOICE_NUMBER_AT_STATUS == '0')){
      $number_check_query = tep_db_query("SELECT invoice_number FROM " . TABLE_INVOICE_NUMBER . " WHERE orders_id = '" . (int)$_GET['oID'] . "'");
      if (tep_db_num_rows($number_check_query)>0) {
        $messageStack->add(WARNING_INVOICE_NUMBER_EXISTS, 'warning');
      } else {
        if (!tep_db_query("insert into " . TABLE_INVOICE_NUMBER . " (orders_id, invoice_date) values ('" . (int)$_GET['oID'] . "', now())")) {
          $messageStack->add(ERROR_INVOICE_NUMBER_CREATE, 'error');
        } else {
          $messageStack->add(SUCCESS_INVOICE_NUMBER_CREATE, 'success');
        }
      }
    }
  }
}
?>