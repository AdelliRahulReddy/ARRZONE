<?php
/**
 * Taxonomy Term Meta Fields
 * Handles custom meta for Deal Categories ONLY
 * Store meta moved to inc/taxonomies/stores.php
 * 
 * @package DealsIndia
 * @version 3.1 - Removed Store Duplicates
 */

// ===================================================== 
// CATEGORY ICON & COLOR FIELDS
// ===================================================== 

/**
 * Add Category Icon Field (Add & Edit Forms)
 */
function dealsindia_add_category_icon_field($term) {
    $category_icon = '';
    $category_icon_image_id = '';
    $category_icon_url = '';
    $category_color = '';
    $is_featured = '';

    if (is_object($term)) {
        $category_icon = get_term_meta($term->term_id, 'category_icon', true);
        $category_icon_image_id = get_term_meta($term->term_id, 'category_icon_image_id', true);
        $category_icon_url = $category_icon_image_id ? wp_get_attachment_url($category_icon_image_id) : '';
        $category_color = get_term_meta($term->term_id, 'category_color', true);
        $is_featured = get_term_meta($term->term_id, 'is_featured', true);
    }
    ?>
    
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_icon"><?php _e('Category Icon (Emoji)', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="category_icon" name="category_icon" 
                   value="<?php echo esc_attr($category_icon); ?>" 
                   placeholder="🏷️" class="regular-text" />
            <p class="description"><?php _e('Enter an emoji icon (e.g., 👕 for Fashion, 📱 for Electronics)', 'dealsindia'); ?></p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_icon_image"><?php _e('Category Icon (Image)', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="category_icon_image_id" name="category_icon_image_id" 
                   value="<?php echo esc_attr($category_icon_image_id); ?>" />
            <div id="category-icon-preview-wrapper">
                <?php if ($category_icon_url) : ?>
                    <img id="category-icon-preview" src="<?php echo esc_url($category_icon_url); ?>" 
                         style="max-width: 80px; height: auto; display: block; margin-bottom: 10px;" />
                <?php else : ?>
                    <img id="category-icon-preview" src="" style="max-width: 80px; height: auto; display: none; margin-bottom: 10px;" />
                <?php endif; ?>
            </div>
            <button type="button" class="button category-icon-upload-btn">
                <?php _e('Upload Icon Image', 'dealsindia'); ?>
            </button>
            <?php if ($category_icon_url) : ?>
                <button type="button" class="button category-icon-remove-btn" style="margin-left: 5px;">
                    <?php _e('Remove Icon', 'dealsindia'); ?>
                </button>
            <?php endif; ?>
            <p class="description"><?php _e('Upload an image icon as an alternative to emoji.', 'dealsindia'); ?></p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="category_color"><?php _e('Category Color', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="category_color" name="category_color" 
                   value="<?php echo esc_attr($category_color ? $category_color : '#667eea'); ?>" 
                   class="color-picker" />
            <p class="description"><?php _e('Choose a brand color for this category', 'dealsindia'); ?></p>
        </td>
    </tr>

    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="is_featured_cat"><?php _e('Featured Category', 'dealsindia'); ?></label>
        </th>
        <td>
            <label>
                <input type="checkbox" id="is_featured_cat" name="is_featured_cat" value="1" 
                       <?php checked($is_featured, '1'); ?> />
                <?php _e('Feature this category in the "Trending Categories" section on homepage', 'dealsindia'); ?>
            </label>
            <p class="description">
                <?php _e('Check this box to display this category prominently on the homepage.', 'dealsindia'); ?>
            </p>
        </td>
    </tr>

    <script>
        jQuery(document).ready(function($) {
            $('.color-picker').wpColorPicker();
        });
    </script>
    <?php
}
add_action('deal_category_edit_form_fields', 'dealsindia_add_category_icon_field');
add_action('deal_category_add_form_fields', 'dealsindia_add_category_icon_field');


/**
 * Save Category Meta
 */
function dealsindia_save_category_meta($term_id) {
    if (isset($_POST['category_icon'])) {
        update_term_meta($term_id, 'category_icon', sanitize_text_field($_POST['category_icon']));
    }
    if (isset($_POST['category_icon_image_id'])) {
        update_term_meta($term_id, 'category_icon_image_id', absint($_POST['category_icon_image_id']));
    }
    if (isset($_POST['category_color'])) {
        update_term_meta($term_id, 'category_color', sanitize_hex_color($_POST['category_color']));
    }
    if (isset($_POST['is_featured_cat'])) {
        update_term_meta($term_id, 'is_featured', '1');
    } else {
        delete_term_meta($term_id, 'is_featured');
    }
}
add_action('created_deal_category', 'dealsindia_save_category_meta');
add_action('edited_deal_category', 'dealsindia_save_category_meta');


// ===================================================== 
// ENQUEUE MEDIA UPLOADER SCRIPTS - CATEGORIES ONLY
// ===================================================== 
function dealsindia_enqueue_category_meta_scripts($hook) {
    if ('edit-tags.php' !== $hook && 'term.php' !== $hook) {
        return;
    }

    // Only load on category pages
    $screen = get_current_screen();
    if (!$screen || $screen->taxonomy !== 'deal_category') {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    wp_enqueue_script(
        'dealsindia-category-icon-upload',
        get_template_directory_uri() . '/assets/js/category-icon-upload.js',
        array('jquery'),
        '1.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'dealsindia_enqueue_category_meta_scripts');
