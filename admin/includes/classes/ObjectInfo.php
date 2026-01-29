<?php
/*
  $Id: ObjectInfo.php,v 1.6 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class ObjectInfo extends stdClass
{

// class constructor
    public function __construct($object_array)
    { 
        if (is_array($object_array)) {
            foreach ($object_array as $key => $value) {
                $key = strval($key);
                if ($key != '') {
                    $this->{$key} = $value;
                }
            }
        }
    }

}
