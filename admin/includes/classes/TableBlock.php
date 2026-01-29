<?php
/*
  $Id: TableBlock.php,v 1.8 2003/06/20 15:51:18 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class TableBlock
{
    private       
      $table_params = [
        'border'          => 0,
        'width'           => '100%',
        'cellspacing'     => 0,
        'cellpadding'     => 2,
        'parameters'      => '',
        'row_parameters'  => '',
        'data_parameters' => '',
      ];

    public function __construct($params='')
    {
        if (is_array($params)) {
            foreach ($params as $name => $value) {
                $this->set_param($name, $value);
            }
        }
    }
    
    public function set_param($param='', $value='')
    {
        if (isset($this->table_params[$param])) {
            $this->table_params[$param] = $value;
            $return = true;
        } else {
            $return = false;
        }
        return $return;
    }
    
    public function get_block($contents)
    {
        $tableBox_string = '';
        $form_set = false;
        if (isset($contents['form'])) {
            $tableBox_string .= $contents['form'] . "\n";
            $form_set = true;
            array_shift($contents);
        }

        $tableBox_string .= '<table border="' . $this->table_params['border'] . '" '
        . 'width="' . $this->table_params['width'] . '" '
        . 'cellspacing="' . $this->table_params['cellspacing'] . '" '
        . 'cellpadding="' . $this->table_params['cellpadding'] . '"';
        if (tep_not_null($this->table_params['parameters'])) {
            $tableBox_string .= ' ' . $this->table_params['parameters'];
        }
        $tableBox_string .= '>' . "\n";

        for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
            $tableBox_string .= '  <tr';
            if (tep_not_null($this->table_params['row_parameters'])) {
                $tableBox_string .= ' ' . $this->table_params['row_parameters'];
            }
            if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params'])) {
                $tableBox_string .= ' ' . $contents[$i]['params'];
            }
            $tableBox_string .= '>' . "\n";

            if (isset($contents[$i][0]) && is_array($contents[$i][0])) {
                for ($x=0, $y=sizeof($contents[$i]); $x<$y; $x++) {
                    if (isset($contents[$i][$x]['text']) && tep_not_null(isset($contents[$i][$x]['text']))) {
                        $tableBox_string .= '    <td';
                        if (isset($contents[$i][$x]['align']) && tep_not_null($contents[$i][$x]['align'])) {
                            $tableBox_string .= ' align="' . $contents[$i][$x]['align'] . '"';
                        }
                        if (isset($contents[$i][$x]['params']) && tep_not_null(isset($contents[$i][$x]['params']))) {
                            $tableBox_string .= ' ' . $contents[$i][$x]['params'];
                        } elseif (tep_not_null($this->table_params['data_parameters'])) {
                            $tableBox_string .= ' ' . $this->table_params['data_parameters'];
                        }
                        $tableBox_string .= '>';
                        if (isset($contents[$i][$x]['form']) && tep_not_null($contents[$i][$x]['form'])) {
                            $tableBox_string .= $contents[$i][$x]['form'];
                        }
                        $tableBox_string .= $contents[$i][$x]['text'];
                        if (isset($contents[$i][$x]['form']) && tep_not_null($contents[$i][$x]['form'])) {
                            $tableBox_string .= '</form>';
                        }
                        $tableBox_string .= '</td>' . "\n";
                    }
                }
            } else {
                $tableBox_string .= '    <td';
                if (isset($contents[$i]['align']) && tep_not_null($contents[$i]['align'])) {
                    $tableBox_string .= ' align="' . $contents[$i]['align'] . '"';
                }
                if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params'])) {
                    $tableBox_string .= ' ' . $contents[$i]['params'];
                } elseif (tep_not_null($this->table_params['data_parameters'])) {
                    $tableBox_string .= ' ' . $this->table_params['data_parameters'];
                }
                $tableBox_string .= '>' . $contents[$i]['text'] . '</td>' . "\n";
            }

            $tableBox_string .= '  </tr>' . "\n";
        }

        $tableBox_string .= '</table>' . "\n";

        if ($form_set == true) $tableBox_string .= '</form>' . "\n";

        return $tableBox_string;
    }
}
