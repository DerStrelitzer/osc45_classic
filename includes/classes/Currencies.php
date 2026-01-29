<?php
/*
  $Id: Currencies.php,v 1.16 2003/06/05 23:16:46 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

////
// Class to handle currencies
// TABLES: currencies
class Currencies 
{
    public $currencies;

// class constructor
    public function __construct()
    {
        $this->currencies = [];
        $currencies_query = tep_db_query("select "
            . "code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value "
            . "from " . TABLE_CURRENCIES
        );
        while ($currencies = tep_db_fetch_array($currencies_query)) {
            $this->currencies[$currencies['code']] = [
                'title'           => $currencies['title'],
                'symbol_left'     => $currencies['symbol_left'],
                'symbol_right'    => $currencies['symbol_right'],
                'decimal_point'   => $currencies['decimal_point'],
                'thousands_point' => $currencies['thousands_point'],
                'decimal_places'  => $currencies['decimal_places'],
                'value'           => $currencies['value']
            ];
        }
        tep_db_free_result($currencies_query);
    }

// class methods
    public function format($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '')
    {

        if (empty($currency_type)) $currency_type = $GLOBALS['currency'];

        if ($calculate_currency_value == true) {
            $number *= tep_not_null($currency_value) ? $currency_value : $this->currencies[$currency_type]['value'];
        }
        $number = tep_round($number, $this->currencies[$currency_type]['decimal_places']);

        $format_string = $this->currencies[$currency_type]['symbol_left'] 
        . number_format($number, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) 
        . $this->currencies[$currency_type]['symbol_right'];

        return $format_string;
    }

    public function is_set($code)
    {
        if (isset($this->currencies[$code]) && tep_not_null($this->currencies[$code])) {
            return true;
        } else {
            return false;
        }
    }

    public function get_value($code)
    {
        $return = -1;
        if ($this->is_set($code)) {
            $return = $this->currencies[$code]['value'];
        }
        return $return;
    }

    public function get_decimal_places($code)
    {
        $return = 2;
        if ($this->is_set($code)) {
            $return = $this->currencies[$code]['decimal_places'];
        }
        return $return;
    }

    public function display_price($products_price, $products_tax, $quantity = 1)
    {
        return $this->format(tep_add_tax($products_price, $products_tax) * $quantity);
    }
}
