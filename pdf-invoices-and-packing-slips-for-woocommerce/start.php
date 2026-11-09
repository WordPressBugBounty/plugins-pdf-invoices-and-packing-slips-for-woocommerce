<?php
/*
 * Plugin Name: PDF Invoices and Packing Slips For WooCommerce
 * Version: 1.4.11
 * Description: Customize and print invoices and packing slips for WooCommerce orders
 * Author: Acowebs
 * Author URI: http://acowebs.com
 * Requires at least: 4.4.0
 * Tested up to: 7.1
 * Text Domain: pdf-invoices-and-packing-slips-for-woocommerce
 * WC requires at least: 3.0.0
 * WC tested up to: 11.2
 * Requires PHP: 7.4
*/
define('APIFW_TOKEN', 'apifw');
define('APIFW_VERSION', '1.4.11');
define('APIFW_FILE', __FILE__);
define('APIFW_PLUGIN_NAME', 'PDF Invoice and Packing Slip Labels For WooCommerce');
define('APIFW_STORE_URL', 'https://api.acowebs.com');
define('APIFW_WP_VERSION', get_bloginfo('version'));

// Acowebs Feedback API (deactivation feedback + newsletter opt-in).
define('APIFW_FEEDBACK_PLUGIN', 'PDF Invoices and Packing Slips'); // Canonical name, same in Free and PRO.
define('APIFW_EDITION', 'free');
define('APIFW_SLUG', 'pdf-invoices-and-packing-slips-for-woocommerce');

// The shared Acowebs classes need PHP 7.1+; on older PHP nothing is sent or shown.
if (version_compare(PHP_VERSION, '7.1', '>=')) {
    require_once plugin_dir_path(__FILE__) . 'includes/class-acowebs-feedback-client.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-acowebs-feedback-optin.php';

    // Newsletter notice: WP Dashboard only (no plugin pages listed).
    Acowebs_Feedback_Optin::register(array(
        'plugin'     => APIFW_FEEDBACK_PLUGIN,
        'edition'    => APIFW_EDITION,
        'list'       => 'pdf invoices and packing slips for woocommerce', // Acowebs panel list for this plugin's subscribers.
        'slug'       => APIFW_SLUG,
        'page_slugs' => array(),
        'prefix'     => 'apifw',
        'kit_dir'    => plugin_dir_path(__FILE__) . 'assets/acowebs-ui-kit/',
        'kit_url'    => plugin_dir_url(__FILE__) . 'assets/acowebs-ui-kit/',
    ));

    // Align the newsletter card with the other admin notices (same width and spacing). Outer box only:
    // the wrapper and the kit card each add a 20px right margin, so the card ended short of the others.
    add_action('admin_enqueue_scripts', function () {
        if (wp_style_is('acowebs-feedback-ui', 'enqueued')) {
            wp_add_inline_style(
                'acowebs-feedback-ui',
                '.notice.acowebs-optin-notice{margin:5px 0 15px !important}' .
                '.acowebs-optin-notice > .acofb.acofb-notice{margin:0}'
            );
        }
    }, 20);
}

// /defining template saving directory name
if ( !defined('APIFW_TEMPLATE_DIR_NAME') ){
    define('APIFW_TEMPLATE_DIR_NAME', 'apifw_uploads');
}

//defining template saving directory
$upload = wp_upload_dir();
$upload_dir = $upload['basedir'];
$upload_url = $upload['baseurl'];
$upload_dir = $upload_dir.'/'.APIFW_TEMPLATE_DIR_NAME;
$upload_url = $upload_url.'/'.APIFW_TEMPLATE_DIR_NAME;
define('APIFW_UPLOAD_TEMPLATE_DIR', $upload_dir);
define('APIFW_UPLOAD_TEMPLATE_URL', $upload_url);

//defining invoice saving directory
$invoice_dir = APIFW_UPLOAD_TEMPLATE_DIR.'/invoice';
$invoice_url = APIFW_UPLOAD_TEMPLATE_URL.'/invoice';
define('APIFW_UPLOAD_INVOICE_DIR', $invoice_dir);
define('APIFW_UPLOAD_INVOICE_URL', $invoice_url);

//Helpers
require_once(realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes/helpers.php');

// Cron
require_once(realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes/apifw-cron.php');

//Init
add_action('plugins_loaded', 'APIFW_init');
if (!function_exists('APIFW_init')) {
    function APIFW_init()
    {
        $plugin_rel_path = basename(dirname(__FILE__)) . '/languages'; /* Relative to WP_PLUGIN_DIR */
        load_plugin_textdomain('pdf-invoices-and-packing-slips-for-woocommerce', false, $plugin_rel_path);
    }
}

//Loading Classes
if (!function_exists('APIFW_autoloader')) {

    function APIFW_autoloader($class_name)
    {
        if (0 === strpos($class_name, 'APIFW')) {
            $classes_dir = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
            $class_file = 'class-' . str_replace('_', '-', strtolower($class_name)) . '.php';
            require_once $classes_dir . $class_file;
        }
    }

}
spl_autoload_register('APIFW_autoloader');

//Backend UI
if (!function_exists('APIFW')) {
    function APIFW()
    {
        $instance = APIFW_Backend::instance(__FILE__, APIFW_VERSION);
        return $instance;
    }
}

if ( is_admin() ) {
    APIFW();
}

//API
new APIFW_Api();

// calling invoice gen class
new APIFW_Invoice();

// Front end
new APIFW_Front_End( __FILE__, APIFW_VERSION );
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
