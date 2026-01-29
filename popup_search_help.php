<?php
/*
  $Id: popup_search_help.php,v 1.4 2003/06/05 23:26:23 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}
require('includes/application_top.php');

if (isset($_SESSION['navigation']) && is_object($_SESSION['navigation'])) {
    $_SESSION['navigation']->remove_current_page();
}

$this_head_include = '<link rel="stylesheet" type="text/css" href="stylesheet.php" />';
require('includes/meta.php');

$contents = [ 
    [
        [
            'params' => 'width="100%" class="infoBoxHeading"',
            'text' => HEADING_SEARCH_HELP
        ]
    ]
];
$box_heading = new TableBox;
$box_heading->set_param('cellpadding', 0);
$box_heading->get_box($contents, true);


$info_box_contents = [['text' => TEXT_SEARCH_HELP]];
new InfoBox($info_box_contents);

?>
<p class="smallText" align="right"><?php echo '<a href="javascript:window.close()">' . TEXT_CLOSE_WINDOW . '</a>'; ?></p>
</body>
</html>
<?php 
require('includes/application_bottom.php');
