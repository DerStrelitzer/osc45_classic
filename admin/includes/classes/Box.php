<?php
/*
  $Id: Box.php,v 1.7 2003/06/20 16:23:08 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License

  Example usage:

  $heading = [];
  $heading[] = array('params' => 'class="menuBoxHeading"',
                     'text'  => BOX_HEADING_TOOLS,
                     'link'  => tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('selected_box')) . 'selected_box=tools'));

  $contents = [];
  $contents[] = array('text'  => SOME_TEXT);

  $box = new Box;
  echo $box->get_info_box($heading, $contents);
*/

class Box extends TableBlock 
{
    public
        $heading = [],
        $contents = [];
  
    public function __construct()
    {
    }

    public function get_info_box($heading, $contents)
    {
        $this->set_param('row_parameters', 'class="infoBoxHeading"');
        $this->set_param('data_parameters', 'class="infoBoxHeading"');
        $this->heading = $this->get_block($heading);

        $this->set_param('row_parameters', '');
        $this->set_param('data_parameters', 'class="infoBoxContent"');
        $this->contents = $this->get_block($contents);

        return $this->heading . $this->contents;
    }

    public function get_menu_box($heading, $contents)
    {
        if (isset($heading[0]['link'])) {
            $this->set_param('data_parameters', 'class="menuBoxHeading" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . $heading[0]['link'] . '\'"');
            $heading[0]['text'] = '&nbsp;<a href="' . $heading[0]['link'] . '" class="menuBoxHeadingLink">' . $heading[0]['text'] . '</a>&nbsp;';
        } else {
            $this->set_param('data_parameters', 'class="menuBoxHeading"');
            $heading[0]['text'] = '&nbsp;' . $heading[0]['text'] . '&nbsp;';
        }
        $this->heading = $this->get_block($heading);

        $this->set_param('data_parameters', 'class="menuBoxContent"');
        $this->contents = $this->get_block($contents);

        return $this->heading . $this->contents;
    }
}
