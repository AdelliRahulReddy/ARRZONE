<?php
/**
 * Stores Taxonomy
 * Registers stores (Amazon, Flipkart, etc.)
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Stores Taxonomy
 */
function dealsindia_register_store_taxonomy() {
    
    $labels = array(
        'name'              => __('Stores', 'dealsindia'),
        'singular_name'     => __('Store', 'dealsindia'),
        'search_items'      => __('Search Stores', 'dealsindia'),
        'all_items'         => __('All Stores', 'dealsindia'),
        'parent_item'       => __('Parent Store', 'dealsindia'),
        'parent_item_colon' => __('Parent Store:', 'dealsindia'),
        'edit_item'         => __('Edit Store', 'dealsindia'),
        'update_item'       => __('Update Store', 'dealsindia'),
        'add_new_item'      => __('Add New Store', 'dealsindia'),
        'new_item_name'     => __('New Store Name', 'dealsindia'),
        'menu_name'         => __('Stores', 'dealsindia'),
    );
    
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'store'),
        'show_in_rest'      => true,
    );
    
    register_taxonomy('store', array('deals'), $args);
}
add_action('init', 'dealsindia_register_store_taxonomy');
