<?php
if (!defined('ABSPATH')) exit; 
/**
 * Deal Meta Box - Minimal Smart Edition
 * Only 2 optional new fields, rest auto-generated
 * 
 * @package ARRZONE
 * @version 5.0
 */

// Add Deal Meta Box
function dealsindia_add_deal_meta_box() {
    add_meta_box(
        'deal_details',
        __('💰 Deal Details', 'dealsindia'),
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
    $is_featured = get_post_meta($post->ID, 'is_featured', true);
    $is_hot = get_post_meta($post->ID, 'is_hot', true);
    $deal_expiry_date = get_post_meta($post->ID, 'deal_expiry_date', true);
    $show_when_expired = get_post_meta($post->ID, 'show_when_expired', true);
    
    // NEW: Only 2 optional fields
    $cashback_percent = get_post_meta($post->ID, 'cashback_percent', true);
    $custom_back_content = get_post_meta($post->ID, 'custom_back_content', true);
    ?>
    
    <style>
        .deal-meta-hint {
            background: #e8f5e9;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #4caf50;
            border-radius: 4px;
        }
        .deal-meta-hint strong { color: #2e7d32; }
    </style>
    
    <div class="deal-meta-hint">
        💡 <strong>Smart Card System:</strong> Card back content auto-generates from post excerpt. 
        Add custom info only if needed!
    </div>
    
    <table class="form-table">
        
        <!-- Deal URL -->
        <tr>
            <th><label for="deal_url"><?php _e('Deal/Affiliate URL', 'dealsindia'); ?> <span style="color:red;">*</span></label></th>
            <td>
                <input type="url" id="deal_url" name="deal_url" 
                       value="<?php echo esc_url($deal_url); ?>" 
                       class="large-text" 
                       placeholder="https://example.com/product" 
                       required />
                <p class="description"><?php _e('Your affiliate link', 'dealsindia'); ?></p>
            </td>
        </tr>
        
        <!-- Coupon Code -->
        <tr>
            <th><label for="coupon_code"><?php _e('Coupon Code', 'dealsindia'); ?></label></th>
            <td>
                <input type="text" id="coupon_code" name="coupon_code" 
                       value="<?php echo esc_attr($coupon_code); ?>" 
                       class="regular-text" 
                       placeholder="SAVE20" 
                       style="text-transform:uppercase;" />
                <p class="description"><?php _e('Leave empty if no coupon. Shows half-covered on card, reveals on flip.', 'dealsindia'); ?></p>
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
            <th><label for="deal_price"><?php _e('Deal Price (₹)', 'dealsindia'); ?> <span style="color:red;">*</span></label></th>
            <td>
                <input type="number" id="deal_price" name="deal_price" 
                       value="<?php echo esc_attr($deal_price); ?>" 
                       class="regular-text" 
                       step="0.01" 
                       placeholder="499.00" 
                       required />
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
                       readonly 
                       style="background:#f0f0f0;" />
                <span>% (Auto-calculated)</span>
            </td>
        </tr>
        
        <!-- NEW: Cashback (Optional) -->
        <tr>
            <th><label for="cashback_percent"><?php _e('💰 Extra Cashback', 'dealsindia'); ?></label></th>
            <td>
                <input type="number" id="cashback_percent" name="cashback_percent" 
                       value="<?php echo esc_attr($cashback_percent); ?>" 
                       class="small-text" 
                       step="0.1" 
                       placeholder="5.5" />
                <span>%</span>
                <p class="description"><?php _e('Optional: Extra cashback % (e.g., "Extra 5% cashback")', 'dealsindia'); ?></p>
            </td>
        </tr>
        
        <!-- Expiry Date -->
        <tr>
            <th><label for="deal_expiry_date"><?php _e('Expiry Date', 'dealsindia'); ?></label></th>
            <td>
                <input type="datetime-local" id="deal_expiry_date" name="deal_expiry_date" 
                       value="<?php echo esc_attr($deal_expiry_date); ?>" 
                       class="regular-text" />
                <p class="description"><?php _e('Leave empty if no expiry', 'dealsindia'); ?></p>
            </td>
        </tr>
        
        <!-- Show When Expired -->
        <tr>
            <th><label for="show_when_expired"><?php _e('Show When Expired', 'dealsindia'); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" id="show_when_expired" name="show_when_expired" value="1" 
                           <?php checked($show_when_expired, '1'); ?> />
                    <?php _e('Display with EXPIRED badge after expiry', 'dealsindia'); ?>
                </label>
            </td>
        </tr>
        
        <!-- Featured Deal -->
        <tr>
            <th><label for="is_featured"><?php _e('Featured Deal ⭐', 'dealsindia'); ?></label></th>
            <td>
                <label>
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                           <?php checked($is_featured, '1'); ?> />
                    <?php _e('Mark as featured', 'dealsindia'); ?>
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
                    <?php _e('Mark as trending', 'dealsindia'); ?>
                </label>
            </td>
        </tr>
        
        <!-- NEW: Custom Back Content (Optional) -->
        <tr style="background:#fffef0; border-top:2px solid #ffc107;">
            <th colspan="2" style="padding:15px;">
                <h3 style="margin:0; color:#f57c00;">📋 Flip Card Back Content (Optional)</h3>
            </th>
        </tr>
        <tr style="background:#fffef0;">
            <th><label for="custom_back_content"><?php _e('Custom Card Info', 'dealsindia'); ?></label></th>
            <td>
                <textarea id="custom_back_content" name="custom_back_content" 
                          rows="4" 
                          class="large-text" 
                          placeholder="Leave empty to auto-generate from post excerpt.&#10;&#10;Or add custom info here:&#10;• Product feature 1&#10;• Product feature 2&#10;• Why this is a great deal"><?php echo esc_textarea($custom_back_content); ?></textarea>
                <p class="description">
                    <strong>Auto-Generation:</strong> If empty, we use post excerpt + "How to Use" instructions based on deal type.<br>
                    <strong>Custom:</strong> Add specific details you want on card back.
                </p>
            </td>
        </tr>
        
    </table>
    
    <script>
    jQuery(document).ready(function($) {
        // Auto-calculate discount
        function calculateDiscount() {
            var original = parseFloat($('#deal_original_price').val());
            var deal = parseFloat($('#deal_price').val());
            
            if (original > 0 && deal > 0 && deal < original) {
                var discount = Math.round(((original - deal) / original) * 100);
                $('#deal_discount_percentage').val(discount);
            } else {
                $('#deal_discount_percentage').val('');
            }
        }
        
        $('#deal_original_price, #deal_price').on('input', calculateDiscount);
        
        // Auto-uppercase coupon
        $('#coupon_code').on('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        calculateDiscount();
    });
    </script>
    
    <?php
}

// Save Deal Meta
function dealsindia_save_deal_meta($post_id) {
    if (!isset($_POST['dealsindia_deal_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['dealsindia_deal_meta_nonce'], 'dealsindia_save_deal_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Save URL separately (needs special sanitization)
    if (isset($_POST['deal_url'])) {
        update_post_meta($post_id, 'deal_url', esc_url_raw($_POST['deal_url']));
    }

    // Save text fields
    $text_fields = array(
        'deal_price',
        'deal_original_price',
        'deal_discount_percentage',
        'coupon_code',
        'deal_expiry_date',
        'cashback_percent'
    );

    foreach ($text_fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    // Save textarea separately
    if (isset($_POST['custom_back_content'])) {
        update_post_meta($post_id, 'custom_back_content', sanitize_textarea_field($_POST['custom_back_content']));
    }


    // Checkboxes
    update_post_meta($post_id, 'is_featured', isset($_POST['is_featured']) ? '1' : '0');
    update_post_meta($post_id, 'is_hot', isset($_POST['is_hot']) ? '1' : '0');
    update_post_meta($post_id, 'show_when_expired', isset($_POST['show_when_expired']) ? '1' : '0');
}
add_action('save_post', 'dealsindia_save_deal_meta');
