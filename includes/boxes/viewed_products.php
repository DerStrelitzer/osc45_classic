<?php
/*
  $Id: viewed_products.php,v 1.0 2006/08/25 Ingo <http://forums.oscommerce.de/index.php?showuser=36>

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2021 xPrioS
  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

  if (isset($_SESSION['viewed_products']) && is_object($_SESSION['viewed_products']) && $_SESSION['viewed_products']->count > 1) {
?>
<!-- viewed_products //-->
          <tr>
            <td>
<?php

    $contents = array( 
      array(
        array(
          'params' => 'width="100%" class="infoBoxHeading"',
          'text' => BOX_HEADING_VIEWED_PRODUCTS
        )
      )
    );
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);
    
    $viewed_products_list = '    <table border="0" width="100%" cellspacing="0" cellpadding="1">' . "\n";
    for ($i=1, $j=$_SESSION['viewed_products']->count; $i<$j; $i++) {
      $viewed_products_list .= '      <tr><td class="boxText" valign="top">&bull;</td><td class="boxText"><a href="' . ingo_product_link($_SESSION['viewed_products']->viewed[$i]['id'], $_SESSION['viewed_products']->viewed[$i]['name']) . '" class="infoboxcontentlink">' . $_SESSION['viewed_products']->viewed[$i]['name'] . '</a></td></tr>' . "\n";
    }
    $viewed_products_list .= '    </table>';

    $info_box_contents = array( 
      array('text' => $viewed_products_list)
    );

    new InfoBox($info_box_contents);

?>
            </td>
          </tr>
<!-- viewed_products_eof //-->
<?php
  }
?>