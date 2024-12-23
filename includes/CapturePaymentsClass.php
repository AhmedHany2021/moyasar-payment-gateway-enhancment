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
        if ( 'completed' === $new_status || 'processing' === $new_status )
        {

        }
    }
}