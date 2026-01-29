<?php
/*
  $Id: tell_a_friend.php,v 1.16 2003/06/10 18:26:33 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- tell_a_friend //-->
          <tr>
            <td>
<?php
  $contents = array( 
    array(
      array(
        'params' => 'width="100%" class="infoBoxHeading"',
        'text' => BOX_HEADING_TELL_A_FRIEND
      )
    )
  );
  $box_heading = new TableBox;
  $box_heading->set_param('cellpadding', 0);
  $box_heading->get_box($contents, true);

  $info_box_contents = array( 
    array(
      'form'  => tep_draw_form('tell_a_friend', tep_href_link(FILENAME_TELL_A_FRIEND, '', 'NONSSL', false), 'get'),
      'align' => 'center',
      'text'  => tep_draw_input_field('to_email_address', '', 'size="10"') . '&nbsp;' . tep_image_submit('button_tell_a_friend.gif', BOX_HEADING_TELL_A_FRIEND) . tep_draw_hidden_field('products_id', $_GET['products_id']) . tep_hide_session_id() . '<br />' . sprintf(BOX_TELL_A_FRIEND_TEXT , basename($PHP_SELF)==FILENAME_PRODUCT_INFO ? $product_info['products_name']: tep_get_products_name($_GET['products_id'])) 
   )
  );

  new InfoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- tell_a_friend_eof //-->

