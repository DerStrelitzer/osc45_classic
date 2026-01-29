<?php
/*
  $Id: ot_cod_fee.php,v 1.02 2003/02/24 06:05:00 harley_vb Exp $

  Copyright (c) 2026 xPrioS
  Copyright (C) 2001 - 2003 TheMedia, Dipl.-Ing Thomas Plänkers
       http://www.themedia.at & http://www.oscommerce.at

                    All rights reserved.

  This program is free software licensed under the GNU General Public License (GPL).

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
  USA

*/

class ot_cod_fee extends OrderTotalModules
{
    public
        $code = 'ot_cod_fee';

    public function __construct()
    {
        $this->enabled = defined('MODULE_ORDER_TOTAL_COD_STATUS') && MODULE_ORDER_TOTAL_COD_STATUS == 'true' ? true : false;
        $this->sort_order = defined('MODULE_ORDER_TOTAL_COD_SORT_ORDER') ? MODULE_ORDER_TOTAL_COD_SORT_ORDER : 0;
        $this->title = MODULE_ORDER_TOTAL_COD_TITLE;
        $this->description = MODULE_ORDER_TOTAL_COD_DESCRIPTION;
    }

    function process()
    {
        global $order, $currencies, $cod_cost, $cod_country;

        if (MODULE_ORDER_TOTAL_COD_STATUS == 'true') {

        //Will become true, if cod can be processed.
            $cod_country = false;

        //check if payment method is cod. If yes, check if cod is possible.
            if ($_SESSION['payment'] == 'cod') {
                //process installed shipping modules
                switch ($_SESSION['shipping']['id']) {
                    case ('flat_flat'):
                        $cod_zones = preg_split('/[:,]/', MODULE_ORDER_TOTAL_COD_FEE_FLAT, -1, PREG_SPLIT_NO_EMPTY);
                        break;
                    case ('item_item'):
                        $cod_zones = preg_split('/[:,]/', MODULE_ORDER_TOTAL_COD_FEE_ITEM, -1, PREG_SPLIT_NO_EMPTY);
                        break;
                    case ('table_table'):
                        $cod_zones = preg_split('/[:,]/', MODULE_ORDER_TOTAL_COD_FEE_TABLE, -1, PREG_SPLIT_NO_EMPTY);
                        break;
                    case ('zones_zones'):
                        $cod_zones = preg_split('/[:,]/', MODULE_ORDER_TOTAL_COD_FEE_ZONES, -1, PREG_SPLIT_NO_EMPTY);
                        break;
                    case ('ap_ap'):
                        $cod_zones = preg_split('/[:,]/', MODULE_ORDER_TOTAL_COD_FEE_AP, -1, PREG_SPLIT_NO_EMPTY);
                        break;
                    case ('dp_dp'):
                        $cod_zones = preg_split('/[:,]/', MODULE_ORDER_TOTAL_COD_FEE_DP, -1, PREG_SPLIT_NO_EMPTY);
                        break;
                    case ('letter_letter'):
                        $cod_zones = preg_split('/[:,]/', MODULE_ORDER_TOTAL_COD_FEE_LETTER, -1, PREG_SPLIT_NO_EMPTY);
                        break;
                    case ('dpch_dpch'):
                        $cod_zones = preg_split('/[:,]/', MODULE_ORDER_TOTAL_COD_FEE_DPCH, -1, PREG_SPLIT_NO_EMPTY);
                        break;
                    default: 
                        $cod_zones = preg_split('/[:,]/', MODULE_ORDER_TOTAL_COD_FEE_SONSTIGE, -1, PREG_SPLIT_NO_EMPTY);
                }


                for ($i = 0; $i < count($cod_zones); $i++) {
                    if ($cod_zones[$i] == $order->billing['country']['iso_code_2']) {
                        $cod_cost = $cod_zones[$i + 1];
                        $cod_country = true;
                        //print('match' . $i . ': ' . $cod_cost);
                        break;
                    } elseif ($cod_zones[$i] == '00') {
                        $cod_cost = $cod_zones[$i + 1];
                        $cod_country = true;
                        //print('match' . $i . ': ' . $cod_cost);
                        break;
                    } else {
                        //print('no match');
                    }
                    $i++;
                }
            } else {
                //COD selected, but no shipping module which offers COD
            }

            if ($cod_country) {
                $cod_tax = $order->tax_max['rate'];
                $cod_tax_description = $order->tax_max['description'];
                //$cod_tax = tep_get_tax_rate(MODULE_ORDER_TOTAL_COD_TAX_CLASS, $order->billing['country']['id'], $order->billing['zone_id']);
                //$cod_tax_description = tep_get_tax_description(MODULE_ORDER_TOTAL_COD_TAX_CLASS, $order->billing['country']['id'], $order->billing['zone_id']);
                if ($cod_tax>0) {
                    $order->info['tax'] += tep_calculate_tax($cod_cost, $cod_tax);
                    $order->info['tax_groups']["$cod_tax_description"] += tep_calculate_tax($cod_cost, $cod_tax);
                }
                $order->info['total'] += ($cod_cost + tep_calculate_tax($cod_cost, $cod_tax));
                $this->output[] = array(
                    'title' => $this->title . ':',
                    'text' => $currencies->format(tep_add_tax($cod_cost, $cod_tax), true, $order->info['currency'], $order->info['currency_value']),
                    'value' => tep_add_tax($cod_cost, $cod_tax)
                );
            } else {
                //Following code should be improved if we can't get the shipping modules disabled, who don't allow COD
                // as well as countries who do not have cod
                //          $this->output[] = array('title' => $this->title . ':',
                //                                  'text' => 'No COD for this module.',
                //                                  'value' => '');
            }
        }
    }

    function check()
    {
        if ($this->_check == null) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_COD_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function keys()
    {
        return array(
            'MODULE_ORDER_TOTAL_COD_STATUS', 
            'MODULE_ORDER_TOTAL_COD_SORT_ORDER', 
            'MODULE_ORDER_TOTAL_COD_FEE_FLAT',
            'MODULE_ORDER_TOTAL_COD_FEE_LETTER',
            'MODULE_ORDER_TOTAL_COD_FEE_DPCH', 
            'MODULE_ORDER_TOTAL_COD_FEE_ITEM', 
            'MODULE_ORDER_TOTAL_COD_FEE_TABLE', 
            'MODULE_ORDER_TOTAL_COD_FEE_ZONES', 
            'MODULE_ORDER_TOTAL_COD_FEE_AP', 
            'MODULE_ORDER_TOTAL_COD_FEE_DP', 
            'MODULE_ORDER_TOTAL_COD_FEE_SONSTIGE', 
            'MODULE_ORDER_TOTAL_COD_TAX_CLASS'
        );
    }

    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Nachnahmeberechnung', 'MODULE_ORDER_TOTAL_COD_STATUS', 'true', 'Soll das Modul zur Berechnung der Nachnahmegebühren angezeigt werden?<br /><b>true</b> = ja<br /><b>false</b> = nein ', '6', '0','tep_cfg_select_option(array(\'true\', \'false\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sortierreihenfolge', 'MODULE_ORDER_TOTAL_COD_SORT_ORDER', '6', 'Reihenfolge der Anzeige. Niedrigste zuerst.<center>_______________________<br />| deutsche Übersetzung |<br />|_____<a href=\"http://www.videoundco.de\"><b><u>In</u>g<u>o Malchow</u></b></a>____|</center>', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gebühr bei pauschale VK', 'MODULE_ORDER_TOTAL_COD_FEE_FLAT', 'DE:2,00:8', 'Für pauschale Versandkosten: &lt;Ländercode&gt;:&lt;Gebühr&gt;, .... 00 als Ländercode = alle Länder. Wenn 00 verwendet, dann zum Schluß. Folgt kein 00:x.xx ist die Gebührenberechnung für nicht aufgeführte Länder unmöglich.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gebühr bei Versand im Brief', 'MODULE_ORDER_TOTAL_COD_FEE_LETTER', 'DE:2,00:8', 'Für Versand im Brief: &lt;Ländercode&gt;:&lt;Gebühr&gt;, .... 00 als Ländercode = alle Länder. Wenn 00 verwendet, dann zum Schluß. Folgt kein 00:x.xx ist die Gebührenberechnung für nicht aufgeführte Länder unmöglich.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gebühr bei Versand im Päckchen', 'MODULE_ORDER_TOTAL_COD_FEE_DPCH', 'DE:2,00:8', 'Für Versand im Päckchen: &lt;Ländercode&gt;:&lt;Gebühr&gt;, .... 00 als Ländercode = alle Länder. Wenn 00 verwendet, dann zum Schluß. Folgt kein 00:x.xx ist die Gebührenberechnung für nicht aufgeführte Länder unmöglich.', '6', '0', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gebühr bei VK je Stück', 'MODULE_ORDER_TOTAL_COD_FEE_ITEM', 'DE:2,00:8', 'Wie vor, jedoch für Berechnung der Versandkosten nach Stückzahl', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gebühr bei VK Tabelle', 'MODULE_ORDER_TOTAL_COD_FEE_TABLE', 'DE:2,00:8', 'Wie vor, jedoch für Berechnung der Versandkosten nach der Tabelle.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gebühr bei VK nach Zonen', 'MODULE_ORDER_TOTAL_COD_FEE_ZONES', 'DE:2,00:8', 'Wie vor, jedoch für Berechnung der Versandkosten nach Zonen.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gebühr bei Österr. Post', 'MODULE_ORDER_TOTAL_COD_FEE_AP', 'DE:2,00:8', 'Wie vor, jedoch für Berechnung der Versandkosten für Österreichische Post.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gebühr für Deutsche Post', 'MODULE_ORDER_TOTAL_COD_FEE_DP', 'DE:2,00:8', 'Wie vor, jedoch für Berechnung der Versandkosten für Packet der Deutschen Post.', '6', '0', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Steuerklasse', 'MODULE_ORDER_TOTAL_COD_TAX_CLASS', '1', '<b>unbenutzt!</b> Es wird der höchste Steuersatz aus dem Warenkorb benutzt.<!-- Die Nachnahmegebühr unterliegt dieser Steuerklasse. -->', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Gebühr für alle anderen', 'MODULE_ORDER_TOTAL_COD_FEE_SONSTIGE', 'DE:2,00:8', 'Für Versand mit alle anderen und versandkostenfreien Lieferungen.', '6', '0', now())");
    }

    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
}
