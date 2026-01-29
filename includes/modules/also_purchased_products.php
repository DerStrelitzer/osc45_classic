<?php
/*
  $Id: also_purchased_products.php,v 1.21 2003/02/12 23:55:58 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if (isset($_GET['products_id'])) {
    $orders_query = tep_db_query("select "
        . "p.products_id, p.products_image, o.date_purchased "
        . "from " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb, " . TABLE_ORDERS . " o, " . TABLE_PRODUCTS . " p "
        . "where opa.products_id = '" . (int)$_GET['products_id'] . "' and opa.orders_id = opb.orders_id "
        . "and opb.products_id != '" . (int)$_GET['products_id'] . "' and opb.products_id = p.products_id "
        . "and opb.orders_id = o.orders_id and p.products_status = '1' "
        . "group by opb.products_id order by o.date_purchased desc limit " . MAX_DISPLAY_ALSO_PURCHASED
    );
    $num_products_ordered = tep_db_num_rows($orders_query);
    if ($num_products_ordered >= MIN_DISPLAY_ALSO_PURCHASED) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
<!-- also_purchased_products //-->
<?php
        $contents = [ 
            [
                [
                    'params' => 'width="100%" class="infoBoxHeading"',
                    'text' => sprintf(TEXT_ALSO_PURCHASED_PRODUCTS, $product_info['products_name'])
                ]
            ]
        ];
        $box_heading = new TableBox;
        $box_heading->set_param('cellpadding', 0);
        $box_heading->get_box($contents, true);

/*
      $info_box_contents = [];
      $info_box_contents[] = array('text' => sprintf(TEXT_ALSO_PURCHASED_PRODUCTS, $product_info['products_name']));

      new infoBoxHeading($info_box_contents);
*/
        $row = 0;
        $col = 0;
        $also_purchased_box_contents = [];
        while ($orders = tep_db_fetch_array($orders_query)) {
            $orders['products_name'] = tep_get_products_name($orders['products_id']);
            $also_purchased_box_contents[$row][$col] = [
                'align'  => 'center',
                'params' => 'class="smallText" width="33%" valign="top"',
                'text'   => '<a href="' . ingo_product_link($orders['products_id'], $orders['products_name']) . '">' . tep_image(DIR_WS_IMAGES . $orders['products_image'], $orders['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a><br />' .
                          '<a href="' . ingo_product_link($orders['products_id'], $orders['products_name']) . '">' . $orders['products_name'] . '</a>'
            ];

            $col ++;
            if ($col > 2) {
                $col = 0;
                $row ++;
            }
        }

        $content = new TableBox();
        $content->set_param('cellpadding', 4);
        $content->set_param('parameters', 'class="infoBoxContents"');
        $content_box_contents = array(array('text' => $content->get_box($also_purchased_box_contents)));
            
        $content_box = new TableBox();
        $content_box->set_param('cellpadding', 1);
        $content_box->set_param('parameters', 'class="infoBox"');
        $content_box->get_box($content_box_contents, true);
?>
<!-- also_purchased_products_eof //-->
        </td>
      </tr>
<?php
    }
}
