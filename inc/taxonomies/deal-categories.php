<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Deal Categories Taxonomy - Enhanced for Homepage-Style Pages
 * Registers deal categories (Electronics, Fashion, Beauty, etc.) with rich meta fields
 * 
 * Meta Fields:
 * - Category Icon (Emoji + Image)
 * - Category Color
 * - Featured Toggle
 * - Banner Image (Hero)
 * - Description (Rich Text)
 * - Featured Deal IDs
 * - Related Categories
 * - Custom Background
 * 
 * @package ARRZONE
 * @version 4.0 - Enhanced Meta Fields
 */

// =====================================================
// REGISTER DEAL CATEGORIES TAXONOMY
// =====================================================

/**
 * Register Deal Categories Taxonomy
 */
function dealsindia_register_deal_category_taxonomy() {
    $labels = array(
        'name'              => __('Deal Categories', 'dealsindia'),
        'singular_name'     => __('Deal Category', 'dealsindia'),
        'search_items'      => __('Search Categories', 'dealsindia'),
        'all_items'         => __('All Categories', 'dealsindia'),
        'parent_item'       => __('Parent Category', 'dealsindia'),
        'parent_item_colon' => __('Parent Category:', 'dealsindia'),
        'edit_item'         => __('Edit Category', 'dealsindia'),
        'update_item'       => __('Update Category', 'dealsindia'),
        'add_new_item'      => __('Add New Category', 'dealsindia'),
        'new_item_name'     => __('New Category Name', 'dealsindia'),
        'menu_name'         => __('Categories', 'dealsindia'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array(
            'slug'         => 'category',
            'with_front'   => false,
            'hierarchical' => true
        ),
        'show_in_rest'      => true,
    );

    register_taxonomy('deal-category', array('deals'), $args);
}
add_action('init', 'dealsindia_register_deal_category_taxonomy', 0);

// =====================================================
// CATEGORY META FIELDS - ADD FORM
// =====================================================

/**
 * Add Category Meta Fields to Add New Category Form
 */
function dealsindia_add_category_meta_fields() {
    ?>
    <!-- Category Icon (Emoji) -->
    <div class="form-field term-icon-wrap">
        <label for="category_icon"><?php _e('Category Icon (Emoji)', 'dealsindia'); ?></label>
        <input type="text" id="category_icon" name="category_icon" placeholder="📱" maxlength="2" style="font-size: 24px;">
        <p class="description"><?php _e('Enter emoji icon (e.g., 📱 for Electronics, 👗 for Fashion)', 'dealsindia'); ?></p>
    </div>

    <!-- Category Icon Image (Fallback) -->
    <div class="form-field term-icon-image-wrap">
        <label for="category_icon_image_id"><?php _e('Category Icon Image (Optional)', 'dealsindia'); ?></label>
        <input type="hidden" id="category_icon_image_id" name="category_icon_image_id" value="">
        <button type="button" class="button category-icon-upload-btn"><?php _e('Upload Icon Image', 'dealsindia'); ?></button>
        <p class="description"><?php _e('Upload custom icon image. This overrides emoji if set. Size: 100x100px', 'dealsindia'); ?></p>
    </div>

    <!-- Category Color -->
    <div class="form-field term-color-wrap">
        <label for="category_color"><?php _e('Category Color', 'dealsindia'); ?></label>
        <input type="text" id="category_color" name="category_color" value="#667eea" class="dealsindia-color-picker">
        <p class="description"><?php _e('Used in category cards and hero backgrounds.', 'dealsindia'); ?></p>
    </div>

    <!-- Banner Image (Hero) -->
    <div class="form-field term-banner-wrap">
        <label for="category_banner_id"><?php _e('Banner Image (Hero)', 'dealsindia'); ?></label>
        <input type="hidden" id="category_banner_id" name="category_banner_id" value="">
        <button type="button" class="button category-banner-upload-btn"><?php _e('Upload Banner Image', 'dealsindia'); ?></button>
        <p class="description"><?php _e('Recommended size: 1920x400px. Used as hero background on category page.', 'dealsindia'); ?></p>
    </div>

    <!-- Featured Category Toggle -->
    <div class="form-field term-featured-wrap">
        <label for="category_is_featured">
            <input type="checkbox" id="category_is_featured" name="category_is_featured" value="1">
            <?php _e('Mark as Featured Category', 'dealsindia'); ?>
        </label>
        <p class="description"><?php _e('Featured categories appear prominently on homepage.', 'dealsindia'); ?></p>
    </div>

    <!-- Featured Deal IDs -->
    <div class="form-field term-featured-deals-wrap">
        <label for="category_featured_deals"><?php _e('Featured Deal IDs (Optional)', 'dealsindia'); ?></label>
        <input type="text" id="category_featured_deals" name="category_featured_deals" placeholder="123, 456, 789" class="regular-text">
        <p class="description"><?php _e('Enter comma-separated deal post IDs to feature on category page.', 'dealsindia'); ?></p>
    </div>

    <!-- Related Categories -->
    <div class="form-field term-related-wrap">
        <label for="category_related_ids"><?php _e('Related Category IDs (Optional)', 'dealsindia'); ?></label>
        <input type="text" id="category_related_ids" name="category_related_ids" placeholder="5, 12, 18" class="regular-text">
        <p class="description"><?php _e('Enter comma-separated category term IDs to show as related categories.', 'dealsindia'); ?></p>
    </div>

    <!-- Custom Background Gradient -->
    <div class="form-field term-bg-gradient-wrap">
        <label for="category_bg_gradient"><?php _e('Hero Background Gradient (Optional)', 'dealsindia'); ?></label>
        <input type="text" id="category_bg_gradient" name="category_bg_gradient" placeholder="linear-gradient(135deg, #667eea 0%, #764ba2 100%)" class="regular-text">
        <p class="description"><?php _e('CSS gradient for hero section. Leave empty to use solid color.', 'dealsindia'); ?></p>
    </div>
    <?php
}
add_action('deal-category_add_form_fields', 'dealsindia_add_category_meta_fields');

// =====================================================
// CATEGORY META FIELDS - EDIT FORM
// =====================================================

/**
 * Edit Category Meta Fields on Edit Category Form
 */
function dealsindia_edit_category_meta_fields($term) {
    // Get existing values
    $icon = get_term_meta($term->term_id, 'category_icon', true);
    $icon_image_id = get_term_meta($term->term_id, 'category_icon_image_id', true);
    $icon_image_url = $icon_image_id ? wp_get_attachment_url($icon_image_id) : '';
    
    $color = get_term_meta($term->term_id, 'category_color', true);
    if (!$color) $color = '#667eea';
    
    $banner_id = get_term_meta($term->term_id, 'category_banner_id', true);
    $banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';
    
    $is_featured = get_term_meta($term->term_id, 'category_is_featured', true);
    $featured_deals = get_term_meta($term->term_id, 'category_featured_deals', true);
    $related_ids = get_term_meta($term->term_id, 'category_related_ids', true);
    $bg_gradient = get_term_meta($term->term_id, 'category_bg_gradient', true);
    ?>
    
    <!-- Category Icon (Emoji) -->
    <tr class="form-field term-icon-wrap">
        <th scope="row" valign="top">
            <label for="category_icon"><?php _e('Category Icon (Emoji)', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="category_icon" name="category_icon" value="<?php echo esc_attr($icon); ?>" maxlength="2" style="font-size: 24px; width: 80px;">
            <p class="description"><?php _e('Enter emoji icon (e.g., 📱 for Electronics)', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Category Icon Image -->
    <tr class="form-field term-icon-image-wrap">
        <th scope="row" valign="top">
            <label for="category_icon_image_id"><?php _e('Category Icon Image', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="category_icon_image_id" name="category_icon_image_id" value="<?php echo esc_attr($icon_image_id); ?>">
            <button type="button" class="button category-icon-upload-btn">
                <?php echo $icon_image_url ? __('Change Icon Image', 'dealsindia') : __('Upload Icon Image', 'dealsindia'); ?>
            </button>
            <?php if ($icon_image_url) : ?>
                <button type="button" class="button category-icon-remove-btn" style="margin-left: 10px;"><?php _e('Remove Icon', 'dealsindia'); ?></button>
                <div class="category-icon-preview">
                    <img src="<?php echo esc_url($icon_image_url); ?>" style="width: 100px; height: 100px; object-fit: contain; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; padding: 5px; display: block;">
                </div>
            <?php endif; ?>
            <p class="description"><?php _e('Overrides emoji if set. Size: 100x100px', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Category Color -->
    <tr class="form-field term-color-wrap">
        <th scope="row" valign="top">
            <label for="category_color"><?php _e('Category Color', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="category_color" name="category_color" value="<?php echo esc_attr($color); ?>" class="dealsindia-color-picker">
            <p class="description"><?php _e('Used in category cards and hero backgrounds.', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Banner Image -->
    <tr class="form-field term-banner-wrap">
        <th scope="row" valign="top">
            <label for="category_banner_id"><?php _e('Banner Image (Hero)', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="category_banner_id" name="category_banner_id" value="<?php echo esc_attr($banner_id); ?>">
            <button type="button" class="button category-banner-upload-btn">
                <?php echo $banner_url ? __('Change Banner', 'dealsindia') : __('Upload Banner', 'dealsindia'); ?>
            </button>
            <?php if ($banner_url) : ?>
                <button type="button" class="button category-banner-remove-btn" style="margin-left: 10px;"><?php _e('Remove Banner', 'dealsindia'); ?></button>
                <div class="category-banner-preview">
                    <img src="<?php echo esc_url($banner_url); ?>" style="max-width: 400px; height: auto; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; display: block;">
                </div>
            <?php endif; ?>
            <p class="description"><?php _e('Recommended size: 1920x400px. Hero background.', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Featured Category -->
    <tr class="form-field term-featured-wrap">
        <th scope="row" valign="top">
            <label for="category_is_featured"><?php _e('Featured Category', 'dealsindia'); ?></label>
        </th>
        <td>
            <label>
                <input type="checkbox" id="category_is_featured" name="category_is_featured" value="1" <?php checked($is_featured, '1'); ?>>
                <?php _e('Mark as Featured Category', 'dealsindia'); ?>
            </label>
            <p class="description"><?php _e('Featured categories appear prominently on homepage.', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Featured Deal IDs -->
    <tr class="form-field term-featured-deals-wrap">
        <th scope="row" valign="top">
            <label for="category_featured_deals"><?php _e('Featured Deal IDs', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="category_featured_deals" name="category_featured_deals" value="<?php echo esc_attr($featured_deals); ?>" class="regular-text" placeholder="123, 456, 789">
            <p class="description"><?php _e('Comma-separated deal post IDs to feature on category page.', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Related Categories -->
    <tr class="form-field term-related-wrap">
        <th scope="row" valign="top">
            <label for="category_related_ids"><?php _e('Related Category IDs', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="category_related_ids" name="category_related_ids" value="<?php echo esc_attr($related_ids); ?>" class="regular-text" placeholder="5, 12, 18">
            <p class="description"><?php _e('Comma-separated category term IDs to show as related.', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Background Gradient -->
    <tr class="form-field term-bg-gradient-wrap">
        <th scope="row" valign="top">
            <label for="category_bg_gradient"><?php _e('Hero Background Gradient', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="category_bg_gradient" name="category_bg_gradient" value="<?php echo esc_attr($bg_gradient); ?>" class="large-text" placeholder="linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
            <p class="description"><?php _e('CSS gradient for hero section. Leave empty to use solid color.', 'dealsindia'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('deal-category_edit_form_fields', 'dealsindia_edit_category_meta_fields');

// =====================================================
// SAVE CATEGORY META
// =====================================================

/**
 * Save Category Meta Fields
 */
function dealsindia_save_category_meta_fields($term_id) {
    if (isset($_POST['category_icon'])) {
        update_term_meta($term_id, 'category_icon', sanitize_text_field($_POST['category_icon']));
    }
    
    if (isset($_POST['category_icon_image_id'])) {
        update_term_meta($term_id, 'category_icon_image_id', absint($_POST['category_icon_image_id']));
    }
    
    if (isset($_POST['category_color'])) {
        update_term_meta($term_id, 'category_color', sanitize_hex_color($_POST['category_color']));
    }
    
    if (isset($_POST['category_banner_id'])) {
        update_term_meta($term_id, 'category_banner_id', absint($_POST['category_banner_id']));
    }
    
    // Featured toggle (checkbox)
    $is_featured = isset($_POST['category_is_featured']) ? '1' : '0';
    update_term_meta($term_id, 'category_is_featured', $is_featured);
    
    // Featured deals (comma-separated IDs)
    if (isset($_POST['category_featured_deals'])) {
        update_term_meta($term_id, 'category_featured_deals', sanitize_text_field($_POST['category_featured_deals']));
    }
    
    if (isset($_POST['category_related_ids'])) {
        update_term_meta($term_id, 'category_related_ids', sanitize_text_field($_POST['category_related_ids']));
    }
    
    if (isset($_POST['category_bg_gradient'])) {
        update_term_meta($term_id, 'category_bg_gradient', sanitize_text_field($_POST['category_bg_gradient']));
    }
}
add_action('created_deal-category', 'dealsindia_save_category_meta_fields');
add_action('edited_deal-category', 'dealsindia_save_category_meta_fields');

// =====================================================
// ADMIN COLUMNS
// =====================================================

/**
 * Add Custom Columns to Category List Table
 */
function dealsindia_category_columns($columns) {
    $new_columns = array();
    
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
    }
    
    $new_columns['icon'] = __('Icon', 'dealsindia');
    $new_columns['name'] = $columns['name'];
    $new_columns['color'] = __('Color', 'dealsindia');
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
add_filter('manage_edit-deal-category_columns', 'dealsindia_category_columns');

/**
 * Display Custom Column Content
 */
function dealsindia_category_column_content($content, $column_name, $term_id) {
    if ($column_name === 'icon') {
        // Try icon image first
        $icon_image_id = get_term_meta($term_id, 'category_icon_image_id', true);
        if ($icon_image_id) {
            $icon_url = wp_get_attachment_url($icon_image_id);
            if ($icon_url) {
                $content = '<img src="' . esc_url($icon_url) . '" style="width: 40px; height: 40px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; padding: 2px;">';
            }
        } else {
            // Fallback to emoji
            $icon = get_term_meta($term_id, 'category_icon', true);
            $content = $icon ? '<span style="font-size: 32px;">' . esc_html($icon) . '</span>' : '—';
        }
    }
    
    if ($column_name === 'color') {
        $color = get_term_meta($term_id, 'category_color', true);
        if ($color) {
            $content = '<span style="display: inline-block; width: 50px; height: 25px; background: ' . esc_attr($color) . '; border: 1px solid #ddd; border-radius: 4px;"></span>';
        } else {
            $content = '—';
        }
    }
    
    if ($column_name === 'featured') {
        $is_featured = get_term_meta($term_id, 'category_is_featured', true);
        $content = $is_featured == '1' ? '<span style="color: #f39c12; font-weight: bold;">⭐ Featured</span>' : '—';
    }
    
    return $content;
}
add_filter('manage_deal-category_custom_column', 'dealsindia_category_column_content', 10, 3);

// =====================================================
// COLOR PICKER SCRIPT
// =====================================================

/**
 * Enqueue Color Picker for Category Taxonomy
 */
function dealsindia_category_admin_scripts($hook) {
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen->taxonomy !== 'deal-category') {
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
add_action('admin_enqueue_scripts', 'dealsindia_category_admin_scripts');

// =====================================================
// CUSTOM REWRITE RULE FOR /categories/ BASE URL
// =====================================================

/**
 * Add Custom Rewrite Rule for /categories/ (All Categories Archive)
 * Maps /categories/ URL to page-all-categories.php template
 */
function dealsindia_categories_base_rewrite_rule() {
    add_rewrite_rule(
        '^categories/?$',
        'index.php?dealsindia_all_categories=1',
        'top'
    );
}
add_action('init', 'dealsindia_categories_base_rewrite_rule');

/**
 * Register Custom Query Var for Categories Archive
 */
function dealsindia_categories_query_var($vars) {
    $vars[] = 'dealsindia_all_categories';
    return $vars;
}
add_filter('query_vars', 'dealsindia_categories_query_var');

/**
 * Load page-all-categories.php Template for /categories/ URL
 */
function dealsindia_categories_template_include($template) {
    if (get_query_var('dealsindia_all_categories')) {
        $new_template = locate_template('page-all-categories.php');
        if ($new_template) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dealsindia_categories_template_include', 99);
