<?php
/*
  $Id: affiliate_logout.php,v 1.3 2003/02/17 22:13:30 harley_vb Exp $

  OSC-Affiliate

  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2015 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/


  require('includes/application_top.php');

  $breadcrumb->add(NAVBAR_TITLE);

  require(DIR_WS_INCLUDES . 'meta.php');
  require(DIR_WS_INCLUDES . 'header.php');
  require(DIR_WS_INCLUDES . 'column_left.php');
  require(DIR_WS_INCLUDES . 'column_center.php');
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
<?php

  if (isset($_SESSION['affiliate_id'])) {
    unset($_SESSION['affiliate_id']);
    echo '            <td class="main">' . TEXT_INFORMATION_ERROR_1 . '</td>';
    
  } else { // if they weren't logged in but came to this page somehow
    echo '            <td class="main">' . TEXT_INFORMATION_ERROR_2 . '</td>';
  }
?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td align="right" class="main"><br /><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td>
      </tr>
    </table></td>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
