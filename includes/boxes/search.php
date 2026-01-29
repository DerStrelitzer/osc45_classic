<?php
/*
  $Id: search.php,v 1.22 2003/02/10 22:31:05 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- search //-->
          <tr>
            <td>
<?php
  
  $contents = array( 
    array(
      array(
        'params' => 'width="100%" class="infoBoxHeading"',
        'text' => BOX_HEADING_SEARCH
      )
    )
  );
  $box_heading = new TableBox;
  $box_heading->set_param('cellpadding', 0);
  $box_heading->get_box($contents, true);
  
  $info_box_contents = array( 
    array(
      'form' => tep_draw_form('quick_find', tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT, '', 'NONSSL', false), 'get'),
      'align' => 'center',
      'text' => tep_draw_input_field('keywords', '', 'maxlength="30" style="width:' . (BOX_WIDTH-60) . 'px;"') . '&nbsp;' . tep_hide_session_id() . tep_image_submit('button_quick_find.gif', BOX_HEADING_SEARCH) . '<br />' . 
                BOX_SEARCH_TEXT . '<br />' .
                '<a href="' . tep_href_link(FILENAME_ADVANCED_SEARCH) . '"><b>' . BOX_SEARCH_ADVANCED_SEARCH . '</b></a>'
    )
  );
  new InfoBox($info_box_contents);
  
  
?>
            </td>
          </tr>
<!-- search_eof //-->

