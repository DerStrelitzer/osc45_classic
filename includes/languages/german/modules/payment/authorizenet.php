<?php
/*
  $Id: authorizenet.php,v 1.16 2003/07/11 09:04:23 jan0815 Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

define('MODULE_PAYMENT_AUTHORIZENET_TEXT_TITLE', 'Authorize.net');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DESCRIPTION', 'Kreditkarten Test Info:<br><br>CC#: 4111111111111111<br />Gültig bis: Any');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_TYPE', 'Typ:');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_OWNER', 'Kreditkarteninhaber:');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_NUMBER', 'Kreditkarten-Nr.:');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_CREDIT_CARD_EXPIRES', 'Gültig bis:');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_OWNER', '* Der Name des Kreditkarteninhabers muss mindestens aus  ' . CC_OWNER_MIN_LENGTH . ' Zeichen bestehen.\n');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_JS_CC_NUMBER', '* Die \'Kreditkarten-Nr.\' muss mindestens aus ' . CC_NUMBER_MIN_LENGTH . ' Zahlen bestehen.\n');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR_MESSAGE', 'Bei der Überpüfung Ihrer Kreditkarte ist ein Fehler aufgetreten! Bitte versuchen Sie es nochmal.');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_DECLINED_MESSAGE', 'Ihre Kreditkarte wurde abgelehnt. Bitte versuchen Sie es mit einer anderen Karte oder kontaktieren Sie Ihre Bank für weitere Informationen.');
define('MODULE_PAYMENT_AUTHORIZENET_TEXT_ERROR', 'Fehler bei der Überpüfung der Kreditkarte!');
