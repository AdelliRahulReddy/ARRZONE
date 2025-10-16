<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Campaigns Taxonomy - Festival & Sale Events
 * Registers campaigns (Amazon Great Indian Festival, Diwali Sale, etc.)
 * 
 * Meta Fields:
 * - Campaign Banner Image (Hero)
 * - Start Date & End Date
 * - Campaign Status (Active/Upcoming/Expired)
 * - Campaign Icon (Emoji)
 * - Campaign Color Theme
 * - Featured Toggle
 * - Campaign Tagline
 * 
 * @package ARRZONE
 * @version 1.0 - Campaigns Launch
 */

// =====================================================
// REGISTER CAMPAIGNS TAXONOMY
// =====================================================

/**
 * Register Campaigns Taxonomy
 */
function dealsindia_register_campaign_taxonomy() {
    $labels = array(
        'name'              => __('Campaigns', 'dealsindia'),
        'singular_name'     => __('Campaign', 'dealsindia'),
        'search_items'      => __('Search Campaigns', 'dealsindia'),
        'all_items'         => __('All Campaigns', 'dealsindia'),
        'edit_item'         => __('Edit Campaign', 'dealsindia'),
        'update_item'       => __('Update Campaign', 'dealsindia'),
        'add_new_item'      => __('Add New Campaign', 'dealsindia'),
        'new_item_name'     => __('New Campaign Name', 'dealsindia'),
        'menu_name'         => __('Campaigns', 'dealsindia'),
    );

    $args = array(
        'hierarchical'      => true,  // ✅ CHANGED TO TRUE - Shows checkboxes
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array(
            'slug'       => 'campaign',
            'with_front' => false
        ),
        'show_in_rest'      => true,
    );

    register_taxonomy('campaign', array('deals'), $args);
}
add_action('init', 'dealsindia_register_campaign_taxonomy', 0);

// =====================================================
// CAMPAIGN META FIELDS - ADD FORM
// =====================================================

/**
 * Add Campaign Meta Fields to Add New Form
 */
function dealsindia_add_campaign_meta_fields() {
    ?>
    <!-- Campaign Icon (Emoji) -->
    <div class="form-field term-icon-wrap">
        <label for="campaign_icon"><?php _e('Campaign Icon (Emoji)', 'dealsindia'); ?></label>
        <input type="text" id="campaign_icon" name="campaign_icon" placeholder="🎉" maxlength="2" style="font-size: 24px;">
        <p class="description"><?php _e('Enter emoji icon (e.g., 🎉 for Festival, 🔥 for Sale)', 'dealsindia'); ?></p>
    </div>

    <!-- Campaign Banner -->
    <div class="form-field term-banner-wrap">
        <label for="campaign_banner_id"><?php _e('Campaign Banner Image', 'dealsindia'); ?></label>
        <input type="hidden" id="campaign_banner_id" name="campaign_banner_id" value="">
        <button type="button" class="button campaign-banner-upload-btn"><?php _e('Upload Banner Image', 'dealsindia'); ?></button>
        <p class="description"><?php _e('Recommended size: 1920x400px. Used as hero background on campaign page.', 'dealsindia'); ?></p>
    </div>

    <!-- Campaign Color -->
    <div class="form-field term-color-wrap">
        <label for="campaign_color"><?php _e('Campaign Color Theme', 'dealsindia'); ?></label>
        <input type="text" id="campaign_color" name="campaign_color" value="#ff6b6b" class="dealsindia-color-picker">
        <p class="description"><?php _e('Used in campaign badges and backgrounds.', 'dealsindia'); ?></p>
    </div>

    <!-- Start Date -->
    <div class="form-field term-start-date-wrap">
        <label for="campaign_start_date"><?php _e('Campaign Start Date', 'dealsindia'); ?></label>
        <input type="date" id="campaign_start_date" name="campaign_start_date" class="regular-text">
        <p class="description"><?php _e('When does this campaign start?', 'dealsindia'); ?></p>
    </div>

    <!-- End Date -->
    <div class="form-field term-end-date-wrap">
        <label for="campaign_end_date"><?php _e('Campaign End Date', 'dealsindia'); ?></label>
        <input type="date" id="campaign_end_date" name="campaign_end_date" class="regular-text">
        <p class="description"><?php _e('When does this campaign end?', 'dealsindia'); ?></p>
    </div>

    <!-- Campaign Tagline -->
    <div class="form-field term-tagline-wrap">
        <label for="campaign_tagline"><?php _e('Campaign Tagline', 'dealsindia'); ?></label>
        <input type="text" id="campaign_tagline" name="campaign_tagline" placeholder="Save Big This Diwali!" class="regular-text">
        <p class="description"><?php _e('Short tagline displayed on campaign page.', 'dealsindia'); ?></p>
    </div>

    <!-- Featured Campaign -->
    <div class="form-field term-featured-wrap">
        <label for="campaign_is_featured">
            <input type="checkbox" id="campaign_is_featured" name="campaign_is_featured" value="1">
            <?php _e('Mark as Featured Campaign', 'dealsindia'); ?>
        </label>
        <p class="description"><?php _e('Featured campaigns appear in Hot Picks section.', 'dealsindia'); ?></p>
    </div>
    <?php
}
add_action('campaign_add_form_fields', 'dealsindia_add_campaign_meta_fields');

// =====================================================
// CAMPAIGN META FIELDS - EDIT FORM
// =====================================================

/**
 * Edit Campaign Meta Fields on Edit Form
 */
function dealsindia_edit_campaign_meta_fields($term) {
    // Get existing values
    $icon = get_term_meta($term->term_id, 'campaign_icon', true);
    $banner_id = get_term_meta($term->term_id, 'campaign_banner_id', true);
    $banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';
    $color = get_term_meta($term->term_id, 'campaign_color', true);
    if (!$color) $color = '#ff6b6b';
    $start_date = get_term_meta($term->term_id, 'campaign_start_date', true);
    $end_date = get_term_meta($term->term_id, 'campaign_end_date', true);
    $tagline = get_term_meta($term->term_id, 'campaign_tagline', true);
    $is_featured = get_term_meta($term->term_id, 'campaign_is_featured', true);
    
    // Calculate status
    $today = current_time('Y-m-d');
    $status = 'Active';
    if ($start_date && $today < $start_date) {
        $status = 'Upcoming';
    } elseif ($end_date && $today > $end_date) {
        $status = 'Expired';
    }
    ?>
    
    <!-- Campaign Status (Auto-calculated) -->
    <tr class="form-field">
        <th scope="row" valign="top">
            <label><?php _e('Campaign Status', 'dealsindia'); ?></label>
        </th>
        <td>
            <?php
            $status_colors = array(
                'Active' => '#00b894',
                'Upcoming' => '#fdcb6e',
                'Expired' => '#d63031'
            );
            $status_color = isset($status_colors[$status]) ? $status_colors[$status] : '#95a5a6';
            ?>
            <span style="display: inline-block; padding: 5px 15px; background: <?php echo esc_attr($status_color); ?>; color: white; border-radius: 4px; font-weight: bold;">
                <?php echo esc_html($status); ?>
            </span>
            <p class="description"><?php _e('Status is automatically calculated from start/end dates.', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Campaign Icon -->
    <tr class="form-field term-icon-wrap">
        <th scope="row" valign="top">
            <label for="campaign_icon"><?php _e('Campaign Icon', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="campaign_icon" name="campaign_icon" value="<?php echo esc_attr($icon); ?>" maxlength="2" style="font-size: 24px; width: 80px;">
            <p class="description"><?php _e('Emoji icon for the campaign', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Campaign Banner -->
    <tr class="form-field term-banner-wrap">
        <th scope="row" valign="top">
            <label for="campaign_banner_id"><?php _e('Campaign Banner', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="hidden" id="campaign_banner_id" name="campaign_banner_id" value="<?php echo esc_attr($banner_id); ?>">
            <button type="button" class="button campaign-banner-upload-btn">
                <?php echo $banner_url ? __('Change Banner', 'dealsindia') : __('Upload Banner', 'dealsindia'); ?>
            </button>
            <?php if ($banner_url) : ?>
                <button type="button" class="button campaign-banner-remove-btn" style="margin-left: 10px;"><?php _e('Remove Banner', 'dealsindia'); ?></button>
                <div class="campaign-banner-preview">
                    <img src="<?php echo esc_url($banner_url); ?>" style="max-width: 400px; height: auto; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; display: block;">
                </div>
            <?php endif; ?>
            <p class="description"><?php _e('Recommended: 1920x400px', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Campaign Color -->
    <tr class="form-field term-color-wrap">
        <th scope="row" valign="top">
            <label for="campaign_color"><?php _e('Campaign Color', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="campaign_color" name="campaign_color" value="<?php echo esc_attr($color); ?>" class="dealsindia-color-picker">
            <p class="description"><?php _e('Theme color for badges and accents', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Start Date -->
    <tr class="form-field term-start-date-wrap">
        <th scope="row" valign="top">
            <label for="campaign_start_date"><?php _e('Start Date', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="date" id="campaign_start_date" name="campaign_start_date" value="<?php echo esc_attr($start_date); ?>" class="regular-text">
            <p class="description"><?php _e('Campaign start date', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- End Date -->
    <tr class="form-field term-end-date-wrap">
        <th scope="row" valign="top">
            <label for="campaign_end_date"><?php _e('End Date', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="date" id="campaign_end_date" name="campaign_end_date" value="<?php echo esc_attr($end_date); ?>" class="regular-text">
            <p class="description"><?php _e('Campaign end date', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Tagline -->
    <tr class="form-field term-tagline-wrap">
        <th scope="row" valign="top">
            <label for="campaign_tagline"><?php _e('Campaign Tagline', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" id="campaign_tagline" name="campaign_tagline" value="<?php echo esc_attr($tagline); ?>" class="large-text">
            <p class="description"><?php _e('Short promotional tagline', 'dealsindia'); ?></p>
        </td>
    </tr>

    <!-- Featured Toggle -->
    <tr class="form-field term-featured-wrap">
        <th scope="row" valign="top">
            <label for="campaign_is_featured"><?php _e('Featured Campaign', 'dealsindia'); ?></label>
        </th>
        <td>
            <label>
                <input type="checkbox" id="campaign_is_featured" name="campaign_is_featured" value="1" <?php checked($is_featured, '1'); ?>>
                <?php _e('Mark as Featured', 'dealsindia'); ?>
            </label>
            <p class="description"><?php _e('Featured campaigns appear in Hot Picks', 'dealsindia'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('campaign_edit_form_fields', 'dealsindia_edit_campaign_meta_fields');

// =====================================================
// SAVE CAMPAIGN META
// =====================================================

/**
 * Save Campaign Meta Fields
 */
function dealsindia_save_campaign_meta_fields($term_id) {
    if (isset($_POST['campaign_icon'])) {
        update_term_meta($term_id, 'campaign_icon', sanitize_text_field($_POST['campaign_icon']));
    }
    
    if (isset($_POST['campaign_banner_id'])) {
        update_term_meta($term_id, 'campaign_banner_id', absint($_POST['campaign_banner_id']));
    }
    
    if (isset($_POST['campaign_color'])) {
        update_term_meta($term_id, 'campaign_color', sanitize_hex_color($_POST['campaign_color']));
    }
    
    if (isset($_POST['campaign_start_date'])) {
        update_term_meta($term_id, 'campaign_start_date', sanitize_text_field($_POST['campaign_start_date']));
    }
    
    if (isset($_POST['campaign_end_date'])) {
        update_term_meta($term_id, 'campaign_end_date', sanitize_text_field($_POST['campaign_end_date']));
    }
    
    if (isset($_POST['campaign_tagline'])) {
        update_term_meta($term_id, 'campaign_tagline', sanitize_text_field($_POST['campaign_tagline']));
    }
    
    // Featured toggle (checkbox - isset is enough)
    $is_featured = isset($_POST['campaign_is_featured']) ? '1' : '0';
    update_term_meta($term_id, 'campaign_is_featured', $is_featured);
}
add_action('created_campaign', 'dealsindia_save_campaign_meta_fields');
add_action('edited_campaign', 'dealsindia_save_campaign_meta_fields');

// =====================================================
// ADMIN COLUMNS
// =====================================================

/**
 * Add Custom Columns to Campaign List Table
 */
function dealsindia_campaign_columns($columns) {
    $new_columns = array();
    
    if (isset($columns['cb'])) {
        $new_columns['cb'] = $columns['cb'];
    }
    
    $new_columns['icon'] = __('Icon', 'dealsindia');
    $new_columns['name'] = $columns['name'];
    $new_columns['status'] = __('Status', 'dealsindia');
    $new_columns['dates'] = __('Duration', 'dealsindia');
    $new_columns['featured'] = __('Featured', 'dealsindia');
    
    if (isset($columns['posts'])) {
        $new_columns['posts'] = $columns['posts'];
    }
    
    return $new_columns;
}
add_filter('manage_edit-campaign_columns', 'dealsindia_campaign_columns');

/**
 * Display Custom Column Content
 */
function dealsindia_campaign_column_content($content, $column_name, $term_id) {
    if ($column_name === 'icon') {
        $icon = get_term_meta($term_id, 'campaign_icon', true);
        $content = $icon ? '<span style="font-size: 32px;">' . esc_html($icon) . '</span>' : '—';
    }
    
    if ($column_name === 'status') {
        $start_date = get_term_meta($term_id, 'campaign_start_date', true);
        $end_date = get_term_meta($term_id, 'campaign_end_date', true);
        $today = current_time('Y-m-d');
        
        $status = 'Active';
        $color = '#00b894';
        
        if ($start_date && $today < $start_date) {
            $status = 'Upcoming';
            $color = '#fdcb6e';
        } elseif ($end_date && $today > $end_date) {
            $status = 'Expired';
            $color = '#d63031';
        }
        
        $content = '<span style="display: inline-block; padding: 3px 10px; background: ' . esc_attr($color) . '; color: white; border-radius: 3px; font-size: 11px; font-weight: bold;">' . esc_html($status) . '</span>';
    }
    
    if ($column_name === 'dates') {
        $start_date = get_term_meta($term_id, 'campaign_start_date', true);
        $end_date = get_term_meta($term_id, 'campaign_end_date', true);
        
        if ($start_date && $end_date) {
            $content = date('M d', strtotime($start_date)) . ' - ' . date('M d, Y', strtotime($end_date));
        } else {
            $content = '—';
        }
    }
    
    if ($column_name === 'featured') {
        $is_featured = get_term_meta($term_id, 'campaign_is_featured', true);
        $content = $is_featured == '1' ? '<span style="color: #f39c12; font-weight: bold;">⭐ Featured</span>' : '—';
    }
    
    return $content;
}
add_filter('manage_campaign_custom_column', 'dealsindia_campaign_column_content', 10, 3);

// =====================================================
// COLOR PICKER SCRIPT
// =====================================================

/**
 * Enqueue Color Picker for Campaign Taxonomy
 */
function dealsindia_campaign_admin_scripts($hook) {
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }
    
    $screen = get_current_screen();
    if ($screen->taxonomy !== 'campaign') {
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
add_action('admin_enqueue_scripts', 'dealsindia_campaign_admin_scripts');

// =====================================================
// CUSTOM REWRITE RULE FOR /campaigns/ BASE URL
// =====================================================

/**
 * Add Custom Rewrite Rule for /campaigns/ (All Campaigns Archive)
 */
function dealsindia_campaigns_base_rewrite_rule() {
    add_rewrite_rule(
        '^campaigns/?$',
        'index.php?dealsindia_all_campaigns=1',
        'top'
    );
}
add_action('init', 'dealsindia_campaigns_base_rewrite_rule');

/**
 * Register Custom Query Var for Campaigns Archive
 */
function dealsindia_campaigns_query_var($vars) {
    $vars[] = 'dealsindia_all_campaigns';
    return $vars;
}
add_filter('query_vars', 'dealsindia_campaigns_query_var');

/**
 * Load page-all-campaigns.php Template for /campaigns/ URL
 */
function dealsindia_campaigns_template_include($template) {
    if (get_query_var('dealsindia_all_campaigns')) {
        $new_template = locate_template('page-all-campaigns.php');
        if ($new_template) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'dealsindia_campaigns_template_include', 99);
