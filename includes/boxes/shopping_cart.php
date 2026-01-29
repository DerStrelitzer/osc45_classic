<?php
/*
  $Id: shopping_cart.php,v 1.18 2003/02/10 22:31:06 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/
?>
<!-- shopping_cart //-->
          <tr>
            <td>
<div id="shoppingcart">
<?php
if ($spider_flag || $cart->count_contents()==0) {
    $heading = BOX_HEADING_SHOPPING_CART;
} else {
    $heading = '<a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . BOX_HEADING_SHOPPING_CART . '</a>';
}
$contents = [ 
    [
        [
            'params' => 'width="100%" class="infoBoxHeading"',
            'text'   => $heading
        ]
    ]
];

$box_heading = new TableBox;
$box_heading->set_param('cellpadding', 0);
$box_heading->get_box($contents, true);

$cart_contents_string = '';
if (!$spider_flag && $cart->count_contents() > 0) {
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

        $cart_contents_string .= $products[$i]['name'] . '</span></a></td></tr>';
      
    }
    $cart_contents_string .= '</table>';
} else {
    $cart_contents_string .= BOX_SHOPPING_CART_EMPTY;
}

$info_box_contents = [ 
    ['text' => $cart_contents_string]
];

if ($cart->count_contents() > 0) {
    $info_box_contents[] = ['text' => tep_draw_separator()];
    $info_box_contents[] = [
        'align' => 'right',
        'text' => $currencies->format($cart->show_total())
    ];
}

new InfoBox($info_box_contents);
?>
</div>
            </td>
          </tr>
<!-- shopping_cart_eof //-->
