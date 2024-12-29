<?php

namespace MOYASARENHANCEMENT\INCLUDES;
if (!defined('ABSPATH'))
{
    die();
}
class ProductCustomFieldClass
{
    public function __construct()
    {
        add_action('woocommerce_product_options_general_product_data', [$this,'add_payment_type_field']);
        add_action('woocommerce_process_product_meta', [$this,'save_payment_type_field']);

    }

    public function add_payment_type_field()
    {
        global $post;
        $payment_type = get_post_meta($post->ID, '_payment_type', true);
        if (!$payment_type) {
            $payment_type = 'authorize';
        }

        woocommerce_wp_select(
            array(
                'id'      => 'payment_type',
                'label'   => __('Payment Type', 'woocommerce'),
                'options' => array(
                    'authorize' => __('Authorize', 'woocommerce'),
                    'capture'   => __('Capture', 'woocommerce')
                ),
                'desc_tip' => 'true',
                'description' => __('Select whether the payment should be authorized or captured, Note: for membership choose authorize and for event choose capture.', 'woocommerce'),
                'value'    => $payment_type,
            )
        );
    }

    public function save_payment_type_field($post_id)
    {
        $payment_type = isset($_POST['payment_type']) ? $_POST['payment_type'] : 'authorize';
        update_post_meta($post_id, 'payment_type', sanitize_text_field($payment_type));
    }

}