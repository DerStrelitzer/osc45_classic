<?php
/*******************************************************************************************
*                                                                                          *
*  CAO-Faktura für Windows Version 1.2 (http://www.cao-wawi.de)                            *
*  Copyright (C) 2004 Jan Pokrandt / Jan@JP-SOFT.de                                        *
*                                                                                          *
*  This program is free software; you can redistribute it and/or                           *
*  modify it under the terms of the GNU General Public License                             *
*  as published by the Free Software Foundation; either version 2                          *
*  of the License, or any later version.                                                   *
*                                                                                          *
*  This program is distributed in the hope that it will be useful,                         *
*  but WITHOUT ANY WARRANTY; without even the implied warranty of                          *
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                           *
*  GNU General Public License for more details.                                            *
*                                                                                          *
*  You should have received a copy of the GNU General Public License                       *
*  along with this program; if not, write to the Free Software                             *
*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
*                                                                                          *
*  ******* CAO-Faktura comes with ABSOLUTELY NO WARRANTY ***************                   *
*                                                                                          *
********************************************************************************************
*                                                                                          *
* Eine Entfernung oder Veraenderung dieses Dateiheaders ist nicht zulaessig !!!            *
* Wenn Sie diese Datei veraendern dann fuegen Sie ihre eigenen Copyrightmeldungen          *
* am Ende diese Headers an                                                                 *
*                                                                                          *
********************************************************************************************
*                                                                                          *
*  Programm     : CAO-Faktura                                                              *
*  Modul        : cao_update.php                                                           *
*  Stand        : 08.12.2004                                                               *
*  Version      : 1.36                                                                     *
*  Beschreibung : Script zum Datenaustausch CAO-Faktura <--> osCommerce-Shop               *
*                                                                                          *
*  based on:                                                                               *
* (c) 2000 - 2001 The Exchange Project                                                     *
* (c) 2001 - 2003 osCommerce, Open Source E-Commerce Solutions                             *
* (c) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers                                      *
* (c) 2003 JP-Soft, Jan Pokrandt                                                           *
* (c) 2003 IN-Solution, Henri Schmidhuber                                                  *
* (c) 2003 www.websl.de, Karl Langmann                                                     *
* (c) 2003 RV-Design Raphael Vullriede                                                     *
*                                                                                          *
* Released under the GNU General Public License                                            *
*                                                                                          *
*  History :                                                                               *
*                                                                                          *
*  - 25.06.2003 JP Version 0.1 released                                                    *
*  - 26.06.2003 HS beim Orderexport orderstatus und comment hinzugefuegt                   *
*  - 29.06.2003 JP order_update entfernt und in die Datei cao_update.php verschoben        *
*  - 20.07.2003 HS Shipping und Paymentklassen aufgenommen                                 *
*  - 02.08.2003 KL MANUFACTURERS_DESCRIPTION  language_id geändert in languages_id         *
*  - 09.08.2003 JP fuer das Modul Banktransfer werden jetzt die daten bei der Bestll-      *
*                  uebermittlung mit ausgegeben                                            *
*  - 10.08.2003 JP Geburtsdatum wird jetzt in den Bestellungen mit uebergeben              *
*  - 18.08.2003 JP Bug bei Products/URL beseitigt                                          *
*  - 18.08.2003 HS Bankdaten werden nur bei Banktransfer ausgelesen                        *
*  - 25.10.2003 RV Kunden-Export hinzugefügt                                               *
*  - 24.11.2003 HS Fix Kunden-Export - Newsletterexport hinzugefügt                        *
*  - 01.12.2003 RV Code für 3 Produktbilder-Erweiterung hinzugefügt.                       *
*  - 31.01.2004 JP Resourcenverbrauch minimiert                                            *
*                  tep_set_time_limit ist jetzt per DEFINE zu- und abschaltbar             *
*  - 06.06.2004 JP per DEFINE kann jetzt die Option "3 Produktbilder" geschaltet werden    *
*  - 09.10.2004 RV automatisch Erkennung von 3 Bilder Contrib laut readme                  *
*  - 09.10.2004 RV vereinheitlicher Adress-Export bei Bestellungen und Kunden              *
*  - 09.10.2004 RV Kunden Vor- und Nachname bei Bestellungen getrennt exportieren          *
*  - 09.10.2004 RV SQL-Cleanup                                                             *
*  - 09.10.2004 RV CODE-Cleanup                                                            *
*  - 14.10.2004 RV Länder bei Bestellungen als ISO-Code                                    *
*  - 03.12.2003 JP Bugfix beim Kunden-Export (Fehlende Felder)                             *
*******************************************************************************************/

// adapted (c) 2021 xPrioS

require('includes/application_top.php');


$order_total_class['ot_cod_fee']['prefix'] = '+';
$order_total_class['ot_cod_fee']['tax'] = '16';

$order_total_class['ot_customer_discount']['prefix'] = '-';
$order_total_class['ot_customer_discount']['tax'] = '16';

$order_total_class['ot_gv']['prefix'] = '-';
$order_total_class['ot_gv']['tax'] = '0';

$order_total_class['ot_loworderfee']['prefix'] = '+';
$order_total_class['ot_loworderfee']['tax'] = '16';

$order_total_class['ot_shipping']['prefix'] = '+';
$order_total_class['ot_shipping']['tax'] = '16';


/******************************************************************************************/
$version_nr    = '1.37';
$version_datum = '2004.12.08';
/******************************************************************************************/


  // define('SET_TIME_LIMIT', 1); // aktivieren um SetTimeLimit einzuschalten

  header ("Last-Modified: ". gmdate ("D, d M Y H:i:s"). " GMT");  // immer geändert
  header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header ("Pragma: no-cache"); // HTTP/1.0
  header ("Content-type: text/xml");


  $table_has_products_image_medium = false;
  $table_has_products_image_large = false;

  $images_query = tep_db_query(' SHOW COLUMNS FROM '.TABLE_PRODUCTS);
  while($column = tep_db_fetch_array($images_query)) {
        if ($column['Field'] == 'products_image_medium') {
          $table_has_products_image_medium = true;
        }
        if ($column['Field'] == 'products_image_large') {
          $table_has_products_image_large = true;
        }
  }
  if ($table_has_products_image_medium && $table_has_products_image_large) {
      define('DREI_PRODUKTBILDER', true);
  } else {
      define('DREI_PRODUKTBILDER', false);
  }


  if ($_GET['action']) {

    switch ($_GET['action']) {


// ----------------------------------------------------------------------------------------
      case 'categories_export':

        if (defined('SET_TIME_LIMIT')) { tep_set_time_limit(0); }

        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<CATEGORIES>' . "\n";

        echo $schema;

        $cat_query = "SELECT
              categories_id,
              categories_image,
              parent_id,
              sort_order,
              date_added,
              last_modified
            FROM
              " . TABLE_CATEGORIES . "
            ORDER BY
              parent_id,
              categories_id";

        $cat_result = tep_db_query($cat_query);

        while ($cat = tep_db_fetch_array($cat_result)) {

          $schema  = '<CATEGORIES_DATA>' . "\n" .
                   '<ID>' . $cat['categories_id'] . '</ID>' . "\n" .
                   '<PARENT_ID>' . $cat['parent_id'] . '</PARENT_ID>' . "\n" .
                   '<IMAGE_URL>' . htmlspecialchars($cat['categories_image'], ENT_QUOTES, CHARSET, false) . '</IMAGE_URL>' . "\n" .
                   '<SORT_ORDER>' . $cat['sort_order'] . '</SORT_ORDER>' . "\n" .
                   '<DATE_ADDED>' . $cat['date_added'] . '</DATE_ADDED>' . "\n" .
                   '<LAST_MODIFIED>' . $cat['last_modified'] . '</LAST_MODIFIED>' . "\n";

          $detail_query = "
              SELECT
                  cd.categories_id,
                  cd.language_id,
                  cd.categories_name,
                  l.code as lang_code,
                  l.name as lang_name
              FROM
                  " . TABLE_CATEGORIES_DESCRIPTION . " cd,
                  " . TABLE_LANGUAGES . " l
              WHERE
                  cd.categories_id=" . $cat['categories_id'] . " AND
                  l.languages_id= cd.language_id";

          $detail_result = tep_db_query($detail_query);

          while ($details = tep_db_fetch_array($detail_result)) {
            $schema .= "<CATEGORIES_DESCRIPTION ID='" . $details["language_id"] ."' CODE='" . $details["lang_code"] . "' NAME='" . $details["lang_name"] . "'>\n";
            $schema .= "<NAME>" . htmlspecialchars($details["categories_name"], ENT_QUOTES, CHARSET, false) . "</NAME>" . "\n";
            $schema .= "</CATEGORIES_DESCRIPTION>\n";
          }

          // Produkte in dieser Categorie auflisten
          $prod2cat_query =
            "SELECT
              categories_id,
              products_id
            FROM
              " . TABLE_PRODUCTS_TO_CATEGORIES . "
            WHERE
              categories_id='" . $cat['categories_id'] . "'";

          $prod2cat_result = tep_db_query($prod2cat_query);

          while ($prod2cat = tep_db_fetch_array($prod2cat_result)) {
            $schema .="<PRODUCTS ID='" . $prod2cat["products_id"] ."'></PRODUCTS>" . "\n";
          }

          $schema .= '</CATEGORIES_DATA>' . "\n";

          echo $schema;
        }

        $schema = '</CATEGORIES>' . "\n";

        echo $schema;
        exit;


// ----------------------------------------------------------------------------------------
      case 'manufacturers_export':

        if (defined('SET_TIME_LIMIT')) {
          tep_set_time_limit(0);
        }

        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<MANUFACTURERS>' . "\n";

        echo $schema;

        $man_query = "
          SELECT
            manufacturers_id,
            manufacturers_name,
            manufacturers_image,
            date_added,
            last_modified
          FROM
            " . TABLE_MANUFACTURERS . "
          ORDER BY
            manufacturers_id";

        $man_result = tep_db_query($man_query);
        while ($cat = tep_db_fetch_array($man_result)) {
          $schema  = '<MANUFACTURERS_DATA>' . "\n" .
                     '<ID>' . $cat['manufacturers_id'] . '</ID>' . "\n" .
                     '<NAME>' . htmlspecialchars($cat['manufacturers_name'], ENT_QUOTES, CHARSET, false) . '</NAME>' . "\n" .
                     '<IMAGE>' . htmlspecialchars($cat['manufacturers_image'], ENT_QUOTES, CHARSET, false) . '</IMAGE>' . "\n" .
                     '<DATE_ADDED>' . $cat['date_added'] . '</DATE_ADDED>' . "\n" .
                     '<LAST_MODIFIED>' . $cat['last_modified'] . '</LAST_MODIFIED>' . "\n";


          $man_info_query = "
            SELECT
              mi.manufacturers_id,
              mi.languages_id,
              mi.manufacturers_url,
              url_clicked,
              date_last_click,
              l.code as lang_code,
              l.name as lang_name
            FROM
              " . TABLE_MANUFACTURERS_INFO . " mi,
              " . TABLE_LANGUAGES . " l
            WHERE
              mi.manufacturers_id= " . $cat['manufacturers_id'] . " AND
              l.languages_id = mi.languages_id";

          $man_info_result = tep_db_query($man_info_query);

          while ($details = tep_db_fetch_array($man_info_result)) {
            $schema .= "<MANUFACTURERS_DESCRIPTION ID='" . $details["languages_id"] ."' CODE='" . $details["lang_code"] . "' NAME='" . $details["lang_name"] . "'>\n";
            $schema .= "<URL>" . htmlspecialchars($details["manufacturers_url"], ENT_QUOTES, CHARSET, false) . "</URL>" . "\n" ;
            $schema .= "<URL_CLICK>" . $details["url_clicked"] . "</URL_CLICK>" . "\n" ;
            $schema .= "<DATE_LAST_CLICK>" . $details["date_last_click"] . "</DATE_LAST_CLICK>" . "\n" ;
            $schema .= "</MANUFACTURERS_DESCRIPTION>\n";
          }

          $schema .= '</MANUFACTURERS_DATA>' . "\n";
          echo $schema;
        }
        $schema = '</MANUFACTURERS>' . "\n";

        echo $schema;
        exit;


// ----------------------------------------------------------------------------------------
      case 'orders_export':
        $order_from = xprios_prepare_get('order_from');
        $order_to = xprios_prepare_get('order_to');
        $order_status = xprios_prepare_get('order_status');

        if (defined('SET_TIME_LIMIT')) { tep_set_time_limit(0); }
        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<ORDER>' . "\n";

        echo $schema;

        $orders_query = "SELECT * FROM " . TABLE_ORDERS . " WHERE orders_id >= '" . tep_db_input($order_from) . "'";
        if (!isset($order_status) && !isset($order_from)) {
          $order_status = 1;
          $orders_query .= " AND orders_status = " . $order_status;
        }

        $orders_result = tep_db_query($orders_query);

        while ($orders = tep_db_fetch_array($orders_result)) {

          // Geburtsdatum laden
          $cust_query = "SELECT * FROM " . TABLE_CUSTOMERS . " WHERE customers_id=" . $orders['customers_id'];
          $cust_result = tep_db_query ($cust_query);
          if (tep_db_num_rows($cust_result) >0) {
              $cust_data = tep_db_fetch_array($cust_result);
              $cust_dob = $cust_data['customers_dob'];
              $cust_gender = $cust_data['customers_gender'];
          } else {
              $cust_dob = '';
              $cust_gender = '';
          }

          $schema  = '<ORDER_INFO>' . "\n" .
                     '<ORDER_HEADER>' . "\n" .
                     '<ORDER_ID>' . $orders['orders_id'] . '</ORDER_ID>' . "\n" .
                     '<CUSTOMER_ID>' . $orders['customers_id'] . '</CUSTOMER_ID>' . "\n" .
                     '<ORDER_DATE>' . $orders['date_purchased'] . '</ORDER_DATE>' . "\n" .
                     '<ORDER_STATUS>' . $orders['orders_status'] . '</ORDER_STATUS>' . "\n" .
                     '<ORDER_CURRENCY>' . htmlspecialchars($orders['currency'], ENT_QUOTES, CHARSET, false) . '</ORDER_CURRENCY>' . "\n" .
                     '<ORDER_CURRENCY_VALUE>' . $orders['currency_value'] . '</ORDER_CURRENCY_VALUE>' . "\n" .
                     '</ORDER_HEADER>' . "\n" .
                     '<BILLING_ADDRESS>' . "\n" .
                     '<COMPANY>' . htmlspecialchars($orders['billing_company'], ENT_QUOTES, CHARSET, false) . '</COMPANY>' . "\n" .
                     '<FIRSTNAME>' . htmlspecialchars($orders['billing_firstname'], ENT_QUOTES, CHARSET, false) . '</FIRSTNAME>' . "\n" .
                     '<LASTNAME>' . htmlspecialchars($orders['billing_lastname'], ENT_QUOTES, CHARSET, false) . '</LASTNAME>' . "\n" .
                     '<STREET>' . htmlspecialchars($orders['billing_street_address'], ENT_QUOTES, CHARSET, false) . '</STREET>' . "\n" .
                     '<POSTCODE>' . htmlspecialchars($orders['billing_postcode'], ENT_QUOTES, CHARSET, false) . '</POSTCODE>' . "\n" .
                     '<CITY>' . htmlspecialchars($orders['billing_city'], ENT_QUOTES, CHARSET, false) . '</CITY>' . "\n" .
                     '<SUBURB>' . htmlspecialchars($orders['billing_suburb'], ENT_QUOTES, CHARSET, false) . '</SUBURB>' . "\n" .
                     '<STATE>' . htmlspecialchars($orders['billing_state'], ENT_QUOTES, CHARSET, false) . '</STATE>' . "\n" .
                     '<COUNTRY>' . htmlspecialchars($orders['billing_country_iso_code_2'], ENT_QUOTES, CHARSET, false) . '</COUNTRY>' . "\n" .
                     '<TELEPHONE>' . htmlspecialchars($orders['customers_telephone'], ENT_QUOTES, CHARSET, false) . '</TELEPHONE>' . "\n" . // JAN
                     '<EMAIL>' . htmlspecialchars($orders['customers_email_address'], ENT_QUOTES, CHARSET, false) . '</EMAIL>' . "\n" . // JAN
                     '<BIRTHDAY>' . htmlspecialchars($cust_dob, ENT_QUOTES, CHARSET, false) . '</BIRTHDAY>' . "\n" .
                     '<GENDER>' . htmlspecialchars($cust_gender, ENT_QUOTES, CHARSET, false) . '</GENDER>' . "\n" .
                     '</BILLING_ADDRESS>' . "\n" .
                     '<DELIVERY_ADDRESS>' . "\n" .
                     '<COMPANY>' . htmlspecialchars($orders['delivery_company'], ENT_QUOTES, CHARSET, false) . '</COMPANY>' . "\n" .
                     '<FIRSTNAME>' . htmlspecialchars($orders['delivery_firstname'], ENT_QUOTES, CHARSET, false) . '</FIRSTNAME>' . "\n" .
                     '<LASTNAME>' . htmlspecialchars($orders['delivery_lastname'], ENT_QUOTES, CHARSET, false) . '</LASTNAME>' . "\n" .
                     '<STREET>' . htmlspecialchars($orders['delivery_street_address'], ENT_QUOTES, CHARSET, false) . '</STREET>' . "\n" .
                     '<POSTCODE>' . htmlspecialchars($orders['delivery_postcode'], ENT_QUOTES, CHARSET, false) . '</POSTCODE>' . "\n" .
                     '<CITY>' . htmlspecialchars($orders['delivery_city'], ENT_QUOTES, CHARSET, false) . '</CITY>' . "\n" .
                     '<SUBURB>' . htmlspecialchars($orders['delivery_suburb'], ENT_QUOTES, CHARSET, false) . '</SUBURB>' . "\n" .
                     '<STATE>' . htmlspecialchars($orders['delivery_state'], ENT_QUOTES, CHARSET, false) . '</STATE>' . "\n" .
                     '<COUNTRY>' . htmlspecialchars($orders['delivery_country_iso_code_2'], ENT_QUOTES, CHARSET, false) . '</COUNTRY>' . "\n" .
                     '</DELIVERY_ADDRESS>' . "\n" .
                     '<PAYMENT>' . "\n" .
                     '<PAYMENT_METHOD>' . htmlspecialchars($orders['payment_method'], ENT_QUOTES, CHARSET, false) . '</PAYMENT_METHOD>'  . "\n" .
                     '<PAYMENT_CLASS>' . htmlspecialchars($orders['payment_class'], ENT_QUOTES, CHARSET, false) . '</PAYMENT_CLASS>'  . "\n";

          switch ($orders['payment_class']) {
            case 'banktransfer':
              // Bankverbindung laden, wenn aktiv
              $bank_name = '';
              $bank_blz  = '';
              $bank_kto  = '';
              $bank_inh  = '';
              $bank_stat = -1;

              $bank_query = "SELECT * FROM banktransfer WHERE orders_id = " . $orders['orders_id'];
              $bank_result = tep_db_query($bank_query);
              if (($bank_result) && ($bankdata = tep_db_fetch_array($bank_result))) {
                $bank_name = $bankdata['banktransfer_bankname'];
                $bank_blz  = $bankdata['banktransfer_blz'];
                $bank_kto  = $bankdata['banktransfer_number'];
                $bank_inh  = $bankdata['banktransfer_owner'];
                $bank_stat = $bankdata['banktransfer_status'];
              }
              $schema .= '<PAYMENT_BANKTRANS_BNAME>' . htmlspecialchars($bank_name, ENT_QUOTES, CHARSET, false) . '</PAYMENT_BANKTRANS_BNAME>' . "\n" .
                         '<PAYMENT_BANKTRANS_BLZ>' . htmlspecialchars($bank_blz, ENT_QUOTES, CHARSET, false) . '</PAYMENT_BANKTRANS_BLZ>' . "\n" .
                         '<PAYMENT_BANKTRANS_NUMBER>' . htmlspecialchars($bank_kto, ENT_QUOTES, CHARSET, false) . '</PAYMENT_BANKTRANS_NUMBER>' . "\n" .
                         '<PAYMENT_BANKTRANS_OWNER>' . htmlspecialchars($bank_inh, ENT_QUOTES, CHARSET, false) . '</PAYMENT_BANKTRANS_OWNER>' . "\n" .
                         '<PAYMENT_BANKTRANS_STATUS>' . htmlspecialchars($bank_stat, ENT_QUOTES, CHARSET, false) . '</PAYMENT_BANKTRANS_STATUS>' . "\n";
              break;
          }

          $schema .= '</PAYMENT>' . "\n" .
                     '<SHIPPING>' . "\n" .
                     '<SHIPPING_METHOD>' . htmlspecialchars($orders['shipping_method'], ENT_QUOTES, CHARSET, false) . '</SHIPPING_METHOD>'  . "\n" .
                     '<SHIPPING_CLASS>' . htmlspecialchars($orders['shipping_class'], ENT_QUOTES, CHARSET, false) . '</SHIPPING_CLASS>'  . "\n" .
                     '</SHIPPING>' . "\n" .
                     '<ORDER_PRODUCTS>' . "\n";

          $products_query = "
            SELECT
              orders_products_id,
              products_id,
              products_model,
              products_name,
              final_price,
              products_tax,
              products_quantity
            FROM
              " . TABLE_ORDERS_PRODUCTS . "
            WHERE
              orders_id = '" . $orders['orders_id'] . "'";

          $products_result = tep_db_query($products_query);

          while ($products = tep_db_fetch_array($products_result)) {

            $schema .= '<PRODUCT>' . "\n" .
                       '<PRODUCTS_ID>' . $products['products_id'] . '</PRODUCTS_ID>' . "\n" .
                       '<PRODUCTS_QUANTITY>' . $products['products_quantity'] . '</PRODUCTS_QUANTITY>' . "\n" .
                       '<PRODUCTS_MODEL>' . htmlspecialchars($products['products_model'], ENT_QUOTES, CHARSET, false) . '</PRODUCTS_MODEL>' . "\n" .
                       '<PRODUCTS_NAME>' . htmlspecialchars($products['products_name'], ENT_QUOTES, CHARSET, false) . '</PRODUCTS_NAME>' . "\n" .
                       '<PRODUCTS_PRICE>' . $products['final_price'] . '</PRODUCTS_PRICE>' . "\n" .
                       '<PRODUCTS_TAX>' . $products['products_tax'] . '</PRODUCTS_TAX>' . "\n";


            $attributes_query = "
              SELECT
                products_options,
                products_options_values,
                options_values_price,
                price_prefix
              FROM
                " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
              WHERE
                orders_id = '" .$orders['orders_id'] . "' AND
                orders_products_id = '" . $products['orders_products_id'] . "'";

            $attributes_result = tep_db_query($attributes_query);


            if (tep_db_num_rows( $attributes_result ) > 0)
            {
              while ($attributes = tep_db_fetch_array($attributes_result))
              {
                $schema .= '<OPTION>' . "\n" .
                           '<PRODUCTS_OPTIONS>' .  htmlspecialchars($attributes['products_options'], ENT_QUOTES, CHARSET, false) . '</PRODUCTS_OPTIONS>' . "\n" .
                           '<PRODUCTS_OPTIONS_VALUES>' .  htmlspecialchars($attributes['products_options_values'], ENT_QUOTES, CHARSET, false) . '</PRODUCTS_OPTIONS_VALUES>' . "\n" .
                           '<PRODUCTS_OPTIONS_PRICE>' .  $attributes['price_prefix'] . ' ' . $attributes['options_values_price'] . '</PRODUCTS_OPTIONS_PRICE>' . "\n" .
                           '</OPTION>' . "\n";
              }
            }
            $schema .=  '</PRODUCT>' . "\n";

          }

          $schema .= '</ORDER_PRODUCTS>' . "\n";
          $schema .= '<ORDER_TOTAL>' . "\n";

          $totals_query = "
            SELECT
              title,
              value,
              class,
              sort_order
            FROM
              " . TABLE_ORDERS_TOTAL . "
            WHERE
              orders_id = '" . $orders['orders_id'] . "'
            ORDER BY
              sort_order";

          $totals_result = tep_db_query($totals_query);

          while ($totals = tep_db_fetch_array($totals_result)) {

            $total_prefix = "";
            $total_tax  = "";
            $total_prefix = $order_total_class[$totals['class']]['prefix'];
            $total_tax = $order_total_class[$totals['class']]['tax'];

            $schema .= '<TOTAL>' . "\n" .
                       '<TOTAL_TITLE>' . htmlspecialchars($totals['title'], ENT_QUOTES, CHARSET, false) . '</TOTAL_TITLE>' . "\n" .
                       '<TOTAL_VALUE>' . htmlspecialchars($totals['value'], ENT_QUOTES, CHARSET, false) . '</TOTAL_VALUE>' . "\n" .
                       '<TOTAL_CLASS>' . htmlspecialchars($totals['class'], ENT_QUOTES, CHARSET, false) . '</TOTAL_CLASS>' . "\n" .
                       '<TOTAL_SORT_ORDER>' . htmlspecialchars($totals['sort_order'], ENT_QUOTES, CHARSET, false) . '</TOTAL_SORT_ORDER>' . "\n" .
                       '<TOTAL_PREFIX>' . htmlspecialchars($total_prefix, ENT_QUOTES, CHARSET, false) . '</TOTAL_PREFIX>' . "\n" .
                       '<TOTAL_TAX>' . htmlspecialchars($total_tax, ENT_QUOTES, CHARSET, false) . '</TOTAL_TAX>' . "\n" .
                       '</TOTAL>' . "\n";
          }

          $schema .= '</ORDER_TOTAL>' . "\n";

          $comments_query = "
            SELECT
              comments
            FROM
              " . TABLE_ORDERS_STATUS_HISTORY . "
            WHERE
              orders_id = '" . $orders['orders_id'] . "' AND
              orders_status_id = '" . $orders['orders_status'] . "' ";

          $comments_result = tep_db_query ($comments_query);

          if ($comments =  tep_db_fetch_array($comments_result)) {
            $schema .=  '<ORDER_COMMENTS>' . htmlspecialchars($comments['comments'], ENT_QUOTES, CHARSET, false) . '</ORDER_COMMENTS>' . "\n";
          }

          $schema .= '</ORDER_INFO>' . "\n\n";
          echo $schema;
        }

        $schema = '</ORDER>' . "\n\n";

        echo $schema;
        exit;


// ----------------------------------------------------------------------------------------
      case 'products_export':
        if (defined('SET_TIME_LIMIT')) {
          tep_set_time_limit(0);
        }

        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<PRODUCTS>' . "\n";
        echo $schema;



        if (DREI_PRODUKTBILDER == true)
        {
          $sql = "select products_id, products_quantity, products_model, products_image, products_image_medium, products_image_large, products_price, " .
                 "products_date_added, products_last_modified, products_date_available, products_weight, " .
                 "products_status, products_tax_class_id, manufacturers_id, products_ordered from " . TABLE_PRODUCTS;
        }
        	else
        {
          $sql = "select products_id, products_quantity, products_model, products_image, products_price, " .
                 "products_date_added, products_last_modified, products_date_available, products_weight, " .
                 "products_status, products_tax_class_id, manufacturers_id, products_ordered from " . TABLE_PRODUCTS;
        }

        $from = xprios_prepare_get('products_from');
        $anz  = xprios_prepare_get('products_count');

        if (isset($from)) {
          if (!isset($anz)) {
            $anz=1000;
          }
          $sql .= " limit " . $from . "," . $anz;
        }

        $orders_query = tep_db_query($sql);
        while ($products = tep_db_fetch_array($orders_query)) {

          $schema  = '<PRODUCT_INFO>' . "\n" .
                     '<PRODUCT_DATA>' . "\n" .
                     '<PRODUCT_ID>' . $products['products_id'] . '</PRODUCT_ID>' . "\n" .
                     '<PRODUCT_QUANTITY>' . $products['products_quantity'] . '</PRODUCT_QUANTITY>' . "\n" .
                     '<PRODUCT_MODEL>' . htmlspecialchars($products['products_model'], ENT_QUOTES, CHARSET, false) . '</PRODUCT_MODEL>' . "\n" .
                     '<PRODUCT_IMAGE>' . htmlspecialchars($products['products_image'], ENT_QUOTES, CHARSET, false) . '</PRODUCT_IMAGE>' . "\n";

          if (DREI_PRODUKTBILDER == true)
          {
             $schema .= '<PRODUCT_IMAGE_MED>' . htmlspecialchars($products['products_image_medium'], ENT_QUOTES, CHARSET, false) . '</PRODUCT_IMAGE_MED>' . "\n" .
                      '<PRODUCT_IMAGE_LARGE>' . htmlspecialchars($products['products_image_large'], ENT_QUOTES, CHARSET, false) . '</PRODUCT_IMAGE_LARGE>' . "\n";
          }

          $schema .= '<PRODUCT_PRICE>' . $products['products_price'] . '</PRODUCT_PRICE>' . "\n" .
                     '<PRODUCT_WEIGHT>' . $products['products_weight'] . '</PRODUCT_WEIGHT>' . "\n" .
                     '<PRODUCT_STATUS>' . $products['products_status'] . '</PRODUCT_STATUS>' . "\n" .
                     '<PRODUCT_TAX_CLASS_ID>' . $products['products_tax_class_id'] . '</PRODUCT_TAX_CLASS_ID>' . "\n"  .
                     '<MANUFACTURERS_ID>' . $products['manufacturers_id'] . '</MANUFACTURERS_ID>' . "\n" .

                     '<PRODUCT_DATE_ADDED>' . $products['products_date_added'] . '</PRODUCT_DATE_ADDED>' . "\n" .
                     '<PRODUCT_LAST_MODIFIED>' . $products['products_last_modified'] . '</PRODUCT_LAST_MODIFIED>' . "\n" .
                     '<PRODUCT_DATE_AVAILABLE>' . $products['products_date_available'] . '</PRODUCT_DATE_AVAILABLE>' . "\n" .

                     '<PRODUCTS_ORDERED>' . $products['products_ordered'] . '</PRODUCTS_ORDERED>' . "\n" ;


          $detail_query = "
            SELECT
              products_id,
              language_id,
              products_name,
              pd.products_description,
              products_url,
              name as language_name,
              code as language_code
            FROM
              " . TABLE_PRODUCTS_DESCRIPTION . " pd,
              " . TABLE_LANGUAGES ." l
            WHERE
              pd.language_id = l.languages_id AND
              pd.products_id=" . $products['products_id'];


          $detail_result = tep_db_query($detail_query);

          while ($details = tep_db_fetch_array($detail_result)) {

            $schema .= "<PRODUCT_DESCRIPTION ID='" . $details["language_id"] ."' CODE='" . $details["language_code"] . "' NAME='" . $details["language_name"] . "'>\n";

            if ($details["products_name"] !='Array') {
              $schema .= "<NAME>" . htmlspecialchars($details["products_name"], ENT_QUOTES, CHARSET, false) . "</NAME>" . "\n" ;
            }

            $schema .=  "<URL>" . htmlspecialchars($details["products_url"], ENT_QUOTES, CHARSET, false) . "</URL>" . "\n" ;

            $prod_details = $details["products_description"];

            if ($prod_details != 'Array') {
              $schema .=  "<DESCRIPTION>" . htmlspecialchars($prod_details, ENT_QUOTES, CHARSET, false) . "</DESCRIPTION>" . "\n";
            }

            $schema .= "</PRODUCT_DESCRIPTION>\n";
          }
          $schema .= '</PRODUCT_DATA>' . "\n" .
                     '</PRODUCT_INFO>' . "\n";
          echo $schema;
        }

        $schema = '</PRODUCTS>' . "\n\n";
        echo $schema;
        exit;


// ----------------------------------------------------------------------------------------
  case 'customers_export':
    if (defined('SET_TIME_LIMIT')) {
      tep_set_time_limit(0);
    }

    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
      '<CUSTOMERS>' . "\n";

    echo $schema;

    $from = xprios_prepare_get('customers_from');
    $anz  = xprios_prepare_get('customers_count');

    $address_query = "
      SELECT
        c.customers_gender,
        c.customers_id,
        c.customers_dob,
        c.customers_email_address,
        c.customers_telephone,
        c.customers_fax,
        ci.customers_info_date_account_created,
        a.entry_firstname,
        a.entry_lastname,
        a.entry_company,
        a.entry_street_address,
        a.entry_city,
        a.entry_postcode,
	a.entry_suburb,
	a.entry_state,
        co.countries_iso_code_2
      FROM
        ".TABLE_CUSTOMERS. " c,
        ".TABLE_CUSTOMERS_INFO. " ci,
        ".TABLE_ADDRESS_BOOK . " a ,
        ".TABLE_COUNTRIES." co
      WHERE
        c.customers_id = ci.customers_info_id AND
        c.customers_id = a.customers_id AND
        c.customers_default_address_id = a.address_book_id AND
        a.entry_country_id  = co.countries_id";

    if (isset($from)) {
      if (!isset($anz)) {
        $anz = 1000;
      }
      $address_query.= " LIMIT " . $from . "," . $anz;
    }

    $address_result = tep_db_query($address_query);

    while ($address = tep_db_fetch_array($address_result))  {

       $schema = '<CUSTOMERS_DATA>' . "\n" .
                 '<CUSTOMERS_ID>' . htmlspecialchars($address['customers_id'], ENT_QUOTES, CHARSET, false) . '</CUSTOMERS_ID>' . "\n" .
//ONLY XTC                 '<CUSTOMERS_CID>' . htmlspecialchars($address['customers_cid'], ENT_QUOTES, CHARSET, false) . '</CUSTOMERS_CID>' . "\n" .
                 '<GENDER>' . htmlspecialchars($address['customers_gender'], ENT_QUOTES, CHARSET, false) . '</GENDER>' . "\n" .
                 '<COMPANY>' . htmlspecialchars($address['entry_company'], ENT_QUOTES, CHARSET, false) . '</COMPANY>' . "\n" .
                 '<FIRSTNAME>' . htmlspecialchars($address['entry_firstname'], ENT_QUOTES, CHARSET, false) . '</FIRSTNAME>' . "\n" .
                 '<LASTNAME>' . htmlspecialchars($address['entry_lastname'], ENT_QUOTES, CHARSET, false) . '</LASTNAME>' . "\n" .
                 '<STREET>' . htmlspecialchars($address['entry_street_address'], ENT_QUOTES, CHARSET, false) . '</STREET>' . "\n" .
                 '<POSTCODE>' . htmlspecialchars($address['entry_postcode'], ENT_QUOTES, CHARSET, false) . '</POSTCODE>' . "\n" .
                 '<CITY>' . htmlspecialchars($address['entry_city'], ENT_QUOTES, CHARSET, false) . '</CITY>' . "\n" .
                 '<SUBURB>' . htmlspecialchars($address['entry_suburb'], ENT_QUOTES, CHARSET, false) . '</SUBURB>' . "\n" .
                 '<STATE>' . htmlspecialchars($address['entry_state'], ENT_QUOTES, CHARSET, false) . '</STATE>' . "\n" .
                 '<COUNTRY>' . htmlspecialchars($address['countries_iso_code_2'], ENT_QUOTES, CHARSET, false) . '</COUNTRY>' . "\n" .
                 '<TELEPHONE>' . htmlspecialchars($address['customers_telephone'], ENT_QUOTES, CHARSET, false) . '</TELEPHONE>' . "\n" . // JAN
                 '<FAX>' . htmlspecialchars($address['customers_fax'], ENT_QUOTES, CHARSET, false) . '</FAX>' . "\n" . // JAN
                 '<EMAIL>' . htmlspecialchars($address['customers_email_address'], ENT_QUOTES, CHARSET, false) . '</EMAIL>' . "\n" . // JAN
                 '<BIRTHDAY>' . htmlspecialchars($address['customers_dob'], ENT_QUOTES, CHARSET, false) . '</BIRTHDAY>' . "\n" .
                 '<DATE_ACCOUNT_CREATED>' . htmlspecialchars($address['customers_info_date_account_created'], ENT_QUOTES, CHARSET, false) . '</DATE_ACCOUNT_CREATED>' . "\n" .
                 '</CUSTOMERS_DATA>' . "\n";
       echo $schema;
    }

    $schema = '</CUSTOMERS>' . "\n\n";
    echo $schema;
    exit;


// ----------------------------------------------------------------------------------------
  case 'customers_newsletter_export':
    if (defined('SET_TIME_LIMIT')) {
      tep_set_time_limit(0);
    }
    $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
      '<CUSTOMERS>' . "\n".

    $from = xprios_prepare_get('customers_from');
    $anz  = xprios_prepare_get('customers_count');

    $address_query = "
      SELECT
        *
      FROM
        " . TABLE_CUSTOMERS. "
      WHERE
        customers_newsletter = 1";

    if (isset($from)) {
      if (!isset($anz)) {
        $anz = 1000;
      }

      $address_query.= " LIMIT " . $from . "," . $anz;
    }

    $address_result = tep_db_query($address_query);

    while ($address = tep_db_fetch_array($address_result)) {
      $schema .= '<CUSTOMERS_DATA>' . "\n";
      $schema .= '<CUSTOMERS_ID>' . $address['customers_id'] . '</CUSTOMERS_ID>' . "\n";
      $schema .= '<CUSTOMERS_GENDER>' . $address['customers_gender'] . '</CUSTOMERS_GENDER>' . "\n";
      $schema .= '<CUSTOMERS_FIRSTNAME>' . $address['customers_firstname'] . '</CUSTOMERS_FIRSTNAME>' . "\n";
      $schema .= '<CUSTOMERS_LASTNAME>' . $address['customers_lastname'] . '</CUSTOMERS_LASTNAME>' . "\n";
      $schema .= '<CUSTOMERS_EMAIL_ADDRESS>' . $address['customers_email_address'] . '</CUSTOMERS_EMAIL_ADDRESS>' . "\n";
      $schema .= '</CUSTOMERS_DATA>' . "\n";
    }

    $schema .= '</CUSTOMERS>' . "\n\n";
    echo $schema;
    exit;


// ----------------------------------------------------------------------------------------
   case 'version':
        // Ausgabe Scriptversion
        $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n" .
                  '<STATUS>' . "\n" .
                  '<STATUS_DATA>' . "\n" .
                  '<ACTION>' . $_GET['action'] . '</ACTION>' . "\n" .
                  '<CODE>' . '111' . '</CODE>' . "\n" .
                  '<SCRIPT_VER>' . $version_nr . '</SCRIPT_VER>' . "\n" .
                  '<SCRIPT_DATE>' . $version_datum . '</SCRIPT_DATE>' . "\n" .
                  '</STATUS_DATA>' . "\n" .
                  '</STATUS>' . "\n\n";
       echo $schema;
       exit;
     }
  }
?>