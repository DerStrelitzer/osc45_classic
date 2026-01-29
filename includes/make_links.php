<?php
/*
  $Id: make_links.php,v 1.21 2005/02/25  Ingo <www.strelitzer.de> $

  xPrioS, Open Source E-Commerce Solutions
  http://www.xprios.de

  Copyright (c) 2026 xPrioS
  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License

*/

$_SESSION['my_links_serv'] = true;
  
function add_to_link($p_id, $p_name='', $c_id='')
{
    global $my_links_text, $categories_info_array;
    $my_links_text .= "<tr>\n <td><h1>" . (($categories_info_array[$c_id]['link']=='')?tep_href_link(FILENAME_DEFAULT, 'cPath=0', 'NONSSL', false) : $categories_info_array[$c_id]['link']) . "</h1></td>\n <td>&nbsp;&nbsp;</td>\n";
    $my_links_text .= ' <td><a href="'. tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . ingo_make_link($p_id, 'p', $p_name), 'NONSSL', false) . '" target="_blank"><h1>' . $p_name . '</h1></a></td>' . "\n";
    $my_links_text .= "</tr>\n";
}


/////
// erstellen der linktabelle
//
$my_links_text = '<table border="0" cellspacing="0" cellpadding="0">' . "\n";

/////
// ermitteln der 10 neuesten artikel und erstellen der links
//
$new_products_query = tep_db_query("select p.products_id, pd.products_name, p2c.categories_id from " . TABLE_PRODUCTS ." p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c WHERE p2c.products_id = p.products_id AND pd.products_id = p.products_id AND p.products_status = '1' AND pd.language_id = '" . (int)$GLOBALS['languages_id'] . "'  order by p.products_date_added desc limit 10");
if (tep_db_num_rows($new_products_query) >= '1') {
    while ($new_products = tep_db_fetch_array($new_products_query)) {
        add_to_link($new_products['products_id'], $new_products['products_name'], $new_products['categories_id']);
    }
}
$my_links_text .= "\n";

/////
// ermitteln der 10 bestverkauften artikel und erstellen der links
//
$best_sellers_query = tep_db_query("select distinct p.products_id, pd.products_name, p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p2c.products_id = p.products_id AND p.products_status = '1' and p.products_ordered > 0 and p.products_id = pd.products_id and pd.language_id = '" . (int)$GLOBALS['languages_id'] . "' order by p.products_ordered desc, pd.products_name limit 10");
if (tep_db_num_rows($best_sellers_query) >= '1') {
    while ($best_sellers = tep_db_fetch_array($best_sellers_query)) {
        add_to_link($best_sellers['products_id'], $best_sellers['products_name'], $best_sellers['categories_id']);
    }
}
$my_links_text .= "\n";

/////
// ermitteln der 10 meistbesuchten artikel und erstellen der links
//
$best_viewed_query = tep_db_query("select p.products_id, pd.products_name, p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c WHERE p2c.products_id = p.products_id AND pd.products_id = p.products_id AND p.products_status = '1' order by pd.products_viewed desc limit 10");
if (tep_db_num_rows($best_viewed_query) >= '1') {
    while ($best_viewed = tep_db_fetch_array($best_viewed_query)) {
        add_to_link($best_viewed['products_id'], $best_viewed['products_name'], $best_viewed['categories_id']);
    }
}

/////
// abschluss der tabelle
//
$my_links_text .= "</table>\n";

/////
// schreiben der links als txt-datei in das catalog-verzeichnis, von wo verlinkende Seiten diese abrufen können
//
if($fp = @fopen(DIR_FS_CATALOG . 'my_links.txt' , 'w')) {
    @fputs($fp, $my_links_text);
    fclose($fp);
    chmod($fp , 0777);
}
