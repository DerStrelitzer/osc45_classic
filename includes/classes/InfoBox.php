<?php
/*
  $Id: info_box.php,v 1.33 2003/06/09 22:22:50 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

class InfoBox extends TableBox 
{

    public function __construct($contents)
    {
        $info_box_contents = array(array('text' => $this->infoBoxContents($contents)));
        $this->set_param('cellpadding', 1);
        $this->set_param('parameters', 'class="infoBox"');
        $this->get_box($info_box_contents, true);
    }

    private function infoBoxContents($contents)
    {
        $this->set_param('cellpadding', 3);
        $this->set_param('parameters', 'class="infoBoxContents"');
        $info_box_contents = [];
        $info_box_contents[] = [
            ['text' => tep_draw_separator('pixel_trans.gif', '100%', '1')]
        ];
        for ($i=0, $n=sizeof($contents); $i<$n; $i++) {
            $info_box_contents[] = [
                [
                    'align'  => isset($contents[$i]['align']) ? $contents[$i]['align'] : '',
                    'form'   => isset($contents[$i]['form']) ? $contents[$i]['form'] : '',
                    'params' => 'class="boxText"',
                    'text'   => isset($contents[$i]['text']) ? $contents[$i]['text'] : ''
                ]
            ];
        }
        $info_box_contents[] = [
            ['text' => tep_draw_separator('pixel_trans.gif', '100%', '1')]
        ];
        return $this->get_box($info_box_contents);
    }

}
