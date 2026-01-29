<?php
/*
  $Id: shopping_cart.php,v 1.18 2003/02/10 22:31:06 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2025 xPrioS
  Copyright (c) 2004 osCommerce

  Released under the GNU General Public License
*/

if (!@define('CURRENT_PAGE', basename(__FILE__))) {
    header('HTTP/1.1 404', true);
    exit('<h1>HTTP 404 - not found</h1>');
}

require ('includes/application_box.php');
  
$contents = [ 
    [
        [
            'params' => 'width="100%" class="infoBoxHeading"',
            'text' => $spider_flag || $_SESSION['cart']->count_contents() < 1 ? BOX_HEADING_SHOPPING_CART : '<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . BOX_HEADING_SHOPPING_CART . '</a>'
        ]
    ]
];
$box_heading = new TableBox;
$box_heading->set_param('cellpadding', 0);
$box_heading->get_box($contents, true);

$cart_contents_string = '';
if ($_SESSION['cart']->count_contents() > 0) {
    $cart_contents_string = '<table border="0" width="100%" cellspacing="0" cellpadding="0">';
    $products = $_SESSION['cart']->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
        $cart_contents_string .= '<tr><td align="right" valign="top" class="boxText">';

        if (isset($_SESSION['new_products_id_in_cart']) && $_SESSION['new_products_id_in_cart'] == $products[$i]['id']) {
            $cart_contents_string .= '<span class="newItemInCart">';
        } else {
            $cart_contents_string .= '<span class="infoBoxContents">';
        }

        $cart_contents_string .= $products[$i]['quantity'] . '&nbsp;x&nbsp;</span></td><td valign="top" class="boxText"><a href="' . ingo_product_link($products[$i]['id'], $products[$i]['name']) . '" class="infoboxcontentlink">';

        if (isset($_SESSION['new_products_id_in_cart']) && $_SESSION['new_products_id_in_cart'] == $products[$i]['id']) {
            unset($_SESSION['new_products_id_in_cart']);
            $cart_contents_string .= '<span class="newItemInCart">';
        } else {
            $cart_contents_string .= '<span class="infoBoxContents">';
        }

        $cart_contents_string .= htmlentities($products[$i]['name'], ENT_NOQUOTES, CHARSET) . '</span></a></td></tr>';

    }
    $cart_contents_string .= '</table>';
} else {
    $cart_contents_string .= BOX_SHOPPING_CART_EMPTY;
}

$info_box_contents = [];
$info_box_contents[] = ['text' => $cart_contents_string];

if ($_SESSION['cart']->count_contents() > 0) {
    $info_box_contents[] = ['text' => tep_draw_separator()];
    $info_box_contents[] = [
        'align' => 'right',
        'text'  => $currencies->format($_SESSION['cart']->show_total())
    ];
}

new info_box($info_box_contents);
  
tep_session_close();
