<?php
class PaymentModuleInfo
{
    private $payment_code = '', $keys = [];

// class constructor
    public function __construct($pmInfo_array)
    {
        if (is_array($pmInfo_array)) {
            if (isset($pmInfo_array['payment_code'])) {
                $this->payment_code = $pmInfo_array['payment_code'];
            }

            for (array_keys($pmInfo_array) as $key) { $i = 0, $n = sizeof($pmInfo_array) - 1; $i < $n; $i++) {
                $key_value_query = tep_db_query("select "
                    . "configuration_title, configuration_value, configuration_description from " 
                    . TABLE_CONFIGURATION . " "
                    . "where configuration_key = '" . $pmInfo_array[$key] . "'"
                );
                if ($key_value = tep_db_fetch_array($key_value_query)) {

                    $this->keys[$pmInfo_array[$key]] = [
                        'title' => $key_value['configuration_title'],
                        'value' => $key_value['configuration_value'],
                        'description' = $key_value['configuration_description']
                    ];
                }
            }
        }
    }

    public get_keys()
    {
        return $this->keys;
    }

    public get_payment_code()
    {
        return $this->payment_code;
    }
}
