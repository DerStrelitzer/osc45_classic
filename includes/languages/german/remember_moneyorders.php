<?php
/*
  $Id: remember_moneyorders.php,v 1.0 2006/08/22 by Ingo <http://forums.oscommerce.de/index.php?showuser=36>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

define('REMEMBER_EMAIL_GREET_MR', 'Sehr geehrter Herr %s,' . "\n\n");
define('REMEMBER_EMAIL_GREET_MS', 'Sehr geehrte Frau %s,' . "\n\n");
define('REMEMBER_EMAIL_GREET_NONE', 'Sehr geehrte/r %s,' . "\n\n");

define('REMEMBER_EMAIL_OPENER', "Sie haben am %s in unserem Shop auf " . HTTP_SERVER . " eine Bestellung aufgegeben.\n
Da wir bis heute noch keinen Zahlungseingang verbuchen konnten, möchten wir Sie gern noch einmal daran erinnern.\n\n
Ihre Bestellung enthält:");

define('REMEMBER_EMAIL_PAYMENT_METHOD','Bezahlung');

define('REMEMBER_EMAIL_REMEMBER_COUNTS', "Dies ist Ihre %s. von %s Erinnerungen.");
define('REMEMBER_EMAIL_REMEMBER_LAST', "Dies ist Ihre letzte Erinnerung. In %s Tagen werden wir Ihre Bestellung deaktivieren.\n
Sollten Sie danach noch an einer Lieferung interessiert sein, bezahlen Sie bitte den Rechnungsbetrag unter Nennung der oben genannten Bestellnummer auf unser Konto oder erteilen einen neuen Auftrag.");

define('REMEMBER_EMAIL_LOGIN', 'Weitere Informationen zu dieser Bestellung erhalten Sie in Ihrem geschützten Kundenbereich');
define('REMEMBER_EMAIL_BOTTOM','Wenn Sie noch Fragen zu unserem Shop oder dieser Bestellung haben, dürfen Sie sich gern auch direkt per Emai an uns wenden');

define('REMEMBER_EMAIL_COMMENT_DE', 'Kommentar für Deutschland');
define('REMEMBER_EMAIL_COMMENT_AT', 'Kommentar für Österreich');
define('REMEMBER_EMAIL_COMMENT_CH', 'Kommentar für Schweiz');

define('REMEMBER_EMAIL_TEXT_SUBJECT', 'Schon vergessen?');
