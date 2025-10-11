<?php
/**
 * Work Step Meta Box
 * 
 * @package DealsIndia
 * @version 2.0 - Added Icon Field
 */

function dealsindia_add_step_meta_box() {
    add_meta_box(
        'step_details',
        __('Step Details', 'dealsindia'),
        'dealsindia_step_meta_box_callback',
        'work_step',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'dealsindia_add_step_meta_box');

function dealsindia_step_meta_box_callback($post) {
    wp_nonce_field('dealsindia_save_step_meta', 'dealsindia_step_meta_nonce');
    
    $step_number = get_post_meta($post->ID, 'step_number', true);
    $step_icon = get_post_meta($post->ID, 'step_icon', true);
    ?>
    
    <table class="form-table">
        <tr>
            <th><label for="step_number"><?php _e('Step Number', 'dealsindia'); ?></label></th>
            <td>
                <input type="number" id="step_number" name="step_number" 
                       value="<?php echo esc_attr($step_number); ?>" 
                       min="1" max="10" class="small-text" />
                <p class="description"><?php _e('Enter the step order (1, 2, 3, etc.)', 'dealsindia'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="step_icon"><?php _e('Step Icon', 'dealsindia'); ?></label></th>
            <td>
                <input type="text" id="step_icon" name="step_icon" 
                       value="<?php echo esc_attr($step_icon); ?>" 
                       class="regular-text" placeholder="🔍" />
                <p class="description">
                    <?php _e('Enter an emoji (🔍, 📋, 💰) or icon class (fa-search). Emojis recommended.', 'dealsindia'); ?>
                </p>
            </td>
        </tr>
    </table>
    
    <?php
}

function dealsindia_save_step_meta($post_id) {
    if (!isset($_POST['dealsindia_step_meta_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['dealsindia_step_meta_nonce'], 'dealsindia_save_step_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save step number
    if (isset($_POST['step_number'])) {
        update_post_meta($post_id, 'step_number', absint($_POST['step_number']));
    }

    // Save step icon
    if (isset($_POST['step_icon'])) {
        update_post_meta($post_id, 'step_icon', sanitize_text_field($_POST['step_icon']));
    }
}
add_action('save_post', 'dealsindia_save_step_meta');
