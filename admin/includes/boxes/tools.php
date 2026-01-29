<?php
/*
  $Id: tools.php,v 1.21 2003/07/09 01:18:53 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- tools //-->
          <tr>
            <td>
<?php
  $heading = [];
  $contents = [];

  $heading[] = array(
    'text' => BOX_HEADING_TOOLS,
    'link' => tep_href_link(FILENAME_BACKUP, 'selected_box=tools')
  );

  if ($_SESSION['selected_box'] == 'tools') {
    $contents[] = array(
      'text'  => '<a href="' . tep_href_link(FILENAME_BACKUP) . '" class="menuBoxContentLink">' . BOX_TOOLS_BACKUP . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER) . '" class="menuBoxContentLink">' . BOX_TOOLS_BANNER_MANAGER . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_CACHE) . '" class="menuBoxContentLink">' . BOX_TOOLS_CACHE . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_DEFINE_LANGUAGE) . '" class="menuBoxContentLink">' . BOX_TOOLS_DEFINE_LANGUAGE . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_FILE_MANAGER) . '" class="menuBoxContentLink">' . BOX_TOOLS_FILE_MANAGER . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_MAIL) . '" class="menuBoxContentLink">' . BOX_TOOLS_MAIL . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_NEWSLETTERS) . '" class="menuBoxContentLink">' . BOX_TOOLS_NEWSLETTER_MANAGER . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_SERVER_INFO) . '" class="menuBoxContentLink">' . BOX_TOOLS_SERVER_INFO . '</a><br>' .
                 '<a href="' . tep_href_link(FILENAME_WHOS_ONLINE) . '" class="menuBoxContentLink">' . BOX_TOOLS_WHOS_ONLINE . '</a><br>'
    );

    if (!isset($_SESSION['phpma_dir'])) {
      $_SESSION['phpma_dir'] = '';
      $exclude_array = array('.', '..', 'tmp', 'cvs', 'backups', 'includes', 'images', 'editor');
      $dir = @dir(DIR_FS_ADMIN);
      while (false !== ($file = $dir->read())) {
        if (!in_array($file, $exclude_array) && @is_dir(DIR_FS_ADMIN . '/' . $file) && @is_file(DIR_FS_ADMIN . '/' . $file . '/libraries/common.lib.php')) {
          $_SESSION['phpma_dir'] = $file . '/';
          break;
        }
      }
    }
    if ($_SESSION['phpma_dir']!='') {
      $contents[]['text'] .= '<a href="' . $_SESSION['phpma_dir'] . '" class="menuBoxContentLink" target="_blank">PHPMyAdmin</a>';
    }

  }

  $box = new Box;
  echo $box->get_menu_box($heading, $contents);
?>
            </td>
          </tr>
<!-- tools_eof //-->
