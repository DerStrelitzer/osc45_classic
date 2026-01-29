<?php
/*
  $Id: moneyorder.php,v 1.6 2003/01/24 21:36:04 thomasamoulton Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  define('MODULE_PAYMENT_MONEYORDER_TEXT_TITLE', 'Money Order');
  define('MODULE_PAYMENT_MONEYORDER_TEXT_SUBTITLE', 'We send more informations about payment on e-mail. Your order will ship, when we receive your payment.');

  define('MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION', 'Make Payable To:&nbsp;<br />' . nl2br(STORE_NAME_ADDRESS) . '<br /><br />' . 'Your order will ship, when we receive your payment.');
  define('MODULE_PAYMENT_MONEYORDER_TEXT_EMAIL_FOOTER', "Make Payable To:\n" .
  STORE_NAME_ADDRESS . "\n".
  STORE_BILLING_TO . "\n\n" . 'Your order will ship, when we receive your payment.' . "\n" . 'Please note the orders code with your payment:');
?>