<?php
/**
 * Performance Optimization Helpers
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Get deals with caching
 */
function dealsindia_get_cached_deals($query_args, $cache_key, $expiration = 3600) {
    // Try to get cached result
    $cached_query = get_transient($cache_key);
    
    if (false !== $cached_query) {
        return $cached_query;
    }
    
    // If no cache, run query
    $query = new WP_Query($query_args);
    
    // Cache the result for 1 hour
    set_transient($cache_key, $query, $expiration);
    
    return $query;
}

/**
 * Clear deal caches when deal is updated
 */
function dealsindia_clear_deal_caches($post_id) {
    if (get_post_type($post_id) !== 'deals') {
        return;
    }
    
    // Clear common cache keys
    $cache_keys = array(
        'dealsindia_hot_picks',
        'dealsindia_latest_deals',
        'dealsindia_popular_deals',
        'dealsindia_trending_categories',
        'dealsindia_top_stores'
    );
    
    foreach ($cache_keys as $key) {
        delete_transient($key);
    }
    
    // Clear expired deals cache
    delete_transient('dealsindia_expired_deal_ids');
}
add_action('save_post_deals', 'dealsindia_clear_deal_caches');
add_action('delete_post', 'dealsindia_clear_deal_caches');

/**
 * Cache expired deal IDs
 */
function dealsindia_get_cached_expired_ids() {
    $cached = get_transient('dealsindia_expired_deal_ids');
    
    if (false !== $cached) {
        return $cached;
    }
    
    global $wpdb;
    $current_time = current_time('mysql');
    
    $query = "
        SELECT p.ID 
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = 'deal_expiry_date'
        LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = 'show_when_expired'
        WHERE p.post_type = 'deals'
        AND p.post_status = 'publish'
        AND pm1.meta_value < %s
        AND (pm2.meta_value IS NULL OR pm2.meta_value != '1')
    ";
    
    $expired_ids = $wpdb->get_col($wpdb->prepare($query, $current_time));
    
    // Cache for 30 minutes
    set_transient('dealsindia_expired_deal_ids', $expired_ids, 1800);
    
    return $expired_ids;
}

/**
 * Optimize database queries - add indexes
 */
function dealsindia_optimize_database() {
    global $wpdb;
    
    // Add index to postmeta for faster deal queries
    $wpdb->query("
        CREATE INDEX IF NOT EXISTS idx_deal_expiry 
        ON {$wpdb->postmeta}(meta_key, meta_value) 
        WHERE meta_key = 'deal_expiry_date'
    ");
    
    $wpdb->query("
        CREATE INDEX IF NOT EXISTS idx_deal_price 
        ON {$wpdb->postmeta}(meta_key, meta_value) 
        WHERE meta_key = 'deal_price'
    ");
}
add_action('after_switch_theme', 'dealsindia_optimize_database');

/**
 * Lazy load images - add loading="lazy" attribute
 */
function dealsindia_add_lazy_loading($attr, $attachment) {
    $attr['loading'] = 'lazy';
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'dealsindia_add_lazy_loading', 10, 2);

/**
 * Defer JavaScript loading
 */
function dealsindia_defer_scripts($tag, $handle) {
    // Skip if admin or it's jQuery
    if (is_admin() || strpos($handle, 'jquery') !== false) {
        return $tag;
    }
    
    // Defer our custom scripts
    $defer_scripts = array(
        'dealsindia-filters',
        'dealsindia-click-tracker'
    );
    
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'dealsindia_defer_scripts', 10, 2);

/**
 * Disable WordPress emoji scripts (performance boost)
 */
function dealsindia_disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('init', 'dealsindia_disable_emojis');

/**
 * Limit post revisions
 */
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 3);
}
