<?php
/**
 * Deal Meta Box
 * Custom fields for deals post type
 * 
 * @package DealsIndia
 * @version 2.0 - Added Expiry Date
 */

if (!defined('ABSPATH')) exit;

// Add Deal Meta Box
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
add_action('add_meta_boxes', 'dealsindia_add_deal_meta_box');

// Meta Box Callback
function dealsindia_deal_meta_box_callback($post) {
    wp_nonce_field('dealsindia_save_deal_meta', 'dealsindia_deal_meta_nonce');
    
    // Get existing values
    $deal_price = get_post_meta($post->ID, 'deal_price', true);
    $deal_original_price = get_post_meta($post->ID, 'deal_original_price', true);
    $deal_discount_percentage = get_post_meta($post->ID, 'deal_discount_percentage', true);
    $deal_url = get_post_meta($post->ID, 'deal_url', true);
    $coupon_code = get_post_meta($post->ID, 'coupon_code', true);
    $deal_type = get_post_meta($post->ID, 'deal_type', true);
    $is_featured = get_post_meta($post->ID, 'is_featured', true);
    $is_hot = get_post_meta($post->ID, 'is_hot', true);
    $deal_expiry_date = get_post_meta($post->ID, 'deal_expiry_date', true);
    $show_when_expired = get_post_meta($post->ID, 'show_when_expired', true);
    ?>
    
    <table class="form-table">
        
        <!-- Deal Type -->
        <tr>
            <th><label for="deal_type"><?php _e('Deal Type', 'dealsindia'); ?></label></th>
            <td>
                <select name="deal_type" id="deal_type" class="regular-text">
                    <option value="deal" <?php selected($deal_type, 'deal'); ?>><?php _e('Deal', 'dealsindia'); ?></option>
                    <option value="coupon" <?php selected($deal_type, 'coupon'); ?>><?php _e('Coupon Code', 'dealsindia'); ?></option>
                    <option value="offer" <?php selected($deal_type, 'offer'); ?>><?php _e('Offer', 'dealsindia'); ?></option>
                </select>
            </td>
        </tr>
        
        <!-- Deal URL -->
        <tr>
            <th><label for="deal_url"><?php _e('Deal/Affiliate URL', 'dealsindia'); ?></label></th>
            <td>
                <input type="url" id="deal_url" name="deal_url" 
                       value="<?php echo esc_url($deal_url); ?>" 
                       class="large-text" 
                       placeholder="https://example.com/product" />
                <p class="description"><?php _e('Enter the affiliate link or product URL', 'dealsindia'); ?></p>
            </td>
        </tr>
        
        <!-- Coupon Code -->
        <tr>
            <th><label for="coupon_code"><?php _e('Coupon Code', 'dealsindia'); ?></label></th>
            <td>
                <input type="text" id="coupon_code" name="coupon_code" 
                       value="<?php echo esc_attr($coupon_code); ?>" 
                       class="regular-text" 
                       placeholder="SAVE20" />
                <p class="description"><?php _e('Leave empty if no coupon code required', 'dealsindia'); ?></p>
            </td>
        </tr>
        
        <!-- Original Price -->
        <tr>
            <th><label for="deal_original_price"><?php _e('Original Price (₹)', 'dealsindia'); ?></label></th>
            <td>
                <input type="number" id="deal_original_price" name="deal_original_price" 
                       value="<?php echo esc_attr($deal_original_price); ?>" 
                       class="regular-text" 
                       step="0.01" 
                       placeholder="999.00" />
            </td>
        </tr>
        
        <!-- Deal Price -->
        <tr>
            <th><label for="deal_price"><?php _e('Deal Price (₹)', 'dealsindia'); ?></label></th>
            <td>
                <input type="number" id="deal_price" name="deal_price" 
                       value="<?php echo esc_attr($deal_price); ?>" 
                       class="regular-text" 
                       step="0.01" 
                       placeholder="499.00" />
            </td>
        </tr>
        
        <!-- Discount Percentage -->
        <tr>
            <th><label for="deal_discount_percentage"><?php _e('Discount %', 'dealsindia'); ?></label></th>
            <td>
                <input type="number" id="deal_discount_percentage" name="deal_discount_percentage" 
                       value="<?php echo esc_attr($deal_discount_percentage); ?>" 
                       class="small-text" 
                       min="0" 
                       max="100" 
                       placeholder="50" />
                <span>%</span>
                <p class="description"><?php _e('Auto-calculated if prices are entered, or enter manually', 'dealsindia'); ?></p>
            </td>
        </tr>
        
        <!-- Expiry Date (NEW) -->
        <tr>
            <th><label for="deal_expiry_date"><?php _e('Expiry Date', 'dealsindia'); ?></label></th>
            <td>
                <input type="datetime-local" id="deal_expiry_date" name="deal_expiry_date" 
                       value="<?php echo esc_attr($deal_expiry_date); ?>" 
                       class="regular-text" />
                <p class="description"><?php _e('Leave empty if deal doesn\'t expire. Format: YYYY-MM-DD HH:MM', 'dealsindia'); ?></p>
            </td>
        </tr>
        
        <!-- Show When Expired (NEW) -->
        <tr>
            <th><label for="show_when_expired"><?php _e('Show When Expired', 'dealsindia'); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" id="show_when_expired" name="show_when_expired" value="1" 
                           <?php checked($show_when_expired, '1'); ?> />
                    <?php _e('Display this deal even after expiry (with EXPIRED badge)', 'dealsindia'); ?>
                </label>
                <p class="description"><?php _e('If unchecked, deal will be hidden from listings after expiry', 'dealsindia'); ?></p>
            </td>
        </tr>
        
        <!-- Featured Deal -->
        <tr>
            <th><label for="is_featured"><?php _e('Featured Deal', 'dealsindia'); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                           <?php checked($is_featured, '1'); ?> />
                    <?php _e('Mark as featured deal', 'dealsindia'); ?>
                </label>
            </td>
        </tr>
        
        <!-- Hot Deal -->
        <tr>
            <th><label for="is_hot"><?php _e('Hot Deal 🔥', 'dealsindia'); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" id="is_hot" name="is_hot" value="1" 
                           <?php checked($is_hot, '1'); ?> />
                    <?php _e('Mark as hot deal (trending)', 'dealsindia'); ?>
                </label>
            </td>
        </tr>
        
    </table>
    
    <script>
    jQuery(document).ready(function($) {
        // Auto-calculate discount percentage
        $('#deal_original_price, #deal_price').on('input', function() {
            var original = parseFloat($('#deal_original_price').val());
            var deal = parseFloat($('#deal_price').val());
            
            if (original > 0 && deal > 0 && deal < original) {
                var discount = Math.round(((original - deal) / original) * 100);
                $('#deal_discount_percentage').val(discount);
            }
        });
    });
    </script>
    
    <?php
}

// Save Deal Meta
function dealsindia_save_deal_meta($post_id) {
    // Security checks
    if (!isset($_POST['dealsindia_deal_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['dealsindia_deal_meta_nonce'], 'dealsindia_save_deal_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Save fields
    $fields = array(
        'deal_price',
        'deal_original_price',
        'deal_discount_percentage',
        'deal_url',
        'coupon_code',
        'deal_type',
        'deal_expiry_date'
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Checkboxes
    update_post_meta($post_id, 'is_featured', isset($_POST['is_featured']) ? '1' : '0');
    update_post_meta($post_id, 'is_hot', isset($_POST['is_hot']) ? '1' : '0');
    update_post_meta($post_id, 'show_when_expired', isset($_POST['show_when_expired']) ? '1' : '0');
}
add_action('save_post', 'dealsindia_save_deal_meta');
