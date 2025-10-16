<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Admin Setup - Consolidated Admin Functions
 * 
 * Merges:
 * - admin-columns.php (Custom admin columns)
 * - analytics-widget.php (Dashboard widget)
 * - cache-manager.php (Cache management)
 * - newsletter-admin.php (Newsletter management)
 * 
 * NO INLINE STYLES - Uses WordPress native admin classes
 * 
 * @package ARRZone
 * @version 1.0
 */

// =====================================================
// SECTION 1: CUSTOM ADMIN COLUMNS
// =====================================================

/**
 * Add custom columns to Deals admin list
 */
function arrzone_deals_admin_columns($columns) {
    $new_columns = array();
    
    // Keep checkbox and title
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    
    // Add custom columns
    $new_columns['deal_image'] = __('Image', 'dealsindia');
    $new_columns['deal_price'] = __('Price', 'dealsindia');
    $new_columns['deal_discount'] = __('Discount', 'dealsindia');
    $new_columns['deal_store'] = __('Store', 'dealsindia');
    $new_columns['deal_category'] = __('Category', 'dealsindia');
    $new_columns['deal_type'] = __('Type', 'dealsindia');
    $new_columns['deal_expiry'] = __('Expiry', 'dealsindia');
    $new_columns['deal_status'] = __('Status', 'dealsindia');
    
    // Keep date
    $new_columns['date'] = $columns['date'];
    
    return $new_columns;
}
add_filter('manage_deals_posts_columns', 'arrzone_deals_admin_columns');

/**
 * Populate custom admin columns
 */
function arrzone_deals_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'deal_image':
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, array(60, 60));
            } else {
                echo '—';
            }
            break;
            
        case 'deal_price':
            $sale_price = get_post_meta($post_id, 'deal_sale_price', true);
            $original_price = get_post_meta($post_id, 'deal_original_price', true);
            
            if ($sale_price) {
                echo '<strong>₹' . number_format($sale_price) . '</strong>';
                if ($original_price && $original_price > $sale_price) {
                    echo '<br><del>₹' . number_format($original_price) . '</del>';
                }
            } else {
                echo '—';
            }
            break;
            
        case 'deal_discount':
            $sale_price = get_post_meta($post_id, 'deal_sale_price', true);
            $original_price = get_post_meta($post_id, 'deal_original_price', true);
            
            if ($original_price && $sale_price && $original_price > $sale_price) {
                $discount = round((($original_price - $sale_price) / $original_price) * 100);
                echo '<span class="button button-small">' . $discount . '% OFF</span>';
            } else {
                echo '—';
            }
            break;
            
        case 'deal_store':
            $stores = get_the_terms($post_id, 'store');
            if ($stores && !is_wp_error($stores)) {
                $store_names = array();
                foreach ($stores as $store) {
                    $store_names[] = '<a href="' . esc_url(admin_url('edit.php?post_type=deals&store=' . $store->slug)) . '">' . esc_html($store->name) . '</a>';
                }
                echo implode(', ', $store_names);
            } else {
                echo '—';
            }
            break;
            
        case 'deal_category':
            $categories = get_the_terms($post_id, 'deal_category');
            if ($categories && !is_wp_error($categories)) {
                $cat_names = array();
                foreach ($categories as $cat) {
                    $cat_names[] = '<a href="' . esc_url(admin_url('edit.php?post_type=deals&deal_category=' . $cat->slug)) . '">' . esc_html($cat->name) . '</a>';
                }
                echo implode(', ', $cat_names);
            } else {
                echo '—';
            }
            break;
            
        case 'deal_type':
            $types = get_the_terms($post_id, 'deal_type');
            if ($types && !is_wp_error($types)) {
                $type_names = array();
                foreach ($types as $type) {
                    // Extract emoji if present
                    $name_parts = explode(' ', $type->name, 2);
                    $display_name = isset($name_parts[1]) ? $name_parts[0] . ' ' . $name_parts[1] : $type->name;
                    $type_names[] = '<span class="button button-small">' . esc_html($display_name) . '</span>';
                }
                echo implode(' ', $type_names);
            } else {
                echo '—';
            }
            break;
            
        case 'deal_expiry':
            $expiry_date = get_post_meta($post_id, 'deal_expiry_date', true);
            if ($expiry_date) {
                $expiry_timestamp = strtotime($expiry_date);
                $current_timestamp = current_time('timestamp');
                $is_expired = ($current_timestamp > $expiry_timestamp);
                
                if ($is_expired) {
                    echo '<span class="dashicons dashicons-no-alt"></span> <strong>Expired</strong><br>';
                    echo '<small>' . date_i18n(get_option('date_format'), $expiry_timestamp) . '</small>';
                } else {
                    $days_left = floor(($expiry_timestamp - $current_timestamp) / 86400);
                    if ($days_left > 7) {
                        echo '<span class="dashicons dashicons-yes-alt"></span> ' . $days_left . ' days';
                    } elseif ($days_left > 0) {
                        echo '<span class="dashicons dashicons-warning"></span> ' . $days_left . ' days';
                    } else {
                        echo '<span class="dashicons dashicons-clock"></span> Expires today!';
                    }
                    echo '<br><small>' . date_i18n(get_option('date_format'), $expiry_timestamp) . '</small>';
                }
            } else {
                echo 'No expiry';
            }
            break;
            
        case 'deal_status':
            $coupon_code = get_post_meta($post_id, 'deal_coupon_code', true);
            $affiliate_link = get_post_meta($post_id, 'deal_affiliate_link', true);
            
            if ($coupon_code) {
                echo '<span class="dashicons dashicons-tickets-alt"></span> Code<br>';
            }
            
            if ($affiliate_link) {
                echo '<span class="dashicons dashicons-admin-links"></span> Link';
            }
            
            if (!$coupon_code && !$affiliate_link) {
                echo '⚠️ Incomplete';
            }
            break;
    }
}
add_action('manage_deals_posts_custom_column', 'arrzone_deals_admin_column_content', 10, 2);

/**
 * Make custom columns sortable
 */
function arrzone_deals_sortable_columns($columns) {
    $columns['deal_expiry'] = 'deal_expiry_date';
    $columns['deal_price'] = 'deal_sale_price';
    return $columns;
}
add_filter('manage_edit-deals_sortable_columns', 'arrzone_deals_sortable_columns');

/**
 * Handle sorting by custom meta
 */
function arrzone_deals_orderby_custom($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('deal_expiry_date' === $orderby) {
        $query->set('meta_key', 'deal_expiry_date');
        $query->set('orderby', 'meta_value');
    }
    
    if ('deal_sale_price' === $orderby) {
        $query->set('meta_key', 'deal_sale_price');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'arrzone_deals_orderby_custom');

// =====================================================
// SECTION 2: DASHBOARD ANALYTICS WIDGET
// =====================================================

/**
 * Add analytics dashboard widget
 */
function arrzone_add_analytics_widget() {
    wp_add_dashboard_widget(
        'arrzone_analytics_widget',
        __('📊 ARRZone Analytics', 'dealsindia'),
        'arrzone_analytics_widget_content'
    );
}
add_action('wp_dashboard_setup', 'arrzone_add_analytics_widget');

/**
 * Analytics widget content - Uses WordPress native admin classes
 */
function arrzone_analytics_widget_content() {
    // Get counts
    $total_deals = wp_count_posts('deals')->publish;
    $total_giveaways = wp_count_posts('giveaway')->publish;
    $total_categories = wp_count_terms('deal_category', array('hide_empty' => false));
    $total_stores = wp_count_terms('store', array('hide_empty' => false));
    
    // Get expired deals count
    $expired_deals_query = new WP_Query(array(
        'post_type' => 'deals',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'deal_expiry_date',
                'value' => current_time('Y-m-d'),
                'compare' => '<',
                'type' => 'DATE'
            )
        ),
        'fields' => 'ids'
    ));
    $expired_count = $expired_deals_query->found_posts;
    
    // Get deals expiring soon (next 7 days)
    $expiring_soon_query = new WP_Query(array(
        'post_type' => 'deals',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'deal_expiry_date',
                'value' => array(
                    current_time('Y-m-d'),
                    date('Y-m-d', strtotime('+7 days'))
                ),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            )
        ),
        'fields' => 'ids'
    ));
    $expiring_soon_count = $expiring_soon_query->found_posts;
    
    ?>
    <div class="arrzone-analytics-widget">
        <table class="widefat striped">
            <tbody>
                <tr>
                    <td><strong>🎟️ Total Deals</strong></td>
                    <td><span class="button button-primary button-large disabled"><?php echo number_format($total_deals); ?></span></td>
                </tr>
                <tr>
                    <td><strong>🎁 Giveaways</strong></td>
                    <td><span class="button button-primary button-large disabled"><?php echo number_format($total_giveaways); ?></span></td>
                </tr>
                <tr>
                    <td><strong>🏪 Stores</strong></td>
                    <td><span class="button button-primary button-large disabled"><?php echo number_format($total_stores); ?></span></td>
                </tr>
                <tr>
                    <td><strong>📂 Categories</strong></td>
                    <td><span class="button button-primary button-large disabled"><?php echo number_format($total_categories); ?></span></td>
                </tr>
            </tbody>
        </table>
        
        <?php if ($expired_count > 0 || $expiring_soon_count > 0) : ?>
        <hr>
        <h4>⚠️ Alerts</h4>
        
        <?php if ($expired_count > 0) : ?>
        <div class="notice notice-error inline">
            <p>
                <span class="dashicons dashicons-no-alt"></span>
                <strong><?php echo $expired_count; ?></strong> deal<?php echo $expired_count > 1 ? 's have' : ' has'; ?> expired
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=deals&expired=1')); ?>" class="button button-small">View →</a>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if ($expiring_soon_count > 0) : ?>
        <div class="notice notice-warning inline">
            <p>
                <span class="dashicons dashicons-warning"></span>
                <strong><?php echo $expiring_soon_count; ?></strong> deal<?php echo $expiring_soon_count > 1 ? 's are' : ' is'; ?> expiring soon
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=deals&expiring_soon=1')); ?>" class="button button-small">View →</a>
            </p>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <p>
            <a href="<?php echo esc_url(admin_url('edit.php?post_type=deals')); ?>" class="button button-primary button-hero">
                Manage All Deals →
            </a>
        </p>
    </div>
    <?php
}

// =====================================================
// SECTION 3: CACHE MANAGEMENT
// =====================================================

/**
 * Clear all transient caches
 */
function arrzone_clear_all_caches() {
    global $wpdb;
    
    // Delete all ARRZone transients
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_arrzone_%' OR option_name LIKE '_transient_timeout_arrzone_%'");
    
    // Clear object cache if available
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    return true;
}

/**
 * Add cache clear button to admin bar
 */
function arrzone_admin_bar_cache_button($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_node(array(
        'id' => 'arrzone-clear-cache',
        'title' => '🗑️ Clear Cache',
        'href' => wp_nonce_url(admin_url('admin-post.php?action=arrzone_clear_cache'), 'arrzone_clear_cache'),
        'meta' => array(
            'title' => __('Clear all ARRZone caches', 'dealsindia')
        )
    ));
}
add_action('admin_bar_menu', 'arrzone_admin_bar_cache_button', 999);

/**
 * Handle cache clear request
 */
function arrzone_handle_clear_cache() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to clear cache.', 'dealsindia'));
    }
    
    check_admin_referer('arrzone_clear_cache');
    
    arrzone_clear_all_caches();
    
    wp_redirect(add_query_arg('cache_cleared', '1', wp_get_referer()));
    exit;
}
add_action('admin_post_arrzone_clear_cache', 'arrzone_handle_clear_cache');

/**
 * Show cache cleared notice
 */
function arrzone_cache_cleared_notice() {
    if (isset($_GET['cache_cleared']) && sanitize_text_field($_GET['cache_cleared']) == '1') {

        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>✅ <?php _e('Cache cleared successfully!', 'dealsindia'); ?></strong></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'arrzone_cache_cleared_notice');

/**
 * Auto-clear cache when deal is saved
 */
function arrzone_auto_clear_cache_on_save($post_id, $post, $update) {
    if ($post->post_type === 'deals' && $post->post_status === 'publish') {
        // Clear specific deal caches
        delete_transient('arrzone_hot_picks');
        delete_transient('arrzone_recent_deals');
        delete_transient('arrzone_featured_deals');
        
        // Clear category/store caches if they exist
        $categories = get_the_terms($post_id, 'deal_category');
        if ($categories && !is_wp_error($categories)) {
            foreach ($categories as $cat) {
                delete_transient('arrzone_category_deals_' . $cat->slug);
            }
        }
        
        $stores = get_the_terms($post_id, 'store');
        if ($stores && !is_wp_error($stores)) {
            foreach ($stores as $store) {
                delete_transient('arrzone_store_deals_' . $store->slug);
            }
        }
    }
}
add_action('save_post', 'arrzone_auto_clear_cache_on_save', 10, 3);

// =====================================================
// SECTION 4: NEWSLETTER ADMIN
// =====================================================

/**
 * Add newsletter subscribers custom post type (for admin tracking)
 */
function arrzone_register_newsletter_cpt() {
    register_post_type('newsletter_sub', array(
        'labels' => array(
            'name' => __('Newsletter Subscribers', 'dealsindia'),
            'singular_name' => __('Subscriber', 'dealsindia'),
            'add_new' => __('Add Subscriber', 'dealsindia'),
            'add_new_item' => __('Add New Subscriber', 'dealsindia'),
            'edit_item' => __('Edit Subscriber', 'dealsindia'),
            'view_item' => __('View Subscriber', 'dealsindia'),
            'search_items' => __('Search Subscribers', 'dealsindia'),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-email',
        'capability_type' => 'post',
        'supports' => array('title', 'custom-fields'),
        'has_archive' => false,
        'rewrite' => false,
    ));
}
add_action('init', 'arrzone_register_newsletter_cpt');

/**
 * Custom columns for newsletter subscribers
 */
function arrzone_newsletter_admin_columns($columns) {
    return array(
        'cb' => $columns['cb'],
        'title' => __('Email Address', 'dealsindia'),
        'sub_date' => __('Subscribed On', 'dealsindia'),
        'sub_source' => __('Source', 'dealsindia'),
        'sub_status' => __('Status', 'dealsindia'),
    );
}
add_filter('manage_newsletter_sub_posts_columns', 'arrzone_newsletter_admin_columns');

/**
 * Populate newsletter admin columns
 */
function arrzone_newsletter_column_content($column, $post_id) {
    switch ($column) {
        case 'sub_date':
            echo get_the_date('M j, Y g:i A', $post_id);
            break;
            
        case 'sub_source':
            $source = get_post_meta($post_id, 'subscriber_source', true);
            echo $source ? esc_html(ucfirst($source)) : '—';
            break;
            
        case 'sub_status':
            $status = get_post_meta($post_id, 'subscriber_status', true);
            $status = $status ? $status : 'active';
            
            if ($status === 'active') {
                echo '<span class="button button-primary button-small disabled">✅ Active</span>';
            } else {
                echo '<span class="button disabled">❌ Unsubscribed</span>';
            }
            break;
    }
}
add_action('manage_newsletter_sub_posts_custom_column', 'arrzone_newsletter_column_content', 10, 2);

/**
 * Add export subscribers button
 */
function arrzone_export_subscribers_button() {
    $screen = get_current_screen();
    
    if ($screen->id === 'edit-newsletter_sub') {
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.wrap h1').after('<a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=arrzone_export_subscribers'), 'arrzone_export_subscribers')); ?>" class="page-title-action">📥 Export to CSV</a>');
        });
        </script>
        <?php
    }
}
add_action('admin_head', 'arrzone_export_subscribers_button');

/**
 * Handle subscriber export
 */
function arrzone_handle_export_subscribers() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to export subscribers.', 'dealsindia'));
    }
    
    check_admin_referer('arrzone_export_subscribers');
    
    $subscribers = get_posts(array(
        'post_type' => 'newsletter_sub',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=arrzone-subscribers-' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Email', 'Subscribed Date', 'Source', 'Status'));
    
    foreach ($subscribers as $sub) {
        $email = $sub->post_title;
        $date = get_the_date('Y-m-d H:i:s', $sub->ID);
        $source = get_post_meta($sub->ID, 'subscriber_source', true);
        $status = get_post_meta($sub->ID, 'subscriber_status', true);
        $status = $status ? $status : 'active';
        
        fputcsv($output, array($email, $date, $source, $status));
    }
    
    fclose($output);
    exit;
}
add_action('admin_post_arrzone_export_subscribers', 'arrzone_handle_export_subscribers');

// =====================================================
// END OF ADMIN SETUP
// =====================================================
