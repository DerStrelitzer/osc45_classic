<?php
/*
  $Id: breadcrumb.php,v 1.3 2003/02/11 00:04:50 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class Breadcrumb
{
    public $_trail;

    public function __construct()
    {
        $this->reset();
    }

    private function reset()
    {
        $this->_trail = [];
    }

    public function add($title, $link = '')
    {
        $this->_trail[] = array(
            'title' => $title, 
            'link'  => $link
        );
    }

    public function trail($separator = ' - ')
    {
        $trail_string = '';

        foreach ($this->_trail as $trail) {
            if ($trail_string != '') {
                $trail_string .= $separator;
            }
        
            if ($trail['link'] != '') {
                $trail_string .= '<a href="' . $trail['link'] . '">' . $trail['title'] . '</a>';
            } else {
                $trail_string .= $trail['title'];
            }
        }
      
        if (defined('YOU_ARE_HERE') && YOU_ARE_HERE!='') {
            $trail_string = trim(YOU_ARE_HERE) . ' ' . $trail_string;
        }

        return $trail_string;
    }
}
