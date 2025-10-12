<?php
/**
 * Giveaways Custom Post Type
 * Registers Giveaways/Contests CPT
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Giveaways Post Type
 */
function dealsindia_register_giveaway_post_type() {
    
    $labels = array(
        'name'               => __('Giveaways', 'dealsindia'),
        'singular_name'      => __('Giveaway', 'dealsindia'),
        'menu_name'          => __('Giveaways', 'dealsindia'),
        'add_new'            => __('Add New Giveaway', 'dealsindia'),
        'add_new_item'       => __('Add New Giveaway', 'dealsindia'),
        'edit_item'          => __('Edit Giveaway', 'dealsindia'),
        'new_item'           => __('New Giveaway', 'dealsindia'),
        'view_item'          => __('View Giveaway', 'dealsindia'),
        'search_items'       => __('Search Giveaways', 'dealsindia'),
        'not_found'          => __('No giveaways found', 'dealsindia'),
        'not_found_in_trash' => __('No giveaways found in trash', 'dealsindia'),
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => true,  // ✅ CHANGED TO TRUE
        'publicly_queryable'  => true,  // ✅ ADDED
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,  // ✅ ADDED
        'rewrite'             => array('slug' => 'giveaway'),  // ✅ ADDED
        'capability_type'     => 'post',
        'has_archive'         => true,  // ✅ ADDED
        'hierarchical'        => false,
        'menu_position'       => 22,
        'menu_icon'           => 'dashicons-awards',
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'        => true,
    );
    
    register_post_type('giveaway', $args);
}
add_action('init', 'dealsindia_register_giveaway_post_type');
