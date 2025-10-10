<?php
/**
 * Work Step Meta Box
 * Custom fields for Work Steps post type
 */

if (!defined('ABSPATH')) exit;

/**
 * Add Step Meta Box
 */
add_action('add_meta_boxes', 'dealsindia_add_step_meta_box');
function dealsindia_add_step_meta_box() {
    add_meta_box(
        'step_icon',
        __('Step Icon', 'dealsindia'),
        'dealsindia_step_meta_box_callback',
        'work_step',
        'side',
        'default'
    );
}

/**
 * Meta Box HTML
 */
function dealsindia_step_meta_box_callback($post) {
    wp_nonce_field('dealsindia_save_step_meta', 'dealsindia_step_meta_nonce');
    
    $icon = get_post_meta($post->ID, 'step_icon', true);
    ?>
    <p>
        <label for="step_icon"><?php _e('Icon (Emoji)', 'dealsindia'); ?></label><br>
        <input type="text" id="step_icon" name="step_icon" value="<?php echo esc_attr($icon); ?>" placeholder="🛍️" style="width: 100%; padding: 8px; font-size: 24px;">
    </p>
    <p class="description"><?php _e('Enter an emoji icon for this step', 'dealsindia'); ?></p>
    <?php
}

/**
 * Save Meta Box Data
 */
add_action('save_post', 'dealsindia_save_step_meta');
function dealsindia_save_step_meta($post_id) {
    
    if (!isset($_POST['dealsindia_step_meta_nonce'])) return;
    if (!wp_verify_nonce($_POST['dealsindia_step_meta_nonce'], 'dealsindia_save_step_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    if (isset($_POST['step_icon'])) {
        update_post_meta($post_id, 'step_icon', sanitize_text_field($_POST['step_icon']));
    }
}
