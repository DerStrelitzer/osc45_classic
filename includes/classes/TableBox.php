<?php
/*
  $Id: table_box.php,v 1.33 2003/06/09 22:22:50 hpdl Exp $ /  Ingo <www.strelitzer.de>

  xPrioS, Open Source E-Commerce Solutions
  http://www.xprios.de

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class TableBox
{
    public       
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
    
    public function get_box($contents, $direct_output = false)
    {
        $tableBox_string = '<table '
        . 'border="' . tep_output_string($this->table_params['border']) . '" '
        . 'width="' . tep_output_string($this->table_params['width']) . '" '
        . 'cellspacing="' . tep_output_string($this->table_params['cellspacing']) . '" '
        . 'cellpadding="' . tep_output_string($this->table_params['cellpadding']) . '"';
        if (tep_not_null($this->table_params['parameters'])) {
            $tableBox_string .= ' ' . $this->table_params['parameters'];
        }
        $tableBox_string .= '>' . "\n";

        for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
            if (isset($contents[$i]['form']) && tep_not_null($contents[$i]['form'])) {
                $tableBox_string .= $contents[$i]['form'] . "\n";
            }
            $tableBox_string .= '    <tr';
            if (tep_not_null($this->table_params['row_parameters'])) {
                $tableBox_string .= ' ' . $this->table_row_parameters;
            }
            if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params'])) {
                $tableBox_string .= ' ' . $contents[$i]['params'];
            }
            $tableBox_string .= '>' . "\n";

            if (isset($contents[$i][0]) && is_array($contents[$i][0])) {
                for ($x=0, $n2=sizeof($contents[$i]); $x<$n2; $x++) {
                    if (isset($contents[$i][$x]['text']) && tep_not_null($contents[$i][$x]['text'])) {
                        $tableBox_string .= '        <td';
                        if (isset($contents[$i][$x]['align']) && tep_not_null($contents[$i][$x]['align'])) {
                            $tableBox_string .= ' align="' . tep_output_string($contents[$i][$x]['align']) . '"';
                        }
                        if (isset($contents[$i][$x]['params']) && tep_not_null($contents[$i][$x]['params'])) {
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
                    $tableBox_string .= ' align="' . tep_output_string($contents[$i]['align']) . '"';
                }
                if (isset($contents[$i]['params']) && tep_not_null($contents[$i]['params'])) {
                    $tableBox_string .= ' ' . $contents[$i]['params'];
                } elseif (tep_not_null($this->table_params['data_parameters'])) {
                    $tableBox_string .= ' ' . $this->table_params['data_parameters'];
                }
                $tableBox_string .= '>' . $contents[$i]['text'] . '</td>' . "\n";
            }

            $tableBox_string .= '  </tr>' . "\n";
            if (isset($contents[$i]['form']) && tep_not_null($contents[$i]['form'])) {
                $tableBox_string .= '</form>' . "\n";
            }
        }

        $tableBox_string .= '</table>' . "\n";
      
        if ($direct_output == true) {
            echo $tableBox_string;
            $return = true;
        } else {
            $return = $tableBox_string;
        }

        return $return;
    }
}
