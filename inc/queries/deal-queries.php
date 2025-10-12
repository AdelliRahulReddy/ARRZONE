<?php
/**
 * Deal Queries
 * Custom queries for deals
 * 
 * @package DealsIndia
 * @version 5.0 - CLEAN (NO DUPLICATES)
 */

if (!defined('ABSPATH')) exit;

/**
 * Get hot picks (featured/hot deals)
 */
function dealsindia_get_hot_picks($limit = 10) {
    $cache_key = 'dealsindia_hot_picks_' . $limit;
    $cached = get_transient($cache_key);

    if (false !== $cached) {
        return $cached;
    }

    // 1. Try to get featured/hot picks first
    $args = array(
        'post_type' => 'deals',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => 'is_hot',
                'value' => '1',
                'compare' => '='
            ),
            array(
                'key' => 'is_featured',
                'value' => '1',
                'compare' => '='
            )
        )
    );
    
    $args = dealsindia_exclude_expired_deals($args);
    $query = new WP_Query($args);

    // 2. If not enough results, fill with latest deals
    if ($query->post_count < $limit) {
        $hot_ids = wp_list_pluck($query->posts, 'ID');
        
        $fill_args = array(
            'post_type' => 'deals',
            'posts_per_page' => $limit - $query->post_count,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'post__not_in' => $hot_ids
        );
        
        $fill_args = dealsindia_exclude_expired_deals($fill_args);
        $fill_query = new WP_Query($fill_args);

        if ($fill_query->have_posts()) {
            foreach ($fill_query->posts as $post) {
                $query->posts[] = $post;
            }
            $query->post_count = count($query->posts);
        }
    }

    set_transient($cache_key, $query, 3600);
    return $query;
}

/**
 * Get work steps
 */
function dealsindia_get_work_steps() {
    $cache_key = 'dealsindia_work_steps';
    $cached = get_transient($cache_key);
    
    if (false !== $cached) {
        return $cached;
    }
    
    $args = array(
        'post_type' => 'work_step',
        'posts_per_page' => 3,
        'post_status' => 'publish',
        'orderby' => 'menu_order',
        'order' => 'ASC'
    );
    
    $query = new WP_Query($args);
    $posts = $query->posts;
    
    set_transient($cache_key, $posts, 86400);
    return $posts;
}

/**
 * Get hero banners
 */
function dealsindia_get_hero_banners($limit = 5) {
    $cache_key = 'dealsindia_hero_banners_' . $limit;
    $cached = get_transient($cache_key);
    
    if (false !== $cached) {
        return $cached;
    }
    
    $args = array(
        'post_type' => 'hero_banner',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => 'banner_active',
                'value' => '1',
                'compare' => '='
            )
        )
    );
    
    $query = new WP_Query($args);
    $posts = $query->posts;
    
    set_transient($cache_key, $posts, 3600);
    return $posts;
}

/**
 * Exclude expired deals from query args
 * Uses function from performance-helpers.php
 */
function dealsindia_exclude_expired_deals($args) {
    if (!isset($args['meta_query'])) {
        $args['meta_query'] = array();
    }
    
    // Get cached expired IDs (from performance-helpers.php)
    $expired_ids = dealsindia_get_cached_expired_ids();
    
    if (!empty($expired_ids)) {
        $args['post__not_in'] = isset($args['post__not_in']) 
            ? array_merge($args['post__not_in'], $expired_ids) 
            : $expired_ids;
    }
    
    return $args;
}

/**
 * Get IDs of expired deals
 */
function dealsindia_get_expired_deal_ids() {
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
    return $expired_ids;
}

// ❌ REMOVED: dealsindia_get_cached_expired_ids() 
// This function already exists in performance-helpers.php

/**
 * Modify main query to exclude expired deals
 */
function dealsindia_exclude_expired_from_archives($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    if (is_post_type_archive('deals') || is_tax('deal_category') || is_tax('store')) {
        $expired_ids = dealsindia_get_cached_expired_ids();
        
        if (!empty($expired_ids)) {
            $post__not_in = $query->get('post__not_in');
            $post__not_in = is_array($post__not_in) ? $post__not_in : array();
            $query->set('post__not_in', array_merge($post__not_in, $expired_ids));
        }
    }
}
add_action('pre_get_posts', 'dealsindia_exclude_expired_from_archives');

/**
 * Clear hot picks cache when deal is saved
 */
function dealsindia_clear_hot_picks_cache($post_id) {
    if (get_post_type($post_id) !== 'deals') {
        return;
    }
    
    // Clear all hot picks caches
    for ($i = 1; $i <= 20; $i++) {
        delete_transient('dealsindia_hot_picks_' . $i);
    }
}
add_action('save_post', 'dealsindia_clear_hot_picks_cache');
