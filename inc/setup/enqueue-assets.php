<?php
/**
 * Enqueue Styles & Scripts
 * UPDATED: Header & Footer CSS enabled (properly fixed versions)
 * Added: Variables CSS (Master), Components CSS, Page CSS
 * 
 * @package DealsIndia
 * @version 5.2 - Page Template Support
 */


if (!defined('ABSPATH')) exit;


/**
 * Enqueue frontend styles
 */
function dealsindia_enqueue_styles() {
    $version = defined('DEALSINDIA_VERSION') ? DEALSINDIA_VERSION : '5.2';
    $uri = defined('DEALSINDIA_URI') ? DEALSINDIA_URI : get_template_directory_uri();
    
    // ===================================================
    // 0. MASTER VARIABLES CSS (Load FIRST!)
    // ===================================================
    wp_enqueue_style('dealsindia-variables', $uri . '/assets/css/variables.css', array(), '2.0');
    
    // Google Fonts
    wp_enqueue_style(
        'dealsindia-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800;900&display=swap',
        array(),
        null
    );
    
    // 1. Base CSS (Load after variables)
    wp_enqueue_style('dealsindia-base', $uri . '/assets/css/base.css', array('dealsindia-variables'), $version);
    
    // 2. Global Components & Layout
    wp_enqueue_style('dealsindia-components', $uri . '/assets/css/components.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-header', $uri . '/assets/css/header.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-footer', $uri . '/assets/css/footer.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-breadcrumb', $uri . '/assets/css/breadcrumb.css', array('dealsindia-base'), $version);
    
    // 3. Page-specific CSS (Load LAST to override everything)
    if (is_front_page() || is_home()) {
        wp_enqueue_style('dealsindia-homepage', $uri . '/assets/css/homepage.css', array('dealsindia-base', 'dealsindia-components'), $version . '-' . time());
        
        // Inline style fix for stores grid
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
        wp_enqueue_style('dealsindia-single-deal', $uri . '/assets/css/single-deal.css', array('dealsindia-base', 'dealsindia-components'), $version);
    }
    
    // Enhanced Archive System - Load on all archive pages
    if (is_post_type_archive('deals') || is_tax('deal_category') || is_tax('store')) {
        // Base archive styles
        wp_enqueue_style('dealsindia-archive', $uri . '/assets/css/archive.css', array('dealsindia-base', 'dealsindia-components'), $version);
        
        // Enhanced archive styles (AJAX filtering, sidebar, grids)
        wp_enqueue_style('dealsindia-archive-enhanced', $uri . '/assets/css/archive-enhanced.css', array('dealsindia-archive'), $version);
    }
    
    // Category-specific styles
    if (is_tax('deal_category')) {
        wp_enqueue_style('dealsindia-category', $uri . '/assets/css/category-page.css', array('dealsindia-base', 'dealsindia-components'), $version);
    }
    
    // Store-specific styles
    if (is_tax('store')) {
        wp_enqueue_style('dealsindia-store-page', $uri . '/assets/css/store-page.css', array('dealsindia-base', 'dealsindia-components'), $version . '-' . time());
    }
    
    // ✅ WordPress Pages (Contact, Privacy, About, etc.)
    if (is_page()) {
        wp_enqueue_style('dealsindia-page', $uri . '/assets/css/page.css', array('dealsindia-base'), $version);
    }
}
add_action('wp_enqueue_scripts', 'dealsindia_enqueue_styles');


/**
 * Enqueue frontend scripts
 */
function dealsindia_enqueue_scripts() {
    $version = defined('DEALSINDIA_VERSION') ? DEALSINDIA_VERSION : '5.2';
    $uri = defined('DEALSINDIA_URI') ? DEALSINDIA_URI : get_template_directory_uri();
    
    wp_enqueue_script(
        'dealsindia-main',
        $uri . '/assets/js/main.js',
        array('jquery'),
        $version,
        true
    );
    
    if (is_front_page() || is_home()) {
        wp_enqueue_script(
            'dealsindia-banner-slider',
            $uri . '/assets/js/banner-slider.js',
            array(),
            $version,
            true
        );
    }
    
    wp_enqueue_script(
        'dealsindia-click-tracker',
        $uri . '/assets/js/click-tracker.js',
        array('jquery'),
        $version,
        true
    );
    
    // Enhanced Archive System - AJAX Filtering
    if (is_post_type_archive('deals') || is_tax('deal_category') || is_tax('store')) {
        // Legacy filter script (keep for backward compatibility)
        wp_enqueue_script(
            'dealsindia-filters',
            $uri . '/assets/js/filter.js',
            array('jquery'),
            $version,
            true
        );
        
        // New AJAX filter script (enhanced archive system)
        wp_enqueue_script(
            'dealsindia-filter-ajax',
            $uri . '/assets/js/filter-ajax.js',
            array('jquery'),
            $version,
            true
        );
    }
    
    // Global AJAX object for backward compatibility
    wp_localize_script('dealsindia-main', 'dealsindia_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dealsindia_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'dealsindia_enqueue_scripts');


/**
 * Enqueue admin scripts
 */
function dealsindia_admin_scripts($hook) {
    $valid_hooks = array('post.php', 'post-new.php', 'edit-tags.php', 'term.php');
    
    if (!in_array($hook, $valid_hooks)) {
        return;
    }
    
    $version = defined('DEALSINDIA_VERSION') ? DEALSINDIA_VERSION : '5.2';
    $uri = defined('DEALSINDIA_URI') ? DEALSINDIA_URI : get_template_directory_uri();
    
    wp_enqueue_media();
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    wp_enqueue_script(
        'dealsindia-store-logo-upload',
        $uri . '/assets/js/store-logo-upload.js',
        array('jquery', 'media-upload', 'media-views'),
        $version,
        true
    );
    
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
