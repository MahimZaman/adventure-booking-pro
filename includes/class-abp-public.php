<?php
if (! defined('ABSPATH')) {
    exit;
}

class ABP_Public
{

    public function enqueue_styles()
    {
        wp_enqueue_style(
            'jquery-ui-datepicker-style',
            'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css'
        );
        wp_enqueue_style(
            'abp-public',
            ABP_PLUGIN_URL . 'assets/public/style.css',
            [],
            ABP_VERSION
        );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script(
            'abp-public',
            ABP_PLUGIN_URL . 'assets/public/script.js',
            ['jquery'],
            time(),
            true
        );
        wp_localize_script('abp-public', 'abpLocal', array(
            'ajax' => admin_url('admin-ajax.php')
        ));
    }
}
