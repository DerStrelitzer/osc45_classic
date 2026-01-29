<?php
/*
  $Id: banktransfer.php,v 1.9 2003/02/18 19:22:15 dogu Exp $
  mod by Ingo <ingo@strelitzer.de>

  OSC German Banktransfer
  (http://www.oscommerce.com/community/contributions,826)

  Contribution based on: 

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/
  define('MODULE_PAYMENT_SEPA_FORM_PAGE', 'fax_deutsch.html');

  define('MODULE_PAYMENT_SEPA_TEXT_TITLE',       'SEPA Lastschriftverfahren');
  define('MODULE_PAYMENT_SEPA_TEXT_DESCRIPTION', 'SEPA Lastschriftverfahren');
  define('MODULE_PAYMENT_SEPA_TEXT_BANK',        'Bankname');
  define('MODULE_PAYMENT_SEPA_TEXT_BANK_INFO',   'Bitte beachten Sie, dass das Lastschriftverfahren <b>nur</b> von einem <b>deutschen Girokonto</b> aus möglich ist');
  define('MODULE_PAYMENT_SEPA_TEXT_BANK_OWNER',  'Kontoinhaber:');
  define('MODULE_PAYMENT_SEPA_TEXT_BANK_IBAN',   'IBAN:');
  define('MODULE_PAYMENT_SEPA_TEXT_BANK_BIC',    'BIC:');
  define('MODULE_PAYMENT_SEPA_TEXT_BANK_NAME',   'Bank:');
  define('MODULE_PAYMENT_SEPA_TEXT_BANK_FAX',    'SEPA Lastschrift wird per Fax/Post geschickt');

  define('MODULE_PAYMENT_SEPA_TEXT_BANK_ERROR', 'FEHLER:');

  define('MODULE_PAYMENT_SEPA_NOTE_TITLE', 'Hinweis:');
  define('MODULE_PAYMENT_SEPA_NOTE_FIELD', 'Wenn Sie aus Sicherheitsbedenken keine Bankdaten über das Internet übertragen wollen, können Sie sich unser <a href="%s" target="_blank"><b>Faxformular</b></a> herunterladen und uns ausgefüllt zusenden.');

  define('JS_BANK_BIC',   'Bitte geben Sie die BIC Ihrer Bank ein!\n');
  define('JS_BANK_NAME',  'Bitte geben Sie den Namen Ihrer Bank ein.\n');
  define('JS_BANK_IBAN',  'Bitte geben Sie Ihre IBAN ein!\n');
  define('JS_BANK_OWNER', 'Bitte geben Sie den Namen des Kontoinhabers ein!\n');

  define('MODULE_PAYMENT_SEPA_TEXT_EMAIL_FOOTER', "Der Rechnungsbetrag wird von dem von Ihnen angegebenen Konto nach Auslieferung per Lastschrift eingezogen.\nWenn Sie noch kein Konto angegeben haben benötigen, wir von Ihnen das ausgefüllte Formular. Sollten Sie es noch nicht ausgedruckt haben, können Sie es hier nachholen:\n" . HTTP_SERVER . DIR_WS_CATALOG . MODULE_PAYMENT_SEPA_FORM_PAGE . "\nRufen Sie diese Seite mit Ihrem Browser auf und betätigen Sie in der Menüleiste das Druckersymbol.\nGeben Sie bitte die Auftragsnummer an:");
