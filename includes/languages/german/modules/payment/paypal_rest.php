<?php
/*
  $Id: paypal_rest.php $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2023 osCommerce

  Released under the GNU General Public License
 */

define('MODULE_PAYMENT_PAYPAL_REST_TEXT_TITLE', 'PayPal Checkout');
define('MODULE_PAYMENT_PAYPAL_REST_TEXT_PUBLIC_TITLE', 'PayPal (inklusive Kredit- und Debitkarten)');
define('MODULE_PAYMENT_PAYPAL_REST_TEXT_DESCRIPTION', '<!-- <img src="images/icon_info.gif" border="0" />&nbsp;<a href="http://library.oscommerce.com/Package&en&paypal&oscom23&express_checkout" target="_blank" style="text-decoration: underline; font-weight: bold;">View Online Documentation</a><br /><br />--><img src="images/icon_popup.gif" border="0" />&nbsp;<a href="https://www.paypal.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit PayPal Website</a>');

define('MODULE_PAYMENT_PAYPAL_REST_ERROR_ADMIN_CURL', 'Dieses Zahlungsmodul erfordert die PHP-Erweiterung <b>cURL</b> und wird nur geladen, wenn diese in PHP aktiviert wurde.');
define('MODULE_PAYMENT_PAYPAL_REST_ERROR_ADMIN_CONFIGURATION', 'Dieses Zahlungsmodul wird nur geladen, wenn in Deinem PayPal Account die API-Parameter aktiviert wurden und hier eingetragen sind. Dafür ist es erforderlich, das Paypal-Konto auf Händler aufzuwerten.');

define('MODULE_PAYMENT_PAYPAL_REST_TEXT_BUTTON', 'Kasse mit PayPal');
define('MODULE_PAYMENT_PAYPAL_REST_TEXT_COMMENTS', 'Kommentare:');

define('MODULE_PAYMENT_PAYPAL_REST_BUTTON', 'https://www.paypalobjects.com/webstatic/en_US/btn/btn_checkout_pp_142x27.png');
define('MODULE_PAYMENT_PAYPAL_REST_LANGUAGE_LOCALE', 'de_DE');

define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_LINK_TITLE', 'Test API Server Connection');
define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_TITLE', 'API Server Connection Test');
define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_GENERAL_TEXT', 'Testing connection to server..');
define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_BUTTON_CLOSE', 'Close');
define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_TIME', 'Connection Time:');
define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_SUCCESS', 'Success!');
define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_FAILED', 'Fehler! Bitte prüfe Deine Daten und Einstellungen noch einmal, danach versuche es noch einmal.');
define('MODULE_PAYMENT_PAYPAL_REST_DIALOG_CONNECTION_ERROR', 'Es ist ein Fehler aufgetreten. Bitte die Seite aktualisieren, prüfe die Angaben und versuche es noch einmal.');

define('MODULE_PAYMENT_PAYPAL_REST_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS', 'Der Versand an die ausgewählte Adresse ist zurzeit nicht möglich. Bitte wähle oder erstelle eine andere Adresse mit dem ausgewählten Warenkorb.');
define('MODULE_PAYMENT_PAYPAL_REST_WARNING_LOCAL_LOGIN_REQUIRED', 'Logge Dich in Dein Konto ein und prüfe Deinen Auftrag.');
define('MODULE_PAYMENT_PAYPAL_REST_NOTICE_CHECKOUT_CONFIRMATION', 'Bitte prüfe und bestätige Deinen unteren Auftrag. Dein Auftrag kann nur nach Deiner Bestätigung bearbeitet werden.');

define('MODULE_PAYMENT_PAYPAL_REST_API_DETAILS', 'PayPal API details');
define('MODULE_PAYMENT_PAYPAL_REST_API_OK', 'All details correct');
define('MODULE_PAYMENT_PAYPAL_REST_API_FAIL_DATA', '');
define('MODULE_PAYMENT_PAYPAL_REST_ORDER_ID_ERROR', 'Ungültige Order Id');
define('MODULE_PAYMENT_PAYPAL_REST_ORDER_DETAILS_ERROR', 'Bestellung ist nicht vollständig');
define('MODULE_PAYMENT_PAYPAL_REST_RESTART', 'Fehler während der Kommunication mit PayPal (incorrect total). Bitte noch einmal bei PayPal einloggen.');
define('MODULE_PAYMENT_PAYPAL_REST_RESTART_AUTHORIZE', 'Fehler während der Kommunication mit PayPal (payment not authorized). Bitte noch einmal bei PayPal einloggen.');
define('MODULE_PAYMENT_PAYPAL_REST_RESTART_CAPTURE', 'Fehler während der Kommunication mit PayPal (payment not captured). Bitte noch einmal bei PayPal einloggen.');
define('TEXT_PAY_UPON_INVOICE', 'Kauf auf Rechnung');
define('TEXT_CANCELLED_BY_CUSTOMER', 'Abbruch durch Kunde');
define('MODULE_PAYMENT_PAYPAL_REST_TEXT_ERROR_CAPTURE', 'Bezahlung konnte nicht erfasst werden');
define('MODULE_PAYMENT_PAYPAL_REST_GENERAL_ERROR', "Auftrag konnte nicht erstellt werden");
define('MODULE_PAYMENT_PAYPAL_REST_PROCESSING_ERROR', "Die Zahlung wurde nicht verarbeitet. Überprüfen Sie Ihre Angaben und versuchen Sie es erneut oder wählen Sie eine andere Zahlungsoption.");

define('MODULE_PAYMENT_PAYPAL_REST_TEXT_CC_NUMBER', "Kartennnummer");
define('MODULE_PAYMENT_PAYPAL_REST_TEXT_EXP', "Ablaufdatum");
define('MODULE_PAYMENT_PAYPAL_REST_TEXT_EXP_PLACEHOLDER', "MM/YY");
define('MODULE_PAYMENT_PAYPAL_REST_TEXT_CVV', "CVV");
define('SESSION_EXPIRED_LOGIN_OR_CHECK_EMAIL', "Ihre Sitzung ist abgelaufen. Bitte überprüfen Sie Ihre E-Mails auf Bestellaktualisierungen oder melden Sie sich bei Ihrem Konto an.");
//define('SESSION_EXPIRED_LOGIN_OR_CHECK_EMAIL', "Überprüfen Sie Ihre E-Mails auf Statusaktualisierungen. Die Sitzung ist abgelaufen.");

  ///////////////////

define('ADD_PAYPAL', 'PayPal Quick Setup');
define('ADD_PAYPAL_TITLE', 'PayPal Setup');
define('PAYPAL_EXISTING_ACCOUNT', 'Hast Du eine PayPal Konto?');
define('PAYPAL_ACCOUNT_OPTIONS_YES', 'Ja, verbinde');
define('PAYPAL_ACCOUNT_OPTIONS_NO', 'Nein, erstelle eins');
define('TEXT_ADVANCED', 'Sofort...');
define('PAYPAL_SANDBOX_TRY', 'Willst Du zuerst in der Sandbox testen? Bedenke - Du kannst jederzeit auf das Livesystem wechseln.');
define('PAYPAL_ACCOUNT_OPTIONS_OWN_API_ACCESS', 'Ich habe meine eigenen Keys für die REST API bei der Hand und ich weiß damit umzugehen');
define('PAYPAL_ACCOUNT_PRESS_BUTTON', 'Klicke auf "PayPal" Button');
define('MODULE_PAYMENT_PAYPAL_REST_CONTINUE_PAYPAL', 'Weiter zu PayPal');
define('MODULE_PAYMENT_PAYPAL_REST_GET_DATA_PAYPAL', '\\num die Daten von PayPal bekommen');
define('PAYPAL_SANDBOX_MODE', 'Use PayPal sandbox account (Du kannst später zum Live-Konto wechseln)');
define('TEXT_ENTER_API_DETAILS', 'API Details eintragen');

define('MODULE_PAYMENT_PAYPAL_REST_SELLER_BOARDED_ERROR_EMAIL', 'Achtung: bitte bestätige Deine E-Mail Adresse auf <a target="blank" href="https://www.paypal.com/businessprofile/settings">https://www.paypal.com/businessprofile/settings</a> um Zahlungen zu empfangen! Du kannst derzeit keine Zahlungen empfangen!');

define('MODULE_PAYMENT_PAYPAL_REST_PAYLATER_TITLE', 'Pay Later (normalerweise sollte auch PayPal Credit ausgewählt werden)');


define('MODULE_PAYMENT_PAYPAL_REST_SELLER_NOT_BOARDED', 'Dein PayPal Konto ist nicht mit diesem osCommerce Shop verbunden.');
define('MODULE_PAYMENT_PAYPAL_REST_SELLER_MERCHANT_ID', 'Seller Merchant Id');
define('MODULE_PAYMENT_PAYPAL_REST_OWN_CLIENT_ID', 'PayPal API Client ID');
define('MODULE_PAYMENT_PAYPAL_REST_OWN_CLIENT_SECRET', 'PayPal API Client secret');
define('MODULE_PAYMENT_PAYPAL_REST_SELLER_EMAIL', 'Seller E-Mail Addresse');

define('MODULE_PAYMENT_PAYPAL_REST_API_TEST', 'Test API connection');

define('MODULE_PAYMENT_PAYPAL_REST_TEXT_ADVANCED_SETTINGS', 'Konto Details und weitere Einstellungen');
define('MODULE_PAYMENT_PAYPAL_REST_TEXT_ACCOUNT_DETAILS', 'PayPal Konto Details');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS', 'PayPal Webhooks');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_REQUIRED_NOTE', '* für alternative Zahlungsmethoden (APMs)');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_REQUIRED', 'Erforderliche Webhooks');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_SUBSCRIBED', 'Abonnierte Webhooks');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_SUBSCRIBE', 'Abonnieren');
define('MODULE_PAYMENT_PAYPAL_REST_WEBHOOKS_SUBSCRIBE_CONFIRM', 'Bitte bestätige, dass Du die erforderlichen Webhooks abonnieren möchten. Du kannst sie jederzeit in Ihrem PayPal-Konto wieder abmelden.');
define('MODULE_PAYMENT_PAYPAL_REST_DELETE_SELLER', 'Anderes PayPal Konto verbinden');

define('MODULE_PAYMENT_PAYPAL_REST_SELLER_BOARDED_ERROR', 'PayPal Details können nicht abgerufen werden');
define('MODULE_PAYMENT_PAYPAL_REST_SAVE_TO_CONTINUE', 'Der PayPal-Transaktionsserver wurde geändert. Speichere die Änderungen, um die API-Konfiguration abzuschließen.');
define('MODULE_PAYMENT_PAYPAL_REST_UNLINK_PROMPT', 'Möchtest Du wirklich ein anderes PayPal-Konto verknüpfen?');

define('MODULE_PAYMENT_PAYPAL_REST_CARD_PROCESSING_VIRTUAL_TERMINAL_TEXT', 'Virtual terminal');
define('MODULE_PAYMENT_PAYPAL_REST_COMMERCIAL_ENTITY_TEXT', 'Commercial entity');
define('MODULE_PAYMENT_PAYPAL_REST_CUSTOM_CARD_PROCESSING_TEXT', 'Custom card processing');
define('MODULE_PAYMENT_PAYPAL_REST_DEBIT_CARD_SWITCH_TEXT', 'Debit card switch');
define('MODULE_PAYMENT_PAYPAL_REST_FRAUD_TOOL_ACCESS_TEXT', 'Fraud tool');
define('MODULE_PAYMENT_PAYPAL_REST_ALT_PAY_PROCESSING_TEXT', 'Alternative payment methods');
define('MODULE_PAYMENT_PAYPAL_REST_RECEIVE_MONEY_TEXT', 'Receive money');
define('MODULE_PAYMENT_PAYPAL_REST_SEND_MONEY_TEXT', 'Send money');
define('MODULE_PAYMENT_PAYPAL_REST_STANDARD_CARD_PROCESSING_TEXT', 'Standard card processing');
define('MODULE_PAYMENT_PAYPAL_REST_WITHDRAW_MONEY_TEXT', 'Withdraw money');

define('MODULE_PAYMENT_PAYPAL_REST_CUSTOM_CARD_FIELDS', 'Custom Card Fields');
define('MODULE_PAYMENT_PAYPAL_REST_CUSTOM_CARD_3DS', '3D Secure');
define('MODULE_PAYMENT_PAYPAL_REST_CUSTOM_CARD_3DS_DESCRIPTION', '3D Secure ermöglicht die Authentifizierung des Karteninhabers durch den Kartenaussteller. Es verringert die Wahrscheinlichkeit von Betrug bei Verwendung unterstützter Karten und verbessert die Transaktionsleistung. Eine erfolgreiche 3D Secure-Authentifizierung kann die Haftung für Rückbuchungen aufgrund von Betrug von Dir auf den Kartenaussteller verlagern.');
define('MODULE_PAYMENT_PAYPAL_REST_CONTINGENCIES', 'Сontingencies');

define('ENTRY_STATUS', 'Status');
define('TEXT_YES', 'Ja');
define('TEXT_NO', 'Nein');
define('TEXT_NEXT', 'Weiter');

define('TEXT_ALTERNATIVE_CHECKOUT_METHODS', 'Oder benutze');
