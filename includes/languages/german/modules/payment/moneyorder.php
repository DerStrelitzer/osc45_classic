<?php
/*
  $Id: moneyorder.php,v 1.9 2003/07/11 09:04:23 jan0815 Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

define('MODULE_PAYMENT_MONEYORDER_TEXT_TITLE', 'Vorkasse');
define('MODULE_PAYMENT_MONEYORDER_TEXT_SUBTITLE', 'Nach Abschluss der Bestellung werden wir Ihnen unsere Bankverbindung per E-Mail schicken. Ihre Bestellung versenden wir dann nach Zahlungseingang.');

define('MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION', 'Nach Abschluss der Bestellung werden wir Ihnen unsere Bankverbindung per E-Mail schicken. <br>Ihre Bestellung versenden wir dann nach Zahlungseingang');
define(
    'MODULE_PAYMENT_MONEYORDER_TEXT_EMAIL_FOOTER', 
    'Zahlbar an:' . "\n"
    . STORE_NAME_ADDRESS . "\n"
    . STORE_BILLING_TO . "\n"
    . "\n"
    . 'Ihre Bestellung versenden wir nach Zahlungseingang.' . "\n" 
    . 'Geben Sie bitte die Auftragsnummer an:'
);
