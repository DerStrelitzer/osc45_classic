<?php
/*
  $Id: affiliate_banners.php,v 1.3 2003/02/16 23:44:24 harley_vb Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Partnerprogramm Bannerverwaltung');

define('TABLE_HEADING_BANNERS', 'Banner');
define('TABLE_HEADING_GROUPS', 'Gruppe');
define('TABLE_HEADING_ACTION', 'Aktion');
define('TABLE_HEADING_STATISTICS', 'Anzeigen / Klicks');
define('TABLE_HEADING_PRODUCT_ID', 'Produkt ID');

define('TEXT_BANNERS_TITLE', 'Titel des Banners:');
define('TEXT_BANNERS_GROUP', 'Banner-Gruppe:');
define('TEXT_BANNERS_NEW_GROUP', ', oder geben Sie unten eine neue Banner-Gruppe ein');
define('TEXT_BANNERS_IMAGE', 'Bild (Datei):');
define('TEXT_BANNERS_IMAGE_LOCAL', ', oder geben Sie unten die lokale Datei auf Ihrem Server an');
define('TEXT_BANNERS_IMAGE_TARGET', 'Bildziel (Speichern nach):');
define('TEXT_BANNERS_HTML_TEXT', 'HTML Text:');
define('TEXT_AFFILIATE_BANNERS_NOTE', '<b>Banner Bemerkung:</b><ul><li>Sie können Bild- oder HTML-Text-Banner verwenden, beides gleichzeitig ist nicht möglich.</li><li>Wenn Sie beide Bannerarten gleichzeitig verwenden, wird nur der HTML-Text Banner angezeigt.</li></ul>');

define('TEXT_BANNERS_LINKED_PRODUCT','Produkt ID');
define('TEXT_BANNERS_LINKED_PRODUCT_NOTE','Wenn Sie das Banner mit einem Produkt verlinken wollen, geben Sie hier die Produkt ID ein. Wenn Sie mit der Startseite verlinken wollen geben Sie "0" ein.');

define('TEXT_BANNERS_DATE_ADDED', 'hinzugefügt am:');
define('TEXT_BANNERS_STATUS_CHANGE', 'Status geändert: %s');

define('TEXT_INFO_DELETE_INTRO', 'Sind Sie sicher, dass Sie diesen Banner löschen möchten?');
define('TEXT_INFO_DELETE_IMAGE', 'Bannerbild löschen');

define('SUCCESS_BANNER_INSERTED', 'Erfolg: Der Banner wurde eingefügt.');
define('SUCCESS_BANNER_UPDATED', 'Erfolg: Der Banner wurde aktualisiert.');
define('SUCCESS_BANNER_REMOVED', 'Erfolg: Der Banner wurde gelöscht.');

define('ERROR_BANNER_TITLE_REQUIRED', 'Fehler: Ein Bannertitel wird benötigt.');
define('ERROR_BANNER_GROUP_REQUIRED', 'Fehler: Eine Bannergruppe wird benötigt.');
define('ERROR_IMAGE_DIRECTORY_DOES_NOT_EXIST', 'Fehler: Zielverzeichnis existiert nicht %s.');
define('ERROR_IMAGE_DIRECTORY_NOT_WRITEABLE', 'Fehler: Zielverzeichnis ist nicht beschreibbar: %s');
define('ERROR_IMAGE_DOES_NOT_EXIST', 'Fehler: Bild existiert nicht.');
define('ERROR_IMAGE_IS_NOT_WRITEABLE', 'Fehler: Bild kann nicht gelöscht werden.');
