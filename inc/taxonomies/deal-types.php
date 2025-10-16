<?php
if (!defined('ABSPATH')) exit; 
/**
 * Deal Types Taxonomy - Enhanced for Homepage-Style Pages
 * Registers deal types (Coupons, Cashback, Bank Offers, etc.) with rich meta fields
 * 
 * Meta Fields:
 * - Deal Type Icon (Emoji)
 * - Deal Type Icon Image
 * - Banner Image (Hero)
 * - Custom Color Theme
 * - Featured Deal IDs
 * - Description (Rich Text)
 * - Custom Background
 * 
 * @package ARRZONE
 * @version 4.0 - Enhanced Meta Fields
 */


if (!defined('ABSPATH')) exit;


// =====================================================
// REGISTER DEAL TYPES TAXONOMY
// =====================================================


/**
 * Register Deal Types Taxonomy
 */
function dealsindia_register_deal_type_taxonomy() {
    $labels = array(
        'name'              => __('Deal Types', 'dealsindia'),
        'singular_name'     => __('Deal Type', 'dealsindia'),
        'search_items'      => __('Search Deal Types', 'dealsindia'),
        'all_items'         => __('All Deal Types', 'dealsindia'),
        'parent_item'       => __('Parent Deal Type', 'dealsindia'),
        'parent_item_colon' => __('Parent Deal Type:', 'dealsindia'),
        'edit_item'         => __('Edit Deal Type', 'dealsindia'),
        'update_item'       => __('Update Deal Type', 'dealsindia'),
        'add_new_item'      => __('Add New Deal Type', 'dealsindia'),
        'new_item_name'     => __('New Deal Type Name', 'dealsindia'),
        'menu_name'         => __('Deal Types', 'dealsindia'),
    );


    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array(
            'slug'       => 'deal-type',
            'with_front' => false
        ),
        'show_in_rest'      => true,
    );


    register_taxonomy('deal-type', array('deals'), $args);
}
add_action('init', 'dealsindia_register_deal_type_taxonomy', 0);


// =====================================================
// AUTO-CREATE DEFAULT DEAL TYPES
// =====================================================


/**
 * Create Default Deal Types on Theme Activation
 */
function dealsindia_create_default_deal_types() {
    if (get_option('dealsindia_default_deal_types_created')) {
        return;
    }


    $default_types = array(
        array(
            'name'  => '🎫 Deals',
            'slug'  => 'deals',
            'icon'  => '🎫',
            'color' => '#667eea'
        ),
        array(
            'name'  => '🎟️ Coupons',
            'slug'  => 'coupons',
            'icon'  => '🎟️',
            'color' => '#f39c12'
        ),
        array(
            'name'  => '💰 Cashback',
            'slug'  => 'cashback',
            'icon'  => '💰',
            'color' => '#00897b'
        ),
        array(
            'name'  => '🏦 Bank Offers',
            'slug'  => 'bank-offers',
            'icon'  => '🏦',
            'color' => '#e74c3c'
        ),
        array(
            'name'  => '🔥 Flash Sale',
            'slug'  => 'flash-sale',
            'icon'  => '🔥',
            'color' => '#ff6b6b'
        ),
        array(
            'name'  => '🎁 Freebies',
            'slug'  => 'freebies',
            'icon'  => '🎁',
            'color' => '#e056fd'
        ),
    );


    foreach ($default_types as $type) {
        if (!term_exists($type['slug'], 'deal-type')) {
            $term = wp_insert_term($type['name'], 'deal-type', array('slug' => $type['slug']));
            
            if (!is_wp_error($term)) {
                update_term_meta($term['term_id'], 'dealtype_icon', $type['icon']);
                update_term_meta($term['term_id'], 'dealtype_color', $type['color']);
            }
        }
    }


    update_option('dealsindia_default_deal_types_created', true);
}
add_action('after_switch_theme', 'dealsindia_create_default_deal_types');


// =====================================================
// DEAL TYPE META FIELDS - ADD FORM
// =====================================================


/**
 * Add Deal Type Meta Fields to Add New Form
 */
function dealsindia_add_dealtype_meta_fields() {
    ?>
    <!-- Deal Type Icon (Emoji) -->
    <div class="form-field term-icon-wrap">
        <label for="dealtype_icon"><?php _e('Deal Type Icon (Emoji)', 'dealsindia'); ?></label>
        <input type="text" id="dealtype_icon" name="dealtype_icon" placeholder="🎟️" maxlength="2" style="font-size: 24px;">
        <p class="description"><?php _e('Enter emoji icon (e.g., 🎟️ for Coupons, 💰 for Cashback)', 'dealsindia'); ?></p>
    </div>


    <!-- Deal Type Icon Image (Fallback) -->
    <div class="form-field term-icon-image-wrap">
        <label for="dealtype_icon_image_id"><?php _e('Icon Image (Optional)', 'dealsindia'); ?></label>
        <input type="hidden" id="dealtype_icon_image_id" name="dealtype_icon_image_id" value="">
        <button type="button" class="button dealtype-icon-upload-btn"><?php _e('Upload Icon Image', 'dealsindia'); ?></button>
        <p class="description"><?php _e('Upload custom icon image. This overrides emoji if set. Size: 100x100px', 'dealsindia'); ?></p>
    </div>


    <!-- Deal Type Color -->
    <div class="form-field term-color-wrap">
        <label for="dealtype_color"><?php _e('Deal Type Color', 'dealsindia'); ?></label>
        <input type="text" id="dealtype_color" name="dealtype_color" value="#667eea" class="dealsindia-color-picker">
        <p class="description"><?php _e('Used in deal type badges and hero backgrounds.', 'dealsindia'); ?></p>
    </div>


    <!-- Banner Image (Hero) -->
    <div class="form-field term-banner-wrap">
        <label for="dealtype_banner_id"><?php _e('Banner Image (Hero)', 'dealsindia'); ?></label>
        <input type="hidden" id="dealtype_banner_id" name="dealtype_banner_id" value="">
        <button type="button" class="button dealtype-banner-upload-btn"><?php _e('Upload Banner Image', 'dealsindia'); ?></button>
        <p class="description"><?php _e('Recommended size: 1920x400px. Used as hero background on deal type page.', 'dealsindia'); ?></p>
    </div>


    <!-- Featured Deal IDs -->
    <div class="form-field term-featured-deals-wrap">
        <label for="dealtype_featured_deals"><?php _e('Featured Deal IDs (Optional)', 'dealsindia'); ?></label>
        <input type="text" id="dealtype_featured_deals" name="dealtype_featured_deals" placeholder="123, 456, 789" class="regular-text">
        <p class="description"><?php _e('Enter comma-separated deal post IDs to feature on deal type page.', 'dealsindia'); ?></p>
    </div>


    <!-- Custom Background Gradient -->
    <div class="form-field term-bg-gradient-wrap">
        <label for="dealtype_bg_gradient"><?php _e('Hero Background Gradient (Optional)', 'dealsindia'); ?></label>
        <input type="text" id="dealtype_bg_gradient" name="dealtype_bg_gradient" placeholder="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" class="regular-text">
        <p class="description"><?php _e('CSS gradient for hero section. Leave empty to use solid color.', 'dealsindia'); ?></p>
    </div>
    <?php
}
add_action('deal-type_add_form_fields', 'dealsindia_add_dealtype_meta_fields');


// =====================================================
// DEAL TYPE META FIELDS - EDIT FORM
// =====================================================


/**
 * Edit Deal Type Meta Fields on Edit Form
 */
function dealsindia_edit_dealtype_meta_fields($term) {
    // Get existing values
    $icon = get_term_meta($term->term_id, 'dealtype_icon', true);
    $icon_image_id = get_term_meta($term->term_id, 'dealtype_icon_image_id', true);
    $icon_image_url = $icon_image_id ? wp_get_attachment_url($icon_image_id) : '';
    
    $color = get_term_meta($term->term_id, 'dealtype_color', true);
    if (!$color) $color = '#667eea';
    
    $banner_id = get_term_meta($term->term_id, 'dealtype_banner_id', true);
    $banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';
    
    $featured_deals = get_term_meta($term->term_id, 'dealtype_featured_deals', true);
    $bg_gradient = get_term_meta($term->term_id, 'dealtype_bg_gradient', true);
    ?>
    
    <!-- Deal Type Icon (Emoji) -->
    <tr class="form-field term-icon-wrap">
        <th scope="row" valign="top">
            <label for="dealtype_icon"><?php _e('Icon (Emoji)', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="dealtype_icon" name="dealtype_icon" value="<?php echo esc_attr($icon); ?>" maxlength="2" style="font-size: 24px; width: 80px;">
            <p class="description"><?php _e('Enter emoji icon (e.g., 🎟️ for Coupons)', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Deal Type Icon Image -->
    <tr class="form-field term-icon-image-wrap">
        <th scope="row" valign="top">
            <label for="dealtype_icon_image_id"><?php _e('Icon Image', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="dealtype_icon_image_id" name="dealtype_icon_image_id" value="<?php echo esc_attr($icon_image_id); ?>">
            <button type="button" class="button dealtype-icon-upload-btn">
                <?php echo $icon_image_url ? __('Change Icon Image', 'dealsindia') : __('Upload Icon Image', 'dealsindia'); ?>
            </button>
            <?php if ($icon_image_url) : ?>
                <button type="button" class="button dealtype-icon-remove-btn" style="margin-left: 10px;"><?php _e('Remove Icon', 'dealsindia'); ?></button>
                <div class="dealtype-icon-preview">
                    <img src="<?php echo esc_url($icon_image_url); ?>" style="width: 100px; height: 100px; object-fit: contain; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; padding: 5px; display: block;">
                </div>
            <?php endif; ?>
            <p class="description"><?php _e('Overrides emoji if set. Size: 100x100px', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Deal Type Color -->
    <tr class="form-field term-color-wrap">
        <th scope="row" valign="top">
            <label for="dealtype_color"><?php _e('Color', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="dealtype_color" name="dealtype_color" value="<?php echo esc_attr($color); ?>" class="dealsindia-color-picker">
            <p class="description"><?php _e('Used in deal type badges and hero backgrounds.', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Banner Image -->
    <tr class="form-field term-banner-wrap">
        <th scope="row" valign="top">
            <label for="dealtype_banner_id"><?php _e('Banner Image (Hero)', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="dealtype_banner_id" name="dealtype_banner_id" value="<?php echo esc_attr($banner_id); ?>">
            <button type="button" class="button dealtype-banner-upload-btn">
                <?php echo $banner_url ? __('Change Banner', 'dealsindia') : __('Upload Banner', 'dealsindia'); ?>
            </button>
            <?php if ($banner_url) : ?>
                <button type="button" class="button dealtype-banner-remove-btn" style="margin-left: 10px;"><?php _e('Remove Banner', 'dealsindia'); ?></button>
                <div class="dealtype-banner-preview">
                    <img src="<?php echo esc_url($banner_url); ?>" style="max-width: 400px; height: auto; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; display: block;">
                </div>
            <?php endif; ?>
            <p class="description"><?php _e('Recommended size: 1920x400px. Hero background.', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Featured Deal IDs -->
    <tr class="form-field term-featured-deals-wrap">
        <th scope="row" valign="top">
            <label for="dealtype_featured_deals"><?php _e('Featured Deal IDs', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="dealtype_featured_deals" name="dealtype_featured_deals" value="<?php echo esc_attr($featured_deals); ?>" class="regular-text" placeholder="123, 456, 789">
            <p class="description"><?php _e('Comma-separated deal post IDs to feature on deal type page.', 'dealsindia'); ?></p>
        </td>
    </tr>


    <!-- Background Gradient -->
    <tr class="form-field term-bg-gradient-wrap">
        <th scope="row" valign="top">
            <label for="dealtype_bg_gradient"><?php _e('Hero Background Gradient', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="dealtype_bg_gradient" name="dealtype_bg_gradient" value="<?php echo esc_attr($bg_gradient); ?>" class="large-text" placeholder="linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
            <p class="description"><?php _e('CSS gradient for hero section. Leave empty to use solid color.', 'dealsindia'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('deal-type_edit_form_fields', 'dealsindia_edit_dealtype_meta_fields');


// =====================================================
// SAVE DEAL TYPE META
// =====================================================


/**
 * Save Deal Type Meta Fields
 */
function dealsindia_save_dealtype_meta_fields($term_id) {
    if (isset($_POST['dealtype_icon'])) {
        update_term_meta($term_id, 'dealtype_icon', sanitize_text_field($_POST['dealtype_icon']));
    }
    
    if (isset($_POST['dealtype_icon_image_id'])) {
        update_term_meta($term_id, 'dealtype_icon_image_id', sanitize_text_field($_POST['dealtype_icon_image_id']));
    }
    
    if (isset($_POST['dealtype_color'])) {
        update_term_meta($term_id, 'dealtype_color', sanitize_hex_color($_POST['dealtype_color']));
    }
    
    if (isset($_POST['dealtype_banner_id'])) {
        update_term_meta($term_id, 'dealtype_banner_id', sanitize_text_field($_POST['dealtype_banner_id']));
    }
    
    if (isset($_POST['dealtype_featured_deals'])) {
        update_term_meta($term_id, 'dealtype_featured_deals', sanitize_text_field($_POST['dealtype_featured_deals']));
    }
    
    if (isset($_POST['dealtype_bg_gradient'])) {
        update_term_meta($term_id, 'dealtype_bg_gradient', sanitize_text_field($_POST['dealtype_bg_gradient']));
    }
}
add_action('created_deal-type', 'dealsindia_save_dealtype_meta_fields');
add_action('edited_deal-type', 'dealsindia_save_dealtype_meta_fields');


// =====================================================
// ADMIN COLUMNS
// =====================================================


/**
 * Add Custom Columns to Deal Type List Table
 */
function dealsindia_dealtype_columns($columns) {
    $new_columns = array();
    
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
    }
    
    $new_columns['icon'] = __('Icon', 'dealsindia');
    $new_columns['name'] = $columns['name'];
    $new_columns['color'] = __('Color', 'dealsindia');
    
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
add_filter('manage_edit-deal-type_columns', 'dealsindia_dealtype_columns');


/**
 * Display Custom Column Content
 */
function dealsindia_dealtype_column_content($content, $column_name, $term_id) {
    if ($column_name === 'icon') {
        // Try icon image first
        $icon_image_id = get_term_meta($term_id, 'dealtype_icon_image_id', true);
        if ($icon_image_id) {
            $icon_url = wp_get_attachment_url($icon_image_id);
            if ($icon_url) {
                $content = '<img src="' . esc_url($icon_url) . '" style="width: 40px; height: 40px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; padding: 2px;">';
            }
        } else {
            // Fallback to emoji
            $icon = get_term_meta($term_id, 'dealtype_icon', true);
            $content = $icon ? '<span style="font-size: 32px;">' . esc_html($icon) . '</span>' : '—';
        }
    }
    
    if ($column_name === 'color') {
        $color = get_term_meta($term_id, 'dealtype_color', true);
        if ($color) {
            $content = '<span style="display: inline-block; width: 50px; height: 25px; background: ' . esc_attr($color) . '; border: 1px solid #ddd; border-radius: 4px;"></span>';
        } else {
            $content = '—';
        }
    }
    
    return $content;
}
add_filter('manage_deal-type_custom_column', 'dealsindia_dealtype_column_content', 10, 3);


// =====================================================
// COLOR PICKER SCRIPT
// =====================================================


/**
 * Enqueue Color Picker for Deal Type Taxonomy
 */
function dealsindia_dealtype_admin_scripts($hook) {
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen->taxonomy !== 'deal-type') {
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
add_action('admin_enqueue_scripts', 'dealsindia_dealtype_admin_scripts');


// =====================================================
// CUSTOM REWRITE RULE FOR /deal-types/ BASE URL
// =====================================================


/**
 * Add Custom Rewrite Rule for /deal-types/ (All Deal Types Archive)
 * Maps /deal-types/ URL to page-all-deal-types.php template
 */
function dealsindia_deal_types_base_rewrite_rule() {
    add_rewrite_rule(
        '^deal-types/?$',
        'index.php?dealsindia_all_deal_types=1',
        'top'
    );
}
add_action('init', 'dealsindia_deal_types_base_rewrite_rule');


/**
 * Register Custom Query Var for Deal Types Archive
 */
function dealsindia_deal_types_query_var($vars) {
    $vars[] = 'dealsindia_all_deal_types';
    return $vars;
}
add_filter('query_vars', 'dealsindia_deal_types_query_var');


/**
 * Load page-all-deal-types.php Template for /deal-types/ URL
 */
function dealsindia_deal_types_template_include($template) {
    if (get_query_var('dealsindia_all_deal_types')) {
        $new_template = locate_template('page-all-deal-types.php');
        if ($new_template) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dealsindia_deal_types_template_include', 99);
