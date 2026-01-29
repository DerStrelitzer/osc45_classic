<?php
/*
  $Id: info.php, 

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- information //-->
          <div class="divibox" style="width:<?php echo BOX_WIDTH; ?>px">
            <div class="diviboxheading"><?php echo BOX_HEADING_INFORMATION; ?></div>
            <div class="diviboxcontent listbox"><?php
     echo '<a href="' . tep_href_link(FILENAME_SHIPPING) . '">' . BOX_INFORMATION_SHIPPING . '</a>' . "\n"
        . '<a href="' . tep_href_link(FILENAME_WIDERRUF) . '">' . BOX_WIDERRUF . '</a>' . "\n"
        . '<a href="' . tep_href_link(FILENAME_PRIVACY) . '">' . BOX_INFORMATION_PRIVACY . '</a>' . "\n"
        . '<a href="' . tep_href_link(FILENAME_CONDITIONS) . '">' . BOX_INFORMATION_CONDITIONS . '</a>' . "\n"
        . '<a href="' . tep_href_link(FILENAME_CONTACT_US) . '">' . BOX_INFORMATION_CONTACT . '</a>' . "\n"
        . '<a href="' . tep_href_link(FILENAME_IMPRESSUM) . '">' . BOX_INFORMATION_IMPRESSUM . '</a>' . "\n"
        . '<a href="' . tep_href_link(FILENAME_ABOUT_US) . '">' . BOX_INFORMATION_ABOUT_US . '</a>' . "\n"
        . '';
            ?></div>
          </div>
<!-- information_eof //-->
