<?php
/**
 * Plugin Name: Adventure Booking Pro
 * Description: A professional adventure booking plugin for WordPress.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: adventure-booking-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ABP_VERSION', '1.0.0' );
define( 'ABP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ABP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include Loader
require_once ABP_PLUGIN_DIR . 'includes/class-abp-loader.php';

// Initialize plugin
function abp_run_plugin() {
    $plugin = new ABP_Loader();
    $plugin->run();
}
abp_run_plugin();
