<?php
/*
  $Id: NavigationHistory.php,v 1.6 2003/06/09 22:23:43 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class NavigationHistory
{
    public 
        $path = [], 
        $snapshot = [], 
        $referer = [];

    public function __construct()
    {
    }

    public function reset()
    {
        $this->path     = 
        $this->snapshot = 
        $this->referer  = [];
    }

    public function add_current_page()
    {
        global $PHP_SELF, $request_type, $cPath;

        if (substr(basename($PHP_SELF),0,5)!='popup') {
            if (isset($this->referer[1])) {
                $this->referer[0] = $this->referer[1];
            } else {
                $this->referer[0] = tep_href_link(FILENAME_DEFAULT);
            }
            $get_string = tep_get_all_get_params(['action', 'x', 'y', tep_session_name()]);
            if (isset($_GET['cPath'])) {
                $get_string = str_replace($_GET['cPath'], ingo_make_link($_GET['cPath'], 'c'), $get_string);
            }
            if (isset($_GET['products_id'])) {
                $get_string = str_replace($_GET['products_id'], ingo_make_link($_GET['products_id'], 'p'), $get_string);
            }
            $this->referer[1] = tep_href_link(basename($PHP_SELF), $get_string, $request_type);
        }

        $set = 'true';
        for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
            if ( ($this->path[$i]['page'] == basename($PHP_SELF)) ) {
                if (isset($cPath)) {
                    if (!isset($this->path[$i]['get']['cPath'])) {
                        continue;
                    } else {
                        if ($this->path[$i]['get']['cPath'] == $cPath) {
                            array_splice($this->path, ($i+1));
                            $set = 'false';
                            break;
                        } else {
                            $old_cPath = explode('_', $this->path[$i]['get']['cPath']);
                            $new_cPath = explode('_', $cPath);

                            for ($j=0, $n2=sizeof($old_cPath); $j<$n2; $j++) {
                                if ($old_cPath[$j] != $new_cPath[$j]) {
                                    array_splice($this->path, ($i));
                                    $set = 'true';
                                    break 2;
                                }
                            }
                        }
                    }
                } else {
                    array_splice($this->path, ($i));
                    $set = 'true';
                    break;
                }
            }
        }

        if ($set == 'true') {
            $this->path[] = array(
                'page' => basename($PHP_SELF),
                'mode' => $request_type,
                'get'  => $_GET,
                'post' => $_POST
            );
        }
    }

    public function remove_current_page()
    {
        global $PHP_SELF;

        $last_entry_position = sizeof($this->path) - 1;
        if ($this->path[$last_entry_position]['page'] == basename($PHP_SELF)) {
            unset($this->path[$last_entry_position]);
        }
    }

    public function set_snapshot($page = '')
    {
        global $PHP_SELF, $request_type;

        if (is_array($page)) {
            $this->snapshot = array(
                'page' => $page['page'],
                'mode' => $page['mode'],
                'get'  => $page['get'],
                'post' => $page['post']
            );
        } else {
            $this->snapshot = array(
                'page' => basename($PHP_SELF),
                'mode' => $request_type,
                'get'  => $_GET,
                'post' => $_POST
            );
        }
    }

    public function clear_snapshot()
    {
        $this->snapshot = [];
    }

    public function set_path_as_snapshot($history = 0)
    {
        $pos = (sizeof($this->path)-1-$history);
        $this->snapshot = array(
            'page' => $this->path[$pos]['page'],
            'mode' => $this->path[$pos]['mode'],
            'get'  => $this->path[$pos]['get'],
            'post' => $this->path[$pos]['post']
        );
    }

    public function debug()
    {
        for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
            echo $this->path[$i]['page'] . '?';
            foreach ($this->path[$i]['get'] as $key => $value) {
                echo $key . '=' . $value . '&';
            }
            if (sizeof($this->path[$i]['post']) > 0) {
                echo '<br />';
                foreach ($this->path[$i]['post'] as $key => $value) {
                    echo '&nbsp;&nbsp;<b>' . $key . '=' . $value . '</b><br />';
                }
            }
            echo '<br />';
        }

        if (sizeof($this->snapshot) > 0) {
            echo '<br /><br />';
            echo $this->snapshot['mode'] . ' ' . $this->snapshot['page'] . '?' . tep_array_to_string($this->snapshot['get'], array(tep_session_name())) . '<br />';
        }
    }

    public function unserialize($broken)
    {
        foreach ($broken as $kv) {
            $key = $kv['key'];
            if (gettype($this->$key)!="user function") {
                $this->{$key} = $kv['value'];
            }
        }
    }
}
