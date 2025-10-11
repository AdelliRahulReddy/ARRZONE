<?php
/**
 * Navigation Menus
 * Register and manage theme menus
 * 
 * @package DealsIndia
 * @version 3.0 - Added footer fallback
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
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . __('Home', 'dealsindia') . '</a></li>';
    echo '<li><a href="' . esc_url(get_post_type_archive_link('deals')) . '">' . __('Deals', 'dealsindia') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/store/')) . '">' . __('Stores', 'dealsindia') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/deal_category/')) . '">' . __('Categories', 'dealsindia') . '</a></li>';
    echo '</ul>';
}

/**
 * Fallback menu for footer navigation
 */
function dealsindia_footer_fallback_menu() {
    echo '<ul class="footer-menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . __('Home', 'dealsindia') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/about')) . '">' . __('About Us', 'dealsindia') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/contact')) . '">' . __('Contact', 'dealsindia') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/privacy-policy')) . '">' . __('Privacy Policy', 'dealsindia') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/terms-of-service')) . '">' . __('Terms of Service', 'dealsindia') . '</a></li>';
    echo '</ul>';
}
