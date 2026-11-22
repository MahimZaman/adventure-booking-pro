# Architecture

## Bootstrap and registration

WordPress loads [adventure-booking-pro.php](../adventure-booking-pro.php). An `ABSPATH` guard precedes definitions for `ABP_VERSION`, `ABP_PLUGIN_DIR` and `ABP_PLUGIN_URL`. The file requires the loader and immediately calls `abp_run_plugin()`. The loader includes and instantiates five components. Constructing the admin component attaches its post-type callback to `init` even on public requests. `ABP_Loader::run()` attaches asset callbacks, invokes the WooCommerce and AJAX registrars, and registers the shortcode directly. There is no service container, autoloader or activation installer.

| Component | Responsibility |
| --- | --- |
| [ABP_Loader](../includes/class-abp-loader.php) | Owns the five instances and wires their entry points. |
| [ABP_Admin](../includes/class-abp-admin.php) | Registers the public `adventures` post type and enqueues admin assets. |
| [ABP_Public](../includes/class-abp-public.php) | Enqueues styles and scripts, passes the AJAX URL to JavaScript as `abpLocal.ajax`. |
| [ABP_Shortcodes](../includes/class-abp-shortcodes.php) | Reads metadata for the current post and buffers the HTML form, range JSON and traveller inputs. |
| [ABP_Ajax](../includes/class-abp-ajax.php) | Renders a transient HTML quote containing matching options, pickup times and the hidden fee. |
| [ABP_WooCommerce](../includes/class-abp-woocommerce.php) | Provisions the shared product, consumes the form POST and adapts cart prices, names and order items. |

## Request and data flow

1. At `init`, `ensure_dummy_booking_product()` reads `abp_product_id`. A published product of any kind satisfies the reuse check. Otherwise it saves a zero-priced, sold-individually, virtual simple product and updates the option with its ID.
2. When the theme renders the shortcode, `booking_form()` maps the option rows to start/end date pairs, encodes and attribute-escapes the JSON, and emits the post ID, group-size controls and optional pickup field. No availability query is made against previous orders.
3. The [public script](../assets/public/script.js) converts ranges to JavaScript dates. Its datepicker and traveller handlers request `load_booking_summary` on changes. The PHP callback compares `strtotime()` values inclusively, splits each matching row's comma-delimited times and formats them as `g:i A`. Overlapping ranges can yield several options. The fee is common to the options for the same party.
4. The browser inserts the HTML response into the form. For `individual` pricing the sum is adult count × adult rate + child count × child rate. The same sum is the fallback for an unrecognized type. For `group` the fee is exactly one adult rate. WooCommerce's `wc_price()` formats displayed amounts.
5. On a later frontend request with `adventure` in POST, `handle_booking_form()` loads the cart if needed, reads the product ID and builds the cart payload below. It adds one unit with a generated `unique_key` and redirects to the cart. The summary request and submit request have no server-side quote record linking them.
6. `update_cart_item_price()` applies the stored numeric fee during totals calculation. `override_booking_product_name()` reads the adventure title and its current `location` for cart display. `save_booking_data_to_order()` resolves the title for the order item and copies the stored fields once the WooCommerce line-item hook runs.

## Storage contract

| Layer | Persistence and ownership |
| --- | --- |
| WordPress content | `adventures` posts, standard featured-image support and built-in category relationships. The [configuration table](../README.md) lists every adventure meta key read. This plugin does not write those configuration fields. |
| Site option | `abp_product_id` stores the shared WooCommerce product ID. It is the only plugin-managed option. |
| WooCommerce product | Created via `WC_Product_Simple::save()`. Booking rows reference this common product. |
| Cart | WooCommerce receives `booking_data` and a separate `unique_key` generated with `uniqid('booking_', true)`. The latter distinguishes cart item data. |
| Order | WooCommerce owns physical order storage. The plugin calls `set_name()` and `add_meta_data()` on the line item. It neither selects an order storage engine nor declares HPOS compatibility. |

| Form input | Cart key inside `booking_data` | Order item meta label |
| --- | --- | --- |
| `adventure` (the adventure post ID) | `booking_id` | `Booking id` |
| `pickup_date` | `pickup_date` | `Pickup date` |
| `adv_time` | `pick_up_time` | `Pick up time` |
| `booking_fee` | `booking_fee` | `Booking fee` |
| `adult` | `adult` | `Adult` |
| `children` | `children` | `Children` |
| `adv_option` (option title) | `option` | `Option` |
| `pickup_location` (when posted) | `pickup_location` | `Pickup location` |

Labels are generated with `ucfirst(str_replace('_', ' ', $key))`. The cart's unique key is outside that loop. Date, time and option are stored as submitted strings. There is no separate reservation ID, capacity counter, schema migration, scheduled housekeeping or uninstall callback. The plugin supplies no lifecycle cleanup for its option or product.
