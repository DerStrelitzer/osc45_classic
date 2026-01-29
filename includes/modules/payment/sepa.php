<?php
/*
  $Id: sepa.php,v 1.15i by Ingo, http://forums.oscommerce.de/index.php?showuser=36

  OSC German SEPA Banktransfer
  (http://www.oscommerce.com/community/contributions,826)

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2026 xPrioS
  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

define('MIN_LENGTH_OWNER', 6); // Kontoinhaber
define('MIN_LENGTH_IBAN', 15); // Bankleitzahl
define('MIN_LENGTH_BIC', 8); // Kontonummer

class sepa extends PaymentModules
{
    CONST TABLE_PAYMENT_SEPA = 'payment_sepa';

    public 
        $code = 'sepa';
    private
        $sepa_owner    = '',
        $sepa_bic      = '',
        $sepa_iban     = '',
        $sepa_fax      = 0;

    public function __construct()
    {
        global $order;

        $this->title = MODULE_PAYMENT_SEPA_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_SEPA_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_SEPA_SORT_ORDER') ? MODULE_PAYMENT_SEPA_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_SEPA_STATUS') && MODULE_PAYMENT_SEPA_STATUS == 'ja' ? true : false;
        $this->email_footer = MODULE_PAYMENT_SEPA_TEXT_EMAIL_FOOTER;

        if (defined('MODULE_PAYMENT_SEPA_ORDER_STATUS_ID') && (int)MODULE_PAYMENT_SEPA_ORDER_STATUS_ID > 0) {
            $this->order_status = MODULE_PAYMENT_SEPA_ORDER_STATUS_ID;
        }
        if (is_object($order)) {
            $this->update_status();
        }
    }

    public function update_status()
    {
        global $order;

        // disable the module if the order only contains virtual products
        if ($order->content_type == 'virtual') {
            $this->enabled = false;
        }

        // disable the module if country of billingaddress not in the allowed
        if (isset($order->billing['country']['iso_code_2']) && !in_array($order->billing['country']['iso_code_2'], explode(',', MODULE_PAYMENT_SEPA_COUNTRIES)) ) {
            $this->enabled = false;
        }

        if (isset($_POST['sepa_owner'])) {
            $this->sepa_owner    = xprios_prepare_post('sepa_owner');
            $this->sepa_bic      = xprios_prepare_post('sepa_bic');
            $this->sepa_iban     = xprios_prepare_post('sepa_iban');
        }
        $this->sepa_fax = isset($_POST['sepa_fax']) && $_POST['sepa_fax'] == 1 ? 1 : 0;
    }

    public function javascript_validation()
    {
        $js = 'if (payment_value == \'' . $this->code . '\') {' . "\n"
        . '  var sepa_owner = document.checkout_payment.sepa_owner.value;' . "\n"
        . '  var sepa_iban = document.checkout_payment.sepa_iban.value;' . "\n"
        . '  var sepa_bic = document.checkout_payment.sepa_bic.value;' . "\n"
        . '  var sepa_fax = document.checkout_payment.sepa_fax.checked;' . "\n"
        . '  var sepa_error = \'\';' . "\n"
        . '  if (sepa_fax == false) {' . "\n"
        . '    if (sepa_owner == "" || sepa_owner.length < ' . MIN_LENGTH_OWNER . ') {' . "\n"
        . '      sepa_error += "' . JS_BANK_OWNER . '";' . "\n"
        . '    }' . "\n"
        . '    if (sepa_iban == "" || sepa_iban.length < ' . MIN_LENGTH_IBAN . ') {' . "\n"
        . '      sepa_error += "' . JS_BANK_IBAN . '";' . "\n"
        . '    }' . "\n"
        . '    if (sepa_bic == "" || sepa_bic.length < ' . MIN_LENGTH_BIC . ' ) {' . "\n"
        . '      sepa_error += "' . JS_BANK_BIC . '";' . "\n"
        . '    }' . "\n"
        . '    if (sepa_error != \'\') {' . "\n"
        . '      error = 1;' . "\n"
        . '      error_message += sepa_error + \'\\n\';' . "\n"
        . '    }' . "\n"
        . '  }' . "\n"
        . '}' . "\n";
        return $js;
    }

    public function selection()
    {
        global $order;

        $last_bank = [
            'sepa_owner' => '',
            'sepa_bic'   => '',
            'sepa_iban'  => ''
        ];

        if ($this->sepa_owner != '' && $this->sepa_bic != '' && $this->sepa_iban != '') {
            $last_bank = [ 
                'sepa_owner' => $this->sepa_owner,
                'sepa_bic'   => $this->sepa_bic,
                'sepa_iban'  => $this->sepa_iban
            ];
        } elseif ($_SESSION['customer_id']>0) {
            $last_bank_query = tep_db_query("select b.sepa_owner, b.sepa_iban, b.sepa_bic from " . self::TABLE_PAYMENT_SEPA . " o, payment_sepa b where o.customers_id = '" . (int)$_SESSION['customer_id'] . "' and o.payment_class = '" . $this->code . "' and b.orders_id = o.orders_id order by b.orders_id desc limit 1");
            if (tep_db_num_rows($last_bank_query)) {
                $last_bank = tep_db_fetch_array($last_bank_query);
            } 
        }
        if ($last_bank['sepa_owner'] == '') {
            $last_bank['sepa_owner'] = $order->billing['firstname'] . ' ' . $order->billing['lastname'];
        }

        $selection = [
            'id'     => $this->code,
            'module' => $this->title,
            'fields' => [
                [
                    'title' => MODULE_PAYMENT_SEPA_TEXT_BANK_OWNER,
                    'field' => tep_draw_input_field('sepa_owner', $last_bank['sepa_owner'], 'size="30"')
                ],
                [
                    'title' => MODULE_PAYMENT_SEPA_TEXT_BANK_IBAN,
                    'field' => tep_draw_input_field('sepa_iban', $last_bank['sepa_iban'], 'size="30" maxlength="34"')
                ],
                [
                    'title' => MODULE_PAYMENT_SEPA_TEXT_BANK_BIC,
                    'field' => tep_draw_input_field('sepa_bic', $last_bank['sepa_bic'], 'size="15" maxlength="11"')
                ]
            ]
        ];

        if (MODULE_PAYMENT_SEPA_FAX_CONFIRMATION == 'true') {
            $selection['fields'][] = [
                'title' => MODULE_PAYMENT_SEPA_NOTE_TITLE,
                'field' => sprintf(MODULE_PAYMENT_SEPA_NOTE_FIELD, MODULE_PAYMENT_SEPA_FORM_PAGE)
            ];
            $selection['fields'][] = [
                'title' => MODULE_PAYMENT_SEPA_TEXT_BANK_FAX,
                'field' => tep_draw_checkbox_field('sepa_fax', '1')
            ];
        }

        return $selection;
    }

    public function pre_confirmation_check()
    {
    }

    public function confirmation()
    {

        if ($this->sepa_owner != '') {
            $confirmation = [
                'title'  => $this->title,
                'fields' => [
                    [
                        'title' => MODULE_PAYMENT_SEPA_TEXT_BANK_OWNER,
                        'field' => $this->sepa_owner
                    ],
                    [
                        'title' => MODULE_PAYMENT_SEPA_TEXT_BANK_IBAN,
                        'field' => $this->sepa_iban
                    ],
                    [
                        'title' => MODULE_PAYMENT_SEPA_TEXT_BANK_BIC,
                        'field' => $this->sepa_bic
                    ],
                ]
            ];
        }
        if ($this->sepa_fax == 1) {
            $confirmation = [
                'title' => '', 
                'fields' => [
                    [
                        'title' => MODULE_PAYMENT_SEPA_TEXT_BANK_FAX, 
                        'field' => ''
                    ]
                ]
            ];
        }
        return $confirmation;
    }

    public function process_button()
    {
        $process_button_string = tep_draw_hidden_field('sepa_owner', $this->sepa_owner)
        . tep_draw_hidden_field('sepa_iban', $this->sepa_iban)
        . tep_draw_hidden_field('sepa_bic', $this->sepa_bic)
        . tep_draw_hidden_field('sepa_fax', $this->sepa_fax);

        return $process_button_string;
    }

    public function before_process()
    {
        return false;
    }

    public function after_process()
    {
        global $insert_id;
        tep_db_perform(self::TABLE_PAYMENT_SEPA, [
                'orders_id'     => $insert_id,
                'sepa_owner'    => substr($this->sepa_owner, 0, 128),
                'sepa_iban'     => preg_replace('/[^0-9a-z]/i', '', $this->sepa_iban),
                'sepa_bic'      => preg_replace('/[^0-9a-z]/i', '', $this->sepa_bic),
                'sepa_fax'      => $this->sepa_fax
            ]
        );
    }

    public function get_error()
    {
        $error = [
            'title' => MODULE_PAYMENT_SEPA_TEXT_BANK_ERROR,
            'error' => urldecode($_GET['error'])
        ];
        return $error;
    }

    public function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_SEPA_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    public function get_data($orders_id='')
    {
        $return = false;
        $query = tep_db_query("select sepa_owner, sepa_iban, sepa_bic, sepa_fax from " . self::TABLE_PAYMENT_SEPA . " where orders_id = " . (int)$orders_id);
        if (tep_db_num_rows($query)) {
            $return = tep_db_fetch_array($query);
        } 
        return $return;
    }

    private function controls()
    {
        $controls = [];
        $controls[] = [
            'configuration_title'       => 'Erlauben Sie SEPA Lastschrift',
            'configuration_key'         => 'MODULE_PAYMENT_SEPA_STATUS',
            'configuration_value'       => 'ja',
            'configuration_description' => 'Wollen Sie die Bezahlung per Lastschrift erlauben?',
            'configuration_group_id'    => 6,
            'sort_order'                => 1,
            'set_function'              => 'tep_cfg_select_option(array(\'ja\', \'nein\'), ',
            'use_function'              => null
        ];
        $controls[] = [
            'configuration_title'       => 'L&auml;nder',
            'configuration_key'         => 'MODULE_PAYMENT_SEPA_COUNTRIES',
            'configuration_value'       => 'DE',
            'configuration_description' => 'Durch Komma getrennte Liste der Länder (ISO-Code 2) in der Rechnungsadresse, in denen Lastschrift erlaubt wird.<br>zB: <div style=\"width:50px;padding:1px;display:inline;border:1px solid black;background:white\">DE,AT,CH</div>',
            'configuration_group_id'    => 6,
            'sort_order'                => 2,
            'set_function'              => null,
            'use_function'              => null
        ];
        $controls[] = [
            'configuration_title'       => 'Anzeigefolge',
            'configuration_key'         => 'MODULE_PAYMENT_SEPA_SORT_ORDER',
            'configuration_value'       => '3',
            'configuration_description' => 'Reihenfolge der Auflistung der Zahlungsarten, niedrigste zuerst.',
            'configuration_group_id'    => 6,
            'sort_order'                => 3,
            'set_function'              => null,
            'use_function'              => null
        ];
        $controls[] = [
            'configuration_title'       => 'Bestellstatus',
            'configuration_key'         => 'MODULE_PAYMENT_SEPA_ORDER_STATUS_ID',
            'configuration_value'       => '0',
            'configuration_description' => 'Wird eine Bestellung ausgelöst, so wird dieser, die hier eingetragene Stufe des Bestellstatus zugewiesen.',
            'configuration_group_id'    => 6,
            'sort_order'                => 4,
            'set_function'              => 'tep_cfg_pull_down_order_statuses(',
            'use_function'              => 'tep_get_order_status_name'
        ];
        $controls[] = [
            'configuration_title'       => 'Angaben per Fax oder Brief',
            'configuration_key'         => 'MODULE_PAYMENT_SEPA_FAX_CONFIRMATION',
            'configuration_value'       => 'true',
            'configuration_description' => 'Erlauben Sie, dass der Kunde die Bankdaten per Post oder Fax schickt?<br><b>(Nicht vergessen, das Formular anzupassen!)</b>',
            'configuration_group_id'    => 6,
            'sort_order'                => 5,
            'set_function'              => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            'use_function'              => null
        ];
        return $controls;
    }

    public function install()
    {
        $controls = $this->$controls();
        foreach ($controls as $control) {
            tep_db_perform(TABLE_CONFIGURATION, $control);
        }
        
        $check_query = tep_db_query('SHOW TABLES LIKE \'' . self::TABLE_PAYMENT_SEPA . '\'');
        if (!tep_db_num_rows($check_query)) {
            tep_db_query(
              'CREATE TABLE IF NOT EXISTS payment_sepa ('
            . '  payment_sepa_id int(11) not null auto_increment, '
            . '  orders_id int() not null default "0", '
            . '  sepa_owner varchar(128) not null default "", '
            . '  sepa_iban varchar(34) not null default "", '
            . '  sepa_bic varchar(11) not null default "", '
            . '  sepa_fax tinyint() not null default "0", '
            . '  PRIMARY KEY (payment_sepa_id), '
            . '  KEY orders_id(orders_id)'
            . ')ENGINE=MyISAM DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci'
            );
        }
    }

    public function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    public function keys()
    {
        $controls = $this->$controls();
        $keys = [];
        foreach ($controls as $control) {
            $keys[] = $control['configuration_key'];
        }
        return $keys;
    }

    public function check_iban($iban)
    {
        
        $return = false;

        $iban = strtolower($iban);
        $countries = array(
            'al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,
            'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,
            'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,
            'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,
            'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,
            'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,
            'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,
            'pl'=>28,'pt'=>25,'qa'=>29,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,
            'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24
        );
        $chars = array(
            'a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,
            'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,
            'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,
            'y'=>34,'z'=>35
        );

        if (strlen($iban) == $countries[substr($iban,0,2)]) { 

            $movedchar = substr($iban, 4) . substr($iban, 0, 4);
            $movedchararray = str_split($movedchar);
            $newstring = '';

            foreach ($movedchararray as $k => $v) {
                if (!is_numeric($movedchararray[$k]) ) {
                    $movedchararray[$k] = $chars[$movedchararray[$k]];
                }
                $newstring .= $movedchararray[$k];
            }

            // http://au2.php.net/manual/en/function.bcmod.php#38474
            $x    = $newstring; 
            $y    = '97';
            $take = 5; 
            $mod  = '';

            do {
                $a = (int)$mod . substr($x, 0, $take);
                $x = substr($x, $take);
                $mod = $a % $y;
            }
            while (strlen($x)>0);
            
            $return = $mod == 1 ? true : false;
        }

        return $return;
    }
}
