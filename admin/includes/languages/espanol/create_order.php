<?php
/*
  $Id: create_order.php,v 1.2i 2006/09/25 Ingo <www.strelitzer.de>
   v1.0: frankl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License

*/
define('HEADING_CREATE', 'Eingabe der Kundendaten');

define('TEXT_SELECT_CUSTOMER', 'Auswahl eines Kunden');
define('TEXT_PLEASE_SELECT', '- Bitte wählen -');
define('TEXT_STEP_1', 'Schritt 1: Auswahl des Kunden / Eingabe Details');
define('ENTRY_CUSTOMERS_ID', 'Kunden ID');

define('ENTRY_REGISTER_NEW', 'Neue Angaben registrieren');
define('ENTRY_REGISTER_ONLY', 'Nur registrieren, kein Auftrag');

define('ENTRY_CUSTOMERS_ID_TEXT', '');
define('ENTRY_GENDER_TEXT', '');
define('ENTRY_FIRST_NAME_TEXT', '');
define('ENTRY_LAST_NAME_TEXT', '');
define('ENTRY_EMAIL_ADDRESS_TEXT', '');
define('ENTRY_COMPANY_TEXT', '');

define('ENTRY_STREET_ADDRESS_TEXT', '');
define('ENTRY_SUBURB_TEXT', '');
define('ENTRY_POST_CODE_TEXT', '');
define('ENTRY_CITY_TEXT', '');
define('ENTRY_STATE_TEXT', '');
define('ENTRY_COUNTRY_TEXT', '');

define('CATEGORY_SHIPPING_AND_PAYMENT', 'Versand & Zahlung');
define('ENTRY_SHIPPING', 'Versand');
define('ENTRY_SHIPPING_TAX', 'Steuersatz');
define('ENTRY_PAYMENT', 'Zahlung');

//Quelle create_account:
define('EMAIL_SUBJECT', 'Willkommen zu ' . STORE_NAME);
define('EMAIL_GREET_MR', 'Sehr geehrter Herr ' . stripslashes($_POST['lastname']) . ',' . "\n\n");
define('EMAIL_GREET_MS', 'Sehr geehrte Frau ' . stripslashes($_POST['lastname']) . ',' . "\n\n");
define('EMAIL_GREET_NONE', 'Sehr geehrte ' . stripslashes($_POST['firstname']) . ',' . "\n\n");
define('EMAIL_WELCOME', 'willkommen zu <b>' . STORE_NAME . '</b>.' . "\n\n");
define('EMAIL_TEXT', 'Sie können jetzt unseren <b>Online-Service</b> nutzen. Der Service bietet unter anderem:' . "\n\n" . '<li><b>Kundenwarenkorb</b> - Jeder Artikel bleibt registriert bis Sie zur Kasse gehen, oder die Produkte aus dem Warenkorb entfernen.' . "\n" . '<li><b>Adressbuch</b> - Wir können jetzt die Produkte zu der von Ihnen ausgesuchten Adresse senden. Der perfekte Weg ein Geburtstagsgeschenk zu versenden.' . "\n" . '<li><b>Vorherige Bestellungen</b> - Sie können jederzeit Ihre vorherigen Bestellungen überprüfen.' . "\n" . '<li><b>Meinungen über Produkte</b> - Teilen Sie Ihre Meinung zu unseren Produkten mit anderen Kunden.' . "\n\n");
define('EMAIL_CONTACT', 'Falls Sie Fragen zu unserem Kunden-Service haben, wenden Sie sich bitte an den Vertrieb: ' . STORE_OWNER_EMAIL_ADDRESS . '.' . "\n\n");

//bearbeitet
define('EMAIL_WARNING', '<b>Achtung:</b> Bitte besuchen Sie <a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'account.php">' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'account.php</a> und kontrollieren Sie Ihre Angaben auf Richtigkeit.' . "\n\n" .
'Zum einloggen benutzen Sie Ihre Email-Adresse und dieses Passwort: %s' . "\n" .
'Wenn sie den genannten Link besuchen, finden Sie auch einen Menüpunkt, um Ihr Passwort zu ändern. Nutzen Sie diese Möglichkeit bitte.');

?>