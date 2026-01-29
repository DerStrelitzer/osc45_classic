<?php
/*
  $Id: banktransfer.php,v 1.9 2003/02/18 19:22:15 dogu Exp $
  mod by Ingo www.strelitzer.de

  OSC German Banktransfer
  (http://www.oscommerce.com/community/contributions,826)

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2001 - 2003 osCommerce

  Released under the GNU General Public License
*/
  define('MODULE_PAYMENT_BANKTRANSFER_URL_NOTE', 'fax_deutsch.html');

  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_TITLE', 'Lastschriftverfahren');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_DESCRIPTION', 'Lastschriftverfahren');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK', 'Bankeinzug');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_INFO', 'Bitte beachten Sie, dass das Lastschriftverfahren <b>nur</b> von einem <b>deutschen Girokonto</b> aus möglich ist');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_OWNER', 'Kontoinhaber:');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_NUMBER', 'Kontonummer:');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_BLZ', 'BLZ:');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_NAME', 'Bank:');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_FAX', 'Einzugsermächtigung wird per Fax bestätigt');

  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR', 'FEHLER:');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_1', 'Kontonummer und BLZ stimmen nicht überein! Bitte überprüfen Sie Ihre Angaben nochmals.');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_2', 'Für diese Kontonummer ist kein Prüfziffernverfahren definiert!');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_3', 'Kontonummer nicht prüfbar!');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_4', 'Kontonummer nicht prüfbar! Bitte überprüfen Sie Ihre Angaben nochmals.');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_5', 'Bankleitzahl nicht gefunden! Bitte überprüfen Sie Ihre Angaben nochmals.');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_8', 'Fehler bei der Bankleitzahl oder keine Bankleitzahl angegeben!');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_BANK_ERROR_9', 'Keine Kontonummer angegeben!');

  define('MODULE_PAYMENT_BANKTRANSFER_NOTE_TITLE', 'Hinweis:');
  define('MODULE_PAYMENT_BANKTRANSFER_NOTE_FIELD', 'Wenn Sie aus Sicherheitsbedenken keine Bankdaten über das Internet übertragen wollen, k&ouml;nnen Sie sich unser <a href="%s" target="_blank"><b>Faxformular</b></a> herunterladen und uns ausgef&uuml;llt zusenden.');

  define('JS_BANK_BLZ', 'Bitte geben Sie die BLZ Ihrer Bank ein!\n');
  define('JS_BANK_NAME', 'Bitte geben Sie den Namen Ihrer Bank ein!\n');
  define('JS_BANK_NUMBER', 'Bitte geben Sie Ihre Kontonummer ein!\n');
  define('JS_BANK_OWNER', 'Bitte geben Sie den Namen des Kontobesitzers ein!\n');

  //define('MODULE_PAYMENT_BANKTRANSFER_TEXT_EMAIL_FOOTER', 'Hinweis: Sie können sich unser Faxformular unter ' . HTTP_SERVER . DIR_WS_CATALOG . MODULE_PAYMENT_BANKTRANSFER_URL_NOTE . ' herunterladen und es ausgefüllt an uns zurücksenden.');
  define('MODULE_PAYMENT_BANKTRANSFER_TEXT_EMAIL_FOOTER', "Der Rechnungsbetrag wird von dem von Ihnen angegebenen Konto nach Auslieferung per Lastschrift eingezogen.\nWenn Sie noch kein Konto angegeben haben benötigen, wir von Ihnen das ausgefüllte Formular. Sollten Sie es noch nicht ausgedruckt haben, können Sie es hier nachholen:\n" . HTTP_SERVER . DIR_WS_CATALOG . MODULE_PAYMENT_BANKTRANSFER_URL_NOTE . "\nRufen Sie diese Seite mit Ihrem Browser auf und betätigen Sie in der Menüleiste das Druckersymbol.\nGeben Sie bitte die Auftragsnummer an:");
?>