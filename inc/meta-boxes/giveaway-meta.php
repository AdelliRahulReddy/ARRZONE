<?php
if (!defined('ABSPATH')) exit; 
/**
 * Giveaway Meta Box
 * Custom fields for Giveaway post type
 */

/**
 * Add Giveaway Meta Box
 */
add_action('add_meta_boxes', 'dealsindia_add_giveaway_meta_box');
function dealsindia_add_giveaway_meta_box() {
    add_meta_box(
        'giveaway_details',
        __('Giveaway Details', 'dealsindia'),
        'dealsindia_giveaway_meta_box_callback',
        'giveaway',
        'normal',
        'high'
    );
}

/**
 * Meta Box HTML
 */
function dealsindia_giveaway_meta_box_callback($post) {
    wp_nonce_field('dealsindia_save_giveaway_meta', 'dealsindia_giveaway_meta_nonce');
    
    $prize = get_post_meta($post->ID, 'giveaway_prize', true);
    $bg_color = get_post_meta($post->ID, 'giveaway_bg_color', true) ?: '#f093fb';
    $button_text = get_post_meta($post->ID, 'giveaway_button_text', true);
    $button_link = get_post_meta($post->ID, 'giveaway_button_link', true);
    $is_active = get_post_meta($post->ID, 'giveaway_is_active', true);
    ?>
    
    <style>
        .giveaway-meta-row { margin-bottom: 15px; }
        .giveaway-meta-row label { display: inline-block; width: 150px; font-weight: 600; }
        .giveaway-meta-row input[type="text"],
        .giveaway-meta-row input[type="url"] { width: 400px; padding: 8px; }
    </style>
    
    <div class="giveaway-meta-row">
        <label for="giveaway_prize"><?php _e('Prize', 'dealsindia'); ?></label>
        <input type="text" id="giveaway_prize" name="giveaway_prize" value="<?php echo esc_attr($prize); ?>" placeholder="iPhone 14 Pro">
    </div>
    
    <div class="giveaway-meta-row">
        <label for="giveaway_bg_color"><?php _e('Background Color', 'dealsindia'); ?></label>
        <input type="text" id="giveaway_bg_color" name="giveaway_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="color-picker">
    </div>
    
    <div class="giveaway-meta-row">
        <label for="giveaway_button_text"><?php _e('Button Text', 'dealsindia'); ?></label>
        <input type="text" id="giveaway_button_text" name="giveaway_button_text" value="<?php echo esc_attr($button_text); ?>" placeholder="Participate Now">
    </div>
    
    <div class="giveaway-meta-row">
        <label for="giveaway_button_link"><?php _e('Button Link', 'dealsindia'); ?></label>
        <input type="url" id="giveaway_button_link" name="giveaway_button_link" value="<?php echo esc_attr($button_link); ?>" placeholder="https://example.com">
    </div>
    
    <div class="giveaway-meta-row">
        <label>
            <input type="checkbox" name="giveaway_is_active" value="1" <?php checked($is_active, '1'); ?>>
            <?php _e('Show on Homepage', 'dealsindia'); ?>
        </label>
    </div>
    
    <script>
        jQuery(document).ready(function($){
            $('.color-picker').wpColorPicker();
        });
    </script>
    <?php
}

/**
 * Save Meta Box Data
 */
add_action('save_post', 'dealsindia_save_giveaway_meta');
function dealsindia_save_giveaway_meta($post_id) {
    
    if (!isset($_POST['dealsindia_giveaway_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['dealsindia_giveaway_meta_nonce'], 'dealsindia_save_giveaway_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    $fields = array('giveaway_prize', 'giveaway_bg_color', 'giveaway_button_text', 'giveaway_button_link');
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = $field === 'giveaway_button_link' ? esc_url_raw($_POST[$field]) : sanitize_text_field($_POST[$field]);
            update_post_meta($post_id, $field, $value);
        }
    }
    
    $is_active = isset($_POST['giveaway_is_active']) ? '1' : '0';
    update_post_meta($post_id, 'giveaway_is_active', $is_active);
}
