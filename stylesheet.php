<?php
/*
  $Id: stylesheet.css,v 1.56 2003/06/30 20:04:02 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

  // grau: grau(wert) ; wert 0 (dunkel)...100 (hell) Beispiel: grau(90)

/*******************************/

  // Änderungen:
  // - hexadezimale Werte für Farben in Anführungsstriche zB '000000' für schwarz, 'ff0000' für rot etc
  //   gefolgt von einem Semikolon
  // - Grauwerte: wie Farben oder das Wort 'grau' und den Prozentwert(0 für dunkel ... 100) in Klammern,
  //   gefolgt von einem Semikolon
  // - Adressen von Bildern: Pfad von der Lokalität dieser Datei 'stylesheet.php'
  // Für Farbwerte schwarz könn

$aussenbereich     = grau(80);                      // Außenfarbe des Shops
$aussenbereich_grafik = 'images/desk/body_bg.gif';  // Außenbereich Hintergrundgrafik
$header_grafik     = 'images/desk/header_bg.jpg';   // Hintergrundgrafik des Shopkopfes
$shop_rahmen_farbe = grau(60);                      // Farbe des Shoprahmens
$schriftfarbe = '000000';
$column_left_width        = '140px;';               // Breite der linken Spalte
$column_left_background   = grau(98);               // Hintergrund linke Spalte Infoboxen
$column_center_background = 'ffffff';               // Hintergrund Hauptbereich
$column_right_background  = grau(98);               // Hintergrund linke Spalte Infoboxen
$footer_background        = grau(98);               // Hintergrund unterer Bereich

$obere_zeile  = '468F36';                           // Hintergrund "Sie sind hier:" ...
$obere_zeile_link  = 'ffffff';                      // Textfarbe der Links
$obere_zeile_link_hover   = 'ffffff';               // Textfarbe der Links bei mouseover
$obere_zeile_link_hintergrund = '005f00';           // Hintergrundfarbe der Links bei mouseover
$untere_zeile = '00bf00';                           // Hintergrundfarbe der Zeile mit Datum und Zähler

$anker_body_passiv = grau(30);                      // Textfarbe der Links
$anker_body_hover  = grau(10);                      // Textfarbe der Links bei mouseover

$dunkle_shop_farbe        = '00bf00';               // Farbe für einige einzufärbende Teile

$info_box_ueberschrift  = '00bf00';                 // Hintergrundfarbe der Infoboxüberschriften
$info_box_ueberschrift_text      = '000000';        // Textfarbe der Infoboxüberschriften
$info_box_ueberschrift_text_link = grau(30);        // Textfarbe verlinkter Infoboxüberschriften
$info_box_ueberschrift_text_link_hover = '000000';  // Textfarbe bei mouseover
$infoBox_content_background = 'ffffff';             // Hintergrundfarben der Infoboxen
$infoBox_content_border     = '00bf00';             // Farbe der Boxumrandung

$infoBox_confirm_background = 'ffddcc';             // Hintergrundfarbe der Bestatigung an der Kasse

$listing_background_odd     = 'e7fff0';             // Hintergrundfarbe in Auflistungen
$listing_background_even    = 'f0ffe7';             // Wechselfarbe in Auflistungen
$modul_background_maus_over = 'e7ffe7';             // Hintergrundfarbe bei Modulauswahl an Kasse
$modul_background_aktiv     = 'd0ffd0';             // Hintergrundfarbe gewähltes Modul an Kasse

/* Ab hier bitte nichts mehr ändern. Es sei denn, Sie wissen, was Sie tun.  */

  // Shop in der Breite von $header_grafik beschränken
  // Header in der Höhe von $header_grafik beschränken
if ($header_grafik_daten = @getimagesize($header_grafik)) {
    $header_grafik_breite = 'width: ' . $header_grafik_daten[0] . "px;";
    $header_grafik_hoehe  = 'height: ' . $header_grafik_daten[1] . "px;";
    $header_hintergrund   = "background-image: url($header_grafik);";
} else {
    $header_grafik_breite = "width: 95%;";
    $header_grafik_hoehe  = "";
    $header_hintergrund   = 'background: #fffff;';
}


$stylesheet = "/*
  stylesheet.php, mode osc45 by Ingo http://forums.oscommerce.de/index.php?showuser=36
  Copyright (c) 2021 xPrioS
  Copyright (c) 2005 osCommerce
  Released under the GNU General Public License
*/
.boxText { font-family: Verdana, Arial, sans-serif; font-size: 10px; }
.stockWarning { font-family : Verdana, Arial, sans-serif; font-size : 10px; color: #cc0033; }
.productsNotifications { background: #$infoBox_content_background; }
.orderEdit { font-family : Verdana, Arial, sans-serif; font-size : 10px; color: #082; text-decoration: underline; }
.marksearchresults {background: #ff0; border:1px solid #999;}

body {
  background: #$aussenbereich;
  background-image: url($aussenbereich_grafik);
  background-repeat: repeat;
  color: #$schriftfarbe; margin: 0px; padding-top: 20px; padding-bottom: 20px;
  text-align: center;
  font: 11px Verdana, Arial, sans-serif;
/*
  scrollbar-face-color: #$dunkle_shop_farbe;
  scrollbar-3dlight-color: #00efef;
  scrollbar-highlight-color: #00cfcf;
  scrollbar-shadow-color: #009f9f;
  scrollbar-darkshadow-color: #007f7f;
  scrollbar-arrow-color: #000000;
  scrollbar-track-color: #ffffbf;
*/
}

img {border: 0;}

.pageicon {
  float: right;
  margin-right: 5px;
}

div.divbox {
  margin: 2px;
  border: 1px solid #$infoBox_content_border;
  background: #fff;
}
div.divboxheading {
  text-align:center;
  font-size: 12px;
  font-weight: bold;
  line-height:1.4;
  background: #$infoBox_content_border;
  color: #$info_box_ueberschrift_text;
}
div.divibox {
  margin:5px;
  border:0px solid #000;
  background:#fff;
}
div.diviboxheading {
  margin:0;
  font-size: 11px;
  line-height: 1.4;
  font-weight: bold;
  text-align: center;
  background: #$dunkle_shop_farbe;
  color:#$info_box_ueberschrift_text;
}
div.diviboxheading a { 
  display:block;
  text-decoration:none
}
div.diviboxheading a:hover {
  margin:0;
  background: #ddd;
}
div.diviboxcontent {
  margin:0px;
  padding:2px 0;
  background:transparent; 
  color:#0bb
}
div.listbox a {
  display: block;
  margin: 0px;
  padding: 0 3px;
  text-decoration: none;
}
div.listbox a:hover {
  color: #000;
  background-color: #ddd;
  text-decoration: none;
}



div#window {
  background: #fff; color: #$schriftfarbe;
  $header_grafik_breite
  padding:0px;
  border: 1px solid #$shop_rahmen_farbe;
  margin: auto auto;
  text-align: left;
}

div.pinfoimage {
  border: 1px solid #$infoBox_content_border;
  float:right;
  text-align:center;
  padding:5px;
  margin: 5px;
}
div.pinfoimage p {margin:0;}

div.productsdescription {
  padding: 5px;
  font: 11px/1.5 Verdana, Arial, sans-serif;
}
span.striked {text-decoration: line-through;}
.underlined {text-decoration: underline;}
.warning {color:#f00}
span.listingprice {font-size: 30px; font-weight:bold; font-style:italic; letter-spacing:-2px;}
span.infoboxprice {font-size: 20px; font-weight:bold; font-style:italic; letter-spacing:-1px;}
span.spacelefteuro {padding-left:2px;}
p.priceadded {margin:1px; padding:1px; font-size: 9px; font-weight: normal; line-height: 9px;}
a.priceadded {text-decoration:none;}
a.priceadded:hover {text-decoration:none;}

table.header {
  $header_hintergrund
  /* background-repeat: repeat-x; */
}
tr.header, td.header {
  $header_grafik_hoehe
}
td.columnleft {
  width: #$column_left_width;
  background: #$column_left_background;
  background-image: url(images/desk/column_left_bg.gif);
}
td.columncenter {
  padding: 5px;
  background: #$column_center_background;
}
td.columnright {
  background: #$column_right_background;
  background-image: url(images/desk/column_right_bg.gif);
}
table.footer {
  background: #$footer_background;
  background-image: url(images/desk/footer_bg.gif);
}

.hsmall {font-size: 10px; font-weight: bold; margin-bottom:0px; margin-top:0px; display:inline;}
.hbig {font-size: 20px; font-weight: bold; margin-bottom:0px; margin-top:0px; display:inline;}
.headingtitle {font: bold 20px/1 Verdana, Arial, sans-serif; margin:0px 5px; display:inline;}
.listingname {font-weight: bold; font-size: 12px; display:inline;}

a {
  color: #$anker_body_passiv;
  text-decoration: none;
}

a:hover {
  color: #$anker_body_hover;
  text-decoration: none;
}

form {
  display: inline;
}

.headerMenuText {
  font-family: Verdana, Arial, sans-serif;
  font-size: 10px;
  color: #ffffff;
  text-decoration: none;
}

tr.headerNavigation {
  background: #$obere_zeile;
}

td.headerNavigation {
  padding-left: 10px;
  padding-right: 3px;
  font: bold 12px/1 Verdana, Arial, sans-serif;
  background: #$obere_zeile;
  color: #000000;
}
td.breadcrump {
  padding-left: 3px;
  padding-right: 10px;
  font: 9px/1.0 Verdana, Arial, sans-serif;
  background: #$obere_zeile;
  color: #000000;
  text-decoration: none;
}

a.headerNavigation {
  color: #$obere_zeile_link;
}

a.headerNavigation:hover {
  color: #$obere_zeile_link_hover;
  background: #$obere_zeile_link_hintergrund;
  text-decoration: none;
}

tr.headerError {
  background: #ff0000;
}

td.headerError {
  font: bold 12px/1.2 Tahoma, Verdana, Arial, sans-serif;
  background: #ff0000;
  color: #ffffff;
  text-align : center;
}

tr.headerInfo {
  background: #00ff00;
}

td.headerInfo {
  font: bold 12px/1.2 Tahoma, Verdana, Arial, sans-serif;
  background: #00ff00;
  color: #ffffff;
  text-align: center;
}

tr.footer {
  background: #$untere_zeile;
}

td.footer {
  font: bold 10px/1.0 Verdana, Arial, sans-serif;
  background: #$untere_zeile;
  color: #ffffff;
}

td.bodyfooter {
  font: 9px/1.2 Verdana, Arial, sans-serif;
  color: #000000;
  text-align: center;
  padding: 2px;
}
a.bodyfooter { color: #1f1f1f; }
a.bodyfooter:hover { color: #3f3f3f; }

.infoBox {
  background: #$infoBox_content_border;
}
tr.infoBoxContents {
  background: #$infoBox_content_background;
}
table.infoBoxContents {
  background: #$infoBox_content_background;
  font: 10px/1.2 Verdana, Arial, sans-serif;
}
tr.infoBoxContentsConfirm {
  background-color: #$infoBox_confirm_background; 
}

table.categoriesbox {
  font: 10px/1.0 Verdana, Arial, sans-serif;
  background-color: #ffffff;
  background-image: url(images/infobox/categories.jpg);
  background-repeat: no-repeat;
  background-position: top;
  border-left: 1px solid #$infoBox_content_border;
  border-right: 1px solid #$infoBox_content_border;
  border-bottom: 1px solid #$infoBox_content_border;
}

.infoBoxNotice {
  background: #ff2020;
}

.infoBoxNoticeContents {
  background: #ff2020;
  font: 10px/1.2 Verdana, Arial, sans-serif;
}


a.infoboxcontentlink {
  display: block;
  padding: 0 4px;
  margin: 0px;
  text-align:left;
  text-decoration: none;
}
a.infoboxcontentlink:hover {
  color: #000;
  background-color: #ddd;
  text-decoration: none;
}
a.passiv {font-weight: normal;}
a.activ {color:#000; font-weight: bold;}

td.infoBoxHeading {
  font: bold 10px/1.0 Verdana, Arial, sans-serif;
  background: #$info_box_ueberschrift;
  color: #$info_box_ueberschrift_text;
  height: 16px;
  padding-left: 5px;
  padding-right: 5px;
  text-align: center;
  vertical-align: middle;
}
a.infoBoxHeading {
  color: #$info_box_ueberschrift_text_link;
  text-decoration: none;
}
a.infoBoxHeading:hover {
  color: #$info_box_ueberschrift_text_link_hover;
  text-decoration: none;
}
td.infoBox, span.infoBox {
  font: 10px/1.2 Verdana, Arial, sans-serif;
}

tr.accountHistory-odd, TR.addressBook-odd, TR.alsoPurchased-odd, TR.payment-odd, TR.productListing-odd, TR.productReviews-odd, TR.upcomingProducts-odd, TR.shippingOptions-odd {
  background: #$listing_background_odd;
}

tr.accountHistory-even, TR.addressBook-even, TR.alsoPurchased-even, TR.payment-even, TR.productListing-even, TR.productReviews-even, TR.upcomingProducts-even, TR.shippingOptions-even {
  background: #$listing_background_even;
}

table.productListing {
  border: 1px;
  border-style: solid;
  border-color: #$info_box_ueberschrift;
  border-spacing: 0px;
}

.productListing-heading {
  font: bold 10px/1.0 Verdana, Arial, sans-serif;
  background: #$info_box_ueberschrift;
  color: #$schriftfarbe;
}
a.productListing-heading {
  color: #$info_box_ueberschrift_text_link;
  text-decoration: none;
}
a.productListing-heading:hover {
  color: #$info_box_ueberschrift_text_link_hover;
  text-decoration: none;
}

td.productListing-data {
  font: 10px/1.2 Verdana, Arial, sans-serif;
}

a.pageResults {
  color: #0000FF;
}

a.pageResults:hover {
  color: #0000FF;
  background: #FFFF33;
}

td.pageHeading, div.pageHeading {
  font: bold 20px/1.0 Verdana, Arial, sans-serif;
  color: #$schriftfarbe;
}

tr.subBar {
  background: #f4f7fd;
}

td.subBar {
  font: 10px/1.0 Verdana, Arial, sans-serif;
  color: #$schriftfarbe;
}

td.main, p.main, div.main {
  font: 11px/1.5 Verdana, Arial, sans-serif;
}

.smallText {
  font: 10px Verdana, Arial, sans-serif;
}

td.accountCategory {
  font: 13px/1.0 Verdana, Arial, sans-serif;
  color: #aabbdd;
}

td.fieldKey {
  font: bold 12px/1.0 Verdana, Arial, sans-serif;
}

td.fieldValue {
  font: 12px/1.0 Verdana, Arial, sans-serif;
}

td.tableHeading {
  font: bold 12px/1.0 Verdana, Arial, sans-serif;
}

span.newItemInCart {
  color: #ff0000;
}

checkbox, input, radio, select {
  font-family: Verdana, Arial, sans-serif;
  font-size: 11px;
}

textarea {
  width: 99%;
  font-family: Verdana, Arial, sans-serif;
  font-size: 11px;
}

span.greetUser {
  font-family: Verdana, Arial, sans-serif;
  font-size: 12px;
  color: #ff0000;
  font-weight: bold;
}

table.formArea {
  background: #f0f8ef;
  border-color: #f0f8ef;
  border-style: solid;
  border-width: 1px;
}

td.formAreaTitle {
  font-family: Tahoma, Verdana, Arial, sans-serif;
  font-size: 12px;
  font-weight: bold;
}

span.markProductOutOfStock {
  font-family: Tahoma, Verdana, Arial, sans-serif;
  font-size: 12px;
  color: #ff0000;
  font-weight: bold;
}

span.productSpecialPrice {
  font-family: Verdana, Arial, sans-serif;
  color: #ff0000;
}

span.errorText {
  font-family: Verdana, Arial, sans-serif;
  color: #ff0000;
}

.moduleRow { }
.moduleRowOver { background-color: #$modul_background_maus_over; cursor: pointer;}
.moduleRowSelected { background-color: #$modul_background_aktiv; }

.checkoutBarFrom, .checkoutBarTo { font-family: Verdana, Arial, sans-serif; font-size: 10px; color: #$dunkle_shop_farbe; }
.checkoutBarCurrent { font-family: Verdana, Arial, sans-serif; font-size: 10px; color: #ff0000; }

div.checkoutbarnew {
  text-align:right;
  float:right;
  white-space:nowrap;
}

div.checkoutactiv, div.checkoutpassiv {
  float: left;
  width: 36px;
  height: 36px;
  margin: 6px;
  text-align: center;
  vertical-align: bottom;
  font-size: 28px;
  font-weight: bold;
}
div.checkoutactiv {border: 1px solid #$info_box_ueberschrift; background: #$modul_background_maus_over; color: #000;}
div.checkoutpassiv {border: 1px solid #$info_box_ueberschrift; background: #$modul_background_aktiv; color: #bbb;}
div.checkoutactiv a {color: #000;}
div.checkoutactiv a:hover {color: #555;}
div.checkoutactiv a, div.checkoutactiv a:hover {text-decoration:none;}

/* message box */

.messageBox { font-family: Verdana, Arial, sans-serif; font-size: 10px; }
.messageStackError, .messageStackWarning { font-family: Verdana, Arial, sans-serif; font-size: 10px; background-color: #ff0000; }
.messageStackSuccess { font-family: Verdana, Arial, sans-serif; font-size: 10px; background-color: #99ff00; }

/* input requirement */

.inputRequirement { font-family: Verdana, Arial, sans-serif; font-size: 10px; color: #ff0000; }
\n"; // end of stylesheet

function grau($grau='0') {
  if ($grau<0) $grau = 0;
  if ($grau>100) $grau = 100;
  $grau = dechex(255-round(255/100*(100-$grau)));
  return str_repeat($grau,3);
}
header("content-type: text/css");
echo $stylesheet;
