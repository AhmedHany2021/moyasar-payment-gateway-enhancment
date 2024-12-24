<?php

namespace MOYASARENHANCEMENT\INCLUDES;

class MoyasarControllerReturnClass extends \Moyasar_Controller_Return
{
    public static function init()
    {
        parent::init();
        remove_action('wp', array($controller, 'handle_user_return'));
        $controller = new static();

        add_action('wp', array($controller, 'handle_user_return'));

        return static::$instance = $controller;
    }

    public function handle_user_return(\WP $wordpress)
    {

        if ($this->get_query_param('moyasar_page') != 'return') {
            return;
        }


        if (! $this->get_query_param('id')) {
            $this->perform_redirect(wc_get_checkout_url());

            return;
        }

        try {
            $order = $this->get_current_order_or_fail();
            $payment_method = $order->get_payment_method();
            $gateway = moyasar_get_payment_method_class($payment_method);
            $payment_id = $order->get_transaction_id('edit');

            if (!$payment_id) {
                moyasar_logger("[Moyasar] [Return] [Payment]: Payment ID not found", 'error', $order->get_id());
                throw new \RuntimeException(__('Cannot retrieve saved invoice ID for order.', 'moyasar'));
            }

            $payment = null;

            if ($payment_method === 'moyasar-stc-pay'){
                if (! $this->get_query_param('otp')) {
                    $this->perform_redirect(wc_get_checkout_url());
                    return;
                }
                $otp = $this->get_query_param('otp');
                $payment = $this->fetch_stc_payment($gateway, $order, $otp);
            }else{
                $payment  = $this->fetch_payment($gateway, $payment_id);
            }

            moyasar_logger("[Moyasar] [Return] [Payment]: " . wp_json_encode($payment), 'info', $order->get_id());


            if ($payment['status'] != 'paid' && $payment['status'] != 'authorized') {
                # Taking last payment
                $message = isset($payment['source']['message']) ? $payment['source']['message'] : 'no message';
                $message = sprintf(
                    __('Payment %1$s for order was not complete. Message: %2$s. Payment Status: %3$s', 'moyasar'),
                    $payment_id,
                    $message,
                    $payment['status']
                );

                $order->set_status('failed');
                $order->add_order_note($message);
                $order->save();

                wc_add_notice($message, 'error');

                $this->perform_redirect(wc_get_checkout_url());
                return;
            }

            add_filter('woocommerce_payment_complete_order_status', array($gateway, 'determine_new_order_status'), PHP_INT_MAX, 3);

            WC()->cart->empty_cart();


            \Moyasar_Helper_Coupons::tryApplyCoupon($order, $payment);

            $payment_id = $payment['id'];
            $paymentSource = $this->paymentSource($payment);
            $status = $gateway->get_option('new_order_status');
            $order->read_meta_data(true);
            if ( ! $order->meta_exists('_moyasar_payment_source') )
            {
                $order->add_order_note("Payment $payment_id for order is complete, new status: $status.");
                $order->set_status($status); // $gateway->get_option('new_order_status'
                $order->payment_complete();
                $order->update_meta_data('_moyasar_payment_source', $paymentSource);
                moyasar_logger("[Moyasar] [Return] [Success]: Payment ID: $payment_id is paid, Redirecting to " . $gateway->get_return_url($order), 'info', $order->get_id());
            } else {
                $order->add_order_note("Payment $payment_id for order is complete, Status Already Updated.");
                moyasar_logger("[Moyasar] [Return] [Success]: Payment ID: $payment_id is paid & Completed, Redirecting to " . $gateway->get_return_url($order), 'info', $order->get_id());
            }

            $order->save();
            $this->perform_redirect(
                $gateway->get_return_url($order)
            );
            return;
        } catch (\Moyasar_Http_Exception $e) {
            $message = $e->getMessage();

            if ($e->response->isJson()) {
                $body = $e->response->json();
                $message = isset($body['message']) ? $body['message'] : $message;
            }

            moyasar_logger("[Moyasar] [Return] [Http_Exception]: $message", 'error');
            wc_add_notice($message, 'error');

            $this->perform_redirect(wc_get_checkout_url());
            return;
        } catch (\Exception $e) {
            $message = $e->getMessage();
            moyasar_logger("[Moyasar] [Return] [Exception]: $message", 'error');
            wc_add_notice($message, 'error');

            $this->perform_redirect(wc_get_checkout_url());
            return;
        }
    }

}