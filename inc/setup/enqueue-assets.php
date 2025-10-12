<?php
/**
 * Enqueue Styles & Scripts
 * FIXED: Inline style override for stores grid
 * Added: Store Page CSS support + Admin Media Upload
 * 
 * @package DealsIndia
 * @version 3.7 - Admin Scripts Fixed
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue frontend styles
 */
function dealsindia_enqueue_styles() {
    $version = defined('DEALSINDIA_VERSION') ? DEALSINDIA_VERSION : '3.0';
    $uri = defined('DEALSINDIA_URI') ? DEALSINDIA_URI : get_template_directory_uri();
    
    // Google Fonts
    wp_enqueue_style(
        'dealsindia-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap',
        array(),
        null
    );
    
    // 1. Base CSS (Load first)
    wp_enqueue_style('dealsindia-base', $uri . '/assets/css/base.css', array(), $version);
    
    // 2. Global Components
    wp_enqueue_style('dealsindia-header', $uri . '/assets/css/header.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-footer', $uri . '/assets/css/footer.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-deal-card', $uri . '/assets/css/deal-card.css', array('dealsindia-base'), $version);
    
    // 3. Responsive CSS (Load before page-specific CSS)
    wp_enqueue_style('dealsindia-responsive', $uri . '/assets/css/responsive.css', array('dealsindia-base'), $version);
    
    // 4. Page-specific CSS (Load LAST to override everything)
    if (is_front_page() || is_home()) {
        // Homepage CSS loads AFTER responsive.css
        wp_enqueue_style('dealsindia-homepage', $uri . '/assets/css/homepage.css', array('dealsindia-base', 'dealsindia-responsive'), $version . '-' . time());
        
        // ✅ INLINE STYLE FIX - This CANNOT be overridden!
        $inline_css = '
            .top-stores-section-premium .stores-grid-cd {
                display: grid !important;
                grid-template-columns: repeat(6, 1fr) !important;
                gap: 16px !important;
                max-width: 1200px !important;
                margin: 0 auto !important;
            }
            @media (max-width: 1199px) {
                .top-stores-section-premium .stores-grid-cd {
                    grid-template-columns: repeat(6, 1fr) !important;
                }
            }
            @media (max-width: 967px) {
                .top-stores-section-premium .stores-grid-cd {
                    grid-template-columns: repeat(4, 1fr) !important;
                }
            }
            @media (max-width: 767px) {
                .top-stores-section-premium .stores-grid-cd {
                    grid-template-columns: repeat(3, 1fr) !important;
                }
            }
            @media (max-width: 639px) {
                .top-stores-section-premium .stores-grid-cd {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }
        ';
        wp_add_inline_style('dealsindia-homepage', $inline_css);
    }
    
    if (is_singular('deals')) {
        wp_enqueue_style('dealsindia-single-deal', $uri . '/assets/css/single-deal.css', array('dealsindia-base', 'dealsindia-responsive'), $version);
    }
    
    if (is_post_type_archive('deals')) {
        wp_enqueue_style('dealsindia-archive', $uri . '/assets/css/archive.css', array('dealsindia-base', 'dealsindia-responsive'), $version);
    }
    
    // Category Archive CSS
    if (is_tax('deal_category')) {
        wp_enqueue_style('dealsindia-category', $uri . '/assets/css/category-page.css', array('dealsindia-base', 'dealsindia-responsive'), $version);
    }
    
    // Store Archive CSS
    if (is_tax('store')) {
        wp_enqueue_style('dealsindia-store-page', $uri . '/assets/css/store-page.css', array('dealsindia-base', 'dealsindia-responsive'), $version . '-' . time());
    }
}
add_action('wp_enqueue_scripts', 'dealsindia_enqueue_styles');

/**
 * Enqueue frontend scripts
 */
function dealsindia_enqueue_scripts() {
    $version = defined('DEALSINDIA_VERSION') ? DEALSINDIA_VERSION : '3.0';
    $uri = defined('DEALSINDIA_URI') ? DEALSINDIA_URI : get_template_directory_uri();
    
    // Main JavaScript
    wp_enqueue_script(
        'dealsindia-main',
        $uri . '/assets/js/main.js',
        array('jquery'),
        $version,
        true
    );
    
    // Banner Slider (Homepage only)
    if (is_front_page() || is_home()) {
        wp_enqueue_script(
            'dealsindia-banner-slider',
            $uri . '/assets/js/banner-slider.js',
            array(),
            $version,
            true
        );
    }
    
    // Click Tracker
    wp_enqueue_script(
        'dealsindia-click-tracker',
        $uri . '/assets/js/click-tracker.js',
        array('jquery'),
        $version,
        true
    );
    
    // Filter JavaScript
    if (is_post_type_archive('deals') || is_tax('deal_category') || is_tax('store')) {
        wp_enqueue_script(
            'dealsindia-filters',
            $uri . '/assets/js/filter.js',
            array('jquery'),
            $version,
            true
        );
    }
    
    // Localize script for AJAX
    wp_localize_script('dealsindia-main', 'dealsindia_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dealsindia_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'dealsindia_enqueue_scripts');

/**
 * Enqueue admin scripts - FIXED
 */
function dealsindia_admin_scripts($hook) {
    $valid_hooks = array('post.php', 'post-new.php', 'edit-tags.php', 'term.php');
    
    if (!in_array($hook, $valid_hooks)) {
        return;
    }
    
    $version = defined('DEALSINDIA_VERSION') ? DEALSINDIA_VERSION : '3.0';
    $uri = defined('DEALSINDIA_URI') ? DEALSINDIA_URI : get_template_directory_uri();
    
    // WordPress Media Library
    wp_enqueue_media();
    
    // Color Picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    // ✅ Store Logo & Banner Upload Script
    wp_enqueue_script(
        'dealsindia-store-logo-upload',
        $uri . '/assets/js/store-logo-upload.js',
        array('jquery', 'media-upload', 'media-views'),
        $version,
        true
    );
    
    // Category Icon Upload Script
    if (file_exists(get_template_directory() . '/assets/js/category-icon-upload.js')) {
        wp_enqueue_script(
            'dealsindia-category-icon-upload',
            $uri . '/assets/js/category-icon-upload.js',
            array('jquery', 'media-upload', 'media-views'),
            $version,
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'dealsindia_admin_scripts');
