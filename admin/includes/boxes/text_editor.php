<?php
/*
  $Id: text_editor.php,v 1.0 2005/07/05 Ingo <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/


  if (isset($text_editor_array) && is_array($text_editor_array)) {

?>
<!-- tools //-->
          <tr>
            <td>
<?php 
    $heading = [];
    $contents = [];

    $heading[] = array(
      'text' => 'Text Editor',
      'link' => tep_href_link(FILENAME_TEXT_EDITOR, 'selected_box=text_editor&text=main_page')
    );

    if ($_SESSION['selected_box'] == 'text_editor') {
    
      $links = '';
      $query = tep_db_query('select code, title from ' . TABLE_INFO_TEXTE . ' where languages_id = "' . intval($_SESSION['languages_id']) );
      $assoc = [];
      while ($result = tep_db_fetch_array($query)) {
        $assoc[$result['code']] = $result['title'];
      }
      for($i=0, $j=count($text_editor_array); $i<$j; $i++) {
        $links .= '<a href="' . tep_href_link(FILENAME_TEXT_EDITOR, 'text=' . $text_editor_array[$i]) . '" class="menuBoxContentLink">';
        $links .= isset($assoc[$text_editor_array[$i]]) && $assoc[$text_editor_array[$i]]!=''? $assoc[$text_editor_array[$i]]:'['.$text_editor_array[$i].']';
        $links .= '</a><br>';
      }

      $contents[] = array('text' => $links);
    }

    $box = new Box;
    echo $box->get_menu_box($heading, $contents);
?>
            </td>
          </tr>
<!-- tools_eof //-->
<?php
  }
?>
