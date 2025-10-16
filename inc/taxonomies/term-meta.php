<?php
if (!defined('ABSPATH')) exit; 
/**
 * Term Meta Handler - Enhanced for All Taxonomies
 * Handles media uploads for:
 * - Stores (logo, banner)
 * - Categories (icon image, banner)
 * - Deal Types (icon image, banner)
 * 
 * @package ARRZONE
 * @version 4.0 - Enhanced for Homepage-Style Pages
 */

if (!defined('ABSPATH')) exit;

/**
 * Enqueue Media Scripts for Term Edit Pages
 * Loads WordPress media uploader on taxonomy edit screens
 */
function dealsindia_enqueue_term_media_scripts($hook) {
    // Only load on term edit pages
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }
    
    $screen = get_current_screen();
    
    // Check if we're on a taxonomy that uses media uploads
    $supported_taxonomies = array('store', 'deal-category', 'deal-type');
    
    if (!in_array($screen->taxonomy, $supported_taxonomies)) {
        return;
    }
    
    // Enqueue WordPress media uploader
    wp_enqueue_media();
    
    // Enqueue our custom admin media uploader script
    wp_enqueue_script(
        'dealsindia-admin-media-uploader',
        get_template_directory_uri() . '/assets/js/admin-media-uploader.js',
        array('jquery'),
        filemtime(get_template_directory() . '/assets/js/admin-media-uploader.js'),
        true
    );
    
    // Pass taxonomy type to JavaScript
    wp_localize_script('dealsindia-admin-media-uploader', 'dealsindiaTermMeta', array(
        'taxonomy' => $screen->taxonomy,
        'strings' => array(
            'selectImage' => __('Select Image', 'dealsindia'),
            'useImage' => __('Use this image', 'dealsindia'),
            'uploadImage' => __('Upload Image', 'dealsindia'),
            'changeImage' => __('Change Image', 'dealsindia'),
            'removeImage' => __('Remove Image', 'dealsindia'),
        )
    ));
}
add_action('admin_enqueue_scripts', 'dealsindia_enqueue_term_media_scripts');

/**
 * Add Custom Styles for Term Meta Fields
 */
function dealsindia_term_meta_admin_styles() {
    $screen = get_current_screen();
    
    if ($screen && ($screen->taxonomy === 'store' || $screen->taxonomy === 'deal-category' || $screen->taxonomy === 'deal-type')) {
        ?>
        <style>
            /* Image Preview Styles */
            .term-logo-wrap img,
            .term-banner-wrap img,
            .term-icon-image-wrap img {
                display: block;
                margin-top: 10px;
                border: 2px solid #ddd;
                border-radius: 8px;
                padding: 5px;
                background: #fff;
            }
            
            .store-logo-preview img {
                max-width: 150px;
                height: auto;
                object-fit: contain;
            }
            
            .store-banner-preview img,
            .category-banner-preview img,
            .dealtype-banner-preview img {
                max-width: 400px;
                height: auto;
                object-fit: cover;
            }
            
            .category-icon-preview img,
            .dealtype-icon-preview img {
                width: 100px;
                height: 100px;
                object-fit: contain;
            }
            
            /* Button Styles */
            .button.store-logo-upload-btn,
            .button.store-banner-upload-btn,
            .button.category-icon-upload-btn,
            .button.category-banner-upload-btn,
            .button.dealtype-icon-upload-btn,
            .button.dealtype-banner-upload-btn {
                margin-right: 10px;
            }
            
            .button.store-logo-remove-btn,
            .button.store-banner-remove-btn,
            .button.category-icon-remove-btn,
            .button.category-banner-remove-btn,
            .button.dealtype-icon-remove-btn,
            .button.dealtype-banner-remove-btn {
                display: none;
                color: #d63638;
                border-color: #d63638;
            }
            
            .button.store-logo-remove-btn:hover,
            .button.store-banner-remove-btn:hover,
            .button.category-icon-remove-btn:hover,
            .button.category-banner-remove-btn:hover,
            .button.dealtype-icon-remove-btn:hover,
            .button.dealtype-banner-remove-btn:hover {
                background: #d63638;
                color: #fff;
            }
            
            /* Form Field Spacing */
            .form-field.term-logo-wrap,
            .form-field.term-banner-wrap,
            .form-field.term-icon-image-wrap,
            .form-field.term-icon-wrap,
            .form-field.term-color-wrap {
                margin-bottom: 20px;
            }
            
            /* Color Picker */
            .wp-picker-container {
                margin-top: 5px;
            }
            
            /* Preview Container */
            .image-preview-container {
                margin-top: 10px;
                padding: 10px;
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
        </style>
        <?php
    }
}
add_action('admin_head', 'dealsindia_term_meta_admin_styles');
