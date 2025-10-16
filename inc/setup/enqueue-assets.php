<?php
if (!defined('ABSPATH')) exit; 
/**
 * Enqueue Assets - Global CSS & JavaScript Loading
 * All CSS files load globally (site-wide) - no conditional loading
 * 
 * @package ARRZONE
 * @version 3.0 - Fixed with Filter Localization
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue all theme styles and scripts
 */
function dealsindia_enqueue_assets() {
    $theme_uri = get_template_directory_uri();
    $version = wp_get_theme()->get('Version');

    // ========================================
    // 1. GOOGLE FONTS
    // ========================================
    wp_enqueue_style(
        'dealsindia-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Roboto+Mono:wght@400;500&display=swap',
        array(),
        null
    );

    // ========================================
    // 2. VARIABLES - Design Tokens (Load First)
    // ========================================
    wp_enqueue_style(
        'dealsindia-variables',
        $theme_uri . '/assets/css/variables.css',
        array('dealsindia-google-fonts'),
        $version
    );

    // ========================================
    // 3. GLOBAL - Resets, Utilities, Typography
    // ========================================
    wp_enqueue_style(
        'dealsindia-global',
        $theme_uri . '/assets/css/global.css',
        array('dealsindia-variables'),
        $version
    );

    // ========================================
    // 4. LAYOUT - Header, Footer, Grid Structure
    // ========================================
    wp_enqueue_style(
        'dealsindia-layout',
        $theme_uri . '/assets/css/layout.css',
        array('dealsindia-global'),
        $version
    );

    // ========================================
    // 5. COMPONENTS - Buttons, Cards, Forms
    // ========================================
    wp_enqueue_style(
        'dealsindia-components',
        $theme_uri . '/assets/css/components.css',
        array('dealsindia-layout'),
        $version
    );

    // ========================================
    // 6. HOMEPAGE - Homepage Specific Styles (GLOBAL)
    // ========================================
    wp_enqueue_style(
        'dealsindia-homepage',
        $theme_uri . '/assets/css/homepage.css',
        array('dealsindia-components'),
        $version
    );

    // ========================================
    // 7. PAGE - Archive/Single Page Styles (GLOBAL)
    // ========================================
    wp_enqueue_style(
        'dealsindia-page',
        $theme_uri . '/assets/css/page.css',
        array('dealsindia-components'),
        $version
    );

    // ========================================
    // JAVASCRIPT FILES
    // ========================================
    
    // Main JS
    wp_enqueue_script(
        'dealsindia-main',
        $theme_uri . '/assets/js/main.js',
        array('jquery'),
        $version,
        true
    );

    // Flip Card JS
    wp_enqueue_script(
        'dealsindia-flip-card',
        $theme_uri . '/assets/js/flip-card.js',
        array('jquery'),
        $version,
        true
    );

    // Banner Slider JS (Homepage)
    if (is_front_page() || is_home()) {
        wp_enqueue_script(
            'dealsindia-banner-slider',
            $theme_uri . '/assets/js/banner-slider.js',
            array('jquery'),
            $version,
            true
        );
    }

    // ========================================
    // FILTER JS (Archive Pages) - WITH LOCALIZATION
    // ========================================
    if (is_post_type_archive('deals') || is_tax()) {
        wp_enqueue_script(
            'dealsindia-filter',
            $theme_uri . '/assets/js/filter.js',
            array('jquery'),
            $version,
            true
        );
        
        // Get current page context for pre-filtering
        $context_type = '';
        $context_slug = '';
        
        if (is_tax('store')) {
            $context_type = 'store';
            $context_slug = get_queried_object()->slug;
        } elseif (is_tax('deal-category')) {
            $context_type = 'deal-category';
            $context_slug = get_queried_object()->slug;
        } elseif (is_tax('deal-type')) {
            $context_type = 'deal-type';
            $context_slug = get_queried_object()->slug;
        }
        
        // Localize script with AJAX data and context
        wp_localize_script('dealsindia-filter', 'dealsindiaFilter', array(
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('dealsindia_filter_nonce'),
            'contextType' => $context_type,
            'contextSlug' => $context_slug,
            'strings'     => array(
                'loading'       => __('Loading deals...', 'dealsindia'),
                'ajaxError'     => __('An error occurred while filtering deals. Please try again.', 'dealsindia'),
                'dealSingular'  => __('deal found', 'dealsindia'),
                'dealPlural'    => __('deals found', 'dealsindia'),
                'noDeals'       => __('No deals found', 'dealsindia'),
                'clearFilters'  => __('Clear All Filters', 'dealsindia'),
            )
        ));
    }

    // ========================================
    // PERFORMANCE OPTIMIZATIONS
    // ========================================
    
    // Defer non-critical scripts
    add_filter('script_loader_tag', 'dealsindia_defer_scripts', 10, 2);
}
add_action('wp_enqueue_scripts', 'dealsindia_enqueue_assets');

/**
 * Defer non-critical JavaScript
 */
function dealsindia_defer_scripts($tag, $handle) {
    $defer_scripts = array(
        'dealsindia-flip-card',
        'dealsindia-banner-slider'
    );

    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }

    return $tag;
}

/**
 * Remove unnecessary WordPress assets
 */
function dealsindia_remove_bloat() {
    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    
    // Remove block library CSS (if not using Gutenberg blocks)
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
}
add_action('wp_enqueue_scripts', 'dealsindia_remove_bloat', 100);

/**
 * Admin Scripts - Media Uploader & Color Picker
 */
function dealsindia_admin_scripts($hook) {
    // Only load on specific admin pages
    $allowed_hooks = array('edit-tags.php', 'term.php', 'post.php', 'post-new.php');
    
    if (!in_array($hook, $allowed_hooks)) {
        return;
    }
    
    // Enqueue WordPress media library
    wp_enqueue_media();
    
    // Enqueue color picker
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    
    // Enqueue custom admin media uploader script
    wp_enqueue_script(
        'dealsindia-admin-media',
        get_template_directory_uri() . '/assets/js/admin-media-uploader.js',
        array('jquery', 'wp-color-picker'),
        filemtime(get_template_directory() . '/assets/js/admin-media-uploader.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'dealsindia_admin_scripts');
