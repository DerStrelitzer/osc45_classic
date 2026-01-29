<?php
/*
  $Id: checkout_success.php,v 1.17 2003/02/16 00:42:03 harley_vb Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

define('NAVBAR_TITLE_1', 'Kasse');
define('NAVBAR_TITLE_2', 'Erfolg');

define('HEADING_TITLE', 'Ihre Bestellung ist fertig');

define('TEXT_SUCCESS', 'Ihre Bestellung ist jetzt aufgenommen und wird bearbeitet!');
define('TEXT_NOTIFY_PRODUCTS', 'Bitte benachrichtigen Sie mich über Aktuelles zu folgenden Produkten:');
define('TEXT_SEE_ORDERS', 'Sie können Ihre Bestellung(en) auf der Seite <a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '"><span class="underlined">\'Ihr Konto\'</a></span> jederzeit einsehen und sich dort auch Ihre <a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '"><span class="underlined">\'Bestellübersicht\'</span></a> anzeigen lassen.');
define('TEXT_CONTACT_STORE_OWNER', 'Falls Sie Fragen bezüglich Ihrer Bestellung haben, wenden Sie sich an unseren <a href="' . tep_href_link(FILENAME_CONTACT_US) . '"><span class="underlined">Vertrieb</span></a>.');
define('TEXT_THANKS_FOR_SHOPPING', 'Wir danken Ihnen für Ihren Online-Einkauf!');

define('TABLE_HEADING_DOWNLOAD_DATE', 'herunterladen möglich bis:');
define('TABLE_HEADING_DOWNLOAD_COUNT', 'max. Anz. Downloads');
define('HEADING_DOWNLOAD', 'Artikel herunterladen:');
define('FOOTER_DOWNLOAD', 'Sie können Ihre Artikel auch später unter \'%s\' herunterladen');
