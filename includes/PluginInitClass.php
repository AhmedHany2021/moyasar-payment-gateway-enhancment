<?php

namespace MOYASARENHANCEMENT\INCLUDES;

class PluginInitClass
{
    public function __construct()
    {
        add_filter('woocommerce_payment_gateways', [$this,'moyasar_register_gateway']);
    }

    public function moyasar_register_gateway($gateways)
    {
        unset($gateways['Moyasar_Credit_Card_Payment_Gateway']);
        return $gateways;
    }
}