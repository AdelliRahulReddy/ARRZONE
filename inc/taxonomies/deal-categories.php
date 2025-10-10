<?php
/**
 * Deal Categories Taxonomy
 * Registers deal categories (Electronics, Fashion, etc.)
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Deal Categories Taxonomy
 */
function dealsindia_register_deal_category_taxonomy() {
    
    $labels = array(
        'name'              => __('Deal Categories', 'dealsindia'),
        'singular_name'     => __('Deal Category', 'dealsindia'),
        'search_items'      => __('Search Categories', 'dealsindia'),
        'all_items'         => __('All Categories', 'dealsindia'),
        'parent_item'       => __('Parent Category', 'dealsindia'),
        'parent_item_colon' => __('Parent Category:', 'dealsindia'),
        'edit_item'         => __('Edit Category', 'dealsindia'),
        'update_item'       => __('Update Category', 'dealsindia'),
        'add_new_item'      => __('Add New Category', 'dealsindia'),
        'new_item_name'     => __('New Category Name', 'dealsindia'),
        'menu_name'         => __('Categories', 'dealsindia'),
    );
    
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'deal_category'),
        'show_in_rest'      => true,
    );
    
    register_taxonomy('deal_category', array('deals'), $args);
}
add_action('init', 'dealsindia_register_deal_category_taxonomy');
