<?php
/*
  $Id: currencies.php,v 1.16 2003/02/12 20:27:31 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

if (isset($currencies) && is_object($currencies) && sizeof($currencies->currencies)>1) {
?>
<!-- currencies //-->
          <tr>
            <td>
<?php
    $contents = [
        [
            [
                'params' => 'width="100%" class="infoBoxHeading"',
                'text' => BOX_HEADING_CURRENCIES
            ]
        ]
    ];
    $box_heading = new TableBox;
    $box_heading->set_param('cellpadding', 0);
    $box_heading->get_box($contents, true);

    $currencies_array = [];
    foreach ($currencies->currencies as $key => $value) {
        $currencies_array[] = ['id' => $key, 'text' => $value['title']];
    }

    $hidden_get_variables = '';
    foreach ($_GET as $key => $value) {
        if ($key != 'currency' && $key != tep_session_name() && $key != 'x' && $key != 'y') {
            $hidden_get_variables .= tep_draw_hidden_field($key, $value);
        }
    }

    $info_box_contents = [
        [
            'form'  => tep_draw_form('currencies', tep_href_link(basename($PHP_SELF), '', $request_type, false), 'get'),
            'align' => 'center',
            'text'  => tep_draw_pull_down_menu('currency', $currencies_array, $GLOBALS['currency'], 'onchange="this.form.submit();" style="width: 100%"') 
                        . $hidden_get_variables . tep_hide_session_id()
        ]
    ];
    new InfoBox($info_box_contents);
?>
            </td>
          </tr>
<!-- currencies_eof //-->
<?php
}
