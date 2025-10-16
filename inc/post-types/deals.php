<?php
if (!defined('ABSPATH')) exit; 
/**
 * Deals Custom Post Type
 * Store-Based Permalink System - SEO Optimized
 * 
 * URL Structure: /deals/{store-slug}/{deal-slug}/
 * Example: /deals/amazon/get-50-off-electronics/
 * 
 * @package DealsIndia
 * @version 5.0 - Store-Based Permalinks
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Deals Post Type with Store-Based Permalinks
 */
function dealsindia_register_deals_post_type() {
    
    $labels = array(
        'name'               => __('Deals', 'dealsindia'),
        'singular_name'      => __('Deal', 'dealsindia'),
        'menu_name'          => __('Deals', 'dealsindia'),
        'add_new'            => __('Add New Deal', 'dealsindia'),
        'add_new_item'       => __('Add New Deal', 'dealsindia'),
        'edit_item'          => __('Edit Deal', 'dealsindia'),
        'new_item'           => __('New Deal', 'dealsindia'),
        'view_item'          => __('View Deal', 'dealsindia'),
        'search_items'       => __('Search Deals', 'dealsindia'),
        'not_found'          => __('No deals found', 'dealsindia'),
        'not_found_in_trash' => __('No deals found in trash', 'dealsindia'),
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => 'deals',
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array(
            'slug'         => 'deals/%store%',
            'with_front'   => false,
            'hierarchical' => false,
        ),
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-tag',
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'        => true,
    );
    
    register_post_type('deals', $args);
}
add_action('init', 'dealsindia_register_deals_post_type', 0);

/**
 * Replace %store% placeholder in deal permalinks
 * Creates URLs like: /deals/amazon/deal-name/
 */
function dealsindia_deal_permalink($permalink, $post, $leavename) {
    // Only process deals post type
    if ($post->post_type !== 'deals') {
        return $permalink;
    }
    
    // Check if placeholder exists
    if (strpos($permalink, '%store%') === false) {
        return $permalink;
    }
    
    // Get store terms
    $stores = get_the_terms($post->ID, 'store');
    
    if ($stores && !is_wp_error($stores)) {
        // Use first store
        $store = array_shift($stores);
        $permalink = str_replace('%store%', $store->slug, $permalink);
    } else {
        // Fallback if no store assigned
        $permalink = str_replace('%store%', 'uncategorized', $permalink);
    }
    
    return $permalink;
}
add_filter('post_type_link', 'dealsindia_deal_permalink', 10, 3);

/**
 * Add custom rewrite rules for store-based URLs
 * Ensures WordPress recognizes URLs like /deals/amazon/deal-slug/
 */
function dealsindia_deal_rewrite_rules() {
    // Get all stores
    $stores = get_terms(array(
        'taxonomy'   => 'store',
        'hide_empty' => false,
    ));
    
    if (!is_wp_error($stores) && !empty($stores)) {
        foreach ($stores as $store) {
            add_rewrite_rule(
                '^deals/' . $store->slug . '/([^/]+)/?$',
                'index.php?deals=$matches[1]',
                'top'
            );
        }
    }
    
    // Fallback rule for deals without store
    add_rewrite_rule(
        '^deals/uncategorized/([^/]+)/?$',
        'index.php?deals=$matches[1]',
        'top'
    );
}
add_action('init', 'dealsindia_deal_rewrite_rules', 20);

/**
 * Flush rewrite rules when stores are updated
 * This ensures new stores work immediately
 */
function dealsindia_flush_rewrite_on_store_change($term_id, $tt_id, $taxonomy) {
    if ($taxonomy === 'store') {
        flush_rewrite_rules();
    }
}
add_action('created_store', 'dealsindia_flush_rewrite_on_store_change', 10, 3);
add_action('edited_store', 'dealsindia_flush_rewrite_on_store_change', 10, 3);
add_action('delete_store', 'dealsindia_flush_rewrite_on_store_change', 10, 3);
