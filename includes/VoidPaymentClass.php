<?php

namespace MOYASARENHANCEMENT\INCLUDES;

class VoidPaymentClass
{
    public function __construct()
    {
        add_action( 'woocommerce_order_status_changed', [$this,'custom_order_status_changed_action'], 10, 4 );
    }

    public function custom_order_status_changed_action( $order_id, $old_status, $new_status, $order )
    {
        if ( 'refunded' === $new_status)
        {
            $order = wc_get_order($order_id);
            if (!$order) {
                return false;
            }
            $payment_id = $order->get_transaction_id('edit');
            if (!$payment_id) {
                return false;
            }
            $payment_method = $order->get_payment_method();
            $gateway = moyasar_get_payment_method_class($payment_method);
            try {
                $payment = \Moyasar_Quick_Http::make()
                    ->basic_auth($gateway->api_sk())
                    ->post("https://api.moyasar.com/v1/payments/$payment_id/void")
                    ->json();
                    moyasar_logger("[Moyasar] [Refund] Voided payment for order $order_id", 'info', $order_id);
            }catch (\Exception $exception){
                moyasar_logger("[Moyasar] [Refund] [Void] [Exception] {$exception->getMessage()}, Fallback to Refund API.", 'error', $order_id);
            }
        }
        return false;
    }
}