<?php
/*
  $Id: checkout_payment.php,v 1.20 2003/02/16 00:42:03 harley_vb Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_CONDITIONS_INFORMATION', 'Allgemeine Geschäfts- und Lieferbedingungen');
define('TEXT_CONDITIONS_CONFIRM', 'Ich habe die <a href="' . tep_href_link(FILENAME_CONDITIONS) . '" class="underlined">Allgemeinen Geschäftsbedingungen</a> zur Kenntnis genommen und bin damit einverstanden, die <a href="' . tep_href_link(FILENAME_CONDITIONS) . '" class="underlined">Widerrufsbelehrung</a> habe ich gelesen.');
define('TEXT_CONDITIONS_DOWNLOAD', 'AGB herunterladen');

define('NAVBAR_TITLE_1', 'Kasse');
define('NAVBAR_TITLE_2', 'Zahlungsweise');

define('HEADING_TITLE', 'Zahlungsweise');

define('TABLE_HEADING_BILLING_ADDRESS', 'Rechnungsadresse');
define('TEXT_SELECTED_BILLING_DESTINATION', 'Bitte wählen Sie aus Ihrem Adressbuch die gewünschte Rechnungsadresse für Ihre Bestellung aus.');
define('TITLE_BILLING_ADDRESS', 'Rechnungsadresse:');

define('TABLE_HEADING_PAYMENT_METHOD', 'Zahlungsweise');
define('TEXT_SELECT_PAYMENT_METHOD', 'Bitte wählen Sie die gewünschte Zahlungsweise für Ihre Bestellung aus.');
define('TITLE_PLEASE_SELECT', 'Bitte wählen Sie');
define('TEXT_ENTER_PAYMENT_INFORMATION', 'Wir bieten folgende Zahlungsweise an:');

define('TABLE_HEADING_COMMENTS', 'Fügen Sie hier Ihre Anmerkungen zu dieser Bestellung ein');

define('TITLE_CONTINUE_CHECKOUT_PROCEDURE', 'Fortsetzung des Bestellvorganges');
define('TEXT_CONTINUE_CHECKOUT_PROCEDURE', 'zur Bestätigung Ihrer Bestellung.');
