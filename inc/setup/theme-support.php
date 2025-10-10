<?php
/**
 * Theme Support & Configuration
 * Registers theme features and capabilities
 */

if (!defined('ABSPATH')) exit;

/**
 * Theme setup
 */
function dealsindia_theme_setup() {
    
    // Enable title tag support
    add_theme_support('title-tag');
    
    // Enable post thumbnails
    add_theme_support('post-thumbnails');
    
    // Add custom image sizes
    add_image_size('deal-thumbnail', 400, 400, true);
    add_image_size('deal-large', 800, 600, false);
    add_image_size('banner-large', 1200, 400, true);
    
    // HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));
    
    // Custom logo support
    add_theme_support('custom-logo', array(
        'height'      => 80,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    
    // Automatic feed links
    add_theme_support('automatic-feed-links');
    
    // Content width
    if (!isset($content_width)) {
        $content_width = 1200;
    }
}
add_action('after_setup_theme', 'dealsindia_theme_setup');
