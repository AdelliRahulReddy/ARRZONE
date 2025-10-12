<?php
/**
 * Deal Types - FULLY DYNAMIC
 * Admin can add/edit/delete deal types from WordPress
 */

if (!defined('ABSPATH')) exit;

// Register Deal Types Taxonomy
function dealsindia_register_deal_types() {
    
    $labels = array(
        'name' => 'Deal Types',
        'singular_name' => 'Deal Type',
        'search_items' => 'Search Deal Types',
        'all_items' => 'All Deal Types',
        'edit_item' => 'Edit Deal Type',
        'update_item' => 'Update Deal Type',
        'add_new_item' => 'Add New Deal Type',
        'new_item_name' => 'New Deal Type',
        'menu_name' => 'Deal Types',
    );
    
    register_taxonomy('deal_type', 'deals', array(
        'labels' => $labels,
        'public' => true,
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'type'),
    ));
}
add_action('init', 'dealsindia_register_deal_types');

// Create SUGGESTED types on first activation (user can delete/edit)
function dealsindia_suggest_deal_types() {
    
    if (get_option('deal_types_suggested')) {
        return;
    }
    
    // Only suggest, don't force
    $suggested_types = array(
        array(
            'name' => '💰 Deals',
            'slug' => 'deals',
            'description' => 'Regular price drops and hot deals',
        ),
        array(
            'name' => '🎟️ Coupons',
            'slug' => 'coupons',
            'description' => 'Coupon codes and discount vouchers',
        ),
        array(
            'name' => '⚠️ Price Errors',
            'slug' => 'price-errors',
            'description' => 'Price glitches and pricing mistakes',
        ),
        array(
            'name' => '💵 Money Earning',
            'slug' => 'money-earning',
            'description' => 'Cashback and earning tricks',
        ),
        array(
            'name' => '🎁 Giveaways',
            'slug' => 'giveaways',
            'description' => 'Free contests and prizes',
        ),
        array(
            'name' => '📰 News',
            'slug' => 'news',
            'description' => 'Shopping news and updates',
        ),
    );
    
    foreach ($suggested_types as $type) {
        if (!term_exists($type['slug'], 'deal_type')) {
            wp_insert_term($type['name'], 'deal_type', array(
                'slug' => $type['slug'],
                'description' => $type['description']
            ));
        }
    }
    
    update_option('deal_types_suggested', true);
}
add_action('after_switch_theme', 'dealsindia_suggest_deal_types');
