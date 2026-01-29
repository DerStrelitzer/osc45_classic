<?php
/*
  $Id: edit_orders.php,v 1.25 2003/11/4 10:00:00 Ingo 

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Auftrag bearbeiten');
define('HEADING_TITLE_SEARCH', 'Auftrags-ID:');
define('HEADING_TITLE_STATUS', 'Status:');
define('ADDING_TITLE', 'Artikel hinzufügen');

define('ENTRY_UPDATE_TO_CC', '(Update Kreditkarte-Felder? "<b>Credit Card</b>" eingeben.)');
define('TABLE_HEADING_COMMENTS', 'Kommentar');
define('TABLE_HEADING_CUSTOMERS', 'Kommentar');
define('TABLE_HEADING_ORDER_TOTAL', 'Gesamtsumme');
define('TABLE_HEADING_DATE_PURCHASED', 'Bestelldatum');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Aktion');
define('TABLE_HEADING_QUANTITY', 'Anz.');
define('TABLE_HEADING_PRODUCTS_MODEL', 'Art-Nr');
define('TABLE_HEADING_PRODUCTS', 'Artikel');
define('TABLE_HEADING_TAX', 'USt');
define('TABLE_HEADING_TOTAL', 'Total');
define('TABLE_HEADING_UNIT_PRICE', 'Einzelpreis');
define('TABLE_HEADING_TOTAL_PRICE', 'Summe');

define('TABLE_HEADING_PRICE_EXCLUDING_TAX', 'Preis (netto)');
define('TABLE_HEADING_PRICE_INCLUDING_TAX', 'Preis (inkl.)');
define('TABLE_HEADING_TOTAL_EXCLUDING_TAX', 'Total (netto)');
define('TABLE_HEADING_TOTAL_INCLUDING_TAX', 'Total (inkl.)');

define('TABLE_HEADING_CUSTOMER_NOTIFIED', 'Kundeninformation');
define('TABLE_HEADING_DATE_ADDED', 'Aufnahmedatum');

define('ENTRY_NAME', 'Name:');
define('ENTRY_CUSTOMER', 'Kunde:');
define('ENTRY_SOLD_TO', 'Rechnung an:');
define('ENTRY_DELIVERY_TO', 'Lieferung an:');
define('ENTRY_SHIP_TO', 'Versand an:');
define('ENTRY_SHIPPING_ADDRESS', 'Versandadresse:');
define('ENTRY_BILLING_ADDRESS', 'Rechnungsadresse:');
define('ENTRY_PAYMENT_METHOD', 'Zahlung durch:');
define('ENTRY_CREDIT_CARD_TYPE', 'Kreditkartenart:');
define('ENTRY_CREDIT_CARD_OWNER', 'Karteninhaber:');
define('ENTRY_CREDIT_CARD_NUMBER', 'Kartennummer:');
define('ENTRY_CREDIT_CARD_EXPIRES', 'Verfalldatum:');
define('ENTRY_SUB_TOTAL', 'Zwi.summe:');
define('ENTRY_TAX', 'USt:');
define('ENTRY_SHIPPING', 'Versand:');
define('ENTRY_TOTAL', 'Gesamt:');
define('ENTRY_DATE_PURCHASED', 'Bestelldatum:');
define('ENTRY_STATUS', 'Status:');
define('ENTRY_DATE_LAST_UPDATED', 'letzte Bearbeitung:');
define('ENTRY_NOTIFY_CUSTOMER', 'Kunde informieren:');
define('ENTRY_NOTIFY_COMMENTS', 'Kommentar senden:');
define('ENTRY_PRINTABLE', 'Rechnung drucken');

define('TEXT_CHOOSE_A_CATEGORY', 'Wählen Sie eine Kategorie');
define('TEXT_CHOOSE_A_PRODUCT', 'Wählen Sie ein Produkt');
define('TEXT_NO_OPTIONS', 'keine Optionen - überspringen...');
define('TEXT_STEP', 'Schritt');
define('TEXT_QUANTITY', 'Anzahl');
define('TEXT_ADD', 'hinzufügen');
define('TEXT_INFO_HEADING_DELETE_ORDER', 'Auftrag löschen');
define('TEXT_INFO_DELETE_INTRO', 'Sind Sie sicher, daß dieser Auftrag gelöscht werden soll?');
define('TEXT_INFO_RESTOCK_PRODUCT_QUANTITY', 'Anzahl dem Lager gutschreiben');
define('TEXT_DATE_ORDER_CREATED', 'Erstellungstag:');
define('TEXT_DATE_ORDER_LAST_MODIFIED', 'letzte Bearbeitung:');
define('TEXT_INFO_PAYMENT_METHOD', 'Bezahlungsart:');

define('TEXT_ALL_ORDERS', 'Alle Aufträge');
define('TEXT_NO_ORDER_HISTORY', 'Kein Auftragshistorie verfügbar');

define('EMAIL_SEPARATOR', '------------------------------------------------------');
define('EMAIL_TEXT_SUBJECT', 'Auftrag wurde bearbeitet');
define('EMAIL_TEXT_ORDER_NUMBER', 'Auftragsnummer:');
define('EMAIL_TEXT_INVOICE_URL', 'Einzelheiten der Rechnung:');
define('EMAIL_TEXT_DATE_ORDERED', 'Bestelldatum:');
define('EMAIL_TEXT_STATUS_UPDATE', 'Ihr Auftrag erhielt den folgenden Status.' . "\n\n" . 'Neuer Status: %s' . "\n\n" . 'Bitte antworten Sie auf diese E-Mail, wenn Sie dazu noch Fragen haben.' . "\n");
define('EMAIL_TEXT_COMMENTS_UPDATE', 'Der Hinweis zu Ihrem Auftrag: ' . "\n\n%s\n\n");

define('ERROR_ORDER_DOES_NOT_EXIST', 'Fehler: Auftrag existiert nicht!');
define('SUCCESS_ORDER_UPDATED', 'Erfolg: Auftrag wurde bearbeitet.');
define('WARNING_ORDER_NOT_UPDATED', 'Achtung: Es wurde nichts geändert.');
