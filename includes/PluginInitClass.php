<?php

namespace MOYASARENHANCEMENT\INCLUDES;

class PluginInitClass
{
    public function __construct()
    {
        add_filter('woocommerce_payment_gateways', [$this,'remove_moyasar_register_gateway']);
    }

    public function remove_moyasar_register_gateway($gateways)
    {
        $gateway_to_remove = 'Moyasar_Credit_Card_Payment_Gateway';
        foreach ($gateways as $key => $gateway) {
            if ($gateway == $gateway_to_remove) {
                unset($gateways[$key]);
            }
        }

        return $gateways;
    }
}