<?php
if (!defined('ABSPATH')) exit; 
/**
 * Work Steps Custom Post Type
 * Registers "How It Works" steps CPT
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Work Steps Post Type
 */
function dealsindia_register_work_steps_post_type() {
    
    $labels = array(
        'name'               => __('Work Steps', 'dealsindia'),
        'singular_name'      => __('Work Step', 'dealsindia'),
        'menu_name'          => __('How It Works', 'dealsindia'),
        'add_new'            => __('Add New Step', 'dealsindia'),
        'add_new_item'       => __('Add New Step', 'dealsindia'),
        'edit_item'          => __('Edit Step', 'dealsindia'),
        'new_item'           => __('New Step', 'dealsindia'),
        'view_item'          => __('View Step', 'dealsindia'),
        'search_items'       => __('Search Steps', 'dealsindia'),
        'not_found'          => __('No steps found', 'dealsindia'),
        'not_found_in_trash' => __('No steps found in trash', 'dealsindia'),
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_position'       => 21,
        'menu_icon'           => 'dashicons-list-view',
        'supports'            => array('title', 'editor'),
        'show_in_rest'        => true,
    );
    
    register_post_type('work_step', $args);
}
add_action('init', 'dealsindia_register_work_steps_post_type');
