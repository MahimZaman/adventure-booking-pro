<?php
if (! defined('ABSPATH')) {
    exit;
}

class ABP_Ajax
{

    public function init_hooks()
    {
        $actions = array(
            'load_booking_summary'
        );

        foreach ($actions as $action) {
            add_action('wp_ajax_' . $action, [$this, $action]);
            add_action('wp_ajax_nopriv_' . $action, [$this, $action]);
        }
    }

    public function load_booking_summary()
    {
        $pickup_date = sanitize_text_field($_POST['pickUpDate']);
        $adult = (int) sanitize_text_field($_POST['adult']);
        $children = (int) sanitize_text_field($_POST['children']);
        $post_id = sanitize_text_field($_POST['post_id']);
        $price_per_adult = (float) get_post_meta($post_id, 'price_per_adult', true);
        $price_per_child = (float) get_post_meta($post_id, 'price_per_child', true);
        $total_price = ($price_per_adult * $adult) + ($price_per_child * $children);
        $options = get_post_meta($post_id, 'adventure_options', true) ? get_post_meta($post_id, 'adventure_options', true) : [];
        $pricing_type = get_post_meta($post_id, 'pricing_type', true);
        if ($pricing_type == 'group') {
            $total_price = $price_per_adult;
        }

        if (empty($options)) {
            echo '<p>No options found.</p>';
            wp_die();
        }

        foreach ($options as $optKey => $option) {
            $start_date = strtotime($option['departure_date_start']);
            $end_date = strtotime($option['departure_date_end']);
            $pickup_timestamp = strtotime($pickup_date);
            if (($pickup_timestamp >= $start_date) && ($pickup_timestamp <= $end_date)) {
                $title = $option['title'];
                $description = $option['description'];
                $times = explode(',', $option['pick_up_times']);
?>
                <label for="adv_option_<?php echo $optKey; ?>" class="abp-single-option">
                    <input type="radio" name="adv_option" value="<?php echo $title ?>" id="adv_option_<?php echo $optKey; ?>">
                    <div class="abp-option-detail">
                        <h2><?php echo $title; ?></h2>
                        <p class="abp-option-desc"><?php echo $description; ?></p>
                        <?php if ($pricing_type == 'individual'): ?>
                            <p><?php echo $adult . ($adult > 1 ? ' Adults x ' : ' Adult x ') . wc_price($price_per_adult); ?></p>
                            <?php if ($children > 0): ?>
                                <p><?php echo $children . ($children > 1 ? ' Children x ' : ' Child x ') . wc_price($price_per_child); ?></p>
                            <?php endif; ?>
                        <?php endif; ?>
                        <p class="abp-total">
                            <?php echo 'Total ' . wc_price($total_price); ?>
                        </p>
                        <?php foreach ($times as $time) {
                        ?>
                            <label for="adv_time_<?php echo strtotime($time); ?>">
                                <input type="radio" name="adv_time" id="adv_time_<?php echo strtotime($time); ?>" value="<?php echo date('g:i A', strtotime($time)) ?>">
                                <span><?php echo date('g:i A', strtotime($time)); ?></span>
                            </label>
                        <?php
                        } ?>
                    </div>
                </label>
        <?php
            }
        }
        ?>
        <input type="hidden" name="booking_fee" value="<?php echo $total_price; ?>">
<?php

        wp_die();
    }
}
