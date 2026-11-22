# Developer guide

## Local workflow

Use a disposable WordPress site with WooCommerce active, a configured cart and checkout, and the plugin directory beneath `wp-content/plugins/`. Follow the [metadata and template setup](../README.md) before exercising the form. Keep the form in a single-adventure Loop. PHP callbacks emit the markup directly. Edit frontend source in [script.js](../assets/public/script.js) and [style.css](../assets/public/style.css) when developing a change. WordPress serves those files directly.

The checkout contains no Composer or npm manifest, lockfile, bundled vendor tree, CI configuration, build step or automated test suite. From PowerShell at the plugin root, with PHP CLI on PATH, lint every PHP file with:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object {
    php -n -l $_.FullName
    if ($LASTEXITCODE) { exit $LASTEXITCODE }
}
```

This checks PHP syntax without loading WordPress or triggering product creation. All eight PHP files passed this check during the audit.

## Hook and callback reference

Unless indicated, registrations use the API's default priority of 10. The arrows below are expressed by the callback column. The plugin exposes no custom `do_action()` or `apply_filters()` extension points.

| WordPress or WooCommerce entry point | Callback | Registration and data |
| --- | --- | --- |
| `init` | [ABP_Admin](../includes/class-abp-admin.php) `register_adventure_post_type()` | Registered in the admin constructor, runs site-wide. |
| `admin_enqueue_scripts` | [ABP_Admin](../includes/class-abp-admin.php) `enqueue_styles()`, `enqueue_scripts()` | Wired by [ABP_Loader::run](../includes/class-abp-loader.php). Both handles are `abp-admin` in their respective registries. Script depends on jQuery. |
| `wp_enqueue_scripts` | [ABP_Public](../includes/class-abp-public.php) `enqueue_styles()`, `enqueue_scripts()` | Wired by the loader. Handles include `abp-public`, `jquery-ui-datepicker-style` and core `jquery-ui-datepicker`. Script receives `abpLocal.ajax`. |
| Shortcode `abp_booking_form` | [ABP_Shortcodes](../includes/class-abp-shortcodes.php) `booking_form($atts)` | Registered by `register_shortcodes()`. Returns buffered markup when a current post ID exists. |
| `wp_ajax_load_booking_summary`, `wp_ajax_nopriv_load_booking_summary` | [ABP_Ajax](../includes/class-abp-ajax.php) `load_booking_summary()` | `init_hooks()` builds both names from one action string. Echoes HTML and calls `wp_die()`. |
| `init` | [ABP_WooCommerce](../includes/class-abp-woocommerce.php) `ensure_dummy_booking_product()` | WooCommerce registrations below and this one are made in `init_hooks()`. |
| `template_redirect` | `ABP_WooCommerce::handle_booking_form()` | Dispatched on the presence of `adventure` in POST. A successful redirect path exits. |
| `woocommerce_before_calculate_totals` | `ABP_WooCommerce::update_cart_item_price($cart)` | Priority 20, one argument. Skips empty carts and admin requests when `DOING_AJAX` is undefined. |
| `woocommerce_cart_item_name` (filter) | `ABP_WooCommerce::override_booking_product_name($name, $cart_item, $cart_item_key)` | Priority 10, three arguments. Returns original name for entries without a nonempty booking ID. |
| `woocommerce_checkout_create_order_line_item` | `ABP_WooCommerce::save_booking_data_to_order($item, $cart_item_key, $values, $order)` | Priority 10, four arguments. Mutates the order item. |

## Integration contracts

A theme can render the shortcode and a field-management component can supply the [post metadata](../README.md). WooCommerce extensions can inspect the cart's `booking_data` or the resulting [line-item metadata](ARCHITECTURE.md). If integrating via existing WooCommerce hooks, account for the price setter at priority 20 and the name filter at 10. The loader keeps its instances private. Instantiating another admin component or running the loader again registers callbacks again.

The AJAX contract uses camel-case `pickUpDate` and `post_id`. The final form uses `pickup_date` and `adventure`. The response is an HTML fragment with option/time radios and a hidden fee, so changing field names or returning JSON requires coordinated changes to both PHP handlers and the public script.

## Manual verification on a disposable site

These checks remain pending. Use synthetic adventure and order data.

1. Confirm the Adventures menu, public single/archive and default core REST collection. Confirm that the stored product ID resolves to the hidden virtual product across successive requests.
2. Configure two overlapping ranges, several times, a group limit and distinct numeric rates. Open the single adventure as a guest and signed-in user. Check endpoints of each range in the site's and visitor's time zones.
3. Exercise each pricing mode, party changes in both directions, first-option/time selection and pickup enabled/disabled. Compare visible summaries with the configured rates.
4. Add a booking, then a different departure. Inspect cart lines, totals and location display. Use the site's test checkout to inspect the order title and each metadata label. Repeat for each cart/checkout experience the site supports.
5. Check an adventure with missing options, a page without the shortcode, narrow-screen controls and keyboard access. Record the theme and WordPress/WooCommerce/PHP versions with observations. No version compatibility matrix is established by this repository.
