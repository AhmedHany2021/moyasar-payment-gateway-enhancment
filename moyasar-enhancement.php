<?php
/*
Plugin Name: moyasar enhancement plugin
Plugin URI: https://github.com/AhmedHany2021
Description: this plugin allow moyasar plugin to authorize the payment and the capture it on order status change
Author: Ahmed Hany
Version: 1.0.9
Author URI: https://github.com/AhmedHany2021
GitHub Plugin URI: https://github.com/AhmedHany2021/moyasar-payment-gateway-enhancment
*/

namespace MOYASARENHANCEMENT;

if (!defined('ABSPATH'))
{
    die();
}

if ( !in_array( 'moyasar/moyasar.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    die("NO ACCESS From Here Fine");
}

/* Add the main global variables */

if(!defined("MOY_BASEDIR")) { define("MOY_BASEDIR",__DIR__ . '/'); }
if(!defined("MOY_INC")) { define("MOY_INC",MOY_BASEDIR.'includes' . '/'); }
if(!defined("MOY_TEMPLATES")) { define("MOY_TEMPLATES",MOY_BASEDIR.'templates' . '/'); }
if(!defined("MOY_URI")) { define("MOY_URI",plugin_dir_url(__FILE__) ); }
if(!defined("MOY_ASSETS")) { define("MOY_ASSETS", MOY_URI.'assets' . '/'); }
if(!defined("MOY_ORIGINAL_DIR")) { define("MOY_ORIGINAL_DIR", WP_PLUGIN_DIR . '/moyasar' . '/'); }

/* Add the autoload class */
require_once MOY_INC . 'autoload.php';
use MOYASARENHANCEMENT\INCLUDES\autoload;
use MOYASARENHANCEMENT\INCLUDES\CapturePaymentsClass;
use MOYASARENHANCEMENT\INCLUDES\MoyasarControllerReturnClass;
use MOYASARENHANCEMENT\INCLUDES\MoyasarPaymentClass;
use MOYASARENHANCEMENT\INCLUDES\PluginInitClass;
use MOYASARENHANCEMENT\INCLUDES\VoidPaymentClass;

autoload::fire();

/*
    Add classes from moyasar original plugin
    Note: we will add this code in the if condition to make sure our plugin will work after the loading of the original plugin
 */

add_action('plugins_loaded', function() {
    if (is_plugin_active('moyasar/moyasar.php')) {

        require_once MOY_ORIGINAL_DIR . 'gateways/shared/moyasar-gateway-trait.php';
        require_once MOY_ORIGINAL_DIR . 'gateways/moyasar-credit-card-payment-gateway.php';
        require_once MOY_ORIGINAL_DIR . 'helpers/moyasar-helper-coupons.php';
        require_once MOY_ORIGINAL_DIR . 'quick-http/class-moyasar-http-exception.php';
        require_once MOY_ORIGINAL_DIR . 'quick-http/class-moyasar-quick-http.php';
        require_once MOY_ORIGINAL_DIR . 'controllers/moyasar-controller-return.php';
        require_once MOY_ORIGINAL_DIR . 'utils/helpers.php';

        /* Remove the basic moyasar payment method and add custom one */
        $PluginInitClass = new PluginInitClass();
        require_once MOY_INC . 'MoyasarPaymentClass.php';
        add_filter('woocommerce_payment_gateways',
            function($gateways) {
                $gateways[] = "MOYASARENHANCEMENT\INCLUDES\MoyasarPaymentClass";
                return $gateways;
            },
            999);
        MoyasarControllerReturnClass::init();
        $capture = new CapturePaymentsClass();
        $void = new VoidPaymentClass();


    }
});







