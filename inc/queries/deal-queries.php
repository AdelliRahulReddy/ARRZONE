<?php
/**
 * Deal Query Functions
 * Retrieve deals, banners, steps, giveaways
 */

if (!defined('ABSPATH')) exit;

/**
 * Get hero banners
 */
function dealsindia_get_hero_banners($limit = 3) {
    $args = array(
        'post_type'      => 'hero_banner',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );
    
    $query = new WP_Query($args);
    return $query->posts;
}

/**
 * Get work steps
 */
function dealsindia_get_work_steps() {
    $args = array(
        'post_type'      => 'work_step',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );
    
    $query = new WP_Query($args);
    return $query->posts;
}

/**
 * Get active giveaway
 */
function dealsindia_get_active_giveaway() {
    $args = array(
        'post_type'      => 'giveaway',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'   => 'giveaway_is_active',
                'value' => '1',
            ),
        ),
    );
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        return $query->posts[0];
    }
    
    return null;
}

/**
 * Get trending/hot deals
 */
function dealsindia_get_hot_picks($limit = 10) {
    $args = array(
        'post_type'      => 'deals',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'   => 'is_trending',
                'value' => '1',
            ),
        ),
    );
    
    $query = new WP_Query($args);
    
    // If no trending deals, get latest deals
    if (!$query->have_posts()) {
        $args = array(
            'post_type'      => 'deals',
            'posts_per_page' => $limit,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        );
        $query = new WP_Query($args);
    }
    
    return $query;
}

/**
 * Calculate discount percentage
 */
function dealsindia_calculate_discount($original_price, $sale_price) {
    if (empty($original_price) || empty($sale_price) || $original_price <= 0) {
        return 0;
    }
    
    $discount = (($original_price - $sale_price) / $original_price) * 100;
    return round($discount);
}
