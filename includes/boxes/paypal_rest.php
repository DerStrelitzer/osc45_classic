<?php
if ($cart->count_contents() > 0) {
?>

          <tr>
            <td>
<?php
    $info_box_contents = array();
    $info_box_contents[] = array('text' => BOX_HEADING_SHOPPING_CART);


    $cart_contents_string = '';
    try {
      require_once(DIR_WS_LANGUAGES . $language . '/modules/payment/paypal_rest.php');
      require_once(DIR_WS_INCLUDES .  'modules/payment/paypal_rest.php');
      $paypal_rest = new paypal_rest();
      $cart_contents_string = '';

      if ($paypal_rest->checkMessageOnProduct() && !in_array($paypal_rest->currentPage(), array('checkout', 'confirmation'))) {
        if ($cart->count_contents() > 0) {
          $price = $cart->show_total(); // rate???
        } else {
          $price = 0;
        }
        $cart_contents_string .= '<div class="pp-pay-later-message" style="padding:0 5px;margin:5px 0" data-pp-amount="' . $price . '" data-pp-style-text-size="10" data-pp-message data-pp-style-logo-type="alternative" data-pp-style-color="white"></div>';
        $js = "<script>
            try {
            /*  paypal.Messages({
                style: {
                  layout: 'text',
                },
              }).render('.pp-pay-later-message');*/
            } catch ( e ) { }
            </script> ";

        $data = '<div class="ui-widget infoBoxContainer">' .
                '  ' . $cart_contents_string .
                $paypal_rest->checkout_initialization_method_js() .
                '</div>';// . $js;

      }
    } catch (\Exception $e ) {

    }



    $info_box_contents = array();
    $info_box_contents[] = array('text' => $data);

    //new infoBox($info_box_contents);
    echo $data;
?>
            </td>
          </tr>
<?php
}
?>
