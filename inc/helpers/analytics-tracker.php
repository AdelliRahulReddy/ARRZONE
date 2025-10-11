<?php
/**
 * Deal Analytics & Click Tracking
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Create analytics tables on theme activation
 */
function dealsindia_create_analytics_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table for click tracking
    $clicks_table = $wpdb->prefix . 'dealsindia_clicks';
    $clicks_sql = "CREATE TABLE IF NOT EXISTS $clicks_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        deal_id bigint(20) NOT NULL,
        user_id bigint(20) DEFAULT 0,
        ip_address varchar(45) DEFAULT NULL,
        user_agent text DEFAULT NULL,
        clicked_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        referrer varchar(255) DEFAULT NULL,
        PRIMARY KEY  (id),
        KEY deal_id (deal_id),
        KEY clicked_at (clicked_at)
    ) $charset_collate;";
    
    // Table for view tracking
    $views_table = $wpdb->prefix . 'dealsindia_views';
    $views_sql = "CREATE TABLE IF NOT EXISTS $views_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        deal_id bigint(20) NOT NULL,
        user_id bigint(20) DEFAULT 0,
        ip_address varchar(45) DEFAULT NULL,
        viewed_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY deal_id (deal_id),
        KEY viewed_at (viewed_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($clicks_sql);
    dbDelta($views_sql);
}
add_action('after_switch_theme', 'dealsindia_create_analytics_tables');

/**
 * Track deal view (page visit)
 */
function dealsindia_track_deal_view() {
    if (!is_singular('deals')) {
        return;
    }
    
    global $wpdb, $post;
    $table = $wpdb->prefix . 'dealsindia_views';
    
    $user_id = get_current_user_id();
    $ip_address = dealsindia_get_user_ip();
    $deal_id = $post->ID;
    
    // Check if already viewed in last 24 hours (prevent spam)
    $recent_view = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table 
         WHERE deal_id = %d 
         AND ip_address = %s 
         AND viewed_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
         LIMIT 1",
        $deal_id,
        $ip_address
    ));
    
    if (!$recent_view) {
        $wpdb->insert(
            $table,
            array(
                'deal_id' => $deal_id,
                'user_id' => $user_id,
                'ip_address' => $ip_address
            ),
            array('%d', '%d', '%s')
        );
        
        // Update view count meta
        $current_views = get_post_meta($deal_id, 'deal_views_count', true);
        $new_views = $current_views ? intval($current_views) + 1 : 1;
        update_post_meta($deal_id, 'deal_views_count', $new_views);
    }
}
add_action('wp', 'dealsindia_track_deal_view');

/**
 * AJAX handler for tracking deal clicks
 */
function dealsindia_track_deal_click() {
    check_ajax_referer('dealsindia_nonce', 'nonce');
    
    $deal_id = isset($_POST['deal_id']) ? intval($_POST['deal_id']) : 0;
    
    if (!$deal_id) {
        wp_send_json_error(array('message' => 'Invalid deal ID'));
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'dealsindia_clicks';
    
    $user_id = get_current_user_id();
    $ip_address = dealsindia_get_user_ip();
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
    $referrer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '';
    
    // Insert click record
    $inserted = $wpdb->insert(
        $table,
        array(
            'deal_id' => $deal_id,
            'user_id' => $user_id,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'referrer' => $referrer
        ),
        array('%d', '%d', '%s', '%s', '%s')
    );
    
    if ($inserted) {
        // Update click count meta
        $current_clicks = get_post_meta($deal_id, 'deal_clicks_count', true);
        $new_clicks = $current_clicks ? intval($current_clicks) + 1 : 1;
        update_post_meta($deal_id, 'deal_clicks_count', $new_clicks);
        
        wp_send_json_success(array(
            'message' => 'Click tracked',
            'clicks' => $new_clicks
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to track click'));
    }
}
add_action('wp_ajax_track_deal_click', 'dealsindia_track_deal_click');
add_action('wp_ajax_nopriv_track_deal_click', 'dealsindia_track_deal_click');

/**
 * Get user IP address
 */
function dealsindia_get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return sanitize_text_field($ip);
}

/**
 * Get deal click count
 */
function dealsindia_get_deal_clicks($deal_id) {
    $clicks = get_post_meta($deal_id, 'deal_clicks_count', true);
    return $clicks ? intval($clicks) : 0;
}

/**
 * Get deal view count
 */
function dealsindia_get_deal_views($deal_id) {
    $views = get_post_meta($deal_id, 'deal_views_count', true);
    return $views ? intval($views) : 0;
}

/**
 * Get popular deals (by clicks)
 */
function dealsindia_get_popular_deals($limit = 10, $days = 30) {
    global $wpdb;
    $clicks_table = $wpdb->prefix . 'dealsindia_clicks';
    
    $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
    
    $popular_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT deal_id, COUNT(*) as click_count 
         FROM $clicks_table 
         WHERE clicked_at > %s 
         GROUP BY deal_id 
         ORDER BY click_count DESC 
         LIMIT %d",
        $date_limit,
        $limit
    ));
    
    if (empty($popular_ids)) {
        return new WP_Query(array('post__in' => array(0)));
    }
    
    $args = array(
        'post_type' => 'deals',
        'post__in' => $popular_ids,
        'posts_per_page' => $limit,
        'orderby' => 'post__in'
    );
    
    return new WP_Query($args);
}

/**
 * Get click-through rate (CTR)
 */
function dealsindia_get_deal_ctr($deal_id) {
    $views = dealsindia_get_deal_views($deal_id);
    $clicks = dealsindia_get_deal_clicks($deal_id);
    
    if ($views == 0) {
        return 0;
    }
    
    return round(($clicks / $views) * 100, 2);
}
