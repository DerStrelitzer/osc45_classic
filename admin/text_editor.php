<?php
  /*
  $Id: text_editor.php,v 1.0 2005/07/05 Ingo <www.strelitzer.de>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

  require('includes/application_top.php');
  
  $text_editor_array = array('main_page', 'about_us', 'conditions', 'checkout_conditions', 'impressum', 'privacy', 'shipping', 'widerruf');

  $text_id = '0';
  $text = xprios_prepare_get('text');

  if (in_array($text, $text_editor_array)) {
    if (isset($_GET['action']) && $_GET['action']=='process') {
      $text_code  = xprios_prepare_post('code');
      $text_title = xprios_prepare_post('title');
      $text_text  = xprios_prepare_post('text', true);
      $text_id    = max(0, intval(xprios_prepare_post('text_id')));
      if ($text_id>0) {
        tep_db_query("UPDATE " . TABLE_INFO_TEXTE . " SET title = '" . tep_db_input($text_title) . "', text = '" . tep_db_input($text_text) . "' WHERE id = '" . $text_id . "'");
      } else {
        tep_db_query("INSERT INTO " . TABLE_INFO_TEXTE . " (languages_id, code, title, text) VALUES ('" . (int)$_SESSION['languages_id'] . "', '" . tep_db_input($text_code) . "', '" . tep_db_input($text_title) . "', '" . tep_db_input($text_text) . "')");
        $text_id = tep_db_insert_id();
      }
    } else {
      $text_query = tep_db_query("SELECT id, code, title, text FROM " . TABLE_INFO_TEXTE . " WHERE code = '" . tep_db_input($text) . "' AND languages_id = '" . (int)$_SESSION['languages_id'] . "'");
      if ($text_result = tep_db_fetch_array($text_query)) {
        $text_id    = $text_result['id'];
        $text_text  = $text_result['text'];
        $text_code  =  $text_result['code'];
        $text_title = $text_result['title'];
      } else {
        $text_id    = 0;
        $text_text  = '';
        $text_code  = $text;
        $text_title = '';
      }
    }
  } else {
    tep_redirect(tep_href_link(FILENAME_TEXT_EDITOR, 'selected_box=text_editor&text=' . $text_editor_arry[0]));
  }

?><!DOCTYPE html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script type="text/javascript" src="ckeditor_4/ckeditor.js" charset="utf-8"></script>
</head>
<body>
<?php 
  require(DIR_WS_INCLUDES . 'header.php'); 
?>
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<?php 
  require(DIR_WS_INCLUDES . 'column_left.php'); 
?>
    <td valign="top" width="90%">
      <form name="infotext" method="post" action="<?php echo basename($PHP_SELF) . '?text=' . $text . '&action=process'; ?>">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td class="main"><?php echo ($text_title!=''?$text_title:'<b>&lt;</b>'.$text_code.'<b>&gt;</b>'); ?></td>
          </td>
          <tr>
            <td class="main"><?php echo '<b>' . 'Seiten&uuml;berschrift' . '</b>&nbsp;' . tep_draw_input_field('title', (isset($text_title)?$text_title:''), 'size="64" style="font:bold 20px/1 arial,verdana,sans-serif"'); ?></td>
          </tr>
          <tr>
            <td class="smallText" width="100%">
              <textarea cols="80" id="text" name="text" rows="10"><?php echo $text_text; ?></textarea>
            </td>
          </tr>
          <tr>
            <td align="center"><br><input type="submit" value="<?php echo IMAGE_SAVE; ?>" style="width:100px"</td>
          </tr>
        </table>
        <script type="text/javascript">
<?php
        $ck_height = defined('CK_EDITOR_HEIGHT') ? CK_EDITOR_HEIGHT : 200;
        if ($ck_height<1 || $ck_height>1300) $ck_height=200;
?>
        CKEDITOR.replace( 'text' );
        CKEDITOR.config['height']   = <?php echo $ck_height; ?>;
        </script>
<?php 
  echo '        ' . tep_draw_hidden_field('text_id', $text_id) . tep_draw_hidden_field('code', $text_code) . "\n";
?>
      </form>
    </td>
  </tr>
</table>
<!-- body_eof //-->

<?php 
  require(DIR_WS_INCLUDES . 'footer.php'); 
?>
</body>
</html>
<?php 
  require(DIR_WS_INCLUDES . 'application_bottom.php'); 
?>