<?php
/**
 * Enqueue Styles & Scripts
 * Loads all CSS and JavaScript files
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue frontend styles
 */
function dealsindia_enqueue_styles() {
    $version = DEALSINDIA_VERSION;
    
    // Google Fonts
    wp_enqueue_style(
        'dealsindia-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap',
        array(),
        null
    );
    
    // Base CSS (Always load first)
    wp_enqueue_style('dealsindia-base', DEALSINDIA_URI . '/assets/css/base.css', array(), $version);
    
    // Global Components (Load on all pages)
    wp_enqueue_style('dealsindia-header', DEALSINDIA_URI . '/assets/css/header.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-footer', DEALSINDIA_URI . '/assets/css/footer.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-deal-card', DEALSINDIA_URI . '/assets/css/deal-card.css', array('dealsindia-base'), $version);
    
    // Page-specific CSS
    if (is_front_page() || is_home()) {
        // Homepage
        wp_enqueue_style('dealsindia-homepage', DEALSINDIA_URI . '/assets/css/homepage.css', array('dealsindia-base'), $version);
    }
    
    if (is_singular('deals')) {
        // Single Deal Page
        wp_enqueue_style('dealsindia-single-deal', DEALSINDIA_URI . '/assets/css/single-deal.css', array('dealsindia-base'), $version);
    }
    
    if (is_post_type_archive('deals')) {
        // Deals Archive
        wp_enqueue_style('dealsindia-archive', DEALSINDIA_URI . '/assets/css/archive.css', array('dealsindia-base'), $version);
    }
    
    if (is_tax('deal_category') || is_tax('store')) {
        // Category & Store Archive Pages
        wp_enqueue_style('dealsindia-category', DEALSINDIA_URI . '/assets/css/category-page.css', array('dealsindia-base'), $version);
    }
}
add_action('wp_enqueue_scripts', 'dealsindia_enqueue_styles');

/**
 * Enqueue frontend scripts
 */
function dealsindia_enqueue_scripts() {
    $version = DEALSINDIA_VERSION;
    
    // Main JavaScript
    wp_enqueue_script(
        'dealsindia-main',
        DEALSINDIA_URI . '/assets/js/main.js',
        array(),
        $version,
        true
    );
}
add_action('wp_enqueue_scripts', 'dealsindia_enqueue_scripts');

/**
 * Enqueue admin scripts
 */
function dealsindia_admin_scripts($hook) {
    // Load only on post edit screens
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }
    
    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
}
add_action('admin_enqueue_scripts', 'dealsindia_admin_scripts');
