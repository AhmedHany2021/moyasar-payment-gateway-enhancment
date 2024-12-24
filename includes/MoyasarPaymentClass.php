<?php

namespace MOYASARENHANCEMENT\INCLUDES;

class MoyasarPaymentClass extends \Moyasar_Credit_Card_Payment_Gateway
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init_form_fields()
    {
        $shared_fields = require MOY_ORIGINAL_DIR . 'utils/admin-settings.php';
        $gateway_fields = require MOY_ORIGINAL_DIR . 'utils/methods/credit-card-admin-settings.php';
        $this->form_fields = array_merge($shared_fields, $gateway_fields);
    }


    public function process_payment($order_id)
    {
        if ( ! isset( $_POST['moyasar-cc-nonce-field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ($_POST['moyasar-cc-nonce-field'] )) , 'moyasar-form' ) ) {
            return [
                'result' => 'failed',
                'message' => __('Nonce verification failed.', 'moyasar')
            ];
        }

        $is_classic = sanitize_text_field( wp_unslash (($_POST['mysr_form'] ?? 'blocks' ) )) === 'classic';
        $source = [
            'type' => 'token',
            'token' => sanitize_text_field( wp_unslash (isset( $_POST['mysr_token'] ) ? $_POST['mysr_token'] : '' )  ),
            '3ds' => true,
            'manual' => true
        ];

        $response = $this->payment($order_id, $source, true);


        if ($is_classic && $response['result'] === 'success') {
            $response['redirect2'] = $response['redirect'];
            $response['redirect'] = '#'; // To Avoid redirection.

        }
        if ($is_classic && $response['message'] === 'APPROVED') {
            $response['redirect2'] = $response['redirect'];
            $response['redirect'] = '#'; // To Avoid redirection.

        }

        if ($response['result'] === 'failed' && $response['message'] === 'APPROVED') {
//            wc_add_notice($response['message'], 'error');
            wc_add_notice("we are here", 'error');
        }

        wc_add_notice('the payment triggered', 'error');
        return $response;
    }

}