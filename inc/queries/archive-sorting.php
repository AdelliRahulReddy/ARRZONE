<?php
if (!defined('ABSPATH')) exit;

/**
 * Archive Sorting
 * Handle store archive sorting (NOT AJAX)
 * 
 * @package ARRZONE
 * @version 2.0 - Fixed AJAX Conflict
 */

/**
 * Modify store archive query for sorting
 * ONLY affects page load, NOT AJAX requests
 */
add_action('pre_get_posts', 'dealsindia_store_archive_sorting');
function dealsindia_store_archive_sorting($query) {
    
    // EXCLUDE: Admin, non-main queries, non-store pages, and AJAX requests
    if (is_admin() || 
        !$query->is_main_query() || 
        !is_tax('store') || 
        wp_doing_ajax() || 
        (defined('DOING_AJAX') && DOING_AJAX)) {
        return;
    }
    
    // Get sorting parameter from URL
    $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date-desc';
    
    switch ($orderby) {
        case 'date-asc':
            $query->set('orderby', 'date');
            $query->set('order', 'ASC');
            break;
            
        case 'price-asc':
            $query->set('meta_key', 'deal_price');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'ASC');
            break;
            
        case 'price-desc':
            $query->set('meta_key', 'deal_price');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'DESC');
            break;
            
        case 'discount-desc':
            $query->set('meta_key', 'deal_discount_percentage');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'DESC');
            break;
            
        default: // date-desc
            $query->set('orderby', 'date');
            $query->set('order', 'DESC');
    }
}
