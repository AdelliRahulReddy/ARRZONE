<?php
if (!defined('ABSPATH')) exit; 
/**
 * Money Earning Tricks Custom Post Type
 * Register CPT for money earning tips and tricks
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Money Earning Tricks Post Type
 */
function dealsindia_register_money_earning_cpt() {
    
    $labels = array(
        'name'               => __('Money Earning', 'dealsindia'),
        'singular_name'      => __('Money Trick', 'dealsindia'),
        'menu_name'          => __('Money Earning', 'dealsindia'),
        'add_new'            => __('Add New Trick', 'dealsindia'),
        'add_new_item'       => __('Add New Money Earning Trick', 'dealsindia'),
        'edit_item'          => __('Edit Money Trick', 'dealsindia'),
        'new_item'           => __('New Money Trick', 'dealsindia'),
        'view_item'          => __('View Money Trick', 'dealsindia'),
        'search_items'       => __('Search Money Tricks', 'dealsindia'),
        'not_found'          => __('No money earning tricks found', 'dealsindia'),
        'not_found_in_trash' => __('No money earning tricks found in trash', 'dealsindia'),
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'money-earning'),
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_position'       => 6,
        'menu_icon'           => 'dashicons-money-alt',
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'comments'),
        'show_in_rest'        => true,
    );
    
    register_post_type('money_earning', $args);
}
add_action('init', 'dealsindia_register_money_earning_cpt');
