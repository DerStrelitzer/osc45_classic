<?php
/*
  $Id: column_center.php,v 1.00 2005/01/01 Ingo <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- body_text //-->
    <td width="100%" valign="top" height="100%" class="columncenter">
<?php

if (defined('HEADING_IMAGE_DISPLAY') && HEADING_IMAGE_DISPLAY=='ja') {
    if (isset($heading_image) && $heading_image!='' && is_file(DIR_FS_CATALOG . DIR_WS_IMAGES . 'desk/' . $heading_image)) {
        echo '      ' . tep_image(DIR_WS_IMAGES . 'desk/' . $heading_image, (defined('HEADING_TITLE'))? HEADING_TITLE:'', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT, 'class="pageicon"') . "\n";
    } elseif (isset($heading_image_tag) && $heading_image_tag!='') {
        echo '      ' . $heading_image_tag . "\n";
    }
} elseif (isset($heading_image_tag) && $heading_image_tag!='' && strpos($heading_image_tag, '<img')===false) {
    echo '      ' . $heading_image_tag . "\n";
}

if (defined('HEADING_TITLE') && HEADING_TITLE !='') {
    echo '      <h1 class="headingtitle">' . HEADING_TITLE . '</h1><br style="clear:both;" />' . "\n";
}

if (isset($this_content_form) && $this_content_form!='') {
    echo '      ' . $this_content_form . "\n";
}
?>
      <table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
/*
  if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
?>
  <tr class="headerError">
    <td class="headerError"><?php echo htmlspecialchars(urldecode($_GET['error_message']), ENT_QUOTES, CHARSET, false); ?></td>
  </tr>
<?php
  }

  if (isset($_GET['info_message']) && tep_not_null($_GET['info_message'])) {
?>
  <tr class="headerInfo">
    <td class="headerInfo"><?php echo htmlspecialchars($_GET['info_message'], ENT_QUOTES, CHARSET, false); ?></td>
  </tr>
<?php
  }
*/
