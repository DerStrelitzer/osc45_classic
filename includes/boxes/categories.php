<?php
/*
  $Id: categories.php,v 1.0 by Ingo http://forums.oscommerce.de/index.php?showuser=36

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2005 osCommerce

  Released under the GNU General Public License
*/

function tep_show_category($counter)
{
    global $tree, $categories_string, $cPath_array, $cPath;

    if (is_array($cPath_array) && in_array($counter, $cPath_array)) {
        $cPath_activ = ' activ';
    } else {
        $cPath_activ = ' passiv';
    }

    $categories_string .= '<a class="infoboxcontentlink' . $cPath_activ .'" href="';

    if (defined('LINKS_USE_REWRITE') && LINKS_USE_REWRITE=='ja') {
        if ($tree[$counter]['parent'] == 0) {
            $rewrite_param = ingo_category_link($counter);
        } else {
            $rewrite_param = ingo_category_link($tree[$counter]['path']);
        }
        $categories_string .= $rewrite_param;
    } else {
        if ($tree[$counter]['parent'] == 0) {
            $cPath_new = 'cPath=' . ingo_make_link($counter, 'c', $tree[$counter]['name']);
        } else {
            $cPath_new = 'cPath=' . ingo_make_link($tree[$counter]['path'], 'c', $tree[$counter]['name']);
        }
        $categories_string .= tep_href_link(FILENAME_DEFAULT, $cPath_new);
    }

    $categories_string .= '">';

    if ($tree[$counter]['level']>0) {
        $deep = [];
        for ($i=$tree[$counter]['level']; $i>0; $i--) {
            $current_id = $counter;
            $deep[$i] = 1;
            $abbruch = false;
            while (!$abbruch) {
                if ($tree[$current_id]['next_id']===false || $tree[$tree[$current_id]['next_id']]['level'] < $i) {
                    $abbruch = true; 
                    break;
                }
                if ($tree[$tree[$current_id]['next_id']]['level']==$i) {
                    $deep[$i] = 0;
                }
                $current_id = $tree[$current_id]['next_id'];
            }
        }

        $last_main = true;
        if ($tree[$counter]['next_id']) {
            $current_next_id = $tree[$counter]['next_id'];
            while ($tree[$current_next_id]['next_id']!='') {
                if ($tree[$tree[$current_next_id]['next_id']]['level'] == $tree[$current_next_id]['level']) {
                    $last_main = false;
                }
                $current_next_id = $tree[$current_next_id]['next_id'];
            }
        }

        if ($tree[$counter]['level']>1) {
            for ($i=1; $i<$tree[$counter]['level']; $i++) {
                if ($deep[$i]==0) {
                    $categories_string .= tep_image(DIR_WS_IMAGES . 'infobox/category_sub_sub.gif');
                } else {
                    $categories_string .= tep_image(DIR_WS_IMAGES . 'pixel_trans.gif', '', '5', '1');
                }
            }
        }
        if ($tree[$counter]['next_id']=='' || $tree[$counter]['level'] > $tree[$tree[$counter]['next_id']]['level']) {
            $categories_string .= tep_image(DIR_WS_IMAGES . 'infobox/category_sub_last.gif');
        } else {
            if ($tree[$counter]['level'] < $tree[$tree[$counter]['next_id']]['level']) {
                if ($deep[$tree[$counter]['level']]==0) {
                    $categories_string .= tep_image(DIR_WS_IMAGES . 'infobox/category_sub_more_have_more.gif');
                } else {
                    $categories_string .= tep_image(DIR_WS_IMAGES . 'infobox/category_sub_more_have_last.gif');
                }
            } else {
                $categories_string .= tep_image(DIR_WS_IMAGES . 'infobox/category_sub_more_nohave.gif');
            }
        }
    }

// display category name
    $categories_string .= str_replace(' ', '&nbsp;', $tree[$counter]['name']);

    $categories_string .= "</a>";

    if ($tree[$counter]['next_id'] != false) {
        tep_show_category($tree[$counter]['next_id']);
    }
}
?>
<!-- categories //-->
<!-- style by Ingo http://forums.oscommerce.de/index.php?showuser=36 //-->
          <tr>
            <td>
<?php

$contents = [ 
    [
        [
            'params' => 'width="100%" class="infoBoxHeading"',
            'text' => '<a href="' . tep_href_link(FILENAME_SITEMAP) . '">' . BOX_HEADING_CATEGORIES . '</a>'
        ]
    ]
];
$box_heading = new TableBox;
$box_heading->set_param('cellpadding', 0);
$box_heading->get_box($contents, true);

$categories_string = '';
$tree = [];
$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id, c.categories_image from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '0' and c.categories_id = cd.categories_id and cd.language_id='" . (int)$GLOBALS['languages_id'] ."' order by sort_order, cd.categories_name");
while ($categories = tep_db_fetch_array($categories_query)) {
    $tree[$categories['categories_id']] = [
        'name' => $categories['categories_name'],
        'image' => $categories['categories_image'],
        'parent' => $categories['parent_id'],
        'level' => 0,
        'path' => $categories['categories_id'],
        'next_id' => false
    ];
    if (isset($parent_id)) {
        $tree[$parent_id]['next_id'] = $categories['categories_id'];
    }

    $parent_id = $categories['categories_id'];

    if (!isset($first_element)) {
        $first_element = $categories['categories_id'];
    }
}

  //------------------------
if (tep_not_null($cPath)) {
    $new_path = '';
    foreach ($cPath_array as $key => $value) {
        unset($parent_id);
        unset($first_id);
        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id, c.categories_image from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$value . "' and c.categories_id = cd.categories_id and cd.language_id='" . (int)$GLOBALS['languages_id'] ."' order by sort_order, cd.categories_name");
        if (tep_db_num_rows($categories_query)) {
            $new_path .= $value;
            while ($row = tep_db_fetch_array($categories_query)) {
                $tree[$row['categories_id']] = [
                    'name' => $row['categories_name'],
                    'image' => $row['categories_image'],
                    'parent' => $row['parent_id'],
                    'level' => $key+1,
                    'path' => $new_path . '_' . $row['categories_id'],
                    'next_id' => false,
                    'image' => $row['categories_image']
                ];

                if (isset($parent_id)) {
                    $tree[$parent_id]['next_id'] = $row['categories_id'];
                }

                $parent_id = $row['categories_id'];

                if (!isset($first_id)) {
                    $first_id = $row['categories_id'];
                }

                $last_id = $row['categories_id'];
            }
            $tree[$last_id]['next_id'] = $tree[$value]['next_id'];
            $tree[$value]['next_id'] = $first_id;
            $new_path .= '_';
        } else {
            break;
        }
    }
}

tep_show_category($first_element);

$categories_string .= tep_draw_separator('pixel_trans.gif', '5', '5');
$categories_string .= '<a href="' . tep_href_link(FILENAME_ALL_PRODUCTS) . '" class="infoboxcontentlink">' . ALL_PRODUCTS_LINK . "</a>";
$categories_string .= '<a href="' . tep_href_link(FILENAME_SPECIALS) . '" class="infoboxcontentlink">' . BOX_HEADING_SPECIALS . "</a>";
$categories_string .= '<a href="' . tep_href_link(FILENAME_PRODUCTS_NEW) . '" class="infoboxcontentlink">' . BOX_HEADING_WHATS_NEW . "</a>";

$info_box_contents = [
    [
        'text' => $categories_string
    ]
];
new InfoBox($info_box_contents);

?>
            </td>
          </tr>
<!-- categories_eof //-->