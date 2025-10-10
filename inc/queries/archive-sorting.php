<?php
/**
 * Archive Sorting
 * Handle store archive sorting
 */

if (!defined('ABSPATH')) exit;

/**
 * Modify store archive query for sorting
 */
add_action('pre_get_posts', 'dealsindia_store_archive_sorting');
function dealsindia_store_archive_sorting($query) {
    
    // Only on frontend, main query, and store taxonomy
    if (is_admin() || !$query->is_main_query() || !is_tax('store')) {
        return;
    }
    
    // Get sorting parameter
    $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date-desc';
    
    switch ($orderby) {
        case 'date-asc':
            $query->set('orderby', 'date');
            $query->set('order', 'ASC');
            break;
            
        case 'price-asc':
            $query->set('meta_key', 'sale_price');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'ASC');
            break;
            
        case 'price-desc':
            $query->set('meta_key', 'sale_price');
            $query->set('orderby', 'meta_value_num');
            $query->set('order', 'DESC');
            break;
            
        case 'discount-desc':
            $query->set('orderby', 'date');
            $query->set('order', 'DESC');
            break;
            
        default: // date-desc
            $query->set('orderby', 'date');
            $query->set('order', 'DESC');
    }
}
