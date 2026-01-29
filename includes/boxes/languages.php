<?php
/*
  $Id: languages.php,v 1.15 2003/06/09 22:10:48 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if (!isset($lng) || (isset($lng) && !is_object($lng))) {
    $lng = new Language;
}

if (sizeof($lng->catalog_languages)>1) {

?>
<!-- languages //-->
          <tr>
            <td>
<?php
$contents = [ 
    [
        [
            'params' => 'width="100%" class="infoBoxHeading"',
            'text' => BOX_HEADING_LANGUAGES
        ]
    ]
];
$box_heading = new TableBox;
$box_heading->set_param('cellpadding', 0);
$box_heading->get_box($contents, true);

$languages_string = '';
foreach ($lng->catalog_languages as $key => $value) {
    $languages_string .= ' <a href="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('language', 'currency')) . 'language=' . $key, $request_type) . '">' . tep_image(DIR_WS_LANGUAGES .  $value['directory'] . '/images/' . $value['image'], $value['name']) . '</a> ';
}

$info_box_contents = [
    [
        'align' => 'center',
        'text'  => $languages_string
    ]
];
new InfoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- languages_eof //-->
<?php
}
