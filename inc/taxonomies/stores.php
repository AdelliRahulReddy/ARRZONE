<?php
if (!defined('ABSPATH')) exit; 
/**
 * Stores Taxonomy - Enhanced for Homepage-Style Pages
 * Registers stores (Amazon, Flipkart, etc.) with rich meta fields
 * 
 * Meta Fields:
 * - Store Logo (Image)
 * - Store Banner (Hero Image)
 * - Cashback Rate (Text)
 * - Featured Badge (Toggle)
 * - Description (Rich Text)
 * - Website URL (External Link)
 * - Featured Deals (Deal IDs)
 * - Custom Background Color
 * 
 * @package ARRZONE
 * @version 4.0 - Enhanced Meta Fields
 */


if (!defined('ABSPATH')) exit;


// =====================================================
// REGISTER STORES TAXONOMY
// =====================================================


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
        'rewrite'           => array(
            'slug'         => 'store',
            'with_front'   => false,
            'hierarchical' => true
        ),
        'show_in_rest'      => true,
    );


    register_taxonomy('store', array('deals'), $args);
}
add_action('init', 'dealsindia_register_store_taxonomy', 0);


// =====================================================
// STORE META FIELDS - ADD FORM
// =====================================================


/**
 * Add Store Meta Fields to Add New Store Form
 */
function dealsindia_add_store_meta_fields() {
    ?>
    <!-- Store Logo -->
    <div class="form-field term-logo-wrap">
        <label for="store_logo_id"><?php _e('Store Logo', 'dealsindia'); ?></label>
        <input type="hidden" id="store_logo_id" name="store_logo_id" value="">
        <button type="button" class="button store-logo-upload-btn"><?php _e('Upload Logo', 'dealsindia'); ?></button>
        <p class="description"><?php _e('Recommended size: 200x200px square. Displayed in store listings.', 'dealsindia'); ?></p>
    </div>


    <!-- Store Banner -->
    <div class="form-field term-banner-wrap">
        <label for="store_banner_id"><?php _e('Store Banner (Hero Image)', 'dealsindia'); ?></label>
        <input type="hidden" id="store_banner_id" name="store_banner_id" value="">
        <button type="button" class="button store-banner-upload-btn"><?php _e('Upload Banner Image', 'dealsindia'); ?></button>
        <p class="description"><?php _e('Recommended size: 1920x400px. Used as hero background on store page.', 'dealsindia'); ?></p>
    </div>


    <!-- Cashback Rate -->
    <div class="form-field term-cashback-wrap">
        <label for="store_cashback"><?php _e('Cashback Rate', 'dealsindia'); ?></label>
        <input type="text" id="store_cashback" name="store_cashback" placeholder="<?php _e('e.g., Up to 5% Cashback', 'dealsindia'); ?>">
        <p class="description"><?php _e('Enter cashback rate, e.g., "5% Cashback" or "Up to 10%"', 'dealsindia'); ?></p>
    </div>


    <!-- Featured Store Toggle -->
    <div class="form-field term-featured-wrap">
        <label for="store_is_featured">
            <input type="checkbox" id="store_is_featured" name="store_is_featured" value="1">
            <?php _e('Mark as Featured Store', 'dealsindia'); ?>
        </label>
        <p class="description"><?php _e('Featured stores appear at the top of listings.', 'dealsindia'); ?></p>
    </div>


    <!-- Store Website URL -->
    <div class="form-field term-website-wrap">
        <label for="store_website_url"><?php _e('Store Website URL', 'dealsindia'); ?></label>
        <input type="url" id="store_website_url" name="store_website_url" placeholder="https://www.amazon.in" class="regular-text">
        <p class="description"><?php _e('Official store website link.', 'dealsindia'); ?></p>
    </div>


    <!-- Custom Background Color -->
    <div class="form-field term-bgcolor-wrap">
        <label for="store_bg_color"><?php _e('Hero Background Color', 'dealsindia'); ?></label>
        <input type="text" id="store_bg_color" name="store_bg_color" value="#667eea" class="dealsindia-color-picker">
        <p class="description"><?php _e('Custom background color for store hero section.', 'dealsindia'); ?></p>
    </div>


    <!-- Featured Deal IDs -->
    <div class="form-field term-featured-deals-wrap">
        <label for="store_featured_deals"><?php _e('Featured Deal IDs (Optional)', 'dealsindia'); ?></label>
        <input type="text" id="store_featured_deals" name="store_featured_deals" placeholder="123, 456, 789" class="regular-text">
        <p class="description"><?php _e('Enter comma-separated deal post IDs to feature on store page.', 'dealsindia'); ?></p>
    </div>
    <?php
}
add_action('store_add_form_fields', 'dealsindia_add_store_meta_fields');


// =====================================================
// STORE META FIELDS - EDIT FORM
// =====================================================


/**
 * Edit Store Meta Fields on Edit Store Form
 */
function dealsindia_edit_store_meta_fields($term) {
    // Get existing values
    $logo_id = get_term_meta($term->term_id, 'store_logo_id', true);
    $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
    
    $banner_id = get_term_meta($term->term_id, 'store_banner_id', true);
    $banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';
    
    $cashback = get_term_meta($term->term_id, 'store_cashback', true);
    $is_featured = get_term_meta($term->term_id, 'store_is_featured', true);
    $website_url = get_term_meta($term->term_id, 'store_website_url', true);
    $bg_color = get_term_meta($term->term_id, 'store_bg_color', true);
    if (!$bg_color) $bg_color = '#667eea';
    $featured_deals = get_term_meta($term->term_id, 'store_featured_deals', true);
    ?>
    
    <!-- Store Logo -->
    <tr class="form-field term-logo-wrap">
        <th scope="row" valign="top">
            <label for="store_logo_id"><?php _e('Store Logo', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="store_logo_id" name="store_logo_id" value="<?php echo esc_attr($logo_id); ?>">
            <button type="button" class="button store-logo-upload-btn">
                <?php echo $logo_url ? __('Change Logo', 'dealsindia') : __('Upload Logo', 'dealsindia'); ?>
            </button>
            <?php if ($logo_url) : ?>
                <button type="button" class="button store-logo-remove-btn" style="margin-left: 10px;"><?php _e('Remove Logo', 'dealsindia'); ?></button>
                <div class="store-logo-preview">
                    <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 150px; height: auto; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; padding: 5px; display: block;">
                </div>
            <?php endif; ?>
            <p class="description"><?php _e('Recommended size: 200x200px square.', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Store Banner -->
    <tr class="form-field term-banner-wrap">
        <th scope="row" valign="top">
            <label for="store_banner_id"><?php _e('Store Banner (Hero)', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="store_banner_id" name="store_banner_id" value="<?php echo esc_attr($banner_id); ?>">
            <button type="button" class="button store-banner-upload-btn">
                <?php echo $banner_url ? __('Change Banner', 'dealsindia') : __('Upload Banner', 'dealsindia'); ?>
            </button>
            <?php if ($banner_url) : ?>
                <button type="button" class="button store-banner-remove-btn" style="margin-left: 10px;"><?php _e('Remove Banner', 'dealsindia'); ?></button>
                <div class="store-banner-preview">
                    <img src="<?php echo esc_url($banner_url); ?>" style="max-width: 400px; height: auto; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; display: block;">
                </div>
            <?php endif; ?>
            <p class="description"><?php _e('Recommended size: 1920x400px. Hero background.', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Cashback Rate -->
    <tr class="form-field term-cashback-wrap">
        <th scope="row" valign="top">
            <label for="store_cashback"><?php _e('Cashback Rate', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="store_cashback" name="store_cashback" value="<?php echo esc_attr($cashback); ?>" class="regular-text" placeholder="<?php _e('e.g., 5% Cashback', 'dealsindia'); ?>">
            <p class="description"><?php _e('Enter cashback rate.', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Featured Store -->
    <tr class="form-field term-featured-wrap">
        <th scope="row" valign="top">
            <label for="store_is_featured"><?php _e('Featured Store', 'dealsindia'); ?></label>
        </th>
        <td>
            <label>
                <input type="checkbox" id="store_is_featured" name="store_is_featured" value="1" <?php checked($is_featured, '1'); ?>>
                <?php _e('Mark as Featured Store', 'dealsindia'); ?>
            </label>
            <p class="description"><?php _e('Featured stores appear at top of listings.', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Website URL -->
    <tr class="form-field term-website-wrap">
        <th scope="row" valign="top">
            <label for="store_website_url"><?php _e('Store Website URL', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="url" id="store_website_url" name="store_website_url" value="<?php echo esc_url($website_url); ?>" class="regular-text" placeholder="https://www.amazon.in">
            <p class="description"><?php _e('Official store website link.', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Background Color -->
    <tr class="form-field term-bgcolor-wrap">
        <th scope="row" valign="top">
            <label for="store_bg_color"><?php _e('Hero Background Color', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="store_bg_color" name="store_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="dealsindia-color-picker">
            <p class="description"><?php _e('Custom background color for store hero.', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Featured Deal IDs -->
    <tr class="form-field term-featured-deals-wrap">
        <th scope="row" valign="top">
            <label for="store_featured_deals"><?php _e('Featured Deal IDs', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="store_featured_deals" name="store_featured_deals" value="<?php echo esc_attr($featured_deals); ?>" class="regular-text" placeholder="123, 456, 789">
            <p class="description"><?php _e('Comma-separated deal post IDs to feature on store page.', 'dealsindia'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('store_edit_form_fields', 'dealsindia_edit_store_meta_fields');


// =====================================================
// SAVE STORE META
// =====================================================


/**
 * Save Store Meta Fields
 */
function dealsindia_save_store_meta_fields($term_id) {
    if (isset($_POST['store_logo_id'])) {
        update_term_meta($term_id, 'store_logo_id', sanitize_text_field($_POST['store_logo_id']));
    }
    
    if (isset($_POST['store_banner_id'])) {
        update_term_meta($term_id, 'store_banner_id', sanitize_text_field($_POST['store_banner_id']));
    }
    
    if (isset($_POST['store_cashback'])) {
        update_term_meta($term_id, 'store_cashback', sanitize_text_field($_POST['store_cashback']));
    }
    
    // Featured toggle
    if (isset($_POST['store_is_featured'])) {
        update_term_meta($term_id, 'store_is_featured', '1');
    } else {
        update_term_meta($term_id, 'store_is_featured', '0');
    }
    
    if (isset($_POST['store_website_url'])) {
        update_term_meta($term_id, 'store_website_url', esc_url_raw($_POST['store_website_url']));
    }
    
    if (isset($_POST['store_bg_color'])) {
        update_term_meta($term_id, 'store_bg_color', sanitize_hex_color($_POST['store_bg_color']));
    }
    
    if (isset($_POST['store_featured_deals'])) {
        update_term_meta($term_id, 'store_featured_deals', sanitize_text_field($_POST['store_featured_deals']));
    }
}
add_action('created_store', 'dealsindia_save_store_meta_fields');
add_action('edited_store', 'dealsindia_save_store_meta_fields');


// =====================================================
// ADMIN COLUMNS
// =====================================================


/**
 * Add Custom Columns to Store List Table
 */
function dealsindia_store_columns($columns) {
    $new_columns = array();
    
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
    }
    
    $new_columns['logo'] = __('Logo', 'dealsindia');
    $new_columns['name'] = $columns['name'];
    $new_columns['cashback'] = __('Cashback', 'dealsindia');
    $new_columns['featured'] = __('Featured', 'dealsindia');
    
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
            }
        } else {
            $content = '—';
        }
    }
    
    if ($column_name === 'cashback') {
        $cashback = get_term_meta($term_id, 'store_cashback', true);
        $content = $cashback ? '<strong style="color: #00897B;">' . esc_html($cashback) . '</strong>' : '—';
    }
    
    if ($column_name === 'featured') {
        $is_featured = get_term_meta($term_id, 'store_is_featured', true);
        $content = $is_featured == '1' ? '<span style="color: #f39c12; font-weight: bold;">⭐ Featured</span>' : '—';
    }
    
    return $content;
}
add_filter('manage_store_custom_column', 'dealsindia_store_column_content', 10, 3);


// =====================================================
// COLOR PICKER SCRIPT
// =====================================================


/**
 * Enqueue Color Picker for Store Taxonomy
 */
function dealsindia_store_admin_scripts($hook) {
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen->taxonomy !== 'store') {
        return;
    }
    
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    wp_add_inline_script('wp-color-picker', '
        jQuery(document).ready(function($) {
            $(".dealsindia-color-picker").wpColorPicker();
        });
    ');
}
add_action('admin_enqueue_scripts', 'dealsindia_store_admin_scripts');


// =====================================================
// CUSTOM REWRITE RULE FOR /stores/ BASE URL
// =====================================================


/**
 * Add Custom Rewrite Rule for /stores/ (All Stores Archive)
 * Maps /stores/ URL to page-all-stores.php template
 */
function dealsindia_stores_base_rewrite_rule() {
    add_rewrite_rule(
        '^stores/?$',
        'index.php?dealsindia_all_stores=1',
        'top'
    );
}
add_action('init', 'dealsindia_stores_base_rewrite_rule');


/**
 * Register Custom Query Var for Stores Archive
 */
function dealsindia_stores_query_var($vars) {
    $vars[] = 'dealsindia_all_stores';
    return $vars;
}
add_filter('query_vars', 'dealsindia_stores_query_var');


/**
 * Load page-all-stores.php Template for /stores/ URL
 */
function dealsindia_stores_template_include($template) {
    if (get_query_var('dealsindia_all_stores')) {
        $new_template = locate_template('page-all-stores.php');
        if ($new_template) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dealsindia_stores_template_include', 99);
