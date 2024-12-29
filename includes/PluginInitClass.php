<?php

namespace MOYASARENHANCEMENT\INCLUDES;
if (!defined('ABSPATH'))
{
    die();
}
class PluginInitClass
{
    public function __construct()
    {
        add_filter('woocommerce_payment_gateways', [$this, 'remove_moyasar_register_gateway'], 998);
    }

    public function remove_moyasar_register_gateway($gateways)
    {
        $gateway_to_remove = 'moyasar-credit-card';

        foreach ($gateways as $key => $gateway) {
            if ($gateway->id === $gateway_to_remove) {
                unset($gateways[$key]);
            }
        }

        return $gateways;
    }

}
