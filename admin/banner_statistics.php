<?php
/*
  $Id: banner_statistics.php,v 1.5 2003/06/20 00:30:15 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require('includes/application_top.php');

$type = isset($_GET['type']) ? $_GET['type'] : '';
$page = $_GET['page'] ?? 1;
$bID  = intval($_GET['bID'] ?? '1');

$banner_extension = tep_banner_image_extension();

// check if the graphs directory exists
$dir_ok = false;
if (function_exists('imagecreate') && tep_not_null($banner_extension)) {
    if (is_dir(DIR_WS_IMAGES . 'graphs')) {
        if (is_writeable(DIR_WS_IMAGES . 'graphs')) {
            $dir_ok = true;
        } else {
            $messageStack->add(ERROR_GRAPHS_DIRECTORY_NOT_WRITEABLE, 'error');
        }
    } else {
        $messageStack->add(ERROR_GRAPHS_DIRECTORY_DOES_NOT_EXIST, 'error');
    }
}

$banner_query = tep_db_query("select banners_title from " . TABLE_BANNERS . " where banners_id = '" . $bID . "'");
$banner = tep_db_fetch_array($banner_query);

$years_array = [];
$years_query = tep_db_query("select distinct year(banners_history_date) as banner_year from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . $bID . "'");
while ($years = tep_db_fetch_array($years_query)) {
    $years_array[] = [
        'id'   => $years['banner_year'],
        'text' => $years['banner_year']
    ];
}

$months_array = [];
for ($i=1; $i<13; $i++) {
    $months_array[] = [
        'id'   => $i,
        'text' => xprios_date_month_name($i)
    ];
}

$type_array = [
    [
        'id'   => 'daily',
        'text' => STATISTICS_TYPE_DAILY
    ],
    [
        'id'   => 'monthly',
        'text' => STATISTICS_TYPE_MONTHLY
    ],
    [
        'id'   => 'yearly',
        'text' => STATISTICS_TYPE_YEARLY
    ]
];
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
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr><?php echo tep_draw_form('year', FILENAME_BANNER_STATISTICS, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', '10', 1); ?></td>
            <td class="main" align="right"><?php echo TITLE_TYPE . ' ' . tep_draw_pull_down_menu('type', $type_array, (tep_not_null($type) ? $type : 'daily'), 'onChange="this.form.submit();"'); ?><noscript><input type="submit" value="GO" /></noscript><br>
<?php
  switch ($type) {
    case 'yearly': break;
    case 'monthly':
      echo TITLE_YEAR . ' ' . tep_draw_pull_down_menu('year', $years_array, (isset($_GET['year']) ? $_GET['year'] : date('Y')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO" /></noscript>';
      break;
    default:
    case 'daily':
      echo TITLE_MONTH . ' ' . tep_draw_pull_down_menu('month', $months_array, (isset($_GET['month']) ? $_GET['month'] : date('n')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO" /></noscript><br>' . TITLE_YEAR . ' ' . tep_draw_pull_down_menu('year', $years_array, (isset($_GET['year']) ? $_GET['year'] : date('Y')), 'onChange="this.form.submit();"') . '<noscript><input type="submit" value="GO" /></noscript>';
      break;
  }
?>
            </td>
          <?php echo tep_draw_hidden_field('page', $page) . ($bID > 0 ? tep_draw_hidden_field('bID', $bID) : ''); ?></form></tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td align="center">
<?php
  if (function_exists('imagecreate') && ($dir_ok == true) && tep_not_null($banner_extension)) {
    $banner_id = (int)$_GET['bID'];

    switch ($type) {
      case 'yearly':
        include(DIR_WS_INCLUDES . 'graphs/banner_yearly.php');
        echo tep_image(DIR_WS_IMAGES . 'graphs/banner_yearly-' . $banner_id . '.' . $banner_extension);
        break;
      case 'monthly':
        include(DIR_WS_INCLUDES . 'graphs/banner_monthly.php');
        echo tep_image(DIR_WS_IMAGES . 'graphs/banner_monthly-' . $banner_id . '.' . $banner_extension);
        break;
      default:
      case 'daily':
        include(DIR_WS_INCLUDES . 'graphs/banner_daily.php');
        echo tep_image(DIR_WS_IMAGES . 'graphs/banner_daily-' . $banner_id . '.' . $banner_extension);
        break;
    }
?>
          <table border="0" width="600" cellspacing="0" cellpadding="2">
            <tr class="dataTableHeadingRow">
             <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SOURCE; ?></td>
             <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_VIEWS; ?></td>
             <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_CLICKS; ?></td>
           </tr>
<?php
    for ($i=0, $n=sizeof($stats); $i<$n; $i++) {
      echo '            <tr class="dataTableRow">' . "\n" .
           '              <td class="dataTableContent">' . $stats[$i][0] . '</td>' . "\n" .
           '              <td class="dataTableContent" align="right">' . number_format($stats[$i][1]) . '</td>' . "\n" .
           '              <td class="dataTableContent" align="right">' . number_format($stats[$i][2]) . '</td>' . "\n" .
           '            </tr>' . "\n";
    }
?>
          </table>
<?php
  } elseif ($bID > 0) {
    include(DIR_WS_FUNCTIONS . 'html_graphs.php');

    switch ($type) {
      case 'yearly':
        echo tep_banner_graph_yearly($_GET['bID']);
        break;
      case 'monthly':
        echo tep_banner_graph_monthly($_GET['bID']);
        break;
      default:
      case 'daily':
        echo tep_banner_graph_daily($_GET['bID']);
        break;
    }
  } else {
      echo 'Select Banner!';
  }
?>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_BANNER_MANAGER, 'page=' . $page . ($bID > 0 ? '&bID=' . $bID : '')) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
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
