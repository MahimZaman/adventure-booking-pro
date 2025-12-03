<?php
if (! defined('ABSPATH')) {
    exit;
}

class ABP_Admin
{
    public function __construct()
    {
        add_action('init', [$this, 'register_adventure_post_type']);
    }

    public function enqueue_styles()
    {
        wp_enqueue_style(
            'abp-admin',
            ABP_PLUGIN_URL . 'assets/admin/admin.css',
            [],
            ABP_VERSION
        );
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script(
            'abp-admin',
            ABP_PLUGIN_URL . 'assets/admin/admin.js',
            ['jquery'],
            ABP_VERSION,
            true
        );
    }

    public function register_adventure_post_type()
    {
        $labels = [
            'name'                  => _x('Adventures', 'Post Type General Name', 'adventure-booking-pro'),
            'singular_name'         => _x('Adventure', 'Post Type Singular Name', 'adventure-booking-pro'),
            'menu_name'             => __('Adventures', 'adventure-booking-pro'),
            'name_admin_bar'        => __('Adventure', 'adventure-booking-pro'),
            'archives'              => __('Adventure Archives', 'adventure-booking-pro'),
            'attributes'            => __('Adventure Attributes', 'adventure-booking-pro'),
            'parent_item_colon'     => __('Parent Adventure:', 'adventure-booking-pro'),
            'all_items'             => __('All Adventures', 'adventure-booking-pro'),
            'add_new_item'          => __('Add New Adventure', 'adventure-booking-pro'),
            'add_new'               => __('Add New', 'adventure-booking-pro'),
            'new_item'              => __('New Adventure', 'adventure-booking-pro'),
            'edit_item'             => __('Edit Adventure', 'adventure-booking-pro'),
            'update_item'           => __('Update Adventure', 'adventure-booking-pro'),
            'view_item'             => __('View Adventure', 'adventure-booking-pro'),
            'view_items'            => __('View Adventures', 'adventure-booking-pro'),
            'search_items'          => __('Search Adventure', 'adventure-booking-pro'),
            'not_found'             => __('Not found', 'adventure-booking-pro'),
            'not_found_in_trash'    => __('Not found in Trash', 'adventure-booking-pro'),
            'featured_image'        => __('Featured Image', 'adventure-booking-pro'),
            'set_featured_image'    => __('Set featured image', 'adventure-booking-pro'),
            'remove_featured_image' => __('Remove featured image', 'adventure-booking-pro'),
            'use_featured_image'    => __('Use as featured image', 'adventure-booking-pro'),
            'insert_into_item'      => __('Insert into adventure', 'adventure-booking-pro'),
            'uploaded_to_this_item' => __('Uploaded to this adventure', 'adventure-booking-pro'),
            'items_list'            => __('Adventures list', 'adventure-booking-pro'),
            'items_list_navigation' => __('Adventures list navigation', 'adventure-booking-pro'),
            'filter_items_list'     => __('Filter adventures list', 'adventure-booking-pro'),
        ];

        $args = [
            'label'                 => __('Adventure', 'adventure-booking-pro'),
            'description'           => __('Adventure custom post type', 'adventure-booking-pro'),
            'labels'                => $labels,
            'supports'              => ['title', 'thumbnail'],
            'taxonomies'            => ['category'],
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-location-alt',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true, // For Gutenberg support
        ];

        register_post_type('adventures', $args);
    }
}
