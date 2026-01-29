<?php
/*
  $Id: spiders.php,v 1.00 2005/01/01 by Ingo

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Spider der Suchmaschinen');

define('TABLE_HEADING_CURRENCY_NAME', 'Währung');
define('TABLE_HEADING_CURRENCY_CODES', 'Kürzel');
define('TABLE_HEADING_CURRENCY_VALUE', 'Wert');
define('TABLE_HEADING_ACTION', 'Aktion');

define('TEXT_INFO_EDIT_INTRO', 'Bitte führen Sie alle notwendigen Änderungen durch');
define('TEXT_INFO_CURRENCY_TITLE', 'Name:');
define('TEXT_INFO_CURRENCY_CODE', 'Kürzel:');
define('TEXT_INFO_CURRENCY_SYMBOL_LEFT', 'Symbol Links:');
define('TEXT_INFO_CURRENCY_SYMBOL_RIGHT', 'Symbol Rechts:');
define('TEXT_INFO_CURRENCY_DECIMAL_POINT', 'Dezimalkomma:');
define('TEXT_INFO_CURRENCY_THOUSANDS_POINT', 'Tausenderpunkt:');
define('TEXT_INFO_CURRENCY_DECIMAL_PLACES', 'Dezimalstellen:');
define('TEXT_INFO_CURRENCY_LAST_UPDATED', 'letzte Änderung:');
define('TEXT_INFO_CURRENCY_VALUE', 'Wert:');
define('TEXT_INFO_CURRENCY_EXAMPLE', 'Beispiel:');
define('TEXT_INFO_INSERT_INTRO', 'Bitte geben Sie die neue Währung mit allen relevanten Daten ein');
define('TEXT_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie diese Währung löschen möchten?');
define('TEXT_INFO_HEADING_NEW_CURRENCY', 'neue Währung');
define('TEXT_INFO_HEADING_EDIT_CURRENCY', 'Währung bearbeiten');
define('TEXT_INFO_HEADING_DELETE_CURRENCY', 'Währung löschen');
define('TEXT_INFO_SET_AS_DEFAULT', TEXT_SET_DEFAULT . ' (manuelles Aktualisieren der Wechselkurse erforderlich.)');
define('TEXT_INFO_CURRENCY_UPDATED', 'The exchange rate for %s (%s) was updated successfully via %s.');

define('ERROR_REMOVE_DEFAULT_CURRENCY', 'Fehler: Die Standardwährung darf nicht gelöscht werden. Bitte definieren Sie eine neue Standardwährung und wiederholen Sie den Vorgang.');
define('ERROR_CURRENCY_INVALID', 'Error: The exchange rate for %s (%s) was not updated via %s. Is it a valid currency code?');
define('WARNING_PRIMARY_SERVER_FAILED', 'Warning: The primary exchange rate server (%s) failed for %s (%s) - trying the secondary exchange rate server.');
