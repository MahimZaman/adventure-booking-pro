<?php
if (! defined('ABSPATH')) {
    exit;
}

class ABP_WooCommerce
{

    public function init_hooks()
    {
        add_action('init', [$this, 'ensure_dummy_booking_product']);
        add_action('template_redirect', [$this, 'handle_booking_form']);

        add_action('woocommerce_before_calculate_totals', [$this, 'update_cart_item_price'], 20, 1);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'save_booking_data_to_order'], 10, 4);

        add_filter('woocommerce_cart_item_name', [$this, 'override_booking_product_name'], 10, 3);
    }

    public function override_booking_product_name($name, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['booking_data']['booking_id']) && ! empty($cart_item['booking_data']['booking_id'])) {
            $name = '<span class="cart-adventure">' . get_the_title($cart_item['booking_data']['booking_id']) . '</span>';
            $name .= '<span class="cart-location">
            <img src="' . ABP_PLUGIN_URL . 'assets/images/map-marker.svg"/>
            <span>' . get_post_meta($cart_item['booking_data']['booking_id'], 'location', true) . '</span></span>';
        }
        return $name;
    }

    public function save_booking_data_to_order($item, $cart_item_key, $values, $order)
    {
        if (isset($values['booking_data']['booking_id'])) {
            $item->set_name(get_the_title($values['booking_data']['booking_id']));
        }

        if (isset($values['booking_data'])) {
            foreach ($values['booking_data'] as $key => $value) {
                $item->add_meta_data(ucfirst(str_replace('_', ' ', $key)), $value, true);
            }
        }
    }

    public function update_cart_item_price($cart)
    {
        if (is_admin() && ! defined('DOING_AJAX')) {
            return;
        }

        if (empty($cart->get_cart())) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item) {
            if (isset($cart_item['booking_data']['booking_fee'])) {
                $price = (float) $cart_item['booking_data']['booking_fee'];
                $cart_item['data']->set_price($price);
            }
        }
    }

    public function handle_booking_form()
    {
        if (!isset($_POST['adventure'])) return;

        if (null === WC()->cart) {
            wc_load_cart();
        }

        $product_id = get_option('abp_product_id');
        if (! $product_id) {
            wp_die(__('Booking product not found.', 'abp'));
        }

        // Generate a more unique ID
        $unique_id = uniqid('booking_', true);

        // Collect booking fields
        $booking_data = [
            'booking_id'       => sanitize_text_field(absint($_POST['adventure'] ?? 0)),
            'pickup_date'      => sanitize_text_field($_POST['pickup_date'] ?? ''),
            'pick_up_time'     => sanitize_text_field($_POST['adv_time'] ?? ''),
            'booking_fee'      => floatval($_POST['booking_fee'] ?? 0),
            'adult'            => intval($_POST['adult'] ?? 0),
            'children'         => intval($_POST['children'] ?? 0),
            'option'            => sanitize_text_field($_POST['adv_option'] ?? ''),
        ];

        if (isset($_POST['pickup_location'])) {
            $booking_data['pickup_location'] = sanitize_text_field($_POST['pickup_location'] ?? '');
        }

        // Add product with booking data
        WC()->cart->add_to_cart($product_id, 1, 0, [], ['booking_data' => $booking_data, 'unique_key'   => $unique_id]);

        // Redirect to cart (or checkout if you prefer)
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }

    public function ensure_dummy_booking_product()
    {
        // Option key to store the dummy product ID
        $option_key = 'abp_product_id';
        $product_id = get_option($option_key);

        // Check if product exists and is valid
        if ($product_id && get_post_type($product_id) === 'product' && get_post_status($product_id) === 'publish') {
            return; // Already exists
        }

        // Create dummy product
        $product = new WC_Product_Simple();
        $product->set_name(__('Booking Product (Placeholder)', 'my-booking-plugin'));
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden'); // not shown in catalog
        $product->set_price(0);
        $product->set_regular_price(0);
        $product->set_sold_individually(true); // only one at a time
        $product->set_virtual(true); // no shipping
        $product->save();

        // Save new ID to option
        update_option($option_key, $product->get_id());
    }
}
