<?php
/**
 * Deals Custom Post Type
 * Spider-Verse Permalink System Compatible
 * 
 * @package DealsIndia
 * @version 3.1 - Spider-Verse Compatible
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Deals Post Type
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
            'slug'         => 'deals/%deal_type%',
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
