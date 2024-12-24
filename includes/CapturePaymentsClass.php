<?php

namespace MOYASARENHANCEMENT\INCLUDES;

class CapturePaymentsClass
{
    public function __construct()
    {
        add_action( 'woocommerce_order_status_changed', [$this,'custom_order_status_changed_action'], 10, 4 );
    }

    public function custom_order_status_changed_action( $order_id, $old_status, $new_status, $order )
    {
        if ( 'completed' === $new_status)
        {
            $order = wc_get_order($order_id);
            if (!$order) {
                return false;
            }
            $transaction_id = $order->get_transaction_id();
            if (!$transaction_id) {
                return false;
            }
            $payment_method = $order->get_payment_method();
            $gateway = moyasar_get_payment_method_class($payment_method);
            $payment = \Moyasar_Quick_Http::make()
                ->basic_auth($gateway->api_sk())
                ->post($this->moyasar_api_url("payments/".$transaction_id."/capture"), [
                    'amount' => \Moyasar_Currency_Helper::amount_to_minor(
                        $order->get_total(),
                        $order->get_currency()
                    ),
                ])
                ->json();
        }
        return false;
    }
}