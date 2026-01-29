<?php
/*
  $Id: imagecheck.php,v 1.2 2002/12/10 16:01:04 crawford Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License

  version 1.0 by Mike Crawford (http://www.nothinbutprints.com)
  version 1.1 by Mattice (http://www.matthijs.org)
  version 1.2 by Gareth Evans (gevans@csctoronto.com)

*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

  require('includes/application_top.php');

  // Change this if needed
  $path = DIR_FS_CATALOG . DIR_WS_IMAGES; //fileserver path (/usr/local/WWW/etc..)
  $ws_path = HTTP_SERVER . DIR_WS_ADMIN . FILENAME_CATEGORIES;   // webserver path to admin categories.php (http://www.etc...)

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body>
<?php 
  require(DIR_WS_INCLUDES . 'header.php'); 
?>

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<?php 
  require(DIR_WS_INCLUDES . 'column_left.php'); 
?>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
          <tr>
            <td class="main" colspan="2"><br><?php echo TEXT_CREDITS; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td>
        <br>
        <table border="0" size="80%" align="left">
          <tr>
           <td><font face="verdana,tahoma,arial" size="1" color="#666666">

<?php
  // Report to user what we're actually doing
  echo TEXT_DIR_LOCATION . '<b>' . $path . '</b><br>' . TEXT_DIR_ERROR . '</font></td></tr><td><font face="verdana,tahoma,arial" size="1" color="666666">';

  $imagecheck_query = tep_db_query("select p.products_id, p.products_model, p.products_image, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd  where p.products_id = pd.products_id and pd.language_id = 1 order by products_id");
  $imagenumber = (tep_db_num_rows($imagecheck_query));
  $misstotal = 1;
  $list = '';
  while ($imagecheck_values = tep_db_fetch_array($imagecheck_query)){
    if (!file_exists($path . $imagecheck_values['products_image'])){
      $list .= '<dl><dt><b>' . TEXT_MISSING_TOTAL . $misstotal . '</b>';
      $list .= '<dd>' . TEXT_MISSING_NAME . $imagecheck_values['products_name'];
      $list .='<dd>' . TEXT_MISSING_MODEL . $imagecheck_values['products_model'];
      $list .='<dd>' . TEXT_MISSING_LOCATION . $path . $imagecheck_values['products_image'];
      $list .='<dd>' . TEXT_MISSING_REPAIR . '<a href="' . $ws_path . '?pID=' . $imagecheck_values['products_id'] . '&action=new_product">';
      $list .= $ws_path . '?pID=' . $imagecheck_values['products_id'] . '&action=new_product</a>';
      $list .='</dl><p>';
      $misstotal ++;
      }
    }

  echo '</td></tr><tr><td><font face="verdana,tahoma,arial" size="1" color="666666">';
  print TEXT_COUNT_MISSING . ($misstotal-1) . ' images.<br>';
  print $list;
  print $imagenumber . TEXT_COUNT_COMPLETE . '</td></tr></table></body></html>';
?>
           </td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
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