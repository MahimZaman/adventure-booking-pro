<?php
if (! defined('ABSPATH')) {
    exit;
}

class ABP_Shortcodes
{

    public function register_shortcodes()
    {
        add_shortcode('abp_booking_form', [$this, 'booking_form']);
    }

    public function booking_form($atts)
    {
        ob_start();
        $post_id = get_the_ID();
        if (!$post_id) return;

        $available_dates = get_post_meta($post_id, 'adventure_options', true);
        $size = get_post_meta($post_id, 'group-size', true);
        $departureDates = array_map(function ($range) {
            return [
                'start' => $range['departure_date_start'] ?? '',
                'end'   => $range['departure_date_end'] ?? ''
            ];
        }, (array) $available_dates);
        $commaSeparatedDates = esc_attr(wp_json_encode($departureDates));
?>
        <form class="abp-booking-form" method="POST" action="">
            <input type="hidden" name="adventure" id="adventure" value="<?php echo $post_id; ?>">
            <div class="abp-form-group">
                <label for="pickup_date" class="abp-label">Select date and travellers</label>
                <div class="abp-select-area">
                    <div class="abp-date-picker">
                        <button type="button" class="abp-picker-preview">
                            <span class="abp-icon">
                                <svg viewBox="0 0 24 24" width="18px" height="18px" class="" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8.007 2.75a.75.75 0 0 1 .748.752l-.002.748h2.512V3.5a.75.75 0 0 1 1.5 0v.75h2.472l-.002-.748a.75.75 0 1 1 1.5-.004l.002.752h4.018v17H3.245v-17h4.008l.002-.752a.75.75 0 0 1 .752-.748m-.758 3H4.745v14h14.51v-14h-2.513l.006 1.747-1.5.005-.006-1.752h-2.477V7.5h-1.5V5.75H8.749l-.004 1.752-1.5-.004zm-.004 6h5.5v5.5h-5.5zm1.5 1.5v2.5h2.5v-2.5z"></path>
                                </svg>
                            </span>
                            <span class="abp-button-text">
                                Pick a date
                            </span>
                        </button>
                        <input type="text" name="pickup_date" id="pickup_date" placeholder="Date of departure" data-dates='<?php echo $commaSeparatedDates; ?>' autocomplete="off" required/>
                    </div>
                    <div class="abp-traveller">
                        <button type="button" class="abp-open-traveller">
                            <span class="abp-icon">
                                <svg viewBox="0 0 24 24" width="18px" height="18px" class="" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M8 4.75a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-3 1.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m11 .5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-3 1.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0m-5 2.5a6.91 6.91 0 0 1 5.36 2.493 6.96 6.96 0 0 1 8.244 2.312l.146.199v4.996H2.25v-6.997l.147-.199A6.91 6.91 0 0 1 8 10.75m3.75 8.5h8.5v-2.995a5.46 5.46 0 0 0-8.5 0zm-1.5 0h-6.5v-4.994a5.41 5.41 0 0 1 4.245-2.006h.01a5.4 5.4 0 0 1 4.006 1.73 7 7 0 0 0-1.615 1.575l-.146.199z"></path>
                                </svg>
                            </span>
                            <span class="abp-button-text">
                                1 Adult
                            </span>
                        </button>
                        <div class="abp-select-traveller">
                            <div class="abp-number-group">
                                <span class="abp-number-label">
                                    <h4>Adults</h4>
                                    <span>Age 16-100</span>
                                </span>
                                <div class="abp-number-field">
                                    <span class="abp-minus">-</span>
                                    <input type="number" name="adult" id="adult" data-allowed="<?php echo $size; ?>" readonly min="1" max="<?php echo $size; ?>" value="1" required />
                                    <span class="abp-plus">+</span>
                                </div>
                            </div>
                            <div class="abp-number-group">
                                <span class="abp-number-label">
                                    <h4>Children</h4>
                                    <span>Age 1-15</span>
                                </span>
                                <div class="abp-number-field">
                                    <span class="abp-minus">-</span>
                                    <input type="number" name="children" id="children" readonly min="0" value="0" required />
                                    <span class="abp-plus">+</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $allow_location = get_post_meta($post_id, 'pickup_location_box', true);
            if ($allow_location == 'allow'):
            ?>
                <div class="abp-form-group">
                    <label for="pickup_location" class="abp-label">Pick Up Location</label>
                    <input name="pickup_location" id="pickup_location" type="text" placeholder="Enter pickup location" required>
                </div>
            <?php endif; ?>
            <div class="abp-booking-summary">

            </div>
            <button type="submit" class="abp-button">Book Now</button>
        </form>
<?php
        return ob_get_clean();
    }
}
