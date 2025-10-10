<?php
/**
 * Navigation Menus
 * Register and manage theme menus
 */

if (!defined('ABSPATH')) exit;

/**
 * Register navigation menus
 */
function dealsindia_register_menus() {
    register_nav_menus(array(
        'primary'   => __('Primary Menu', 'dealsindia'),
        'footer'    => __('Footer Menu', 'dealsindia'),
        'footer-2'  => __('Footer Menu 2', 'dealsindia'),
    ));
}
add_action('init', 'dealsindia_register_menus');

/**
 * Fallback menu for primary navigation
 */
function dealsindia_fallback_menu() {
    echo '<ul class="nav-menu-items">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('deals')) . '">Deals</a></li>';
    echo '<li><a href="' . esc_url(home_url('/store/')) . '">Stores</a></li>';
    echo '<li><a href="' . esc_url(home_url('/deal_category/')) . '">Categories</a></li>';
    echo '</ul>';
}
