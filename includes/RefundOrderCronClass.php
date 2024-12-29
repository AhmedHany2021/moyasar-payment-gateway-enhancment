<?php

namespace MOYASARENHANCEMENT\INCLUDES;
if (!defined('ABSPATH'))
{
    die();
}
class RefundOrderCronClass
{
    public function __construct()
    {
        $this->execute();
    }
    public function execute()
    {
        if (!wp_next_scheduled('check_on_hold_orders_daily')) {
            wp_schedule_event(time(), 'daily', 'check_on_hold_orders_daily');
        }
        add_action('check_on_hold_orders_daily', [$this,'check_on_hold_orders_and_perform_action']);
    }
    public function check_on_hold_orders_and_perform_action()
    {
        $current_date = new DateTime();
        $date_7_days_ago = $current_date->modify('-7 days')->format('Y-m-d H:i:s');
        $args = array(
            'status' => 'on-hold',
            'date_created' => '<' . $date_7_days_ago,
            'limit' => -1,
        );
        $query = new WC_Order_Query($args);
        $orders = $query->get_orders();
        if ($orders) {
            foreach ($orders as $order) {
                if ($this->check_order_items_payment_type($order))
                {
                    $order->update_status('refunded');
                    $order->save();
                }
                else
                {
                    $order->update_status('processing');
                    $order->save();
                }
                $order_id = $order->get_id();
                $order_date = $order->get_date_created()->date('Y-m-d H:i:s');
                error_log("Order ID: $order_id, Created on: $order_date");
            }
        }
    }

    public function check_order_items_payment_type($order) {
        foreach ($order->get_items() as $item_id => $item) {
            $product_id = $item->get_product_id();
            $payment_type = get_post_meta($product_id, 'payment_type', true);
            if ($payment_type === 'authorize') {
                return true;
            }
        }
        return false;
    }

}