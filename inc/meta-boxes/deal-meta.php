<?php
/**
 * Deal Meta Box
 * Custom fields for Deals post type (price, coupon, affiliate link, etc.)
 */

if (!defined('ABSPATH')) exit;

/**
 * Add Deal Details Meta Box
 */
add_action('add_meta_boxes', 'dealsindia_add_deal_meta_box');
function dealsindia_add_deal_meta_box() {
    add_meta_box(
        'deal_details',
        __('Deal Details', 'dealsindia'),
        'dealsindia_deal_meta_box_callback',
        'deals',
        'normal',
        'high'
    );
}

/**
 * Meta Box HTML
 */
function dealsindia_deal_meta_box_callback($post) {
    wp_nonce_field('dealsindia_save_deal_meta', 'dealsindia_deal_meta_nonce');
    
    // Get existing values
    $original_price = get_post_meta($post->ID, 'original_price', true);
    $sale_price = get_post_meta($post->ID, 'sale_price', true);
    $coupon_code = get_post_meta($post->ID, 'coupon_code', true);
    $affiliate_link = get_post_meta($post->ID, 'affiliate_link', true);
    $expiry_date = get_post_meta($post->ID, 'expiry_date', true);
    $is_trending = get_post_meta($post->ID, 'is_trending', true);
    ?>
    
    <style>
        .deal-meta-row { margin-bottom: 15px; }
        .deal-meta-row label { display: inline-block; width: 150px; font-weight: 600; }
        .deal-meta-row input[type="text"],
        .deal-meta-row input[type="number"],
        .deal-meta-row input[type="url"],
        .deal-meta-row input[type="date"] { width: 400px; padding: 8px; }
        .deal-meta-row input[type="checkbox"] { margin-left: 150px; }
    </style>
    
    <div class="deal-meta-row">
        <label for="original_price"><?php _e('Original Price (₹)', 'dealsindia'); ?></label>
        <input type="number" id="original_price" name="original_price" value="<?php echo esc_attr($original_price); ?>" placeholder="999">
    </div>
    
    <div class="deal-meta-row">
        <label for="sale_price"><?php _e('Sale Price (₹)', 'dealsindia'); ?></label>
        <input type="number" id="sale_price" name="sale_price" value="<?php echo esc_attr($sale_price); ?>" placeholder="499">
    </div>
    
    <div class="deal-meta-row">
        <label for="coupon_code"><?php _e('Coupon Code', 'dealsindia'); ?></label>
        <input type="text" id="coupon_code" name="coupon_code" value="<?php echo esc_attr($coupon_code); ?>" placeholder="SAVE50">
    </div>
    
    <div class="deal-meta-row">
        <label for="affiliate_link"><?php _e('Affiliate Link', 'dealsindia'); ?></label>
        <input type="url" id="affiliate_link" name="affiliate_link" value="<?php echo esc_attr($affiliate_link); ?>" placeholder="https://example.com/deal">
    </div>
    
    <div class="deal-meta-row">
        <label for="expiry_date"><?php _e('Expiry Date', 'dealsindia'); ?></label>
        <input type="date" id="expiry_date" name="expiry_date" value="<?php echo esc_attr($expiry_date); ?>">
    </div>
    
    <div class="deal-meta-row">
        <label>
            <input type="checkbox" name="is_trending" value="1" <?php checked($is_trending, '1'); ?>>
            <?php _e('Mark as Hot Pick / Trending', 'dealsindia'); ?>
        </label>
    </div>
    
    <?php
}

/**
 * Save Meta Box Data
 */
add_action('save_post', 'dealsindia_save_deal_meta');
function dealsindia_save_deal_meta($post_id) {
    
    // Security checks
    if (!isset($_POST['dealsindia_deal_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['dealsindia_deal_meta_nonce'], 'dealsindia_save_deal_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    // Save fields
    if (isset($_POST['original_price'])) {
        update_post_meta($post_id, 'original_price', sanitize_text_field($_POST['original_price']));
    }
    
    if (isset($_POST['sale_price'])) {
        update_post_meta($post_id, 'sale_price', sanitize_text_field($_POST['sale_price']));
    }
    
    if (isset($_POST['coupon_code'])) {
        update_post_meta($post_id, 'coupon_code', sanitize_text_field($_POST['coupon_code']));
    }
    
    if (isset($_POST['affiliate_link'])) {
        update_post_meta($post_id, 'affiliate_link', esc_url_raw($_POST['affiliate_link']));
    }
    
    if (isset($_POST['expiry_date'])) {
        update_post_meta($post_id, 'expiry_date', sanitize_text_field($_POST['expiry_date']));
    }
    
    // Trending checkbox
    $is_trending = isset($_POST['is_trending']) ? '1' : '0';
    update_post_meta($post_id, 'is_trending', $is_trending);
}
