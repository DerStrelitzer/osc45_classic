<?php
/*
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class PaymentModules {

    public
        $code = '',
        $enabled = false,
        $_check = null,
        $title = '',
        $description = '',
        $sort_order = 0,
        $order_status = 0,
        $output = [],
        $confirmation_text = '',
        $email_footer = '',
        $form_action_url = '';
        
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
    public function get_output()
    {
        return $this->output;
    }
}
