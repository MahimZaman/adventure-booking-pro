<?php
if (! defined('ABSPATH')) {
    exit;
}

require_once ABP_PLUGIN_DIR . 'includes/class-abp-admin.php';
require_once ABP_PLUGIN_DIR . 'includes/class-abp-public.php';
require_once ABP_PLUGIN_DIR . 'includes/class-abp-woocommerce.php';
require_once ABP_PLUGIN_DIR . 'includes/class-abp-ajax.php';
require_once ABP_PLUGIN_DIR . 'includes/class-abp-shortcodes.php';

class ABP_Loader
{

    private $admin;
    private $public;
    private $woocommerce;
    private $ajax;
    private $shortcodes;

    public function __construct()
    {
        $this->admin       = new ABP_Admin();
        $this->public      = new ABP_Public();
        $this->woocommerce = new ABP_WooCommerce();
        $this->ajax        = new ABP_Ajax();
        $this->shortcodes  = new ABP_Shortcodes();
    }

    public function run()
    {
        // Admin
        add_action('admin_enqueue_scripts', [$this->admin, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this->admin, 'enqueue_scripts']);

        // Public
        add_action('wp_enqueue_scripts', [$this->public, 'enqueue_styles']);
        add_action('wp_enqueue_scripts', [$this->public, 'enqueue_scripts']);

        // WooCommerce
        $this->woocommerce->init_hooks();

        // AJAX
        $this->ajax->init_hooks();

        // Shortcodes
        $this->shortcodes->register_shortcodes();
    }
}
