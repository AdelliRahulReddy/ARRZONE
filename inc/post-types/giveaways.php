<?php
if (!defined('ABSPATH')) exit; 
/**
 * Giveaways Custom Post Type
 * Registers Giveaways/Contests CPT for ARRZONE
 * 
 * @package ARRZONE
 * @version 2.0 - Fixed & Complete
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Giveaways Post Type
 */
function arrzone_register_giveaway_post_type() {
    
    $labels = array(
        'name'                  => _x('Giveaways', 'Post Type General Name', 'arrzone'),
        'singular_name'         => _x('Giveaway', 'Post Type Singular Name', 'arrzone'),
        'menu_name'             => __('Giveaways', 'arrzone'),
        'name_admin_bar'        => __('Giveaway', 'arrzone'),
        'archives'              => __('Giveaway Archives', 'arrzone'),
        'attributes'            => __('Giveaway Attributes', 'arrzone'),
        'parent_item_colon'     => __('Parent Giveaway:', 'arrzone'),
        'all_items'             => __('All Giveaways', 'arrzone'),
        'add_new_item'          => __('Add New Giveaway', 'arrzone'),
        'add_new'               => __('Add New', 'arrzone'),
        'new_item'              => __('New Giveaway', 'arrzone'),
        'edit_item'             => __('Edit Giveaway', 'arrzone'),
        'update_item'           => __('Update Giveaway', 'arrzone'),
        'view_item'             => __('View Giveaway', 'arrzone'),
        'view_items'            => __('View Giveaways', 'arrzone'),
        'search_items'          => __('Search Giveaway', 'arrzone'),
        'not_found'             => __('No giveaways found', 'arrzone'),
        'not_found_in_trash'    => __('No giveaways found in Trash', 'arrzone'),
        'featured_image'        => __('Giveaway Image', 'arrzone'),
        'set_featured_image'    => __('Set giveaway image', 'arrzone'),
        'remove_featured_image' => __('Remove giveaway image', 'arrzone'),
        'use_featured_image'    => __('Use as giveaway image', 'arrzone'),
        'insert_into_item'      => __('Insert into giveaway', 'arrzone'),
        'uploaded_to_this_item' => __('Uploaded to this giveaway', 'arrzone'),
        'items_list'            => __('Giveaways list', 'arrzone'),
        'items_list_navigation' => __('Giveaways list navigation', 'arrzone'),
        'filter_items_list'     => __('Filter giveaways list', 'arrzone'),
    );
    
    $args = array(
        'label'               => __('Giveaway', 'arrzone'),
        'description'         => __('Contests and Giveaways', 'arrzone'),
        'labels'              => $labels,
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
        'taxonomies'          => array(),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 22,
        'menu_icon'           => 'dashicons-awards',
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => true,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'query_var'           => true,
        'rewrite'             => array(
            'slug'       => 'giveaway',
            'with_front' => false,
            'pages'      => true,
            'feeds'      => true,
        ),
        'capability_type'     => 'post',
        'show_in_rest'        => true,
        'rest_base'           => 'giveaways',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );
    
    register_post_type('giveaway', $args);
}
add_action('init', 'arrzone_register_giveaway_post_type', 0);


/**
 * Add Giveaway Meta Boxes
 */
function arrzone_add_giveaway_meta_boxes() {
    add_meta_box(
        'giveaway_details',
        __('Giveaway Details', 'arrzone'),
        'arrzone_giveaway_details_callback',
        'giveaway',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'arrzone_add_giveaway_meta_boxes');


/**
 * Giveaway Meta Box Callback
 */
function arrzone_giveaway_details_callback($post) {
    wp_nonce_field('arrzone_save_giveaway_meta', 'arrzone_giveaway_nonce');
    
    $prize = get_post_meta($post->ID, 'giveaway_prize', true);
    $end_date = get_post_meta($post->ID, 'giveaway_end_date', true);
    $bg_color = get_post_meta($post->ID, 'giveaway_bg_color', true);
    $entry_url = get_post_meta($post->ID, 'giveaway_entry_url', true);
    $max_entries = get_post_meta($post->ID, 'giveaway_max_entries', true);
    $terms = get_post_meta($post->ID, 'giveaway_terms', true);
    ?>
    
    <table class="form-table">
        <tr>
            <th><label for="giveaway_prize"><?php _e('Prize', 'arrzone'); ?></label></th>
            <td>
                <input type="text" 
                       id="giveaway_prize" 
                       name="giveaway_prize" 
                       value="<?php echo esc_attr($prize); ?>" 
                       class="regular-text"
                       placeholder="<?php _e('e.g., iPhone 15 Pro Max', 'arrzone'); ?>">
                <p class="description"><?php _e('What will the winner receive?', 'arrzone'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="giveaway_end_date"><?php _e('End Date', 'arrzone'); ?></label></th>
            <td>
                <input type="date" 
                       id="giveaway_end_date" 
                       name="giveaway_end_date" 
                       value="<?php echo esc_attr($end_date); ?>" 
                       class="regular-text">
                <p class="description"><?php _e('When does this giveaway end?', 'arrzone'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="giveaway_bg_color"><?php _e('Background Color', 'arrzone'); ?></label></th>
            <td>
                <input type="color" 
                       id="giveaway_bg_color" 
                       name="giveaway_bg_color" 
                       value="<?php echo esc_attr($bg_color ? $bg_color : '#667eea'); ?>">
                <p class="description"><?php _e('Choose card background color for homepage display', 'arrzone'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="giveaway_entry_url"><?php _e('Entry URL (Optional)', 'arrzone'); ?></label></th>
            <td>
                <input type="url" 
                       id="giveaway_entry_url" 
                       name="giveaway_entry_url" 
                       value="<?php echo esc_url($entry_url); ?>" 
                       class="regular-text"
                       placeholder="<?php _e('https://forms.google.com/...', 'arrzone'); ?>">
                <p class="description"><?php _e('External entry form URL (Google Forms, Typeform, etc.)', 'arrzone'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="giveaway_max_entries"><?php _e('Max Entries (Optional)', 'arrzone'); ?></label></th>
            <td>
                <input type="number" 
                       id="giveaway_max_entries" 
                       name="giveaway_max_entries" 
                       value="<?php echo esc_attr($max_entries); ?>" 
                       class="small-text"
                       min="0"
                       placeholder="1000">
                <p class="description"><?php _e('Maximum number of entries allowed (leave empty for unlimited)', 'arrzone'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="giveaway_terms"><?php _e('Terms & Conditions', 'arrzone'); ?></label></th>
            <td>
                <textarea id="giveaway_terms" 
                          name="giveaway_terms" 
                          rows="5" 
                          class="large-text"
                          placeholder="<?php _e('Enter terms and conditions here...', 'arrzone'); ?>"><?php echo esc_textarea($terms); ?></textarea>
                <p class="description"><?php _e('Important rules and eligibility requirements', 'arrzone'); ?></p>
            </td>
        </tr>
    </table>
    
    <?php
}


/**
 * Save Giveaway Meta Data
 */
function arrzone_save_giveaway_meta($post_id) {
    // Check nonce
    if (!isset($_POST['arrzone_giveaway_nonce']) || 
        !wp_verify_nonce($_POST['arrzone_giveaway_nonce'], 'arrzone_save_giveaway_meta')) {
        return;
    }
    
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save fields
    $fields = array(
        'giveaway_prize'       => 'sanitize_text_field',
        'giveaway_end_date'    => 'sanitize_text_field',
        'giveaway_bg_color'    => 'sanitize_hex_color',
        'giveaway_entry_url'   => 'esc_url_raw',
        'giveaway_max_entries' => 'absint',
        'giveaway_terms'       => 'wp_kses_post',
    );
    
    foreach ($fields as $field => $sanitize) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, $sanitize($_POST[$field]));
        }
    }
}
add_action('save_post_giveaway', 'arrzone_save_giveaway_meta');


/**
 * Add Custom Columns to Giveaways List
 */
function arrzone_giveaway_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        if ($key === 'title') {
            $new_columns['prize'] = __('Prize', 'arrzone');
            $new_columns['end_date'] = __('End Date', 'arrzone');
            $new_columns['status'] = __('Status', 'arrzone');
        }
    }
    
    return $new_columns;
}
add_filter('manage_giveaway_posts_columns', 'arrzone_giveaway_columns');


/**
 * Populate Custom Columns
 */
function arrzone_giveaway_column_content($column, $post_id) {
    switch ($column) {
        case 'prize':
            $prize = get_post_meta($post_id, 'giveaway_prize', true);
            echo $prize ? esc_html($prize) : '—';
            break;
            
        case 'end_date':
            $end_date = get_post_meta($post_id, 'giveaway_end_date', true);
            if ($end_date) {
                echo esc_html(date('M d, Y', strtotime($end_date)));
            } else {
                echo '—';
            }
            break;
            
        case 'status':
            $end_date = get_post_meta($post_id, 'giveaway_end_date', true);
            if ($end_date) {
                $now = current_time('timestamp');
                $end = strtotime($end_date);
                
                if ($end < $now) {
                    echo '<span style="color: #dc3545; font-weight: bold;">⏰ Ended</span>';
                } else {
                    $days_left = floor(($end - $now) / (60 * 60 * 24));
                    echo '<span style="color: #28a745; font-weight: bold;">✅ Active (' . $days_left . ' days left)</span>';
                }
            } else {
                echo '<span style="color: #6c757d;">— No end date</span>';
            }
            break;
    }
}
add_action('manage_giveaway_posts_custom_column', 'arrzone_giveaway_column_content', 10, 2);


/**
 * Make Columns Sortable
 */
function arrzone_giveaway_sortable_columns($columns) {
    $columns['end_date'] = 'end_date';
    return $columns;
}
add_filter('manage_edit-giveaway_sortable_columns', 'arrzone_giveaway_sortable_columns');


/**
 * Flush Rewrite Rules on Activation
 */
function arrzone_giveaway_flush_rewrites() {
    arrzone_register_giveaway_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'arrzone_giveaway_flush_rewrites');
