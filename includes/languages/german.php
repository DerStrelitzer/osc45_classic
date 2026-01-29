<?php
/*
  $Id: german.php,v 1.124 2003/07/11 09:03:49 jan0815 Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

// Global entries for the <html> tag
define('HTML_PARAMS','dir="ltr"');

// if USE_DEFAULT_LANGUAGE_CURRENCY is true, use the following currency, instead of the applications default currency (used when changing language)
define('LANGUAGE_CURRENCY', 'EUR');

// look in your $PATH_LOCALE/locale directory for available locales
// or type locale -a on the server.
// Examples:
// on RedHat try 'de_DE'
// on FreeBSD try 'de_DE.utf8'
// on Windows try 'de' or 'German'
// @setlocale(LC_TIME, 'de_DE.utf8');
define ('LOCALE', strpos(strtoupper(PHP_OS), 'WIN')!==false ? @setlocale(LC_TIME, 'german_Germany')  : @setlocale(LC_TIME, 'de_DE.UTF8', 'de_DE@euro', 'de_DE'));

define('DATE_FORMATTER_LOCALE', 'de'); 
define('DATE_FORMATTER_DATE', 'EEEE, dd. MMMM YYYY'); // unused at this time, reserved
define('DATE_FORMATTER_DATETIME', 'EEEE, dd. MMMM YYYY, HH:mm:ss'); // this is reserved for tep_date_long()
define('DATE_FORMAT_LONG', '%A, %d. %B %Y');  // this is used with tep_date_long()
define('DATE_FORMAT_SHORT', 'd.m.Y');  // this is used with date()
define('DATE_FORMAT', 'd.m.Y');  // this is used with date()
define('DATE_TIME_FORMAT', DATE_FORMAT_SHORT . ' H:i:s'); // this is used with date()

// text for date of birth example
define('DOB_FORMAT_STRING', 'tt.mm.jjjj');

define('DATE_DAY_NAMES', 'Montag,Dienstag,Mittwoch,Donnerstag,Freitag,Samstag,Sonntag');
define('DATE_DAY_NAMES_SHORT', 'Mo,Di,Mi,Do,Fr,Sa,So');
define('DATE_MONTH_NAMES', 'Januar,Februar,März,April,Mai,Juni,Juli,August,September,Oktober,November,Dezember');
define('DATE_MONTH_NAMES_SHORT', 'Jan,Feb,Mar,Apr,Mai,Jun,Jul,Aug,Sep,Okt,Nov,Dez');

////
// Return date in raw format
// $date should be in format mm/dd/yyyy
// raw date is in format YYYYMMDD, or DDMMYYYY
function tep_date_raw($date, $reverse = false)
{
    if ($reverse) {
        return substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 4);
    } else {
        return substr($date, 6, 4) . substr($date, 3, 2) . substr($date, 0, 2);
    }
}

// page title
define('TITLE', STORE_NAME);

// Ingo
define('TEXT_SPECIAL_PRICE_OLD', 'früher');
define('TEXT_SPECIAL_PRICE_NOW', 'jetzt');

define('BOX_INFORMATION_ABOUT_US', 'Wir über uns');
define('BOX_HEADING_VIEWED_PRODUCTS', 'Zuletzt gesehen');

define('TABLE_HEADING_LATEST_NEWS', 'Neuigkeiten');

define('TEXT_DOWNLOAD_CONDITIONS_PDF', 'Klicken Sie hier, um die Geschäftsbedingungen als PDF herunter zu laden. Zum Lesen benötigen Sie den Acrobat Reader.');
define('TEXT_DOWNLOAD_ACROBAT', 'Klicken Sie auf das rechte Symbol um den Acrobat Reader in der neuesten Version kostenlos herunter zu laden.');

define('TEXT_READ_MORE', 'mehr lesen');
define('YOU_ARE_HERE','Sie sind hier:&nbsp;');

define('SIMPLE_WORD_TAX', 'Umsatzsteuer');
define('SIMPLE_WORD_SHIPPING', 'Versandkosten, hier klicken');
define('SIMPLE_WORD_INCL', 'inkl.');
define('SIMPLE_WORD_EXCL', 'zzgl.');

define('DOWNLOAD_NOT_FREE', 'nicht freigegeben');

define('BOX_WIDERRUF','Kauf auf Probe?');
define('BOX_INFORMATION_IMPRESSUM', 'Impressum');
define('ERROR_CONDITIONS_NOT_ACCEPTED', 'Sofern Sie unsere AGB nicht akzeptieren, können wir Ihre Bestellung bedauerlicherweise nicht entgegen nehmen!');
define('DISCLAIMER', '<font size="+1" color="ff0000"><div align="center"><b>Achtung!</b><br />Wir distanzieren uns hiermit ausdrücklich von den Inhalten aller durch Links verbundenen Seiten ausserhalb unserer Domain <br />"<b>' . HTTP_SERVER .  '</b>"!</div></font>');

// Wer ist online
define('BOX_HEADING_WHOS_ONLINE', 'Wer ist online?');
define('BOX_WHOS_ONLINE_THEREIS', 'Zur Zeit ist');
define('BOX_WHOS_ONLINE_THEREARE', 'Zur Zeit sind');
define('BOX_WHOS_ONLINE_GUEST', 'Gast');
define('BOX_WHOS_ONLINE_GUESTS', 'Gäste');
define('BOX_WHOS_ONLINE_AND', 'und');
define('BOX_WHOS_ONLINE_MEMBER', 'Mitglied');
define('BOX_WHOS_ONLINE_MEMBERS', 'Mitglieder');

// all products
define ('ALL_PRODUCTS_LINK', 'Alle Artikel');

// header text in includes/header.php
define('HEADER_TITLE_CREATE_ACCOUNT', 'Neues Konto');
define('HEADER_TITLE_MY_ACCOUNT', 'Ihr Konto');
define('HEADER_TITLE_CART_CONTENTS', 'Warenkorb');
define('HEADER_TITLE_CHECKOUT', 'Kasse');
define('HEADER_TITLE_TOP', 'Startseite');
define('HEADER_TITLE_CATALOG', 'Onlineshop');
define('HEADER_TITLE_LOGOFF', 'Abmelden');
define('HEADER_TITLE_LOGIN', 'Anmelden');

// footer text in includes/footer.php
define('FOOTER_TEXT_REQUESTS_SINCE', 'Zugriffe seit');

// text for gender
define('MALE', 'Herr');
define('FEMALE', 'Frau');
define('MALE_ADDRESS', 'Herr');
define('FEMALE_ADDRESS', 'Frau');

// categories box text in includes/boxes/categories.php
define('BOX_HEADING_CATEGORIES', 'Kategorien');

// manufacturers box text in includes/boxes/manufacturers.php
define('BOX_HEADING_MANUFACTURERS', 'Hersteller');

// whats_new box text in includes/boxes/whats_new.php
define('BOX_HEADING_WHATS_NEW', 'Neue Produkte');

// quick_find box text in includes/boxes/quick_find.php
define('BOX_HEADING_SEARCH', 'Schnellsuche');
define('BOX_SEARCH_TEXT', 'Verwenden Sie Stichworte, um ein Produkt zu finden.');
define('BOX_SEARCH_ADVANCED_SEARCH', 'erweiterte Suche');

// specials box text in includes/boxes/specials.php
define('BOX_HEADING_SPECIALS', 'Angebote');

// reviews box text in includes/boxes/reviews.php
define('BOX_HEADING_REVIEWS', 'Bewertungen');
define('BOX_REVIEWS_WRITE_REVIEW', 'Bewerten Sie');
define('BOX_REVIEWS_NO_REVIEWS', 'Es liegen noch keine Bewertungen vor');
define('BOX_REVIEWS_TEXT_OF_5_STARS', '%s von 5 Sternen!');

// shopping_cart box text in includes/boxes/shopping_cart.php
define('BOX_HEADING_SHOPPING_CART', 'Warenkorb');
define('BOX_SHOPPING_CART_EMPTY', 'ist leer!');

// order_history box text in includes/boxes/order_history.php
define('BOX_HEADING_CUSTOMER_ORDERS', 'Bestellübersicht');

// best_sellers box text in includes/boxes/best_sellers.php
define('BOX_HEADING_BESTSELLERS', 'Bestseller');
define('BOX_HEADING_BESTSELLERS_IN', 'Bestseller<br />&nbsp;&nbsp;');

// notifications box text in includes/boxes/products_notifications.php
define('BOX_HEADING_NOTIFICATIONS', 'Aktuelles');
define('BOX_NOTIFICATIONS_NOTIFY', 'Benach- richtigen Sie mich über Aktuelles zu <b>%s</b>');
define('BOX_NOTIFICATIONS_NOTIFY_REMOVE', 'Benach- richtigen Sie mich nicht mehr zu <b>%s</b>');

// manufacturer box text
define('BOX_HEADING_MANUFACTURER_INFO', 'Hersteller Info');
define('BOX_MANUFACTURER_INFO_HOMEPAGE', '%s Homepage');
define('BOX_MANUFACTURER_INFO_OTHER_PRODUCTS', 'Mehr Produkte');

// languages box test in includes/boxes/languages.php
define('BOX_HEADING_LANGUAGES', 'Sprachen');

// currencies box text in includes/boxes/currencies.php
define('BOX_HEADING_CURRENCIES', 'Währungen');

// information box text in includes/boxes/information.php
define('BOX_HEADING_INFORMATION', 'Informationen');
define('BOX_INFORMATION_PRIVACY', 'Privatsphäre<br />&nbsp;und Datenschutz');
define('BOX_INFORMATION_CONDITIONS', 'Unsere&nbsp;AGB');
define('BOX_INFORMATION_SHIPPING', 'Liefer- und<br />&nbsp;Versandkosten');
define('BOX_INFORMATION_CONTACT', 'Kontakt');

// tell a friend box text in includes/boxes/tell_a_friend.php
define('BOX_HEADING_TELL_A_FRIEND', 'Empfehlungen');
define('BOX_TELL_A_FRIEND_TEXT', 'Empfehlen Sie <br /><b>%s</b><br /> einfach per E-Mail weiter.');

// checkout procedure text
define('CHECKOUT_BAR_DELIVERY', 'Versandinformationen');
define('CHECKOUT_BAR_PAYMENT', 'Zahlungsweise');
define('CHECKOUT_BAR_CONFIRMATION', 'Bestätigung');
define('CHECKOUT_BAR_FINISHED', 'Fertig!');

// pull down default text
define('PULL_DOWN_DEFAULT', 'Bitte wählen');
define('TYPE_BELOW', 'bitte unten eingeben');

// javascript messages
define('JS_ERROR', 'Notwendige Angaben fehlen!\nBitte richtig ausfüllen.\n\n');

define('JS_REVIEW_TEXT', '* Der Text muss mindestens aus ' . REVIEW_TEXT_MIN_LENGTH . ' Buchstaben bestehen.\n');
define('JS_REVIEW_RATING', '* Geben Sie Ihre Bewertung ein.\n');

define('JS_ERROR_NO_PAYMENT_MODULE_SELECTED', '* Bitte wählen Sie eine Zahlungsweise für Ihre Bestellung.\n');

define('JS_ERROR_SUBMITTED', 'Diese Seite wurde bereits bestätigt. Betätigen Sie bitte OK und warten bis der Prozess durchgeführt wurde.');

define('ERROR_NO_PAYMENT_MODULE_SELECTED', 'Bitte wählen Sie eine Zahlungsweise für Ihre Bestellung.');

define('CATEGORY_COMPANY', 'Firmendaten');
define('CATEGORY_PERSONAL', 'Ihre persönlichen Daten');
define('CATEGORY_ADDRESS', 'Ihre Adresse');
define('CATEGORY_CONTACT', 'Ihre Kontaktinformationen');
define('CATEGORY_OPTIONS', 'Optionen');
define('CATEGORY_PASSWORD', 'Ihr Passwort');

define('ENTRY_COMPANY', 'Firmenname:');
define('ENTRY_COMPANY_ERROR', '');
define('ENTRY_COMPANY_TEXT', '');
define('ENTRY_GENDER', 'Anrede:');
define('ENTRY_GENDER_ERROR', 'Bitte das Geschlecht angeben.');
define('ENTRY_GENDER_TEXT', '*');
define('ENTRY_FIRST_NAME', 'Vorname:');
define('ENTRY_FIRST_NAME_ERROR', 'Der Vorname sollte mindestens ' . ENTRY_FIRST_NAME_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_FIRST_NAME_TEXT', '*');
define('ENTRY_LAST_NAME', 'Nachname:');
define('ENTRY_LAST_NAME_ERROR', 'Der Nachname sollte mindestens ' . ENTRY_LAST_NAME_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_LAST_NAME_TEXT', '*');
define('ENTRY_DATE_OF_BIRTH', 'Geburtsdatum:');
define('ENTRY_DATE_OF_BIRTH_ERROR', 'Bitte geben Sie Ihr Geburtsdatum in folgendem Format ein: TT.MM.JJJJ (z.B. 21.05.1970)');
define('ENTRY_DATE_OF_BIRTH_TEXT', '* (z.B. 21.05.1970)');
define('ENTRY_EMAIL_ADDRESS', 'E-Mail-Adresse:');
define('ENTRY_EMAIL_ADDRESS_ERROR', 'Die E-Mail Adresse sollte mindestens ' . ENTRY_EMAIL_ADDRESS_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_EMAIL_ADDRESS_CHECK_ERROR', 'Die E-Mail Adresse scheint nicht gültig zu sein - bitte korrigieren.');
define('ENTRY_EMAIL_ADDRESS_ERROR_EXISTS', 'Die E-Mail Adresse ist bereits gespeichert - bitte melden Sie sich mit dieser Adresse an oder eröffnen Sie ein neues Konto mit einer anderen Adresse.');
define('ENTRY_EMAIL_ADDRESS_TEXT', '*');
define('ENTRY_STREET_ADDRESS', 'Strasse/Nr.:');
define('ENTRY_STREET_ADDRESS_ERROR', 'Die Strassenadresse sollte mindestens ' . ENTRY_STREET_ADDRESS_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_STREET_ADDRESS_TEXT', '*');
define('ENTRY_SUBURB', 'Stadtteil:');
define('ENTRY_SUBURB_ERROR', '');
define('ENTRY_SUBURB_TEXT', '');
define('ENTRY_POST_CODE', 'Postleitzahl:');
define('ENTRY_POST_CODE_ERROR', 'Die Postleitzahl sollte mindestens ' . ENTRY_POSTCODE_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_POST_CODE_TEXT', '*');
define('ENTRY_CITY', 'Ort:');
define('ENTRY_CITY_ERROR', 'Die Stadt sollte mindestens ' . ENTRY_CITY_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_CITY_TEXT', '*');
define('ENTRY_STATE', 'Bundesland:');
define('ENTRY_STATE_ERROR', 'Das Bundesland sollte mindestens ' . ENTRY_STATE_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_STATE_ERROR_SELECT', 'Bitte wählen Sie ein Bundesland aus der Liste.');
define('ENTRY_STATE_TEXT', '*');
define('ENTRY_COUNTRY', 'Land:');
define('ENTRY_COUNTRY_ERROR', 'Bitte wählen Sie ein Land aus der Liste.');
define('ENTRY_COUNTRY_TEXT', '*');
define('ENTRY_TELEPHONE_NUMBER', 'Telefonnummer:');
define('ENTRY_TELEPHONE_NUMBER_ERROR', 'Die Telefonnummer sollte mindestens ' . ENTRY_TELEPHONE_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_TELEPHONE_NUMBER_TEXT', '*');
define('ENTRY_FAX_NUMBER', 'Telefaxnummer:');
define('ENTRY_FAX_NUMBER_ERROR', '');
define('ENTRY_FAX_NUMBER_TEXT', '');
define('ENTRY_NEWSLETTER', 'Newsletter:');
define('ENTRY_NEWSLETTER_TEXT', '');
define('ENTRY_NEWSLETTER_YES', 'abonniert');
define('ENTRY_NEWSLETTER_NO', 'nicht abonniert');
define('ENTRY_NEWSLETTER_ERROR', '');
define('ENTRY_PASSWORD', 'Passwort:');
define('ENTRY_PASSWORD_ERROR', 'Das Passwort sollte mindestens ' . ENTRY_PASSWORD_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_PASSWORD_ERROR_NOT_MATCHING', 'Beide eingegebenen Passwörter müssen identisch sein.');
define('ENTRY_PASSWORD_TEXT', '*');
define('ENTRY_PASSWORD_CONFIRMATION', 'Bestätigung:');
define('ENTRY_PASSWORD_CONFIRMATION_TEXT', '*');
define('ENTRY_PASSWORD_CURRENT', 'Jetziges Passwort:');
define('ENTRY_PASSWORD_CURRENT_TEXT', '*');
define('ENTRY_PASSWORD_CURRENT_ERROR', 'Das Passwort sollte mindestens ' . ENTRY_PASSWORD_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_PASSWORD_NEW', 'Neues Passwort:');
define('ENTRY_PASSWORD_NEW_TEXT', '*');
define('ENTRY_PASSWORD_NEW_ERROR', 'Das neue Passwort sollte mindestens ' . ENTRY_PASSWORD_MIN_LENGTH . ' Zeichen enthalten.');
define('ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING', 'Die Passwort-Bestätigung muss mit Ihrem neuen Passwort übereinstimmen.');
define('PASSWORD_HIDDEN', '--VERSTECKT--');

define('FORM_REQUIRED_INFORMATION', '* Notwendige Eingabe');

// constants for use in tep_prev_next_display function
define('TEXT_RESULT_PAGE', 'Seiten:');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS', 'angezeigte Produkte: <b>%d</b> bis <b>%d</b> (von <b>%d</b> insgesamt)');
define('TEXT_DISPLAY_NUMBER_OF_ORDERS', 'angezeigte Bestellungen: <b>%d</b> bis <b>%d</b> (von <b>%d</b> insgesamt)');
define('TEXT_DISPLAY_NUMBER_OF_REVIEWS', 'angezeigte Meinungen: <b>%d</b> bis <b>%d</b> (von <b>%d</b> insgesamt)');
define('TEXT_DISPLAY_NUMBER_OF_PRODUCTS_NEW', 'angezeigte neue Produkte: <b>%d</b> bis <b>%d</b> (von <b>%d</b> insgesamt)');
define('TEXT_DISPLAY_NUMBER_OF_SPECIALS', 'angezeigte Angebote <b>%d</b> bis <b>%d</b> (von <b>%d</b> insgesamt)');

define('PREVNEXT_TITLE_FIRST_PAGE', 'erste Seite');
define('PREVNEXT_TITLE_PREVIOUS_PAGE', 'vorherige Seite');
define('PREVNEXT_TITLE_NEXT_PAGE', 'nächste Seite');
define('PREVNEXT_TITLE_LAST_PAGE', 'letzte Seite');
define('PREVNEXT_TITLE_PAGE_NO', 'Seite %d');
define('PREVNEXT_TITLE_PREV_SET_OF_NO_PAGE', 'Vorhergehende %d Seiten');
define('PREVNEXT_TITLE_NEXT_SET_OF_NO_PAGE', 'Nächste %d Seiten');
define('PREVNEXT_BUTTON_FIRST', '&lt;&lt;ERSTE');
define('PREVNEXT_BUTTON_PREV', '[&laquo;]');
define('PREVNEXT_BUTTON_NEXT', '[&raquo;]');
define('PREVNEXT_BUTTON_LAST', 'LETZTE&gt;&gt;');

define('IMAGE_BUTTON_ADD_ADDRESS', 'Neue Adresse');
define('IMAGE_BUTTON_ADDRESS_BOOK', 'Adressbuch');
define('IMAGE_BUTTON_BACK', 'Zurück');
define('IMAGE_BUTTON_BUY_NOW', 'Kaufen');
define('IMAGE_BUTTON_CHANGE_ADDRESS', 'Adresse ändern');
define('IMAGE_BUTTON_CHECKOUT', 'Kasse');
define('IMAGE_BUTTON_CONFIRM_ORDER', 'Bestellung bestätigen');
define('IMAGE_BUTTON_CONTINUE', 'Weiter');
define('IMAGE_BUTTON_CONTINUE_SHOPPING', 'Einkauf fortsetzen');
define('IMAGE_BUTTON_DELETE', 'Löschen');
define('IMAGE_BUTTON_EDIT_ACCOUNT', 'Daten ändern');
define('IMAGE_BUTTON_HISTORY', 'Bestellübersicht');
define('IMAGE_BUTTON_LOGIN', 'Anmelden');
define('IMAGE_BUTTON_IN_CART', 'In den Warenkorb');
define('IMAGE_BUTTON_NOTIFICATIONS', 'Benachrichtigungen');
define('IMAGE_BUTTON_QUICK_FIND', 'Schnellsuche');
define('IMAGE_BUTTON_REMOVE_NOTIFICATIONS', 'Benachrichtigungen löschen');
define('IMAGE_BUTTON_REVIEWS', 'Bewertungen');
define('IMAGE_BUTTON_SEARCH', 'Suchen');
define('IMAGE_BUTTON_SHIPPING_OPTIONS', 'Versandoptionen');
define('IMAGE_BUTTON_TELL_A_FRIEND', 'Weiterempfehlen');
define('IMAGE_BUTTON_UPDATE', 'Aktualisieren');
define('IMAGE_BUTTON_UPDATE_CART', 'Warenkorb aktualisieren');
define('IMAGE_BUTTON_WRITE_REVIEW', 'Bewertung schreiben');

define('SMALL_IMAGE_BUTTON_DELETE', 'Löschen');
define('SMALL_IMAGE_BUTTON_EDIT', 'Ändern');
define('SMALL_IMAGE_BUTTON_VIEW', 'Ansehen');

define('ICON_ARROW_RIGHT', 'Zeige mehr');
define('ICON_CART', 'In den Warenkorb');
define('ICON_ERROR', 'Fehler');
define('ICON_SUCCESS', 'Erfolg');
define('ICON_WARNING', 'Warnung');

define('TEXT_GREETING_PERSONAL', 'Schön das Sie wieder da sind <span class="greetUser">%s!</span> Möchten Sie die <a href="%s"><span class="underlined">neue Produkte</span></a> ansehen?');
define('TEXT_GREETING_PERSONAL_RELOGON', '<small>Wenn Sie nicht %s sind, melden Sie sich bitte <a href="%s"><span class="underlined">hier</span></a> mit Ihrem Kundenkonto an.</small>');
define('TEXT_GREETING_GUEST', 'Möchten Sie sich <a href="%s"><span class="underlined">anmelden</span></a> oder ein <a href="%s"><span class="underlined">Kundenkonto</span></a> eröffnen?');

define('TEXT_SORT_PRODUCTS', 'Sortierung der Artikel ist ');
define('TEXT_DESCENDINGLY', 'absteigend');
define('TEXT_ASCENDINGLY', 'aufsteigend');
define('TEXT_BY', ' nach ');

define('TEXT_REVIEW_BY', 'von %s');
define('TEXT_REVIEW_WORD_COUNT', '%s Worte');
define('TEXT_REVIEW_RATING', 'Bewertung: %s [%s]');
define('TEXT_REVIEW_DATE_ADDED', 'Datum hinzugefügt: %s');
define('TEXT_NO_REVIEWS', 'Es liegen noch keine Bewertungen vor.');

define('TEXT_NO_NEW_PRODUCTS', 'Zur Zeit gibt es keine neuen Produkte.');

define('TEXT_UNKNOWN_TAX_RATE', 'Unbekannter Steuersatz');

define('TEXT_REQUIRED', '<span class="errorText">erforderlich</span>');

define('ERROR_TEP_MAIL', '<font face="Verdana, Arial" size="2" color="#ff0000"><b><small>Fehler:</small> Die E-Mail kann nicht über den angegebenen SMTP-Server verschickt werden. Bitte kontrollieren Sie die Einstellungen in der php.ini Datei und führen Sie notwendige Korrekturen durch!</b></font>');
define('WARNING_SESSION_DIRECTORY_NON_EXISTENT', 'Warnung: Das Verzeichnis für die Sessions existiert nicht: ' . tep_session_save_path() . '. Die Sessions werden nicht funktionieren bis das Verzeichnis erstellt wurde!');
define('WARNING_SESSION_DIRECTORY_NOT_WRITEABLE', 'Warnung: osC kann nicht in das Sessions Verzeichnis schreiben: ' . tep_session_save_path() . '. Die Sessions werden nicht funktionieren bis die richtigen Benutzerberechtigungen gesetzt wurden!');
define('WARNING_SESSION_AUTO_START', 'Warnung: session.auto_start ist enabled - Bitte disablen Sie dieses PHP Feature in der php.ini und starten Sie den WEB-Server neu!');
define('WARNING_DOWNLOAD_DIRECTORY_NON_EXISTENT', 'Warnung: Das Verzeichnis für den Artikel Download existiert nicht: ' . DIR_FS_DOWNLOAD . '. Diese Funktion wird nicht funktionieren bis das Verzeichnis erstellt wurde!');

define('TEXT_CCVAL_ERROR_INVALID_DATE', 'Das "Gültig bis" Datum ist ungültig.<br />Bitte korrigieren Sie Ihre Angaben.');
define('TEXT_CCVAL_ERROR_INVALID_NUMBER', 'Die "KreditkarteNummer", die Sie angegeben haben, ist ungültig.<br />Bitte korrigieren Sie Ihre Angaben.');
define('TEXT_CCVAL_ERROR_UNKNOWN_CARD', 'Die ersten 4 Ziffern Ihrer Kreditkarte sind: %s<br />Wenn diese Angaben stimmen, wird dieser Kartentyp leider nicht akzeptiert.<br />Bitte korrigieren Sie Ihre Angaben gegebenfalls.');

/*
  The following copyright announcement can only be
  appropriately modified or removed if the layout of
  the site theme has been modified to distinguish
  itself from the default osCommerce-copyrighted
  theme.

  For more information please read the following
  Frequently Asked Questions entry on the osCommerce
  support site:

  http://www.oscommerce.com/community.php/faq,26/q,50

  Please leave this comment intact together with the
  following copyright announcement.
*/
define('FOOTER_TEXT_BODY', '
Alle Preise inklusive Umsatzsteuer und zzgl.
<a href="' . tep_href_link(FILENAME_SHIPPING) . '" class="bodyfooter">Versandkosten</a>,
<a href="' . tep_href_link(FILENAME_ALL_PRODUCTS) . '" class="bodyfooter">alle Angebote</a> freibleibend.<br />
Verkauf unter Zugrundelegung unserer <a href="' . tep_href_link(FILENAME_CONDITIONS) . '" class="bodyfooter">Allgemeinen Geschäftsbedingungen</a><br />
<a href="http://www.strelitzer.de" target="_blank" title=" ' . CUSTOMIZED_VERSION . ' by Ingo, based Milestone2 " class="bodyfooter">&copy;</a> ' . date("Y") . ' ' . STORE_OWNER . ',
powered by <a href="http://www.oscommerce.com" target="_blank" class="bodyfooter">osCommerce</a> ms2
');
