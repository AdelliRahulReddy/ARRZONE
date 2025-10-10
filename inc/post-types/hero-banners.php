<?php
/**
 * Hero Banners Custom Post Type
 * Registers Hero Banner CPT for homepage slider
 */

if (!defined('ABSPATH')) exit;

/**
 * Register Hero Banners Post Type
 */
function dealsindia_register_hero_banner_post_type() {
    
    $labels = array(
        'name'               => __('Hero Banners', 'dealsindia'),
        'singular_name'      => __('Hero Banner', 'dealsindia'),
        'menu_name'          => __('Hero Banners', 'dealsindia'),
        'add_new'            => __('Add New Banner', 'dealsindia'),
        'add_new_item'       => __('Add New Banner', 'dealsindia'),
        'edit_item'          => __('Edit Banner', 'dealsindia'),
        'new_item'           => __('New Banner', 'dealsindia'),
        'view_item'          => __('View Banner', 'dealsindia'),
        'search_items'       => __('Search Banners', 'dealsindia'),
        'not_found'          => __('No banners found', 'dealsindia'),
        'not_found_in_trash' => __('No banners found in trash', 'dealsindia'),
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'menu_position'       => 20,
        'menu_icon'           => 'dashicons-images-alt2',
        'supports'            => array('title', 'thumbnail'),
        'show_in_rest'        => true,
    );
    
    register_post_type('hero_banner', $args);
}
add_action('init', 'dealsindia_register_hero_banner_post_type');
