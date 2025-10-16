<?php
if (!defined('ABSPATH')) exit; 
/**
 * Hero Banner Meta Box
 * Custom fields for Hero Banner post type
 */

/**
 * Add Banner Meta Box
 */
add_action('add_meta_boxes', 'dealsindia_add_banner_meta_box');
function dealsindia_add_banner_meta_box() {
    add_meta_box(
        'banner_details',
        __('Banner Details', 'dealsindia'),
        'dealsindia_banner_meta_box_callback',
        'hero_banner',
        'normal',
        'high'
    );
}

/**
 * Meta Box HTML
 */
function dealsindia_banner_meta_box_callback($post) {
    wp_nonce_field('dealsindia_save_banner_meta', 'dealsindia_banner_meta_nonce');
    
    // Get existing values
    $heading = get_post_meta($post->ID, 'banner_heading', true);
    $subheading = get_post_meta($post->ID, 'banner_subheading', true);
    $store_name = get_post_meta($post->ID, 'banner_store', true);
    $bg_color = get_post_meta($post->ID, 'banner_bg_color', true) ?: '#667eea';
    $cashback_text = get_post_meta($post->ID, 'banner_cashback', true);
    $button_text = get_post_meta($post->ID, 'banner_button_text', true);
    $button_link = get_post_meta($post->ID, 'banner_button_link', true);
    ?>
    
    <style>
        .banner-meta-row { margin-bottom: 15px; }
        .banner-meta-row label { display: inline-block; width: 150px; font-weight: 600; }
        .banner-meta-row input[type="text"],
        .banner-meta-row input[type="url"] { width: 400px; padding: 8px; }
    </style>
    
    <div class="banner-meta-row">
        <label for="banner_store"><?php _e('Store Name', 'dealsindia'); ?></label>
        <input type="text" id="banner_store" name="banner_store" value="<?php echo esc_attr($store_name); ?>" placeholder="Amazon">
    </div>
    
    <div class="banner-meta-row">
        <label for="banner_heading"><?php _e('Main Heading', 'dealsindia'); ?></label>
        <input type="text" id="banner_heading" name="banner_heading" value="<?php echo esc_attr($heading); ?>" placeholder="Upto 70% Off">
    </div>
    
    <div class="banner-meta-row">
        <label for="banner_subheading"><?php _e('Subheading', 'dealsindia'); ?></label>
        <input type="text" id="banner_subheading" name="banner_subheading" value="<?php echo esc_attr($subheading); ?>" placeholder="Electronics & Gadgets">
    </div>
    
    <div class="banner-meta-row">
        <label for="banner_bg_color"><?php _e('Background Color', 'dealsindia'); ?></label>
        <input type="text" id="banner_bg_color" name="banner_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="color-picker">
    </div>
    
    <div class="banner-meta-row">
        <label for="banner_cashback"><?php _e('Cashback Text', 'dealsindia'); ?></label>
        <input type="text" id="banner_cashback" name="banner_cashback" value="<?php echo esc_attr($cashback_text); ?>" placeholder="Upto 3% Cashback">
    </div>
    
    <div class="banner-meta-row">
        <label for="banner_button_text"><?php _e('Button Text', 'dealsindia'); ?></label>
        <input type="text" id="banner_button_text" name="banner_button_text" value="<?php echo esc_attr($button_text); ?>" placeholder="Shop Now">
    </div>
    
    <div class="banner-meta-row">
        <label for="banner_button_link"><?php _e('Button Link', 'dealsindia'); ?></label>
        <input type="url" id="banner_button_link" name="banner_button_link" value="<?php echo esc_attr($button_link); ?>" placeholder="https://example.com">
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
add_action('save_post', 'dealsindia_save_banner_meta');
function dealsindia_save_banner_meta($post_id) {
    
    if (!isset($_POST['dealsindia_banner_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['dealsindia_banner_meta_nonce'], 'dealsindia_save_banner_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    $fields = array('banner_heading', 'banner_subheading', 'banner_store', 'banner_bg_color', 
                    'banner_cashback', 'banner_button_text', 'banner_button_link');
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = $field === 'banner_button_link' ? esc_url_raw($_POST[$field]) : sanitize_text_field($_POST[$field]);
            update_post_meta($post_id, $field, $value);
        }
    }
}
