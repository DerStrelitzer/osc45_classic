<?php
/*
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

abstract class ShippingModules {

    public
        $code = '',
        $icon = '',
        $enabled = false,
        $_check = null,
        $sort_order = '1',
        $title = '', 
        $description = '',
        $tax_class = '0',
        $quotes = [];

    public function __construct()
    {
    }

    public function get_code()
    {
        return $this->code;
    }
    public function get_enabled()
    {
        return $this->enabled;
    }
    public function get_sort_order()
    {
        return $this->sort_order;
    }
    public function get_title()
    {
        return $this->title;
    }
    public function get_description()
    {
        return $this->description;
    }
    public function get_tax_class()
    {
        return $this->tax_class;
    }
    public function get_quotes()
    {
        return $this->quotes;
    }
}
