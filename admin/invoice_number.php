<?php
/*
  $Id: invoice_number.php,v 1.01 2005/01/01 by Ingo <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/

if (defined('INVOICE_NUMBER_LENGTH')) {
  $invoice_number_query = tep_db_query("SELECT invoice_number, invoice_date, shipping_date FROM " . TABLE_INVOICE_NUMBER . " WHERE orders_id = '" . (int)$oID . "'");
  if (tep_db_num_rows($invoice_number_query)<1) {
    $messageStack->add(ERROR_INVOICE_NUMBER_CREATE, 'warning');
  } else {
    $invoice = tep_db_fetch_array($invoice_number_query);
    while (strlen($invoice['invoice_number']) < INVOICE_NUMBER_LENGTH) $invoice['invoice_number']= '0' . $invoice['invoice_number'];
    $invoice_number = INVOICE_NUMBER_PREFIX . $invoice['invoice_number'];
    $invoice_date = tep_date_short($invoice['invoice_date']);
    if ($invoice['shipping_date']!='') $shipping_date = tep_date_short($invoice['shipping_date']);
    else $shipping_date = $invoice_date;
  }
}