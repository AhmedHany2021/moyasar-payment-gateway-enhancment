<?php

namespace MOYASARENHANCEMENT\INCLUDES;
if (!defined('ABSPATH'))
{
    die();
}
class CapturePaymentsClass
{
    public function __construct()
    {
        add_action( 'woocommerce_order_status_changed', [$this,'custom_order_status_changed_action'], 10, 4 );
    }

    public function custom_order_status_changed_action( $order_id, $old_status, $new_status, $order )
    {
        if ( 'completed' === $new_status && $old_status === 'on-hold' )
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
                    ->post("https://api.moyasar.com/v1/payments/$payment_id/capture", [
                        'amount' => \Moyasar_Currency_Helper::amount_to_minor(
                            $order->get_total(),
                            $order->get_currency()
                        ),
                    ])
                    ->json();
            }catch (\Exception $exception){
                moyasar_logger("[Moyasar] [Refund] [Void] [Exception] {$exception->getMessage()}, Fallback to Refund API.", 'error', $order_id);
            }
        }
        return false;
    }
}