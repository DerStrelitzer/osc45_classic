<?php
/*  $Id: ot_lev_discount.php,v 1.31 ger 2003/05/04 21:10:11

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class ot_lev_discount extends OrderTotalModules
{
    public
        $code = 'ot_lev_discount',
        $table = '';

    public function __construct()
    {
        $this->enabled = defined('MODULE_LEV_DISCOUNT_STATUS') && MODULE_LEV_DISCOUNT_STATUS=='true' ? true : false;
        $this->sort_order = defined('MODULE_LEV_DISCOUNT_SORT_ORDER') ? MODULE_LEV_DISCOUNT_SORT_ORDER : 0;
        $this->title = '<b>' . MODULE_LEV_DISCOUNT_TITLE . ':</b>';
        $this->description = MODULE_LEV_DISCOUNT_DESCRIPTION;
        $this->table = defined('MODULE_LEV_DISCOUNT_TABLE') ? MODULE_LEV_DISCOUNT_TABLE : '';
    }

    function process()
    {
        global $order, $ot_subtotal, $currencies;
        $od_amount = $this->calculate_credit($this->get_order_total());
        if ($od_amount>0) {
            $this->deduction = $od_amount;
            $this->output[] = array(
                'title' => $this->title,
                'text'  => '-' . $currencies->format($od_amount) ,
                'value' => $od_amount
            );
            $order->info['total'] = $order->info['total'] - $od_amount;
            if ($this->sort_order < $ot_subtotal->sort_order) {
                $order->info['subtotal'] = $order->info['subtotal'] - $od_amount;
            }
        }
    }


    function calculate_credit($amount)
    {
        global $order;
        $od_amount=0;
        $table_cost = preg_split('/[:,]/' , MODULE_LEV_DISCOUNT_TABLE, -1, PREG_SPLIT_NO_EMPTY);
        for ($i = 0; $i < count($table_cost); $i+=2) {
            if ($amount >= $table_cost[$i]) {
                $od_pc = $table_cost[$i+1];
            }
        }

// Calculate main tax reduction
        $tod_amount = round($order->info['tax']*10)/10*$od_pc/100;
        $order->info['tax'] = $order->info['tax'] - $tod_amount;
// Calculate tax group deductions
        foreach ($order->info['tax_groups'] as $key => $value) {
            $god_amount = round($value*10)/10*$od_pc/100;
            $order->info['tax_groups'][$key] = $order->info['tax_groups'][$key] - $god_amount;
        }
        $od_amount = round($amount*10)/10*$od_pc/100;
        $od_amount = $od_amount + $tod_amount;
        return $od_amount;
    }


    function get_order_total()
    {
        global  $order;
        $order_total = $order->info['subtotal'];
// Check if gift voucher is in cart and adjust total
        $products = $_SESSION['cart']->get_products();
        for ($i=0; $i<sizeof($products); $i++) {
            $t_prid = tep_get_prid($products[$i]['id']);
            $gv_query = tep_db_query("select products_price, products_tax_class_id, products_model from " . TABLE_PRODUCTS . " where products_id = '" . $t_prid . "'");
            $gv_result = tep_db_fetch_array($gv_query);
            if (preg_match('/^GIFT/i', $gv_result['products_model'])) {
                $qty = $_SESSION['cart']->get_quantity($t_prid);
                $products_tax = tep_get_tax_rate($gv_result['products_tax_class_id']);
                $gv_amount = $gv_result['products_price'] * $qty;
                $order_total=$order_total - $gv_amount;
            }
        }
        $order_total=$order_total-$order->info['tax'];
        return $order_total;
    }



    function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_LEV_DISCOUNT_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys()
    {
        return array('MODULE_LEV_DISCOUNT_STATUS', 'MODULE_LEV_DISCOUNT_SORT_ORDER','MODULE_LEV_DISCOUNT_TABLE');
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Bestellrabatt', 'MODULE_LEV_DISCOUNT_STATUS', 'true', 'Wird der Bestellrabatt angeboten?', '6', '1','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_LEV_DISCOUNT_SORT_ORDER', '2', 'Reihenfolge der Anzeige, niedrigste zuerst.', '6', '2', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Rabattsatz', 'MODULE_LEV_DISCOUNT_TABLE', '27:10,45:20,500:12.5,1000:15', 'Hier wird die Rabattstaffel bestimmt. <b>Netto-Preise!</b><br /> (100:7.5,250:10 = ab 100 gibt es 7,5% / ab 250 gibt es 10%...)', '6', '6', now())");
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
