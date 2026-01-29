<?php 
/*
  $Id: meta.php, Ingo $

  xPrioS, Open Source E-Commerce Solutions
  http://www.xprios.de

  Copyright (c) 2026 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

if (!(isset($page_title) && $page_title != '')) {
    if (defined('HEADING_TITLE')) {
        $page_title = html_entity_decode(HEADING_TITLE, ENT_QUOTES, CHARSET);
    } else {
        $page_title = $default_title;
    }
}
if (!(isset($page_keywords) && $page_keywords != '')) {
    $page_keywords = $default_keywords;
}
if (!(isset($page_description) && $page_description != '')) {
    $page_description = $default_description;
}
$page_title = strip_tags($page_title);
$page_keywords = tep_output_string(preg_replace('/\\s+/' , ' ', $page_keywords));
$page_description = tep_output_string(preg_replace('/\\s+/' , ' ', $page_description));

/*
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html <?php echo HTML_PARAMS; ?> xmlns="http://www.w3.org/1999/xhtml">

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html <?php echo HTML_PARAMS; ?> xmlns="http://www.w3.org/1999/xhtml">
*/
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html <?php echo HTML_PARAMS; ?> xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=<?php echo CHARSET; ?>" />
<meta http-equiv="content-language" content="<?php echo $language_code; ?>" />
<title><?php echo $page_title; ?></title>
<meta name="date" content="<?php echo date('Y-m-d', (time()-1209600)); ?>" />
<meta name="keywords" content="<?php echo $page_keywords; ?>" />
<meta name="description" content="<?php echo $page_description; ?>" />
<meta name="rating" content="general" />
<meta name="distribution" content="global" />
<meta name="resource-type" content="document" />
<meta name="robots" content="index,follow" />
<meta name="allow-search" content="yes" />
<meta name="generator" content="xPrioS - osc45" />
<meta name="organization" content="<?php echo STORE_NAME; ?>" />
<meta name="author" content="<?php echo $_SERVER['SERVER_NAME'] . ' - ' . STORE_OWNER; ?>" />
<meta name="publisher" content="<?php echo $_SERVER['SERVER_NAME'] . ' - ' . STORE_OWNER; ?>" />
<meta name="copyright" content="(c) <?php echo date('Y'). ' by ' . STORE_OWNER; ?>" />
<meta name="revisit-after" content="30 days" />
<base href="<?php echo ($request_type == 'SSL' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>" />
<?php
if (substr(basename($PHP_SELF),0,5)!= 'popup') {
    echo '<link rel="stylesheet" type="text/css" href="stylesheet.php" />' . "\n";
}
if (isset($this_head_include) && $this_head_include!='') {
    echo $this_head_include . "\n";
}
if (isset($this_head_file) && substr($this_head_file,-3)=='php' && is_file($this_head_file)) {
    require($this_head_file);
}
?>
</head>
<body>
