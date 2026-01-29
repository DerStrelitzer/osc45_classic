<?php
/*
  $Id: viewed_products.php,v 1.0 4

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

class viewed_products
{
    public $viewed = [], $count = 0, $max = 10;

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->viewed = [];
        $this->count = 0;
    }

    function set_max($max='')
    {
        $this->max = max(0, intval($max));
    }

    public function store($products_id='', $products_name='')
    {
        $products_id = tep_get_prid($products_id);
        if ($this->max > 0 && $products_id > 0) {
            if ($products_name=='') {
                $products_name = tep_get_products_name($products_id);
            }
            $j = count($this->viewed);
            if ($j==0) {
                $this->viewed[] = array('id' => $products_id, 'name' => $products_name);
            } elseif ($this->viewed[0]['id']!=$products_id) {
                for ($i=1; $i<$j; $i++) {
                    if ($this->viewed[$i]['id']==$products_id) {
                        unset($this->viewed[$i]);
                    }
                }
                $k = count($this->viewed);
                if ($k!=$j) {
                    $m = [];
                    foreach ($this->viewed as $value) {
                        $m[] = array('id' => $value['id'], 'name' => $value['name']);
                    }
                    $this->viewed = $m;
                }
                array_unshift($this->viewed, array('id' => $products_id, 'name' => $products_name));
                if (isset($this->viewed[$this->max])) {
                    unset($this->viewed[$this->max]);
                }
            }
        }
        $this->count = count($this->viewed);
    }
}
