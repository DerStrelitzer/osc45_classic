<?php
/*
  $Id: ingo_function.php,v 1.3 2006/02/10  Ingo (www.strelitzer.de)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License

*/

// entfernt diverse Zeichen aus dem Produktnamen und ersetzt sie jeweils durch $eparator
//
function ingo_make_filename($filename='')
{
    $separator = '-';
    $umlaute = array(
      'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss', '&' => 'und',
      'á' => 'a', 'à' => 'a', 'â' => 'a', 'å' => 'a', 'å' => 'a', 
      'ë' => 'e', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 
      'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 
      'ó' => 'o', 'ò' => 'o', 'ø' => 'o', 'ô' => 'o', 
      'ú' => 'u', 'ù' => 'u', 'û' => 'u', 
      'æ' => 'ae', 
      'ý' => 'y', 'ÿ' => 'y', 
      'ç' => 'c', 'ç' => 'c',
      '£' => 'L', '×' => 'x',
      'ñ' => 'n',
      '[' => '(', ']' => ')', '{' => '(', '}' => ')'
    );
    $new_name = preg_replace('/' . preg_quote($separator, '/') . '{2,}/', $separator, preg_replace('/[^a-zA-Z0-9' . preg_quote('+*,;#@$%()[]', '/') . ']/i', $separator, strtr(strtolower($filename), $umlaute)));
    while (substr($new_name,-1) == $separator) {
        $new_name = substr($new_name, 0, strlen($new_name)-1);
    }
    return $new_name;
}

// neue Funktion zur Manipulation von $_GET['products_id'] und 'cPath'
//
function ingo_make_link($id='0', $mode='p', $name='')
{
    global $categories_info_array;
    $separator = '-';
    if ($id=='0') {
        return $id;
    }
    switch ($mode) {
        case 'p':
            $new_id = ($name==''? ingo_make_filename(tep_get_products_name($id)) : ingo_make_filename($name)) . $separator . trim($id);
        break;
        case 'c':
            if ($name == '') {
                $split_array = preg_split('/_/', $id, -1, PREG_SPLIT_NO_EMPTY);
                $size = sizeof($split_array);
                if (!isset($categories_info_array) || !is_array($categories_info_array)) {
                    ingo_categories_info();
                }
                for ($i=0; $i<$size; $i++) {
                    $name .= ($name!='' ? $separator:'') . $categories_info_array[$split_array[$i]]['name'];
                }
            }
            $new_id = ingo_make_filename($name) . $separator . $id;
        break;
        default:
          $new_id = $id;
    }
    return $new_id;
}

// Erstellung eines Infoarrays der Kategoriedaten in der gegenwärtigen Sprache
//
function ingo_categories_info()
{
    global $categories_info_array;

    $included_categories_query = tep_db_query("SELECT c.categories_id, c.parent_id, cd.categories_name FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd WHERE c.categories_id = cd.categories_id AND cd.language_id = '" . (int)$GLOBALS['languages_id'] . "'");
    $inc_cat = [];
    while ($included_categories = tep_db_fetch_array($included_categories_query)) {
        $inc_cat[] = [
            'id' => $included_categories['categories_id'],
            'parent' => $included_categories['parent_id'],
            'name' => $included_categories['categories_name']
        ];
    }

    $num_of_cats = sizeof($inc_cat);
    $categories_info_array = [];
    for ($i=0; $i<$num_of_cats; $i++) {
        $categories_info_array[$inc_cat[$i]['id']] = [
            'parent'=> $inc_cat[$i]['parent'],
            'name'  => $inc_cat[$i]['name'],
            'path'  => $inc_cat[$i]['id'],
            'link'  => '',
            'sons'  => $inc_cat[$i]['id']
        ];
    }

    for ($i=0; $i<$num_of_cats; $i++) {
        $cat_id = $inc_cat[$i]['id'];
        while ($categories_info_array[$cat_id]['parent'] != 0) {
            $categories_info_array[$categories_info_array[$cat_id]['parent']]['sons'] .= ( (strpos($categories_info_array[$categories_info_array[$cat_id]['parent']]['sons'],$cat_id)===false) ? (($categories_info_array[$categories_info_array[$cat_id]['parent']]['sons']!='')?',':'') . $cat_id : '');
            $categories_info_array[$inc_cat[$i]['id']]['path'] = $categories_info_array[$cat_id]['parent'] . '_' . $categories_info_array[$inc_cat[$i]['id']]['path'];
            $cat_id = $categories_info_array[$cat_id]['parent'];
        }
        $link_array = preg_split('/_/', $categories_info_array[$inc_cat[$i]['id']]['path'], -1, PREG_SPLIT_NO_EMPTY);
        for ($j=0; $j<sizeof($link_array); $j++) {
            $categories_info_array[$inc_cat[$i]['id']]['link'] .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'cPath=' . ingo_make_filename($categories_info_array[$link_array[$j]]['name']) . '-' . $categories_info_array[$link_array[$j]]['path']) . '"><span style="white-space:nowrap">' . $categories_info_array[$link_array[$j]]['name'] . '</span></a> &raquo; ';
        }
    }
}

// Beschneiden von String $string auf Länge $max_length und Verlinkung auf product_info, wenn ID übergeben.
// Konstante TEXT_READ_MORE muss in Sprachdatei ../languages/(sprache).php deklariert werden
function ingo_cut_description($string='', $max_length=500, $p_id='0', $p_name='')
{
    if ($string == '' || $max_length > strlen($string)) {
        return $string;
    }
    for ($i=$max_length; $i>0; $i-- ) {
        if (substr($string, $i, 1) == ' ') {
            $j=$i; 
            break;
        }
    }
    return substr($string, 0, $j) . '<span style="white-space:nowrap">&nbsp;...&nbsp;' . ($p_id != '0' ? '[<a href="'. ingo_product_link($p_id, $p_name) . '"><b>' . TEXT_READ_MORE . '</b></a>]' : '') . '</span>';
}

// Realisierung von Produktlinks in Beschreibungstexten
//
function ingo_link_in_text($text='')
{
    if (strpos($text, '{{')!==false && strpos($text, '}}')!==false) {
        $text = preg_replace_callback("|({{.+}})|Us", 'ingo_link_replace', $text);
    }
    return $text;
}

function ingo_link_replace($found='')
{
    global $categories_info_array;
    if (!is_array($found)) {
        $found = ['',''];
    }
    if (substr($found[1],0,2)=='{{' && substr($found[1],-2)=='}}' && strlen($found[1])>4) {
        $found = substr($found[1], 2, strlen($found[1])-4);
        $elements = explode(',', $found);
        if (count($elements)) {
            $id = trim($elements[0]);
            $mod = isset($elements[1])&&trim($elements[1])!='' ? trim($elements[1]):'';
            $name = isset($elements[2])&&trim($elements[2])!='' ? trim($elements[2]):'';
            if ($mod=='c') {
                if (isset($categories_info_array[$elements[0]]['path'])) {
                    $id = $categories_info_array[trim($elements[0])]['path'];
                    $text = ($name!=''?$name:$categories_info_array[trim($elements[0])]['name']);
                    $back = '<a href="' . tep_href_link(FILEMANE_DEFAULT, 'cPath=' . ingo_make_link($id, $mod, $name)) . '">' . $text . '</a>';
                } else {
                    $back = $text;
                }
                $back = $text;
            } else {
                $text = ($name!=''?$name:tep_get_products_name($id));
                $back = '<a href="' . ingo_product_link($id, $name) . '">' . $text . '</a>';
            }
        }
    }
    return $back;
}

// graphischer Preis
//
function graphic_numeral($numeral='', $charset='a/')
{
    global $spider_flag;
    $replace = [
        '0' => tep_image(DIR_WS_ICONS . $charset . '0.gif', $numeral),
        '1' => tep_image(DIR_WS_ICONS . $charset . '1.gif', $numeral),
        '2' => tep_image(DIR_WS_ICONS . $charset . '2.gif', $numeral),
        '3' => tep_image(DIR_WS_ICONS . $charset . '3.gif', $numeral),
        '4' => tep_image(DIR_WS_ICONS . $charset . '4.gif', $numeral),
        '5' => tep_image(DIR_WS_ICONS . $charset . '5.gif', $numeral),
        '6' => tep_image(DIR_WS_ICONS . $charset . '6.gif', $numeral),
        '7' => tep_image(DIR_WS_ICONS . $charset . '7.gif', $numeral),
        '8' => tep_image(DIR_WS_ICONS . $charset . '8.gif', $numeral),
        '9' => tep_image(DIR_WS_ICONS . $charset . '9.gif', $numeral),
        '.' => tep_image(DIR_WS_ICONS . $charset . 'point.gif', $numeral),
        ',' => tep_image(DIR_WS_ICONS . $charset . 'comma.gif', $numeral),
        ' ' => tep_image(DIR_WS_ICONS . $charset . 'space.gif', $numeral),
        '$' => tep_image(DIR_WS_ICONS . $charset . 'dollar.gif', $numeral),
        'EUR' => tep_image(DIR_WS_ICONS . $charset . 'eur.gif', $numeral),
    ];
    if (!$spider_flag) {
        return strtr($numeral, $replace);
    }
    return $numeral;
}

function ingo_make_euro($string='') {
    return str_replace('EUR', '<span class="spacelefteuro">&euro;</span>', $string);
}

function ingo_price_added($tax_rate=0, $shipping=true) {
    $added = '';
    if ($tax_rate != 0) {
        $added .= (DISPLAY_PRICE_WITH_TAX=='ja'?SIMPLE_WORD_INCL:SIMPLE_WORD_EXCL) . ' ' . ($tax_rate>0?$tax_rate . '%&nbsp;':'') . SIMPLE_WORD_TAX . '<br />';
    }
    if ($shipping == true) {
        $added .= '<a href="' . tep_href_link(FILENAME_SHIPPING) . '" class="priceadded">' . SIMPLE_WORD_EXCL . SIMPLE_WORD_SHIPPING . '</a>';
    }
    if ($added!='') {
        $added = '<p class="priceadded">' . $added . '</p>';
    }
    return $added ;
}

function ingo_product_link($products_id='0', $products_name='', $query='')
{
    global $spider_flag, $SID, $request_type;
    if(defined('LINKS_USE_REWRITE') && LINKS_USE_REWRITE=='ja') {
        if ($query!='') {
            $separator = '&';
            if ($query[0]=='&') {
                $query = substr($query,1);
            }
            $query = '?' . $query;
        } else {
            $separator = '?';
        }
        $link = ($request_type=='SSL' ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_HTTP_CATALOG) . ingo_make_link($products_id, 'p', $products_name) . 'p.html' . $query  . ($SID!=''&&!$spider_flag? $separator . $SID:'');
    } else {
        if ($query!='' && $query[0]!='&') {
            $query = '&' . $query;
        }
        $link = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . ingo_make_link($products_id, 'p', $products_name) . $query);
    }
    return $link;
}

function ingo_category_link($cPath='', $query='')
{
    global $spider_flag, $SID, $request_type;
    if (defined('LINKS_USE_REWRITE') && LINKS_USE_REWRITE=='ja') {
        if ($query!='') {
            $separator = '&';
            if ($query[0]=='&') {
                $query = substr($query,1);
            }
            $query = '?' . $query;
        } else {
            $separator = '?';
        }
        $link = ingo_make_link($cPath, 'c') . 'c.html' . $query  . ($SID!=''&&!$spider_flag? $separator . $SID:'');
    } else {
        if ($query!='' && $query[0]!='&') $query = '&' . $query;
        $link = tep_href_link(FILENAME_DEFAULT, 'cPath=' . ingo_make_link($cPath, 'c') . $query);
    }
    return $link;
}

function is_utf8($str='')
{  
    $strlen = strlen($str);  
    for ($i=0; $i<$strlen; $i++) {
        $ord = ord($str[$i]);
        if($ord < 0x80) {
            continue; // 0bbbbbbb    
        } elseif (($ord & 0xE0)===0xC0 && $ord>0xC1) {
            $n = 1; // 110bbbbb (exkl C0-C1)    
        } elseif (($ord & 0xF0)===0xE0) {
            $n = 2; // 1110bbbb    
        } elseif (($ord & 0xF8)===0xF0 && $ord<0xF5) {
            $n = 3; // 11110bbb (exkl F5-FF)    
        } else {
            return false; // ungültiges UTF-8-Zeichen    
        }
        for ($c=0; $c<$n; $c++) {// $n Folgebytes? // 10bbbbbb      
            if (++$i===$strlen || (ord($str[$i])&0xC0)!==0x80) {      
                return false; // ungültiges UTF-8-Zeichen 
            }
        }            
    }  
    return true; // kein ungültiges UTF-8-Zeichen gefunden
}
