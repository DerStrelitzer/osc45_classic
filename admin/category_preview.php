<?php
/*
  $Id: category_preview.php,v 1.0 2005/12/19 by Ingo

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

  $content = $_POST['categories_description'][$_GET['language']];

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title>Preview of Categories Description by Ingo</title>
<base href="<?php echo HTTP_CATALOG_SERVER . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<style type="text/css">
body {margin:2px;background:#fff;text-align:left;}
</style>
</head>
<body>
<?php echo $content . "\n"; ?>
<hr size="1">
<div style="text-align:center;padding:2px;">
<a href="javascript:window.close();"><?php echo tep_image_button('button_back.gif', IMAGE_BACK); ?></a>
</div>
</body>
</html>