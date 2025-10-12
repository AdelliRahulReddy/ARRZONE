<?php
/**
 * Stores Taxonomy
 * Registers stores (Amazon, Flipkart, etc.) with Logo & Banner support
 * 
 * @package DealsIndia
 * @version 2.0 - Added Logo & Banner Meta Fields
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Stores Taxonomy
 */
function dealsindia_register_store_taxonomy() {
    
    $labels = array(
        'name'              => __('Stores', 'dealsindia'),
        'singular_name'     => __('Store', 'dealsindia'),
        'search_items'      => __('Search Stores', 'dealsindia'),
        'all_items'         => __('All Stores', 'dealsindia'),
        'parent_item'       => __('Parent Store', 'dealsindia'),
        'parent_item_colon' => __('Parent Store:', 'dealsindia'),
        'edit_item'         => __('Edit Store', 'dealsindia'),
        'update_item'       => __('Update Store', 'dealsindia'),
        'add_new_item'      => __('Add New Store', 'dealsindia'),
        'new_item_name'     => __('New Store Name', 'dealsindia'),
        'menu_name'         => __('Stores', 'dealsindia'),
    );
    
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'store'),
        'show_in_rest'      => true,
    );
    
    register_taxonomy('store', array('deals'), $args);
}
add_action('init', 'dealsindia_register_store_taxonomy');


// =====================================================
// STORE LOGO & BANNER META FIELDS
// =====================================================

/**
 * Add Store Meta Fields (New Store Form)
 */
function dealsindia_add_store_meta_fields() {
    ?>
    <div class="form-field term-logo-wrap">
        <label for="store_logo_id"><?php _e('Store Logo', 'dealsindia'); ?></label>
        <input type="hidden" id="store_logo_id" name="store_logo_id" value="">
        <button type="button" class="button store-logo-upload-btn"><?php _e('Upload Logo', 'dealsindia'); ?></button>
        <p class="description"><?php _e('Recommended size: 200x200px (square). Will be displayed in store listings.', 'dealsindia'); ?></p>
    </div>
    
    <div class="form-field term-banner-wrap">
        <label for="store_banner_id"><?php _e('Store Banner', 'dealsindia'); ?></label>
        <input type="hidden" id="store_banner_id" name="store_banner_id" value="">
        <button type="button" class="button store-banner-upload-btn"><?php _e('Upload Banner Image', 'dealsindia'); ?></button>
        <p class="description"><?php _e('Recommended size: 1920x400px. Will be used as hero background on store page.', 'dealsindia'); ?></p>
    </div>
    
    <div class="form-field term-cashback-wrap">
        <label for="store_cashback"><?php _e('Cashback Rate', 'dealsindia'); ?></label>
        <input type="text" id="store_cashback" name="store_cashback" placeholder="<?php _e('e.g., 5% Cashback', 'dealsindia'); ?>">
        <p class="description"><?php _e('Enter cashback rate (e.g., "5% Cashback" or "Upto 10%")', 'dealsindia'); ?></p>
    </div>
    <?php
}
add_action('store_add_form_fields', 'dealsindia_add_store_meta_fields');


/**
 * Edit Store Meta Fields (Edit Store Form)
 */
function dealsindia_edit_store_meta_fields($term) {
    // Get existing values
    $logo_id = get_term_meta($term->term_id, 'store_logo_id', true);
    $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
    
    $banner_id = get_term_meta($term->term_id, 'store_banner_id', true);
    $banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';
    
    $cashback = get_term_meta($term->term_id, 'store_cashback', true);
    ?>
    
    <!-- Store Logo Field -->
    <tr class="form-field term-logo-wrap">
        <th scope="row">
            <label for="store_logo_id"><?php _e('Store Logo', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="store_logo_id" name="store_logo_id" value="<?php echo esc_attr($logo_id); ?>">
            <button type="button" class="button store-logo-upload-btn">
                <?php echo $logo_url ? __('Change Logo', 'dealsindia') : __('Upload Logo', 'dealsindia'); ?>
            </button>
            <?php if ($logo_url) : ?>
                <button type="button" class="button store-logo-remove-btn" style="margin-left: 10px;">
                    <?php _e('Remove Logo', 'dealsindia'); ?>
                </button>
                <div class="store-logo-preview">
                    <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 150px; height: auto; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; padding: 5px; display: block;">
                </div>
            <?php endif; ?>
            <p class="description">
                <?php _e('Recommended size: 200x200px (square). Will be displayed in store listings.', 'dealsindia'); ?>
            </p>
        </td>
    </tr>
    
    <!-- Store Banner Field -->
    <tr class="form-field term-banner-wrap">
        <th scope="row">
            <label for="store_banner_id"><?php _e('Store Banner', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="store_banner_id" name="store_banner_id" value="<?php echo esc_attr($banner_id); ?>">
            <button type="button" class="button store-banner-upload-btn">
                <?php echo $banner_url ? __('Change Banner Image', 'dealsindia') : __('Upload Banner Image', 'dealsindia'); ?>
            </button>
            <?php if ($banner_url) : ?>
                <button type="button" class="button store-banner-remove-btn" style="margin-left: 10px;">
                    <?php _e('Remove Banner', 'dealsindia'); ?>
                </button>
                <div class="store-banner-preview">
                    <img src="<?php echo esc_url($banner_url); ?>" style="max-width: 400px; height: auto; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; display: block;">
                </div>
            <?php endif; ?>
            <p class="description">
                <?php _e('Recommended size: 1920x400px. This will be the hero background on the store page.', 'dealsindia'); ?>
            </p>
        </td>
    </tr>
    
    <!-- Cashback Rate Field -->
    <tr class="form-field term-cashback-wrap">
        <th scope="row">
            <label for="store_cashback"><?php _e('Cashback Rate', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="store_cashback" name="store_cashback" value="<?php echo esc_attr($cashback); ?>" class="regular-text" placeholder="<?php _e('e.g., 5% Cashback', 'dealsindia'); ?>">
            <p class="description">
                <?php _e('Enter cashback rate (e.g., "5% Cashback" or "Upto 10%")', 'dealsindia'); ?>
            </p>
        </td>
    </tr>
    <?php
}
add_action('store_edit_form_fields', 'dealsindia_edit_store_meta_fields');


/**
 * Save Store Meta Fields
 */
function dealsindia_save_store_meta_fields($term_id) {
    // Save Store Logo
    if (isset($_POST['store_logo_id'])) {
        update_term_meta($term_id, 'store_logo_id', sanitize_text_field($_POST['store_logo_id']));
    }
    
    // Save Store Banner
    if (isset($_POST['store_banner_id'])) {
        update_term_meta($term_id, 'store_banner_id', sanitize_text_field($_POST['store_banner_id']));
    }
    
    // Save Cashback Rate
    if (isset($_POST['store_cashback'])) {
        update_term_meta($term_id, 'store_cashback', sanitize_text_field($_POST['store_cashback']));
    }
}
add_action('created_store', 'dealsindia_save_store_meta_fields');
add_action('edited_store', 'dealsindia_save_store_meta_fields');


/**
 * Add Custom Columns to Store List Table
 */
function dealsindia_store_columns($columns) {
    $new_columns = array();
    
    // Checkbox column
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
    }
    
    // Logo column
    $new_columns['logo'] = __('Logo', 'dealsindia');
    
    // Name column
    $new_columns['name'] = $columns['name'];
    
    // Cashback column
    $new_columns['cashback'] = __('Cashback', 'dealsindia');
    
    // Other columns
    if (isset($columns['description'])) {
        $new_columns['description'] = $columns['description'];
    }
    if (isset($columns['slug'])) {
        $new_columns['slug'] = $columns['slug'];
    }
    if (isset($columns['posts'])) {
        $new_columns['posts'] = $columns['posts'];
    }
    
    return $new_columns;
}
add_filter('manage_edit-store_columns', 'dealsindia_store_columns');


/**
 * Display Custom Column Content
 */
function dealsindia_store_column_content($content, $column_name, $term_id) {
    if ($column_name === 'logo') {
        $logo_id = get_term_meta($term_id, 'store_logo_id', true);
        if ($logo_id) {
            $logo_url = wp_get_attachment_url($logo_id);
            if ($logo_url) {
                $content = '<img src="' . esc_url($logo_url) . '" style="width: 50px; height: 50px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; padding: 3px;">';
            } else {
                $content = '—';
            }
        } else {
            $content = '—';
        }
    }
    
    if ($column_name === 'cashback') {
        $cashback = get_term_meta($term_id, 'store_cashback', true);
        $content = $cashback ? '<strong style="color: #00897B;">' . esc_html($cashback) . '</strong>' : '—';
    }
    
    return $content;
}
add_filter('manage_store_custom_column', 'dealsindia_store_column_content', 10, 3);
