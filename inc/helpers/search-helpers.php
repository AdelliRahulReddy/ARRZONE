<?php
/**
 * Search & Filter Helper Functions
 * Enhanced for Spider-Verse Archive System
 * 
 * @package DealsIndia
 * @version 2.0 - Removed duplicate AJAX handler (moved to filter-handler.php)
 */


if (!defined('ABSPATH')) exit;


/**
 * Modify search query to include custom fields
 */
function dealsindia_extend_search($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        
        // Only search in deals
        $query->set('post_type', 'deals');
        
        // Get search term
        $search_term = $query->get('s');
        
        if (!empty($search_term)) {
            // Search in title, content, and meta fields
            add_filter('posts_search', 'dealsindia_custom_search_where', 10, 2);
            add_filter('posts_join', 'dealsindia_custom_search_join', 10, 2);
            add_filter('posts_groupby', 'dealsindia_custom_search_groupby', 10, 2);
        }
        
        // Exclude expired deals from search
        $expired_ids = dealsindia_get_expired_deal_ids();
        if (!empty($expired_ids)) {
            $post__not_in = $query->get('post__not_in');
            $post__not_in = is_array($post__not_in) ? $post__not_in : array();
            $query->set('post__not_in', array_merge($post__not_in, $expired_ids));
        }
    }
    
    return $query;
}
add_action('pre_get_posts', 'dealsindia_extend_search');


/**
 * Custom WHERE clause for search
 */
function dealsindia_custom_search_where($where, $query) {
    global $wpdb;
    
    if (!is_admin() && $query->is_search()) {
        $search_term = $query->get('s');
        
        if (!empty($search_term)) {
            $where .= " OR (
                {$wpdb->postmeta}.meta_key IN ('coupon_code', 'deal_url') 
                AND {$wpdb->postmeta}.meta_value LIKE '%" . esc_sql($wpdb->esc_like($search_term)) . "%'
            )";
        }
    }
    
    return $where;
}


/**
 * Custom JOIN for search
 */
function dealsindia_custom_search_join($join, $query) {
    global $wpdb;
    
    if (!is_admin() && $query->is_search()) {
        $join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
    }
    
    return $join;
}


/**
 * Custom GROUP BY for search
 */
function dealsindia_custom_search_groupby($groupby, $query) {
    global $wpdb;
    
    if (!is_admin() && $query->is_search()) {
        $groupby = "{$wpdb->posts}.ID";
    }
    
    return $groupby;
}


/**
 * REMOVED: dealsindia_ajax_filter_deals()
 * This function has been moved to inc/ajax/filter-handler.php
 * for better organization and enhanced functionality.
 * 
 * The new handler includes:
 * - Enhanced filtering options (deal types, discount, expiry)
 * - Context-aware filtering (store/category archives)
 * - Cached filter counts
 * - Better security and performance
 */
