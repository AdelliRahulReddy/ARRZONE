<?php
if (!defined('ABSPATH')) exit;

/**
 * Deal Filters AJAX Handler
 * FIXED VERSION - Uses correct meta keys from deal-card.php
 * 
 * @package ARRZONE
 * @version 9.0 - Meta Key Fix
 */

add_action('wp_ajax_filter_deals', 'arrzone_ajax_filter_deals');
add_action('wp_ajax_nopriv_filter_deals', 'arrzone_ajax_filter_deals');

function arrzone_ajax_filter_deals() {
    // Security check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dealsindia_filter_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed', 'dealsindia')));
        return;
    }
    
    // Get filters
    $filters_json = isset($_POST['filters']) ? stripslashes($_POST['filters']) : '{}';
    $filters = json_decode($filters_json, true);
    
    if (!is_array($filters)) {
        wp_send_json_error(array('message' => __('Invalid filter data', 'dealsindia')));
        return;
    }
    
    // Base query
    $query_args = array(
        'post_type' => 'deals',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'paged' => 1,
    );
    
    // TAXONOMY FILTERS
    $tax_query = array('relation' => 'AND');
    
    // Context
    if (!empty($filters['context']['type']) && !empty($filters['context']['slug'])) {
        $tax_query[] = array(
            'taxonomy' => sanitize_text_field($filters['context']['type']),
            'field' => 'slug',
            'terms' => sanitize_text_field($filters['context']['slug']),
        );
    }
    
    // Stores
    if (!empty($filters['stores']) && is_array($filters['stores'])) {
        $tax_query[] = array(
            'taxonomy' => 'store',
            'field' => 'slug',
            'terms' => array_map('sanitize_text_field', $filters['stores']),
            'operator' => 'IN',
        );
    }
    
    // Categories
    if (!empty($filters['categories']) && is_array($filters['categories'])) {
        $tax_query[] = array(
            'taxonomy' => 'deal-category',
            'field' => 'slug',
            'terms' => array_map('sanitize_text_field', $filters['categories']),
            'operator' => 'IN',
        );
    }
    
    // Deal Types
    if (!empty($filters['dealTypes']) && is_array($filters['dealTypes'])) {
        $tax_query[] = array(
            'taxonomy' => 'deal-type',
            'field' => 'slug',
            'terms' => array_map('sanitize_text_field', $filters['dealTypes']),
            'operator' => 'IN',
        );
    }
    
    if (count($tax_query) > 1) {
        $query_args['tax_query'] = $tax_query;
    }
    
    // META FILTERS
    $meta_query = array('relation' => 'AND');
    
    // Price range - USING deal_sale_price (the correct meta key!)
    if (!empty($filters['priceMin']) || !empty($filters['priceMax'])) {
        $price_min = !empty($filters['priceMin']) ? floatval($filters['priceMin']) : 0;
        $price_max = !empty($filters['priceMax']) ? floatval($filters['priceMax']) : 999999;
        
        $meta_query[] = array(
            'key' => 'deal_sale_price',
            'value' => array($price_min, $price_max),
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN',
        );
    }
    
    // Discount
    if (!empty($filters['discountMin']) && intval($filters['discountMin']) > 0) {
        $meta_query[] = array(
            'key' => 'deal_discount_percentage',
            'value' => intval($filters['discountMin']),
            'type' => 'NUMERIC',
            'compare' => '>=',
        );
    }
    
    // Status
    if (!empty($filters['status'])) {
        $today = current_time('Y-m-d');
        
        if ($filters['status'] === 'active') {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => 'deal_expiry_date',
                    'value' => $today,
                    'compare' => '>=',
                    'type' => 'DATE',
                ),
                array(
                    'key' => 'deal_expiry_date',
                    'compare' => 'NOT EXISTS',
                ),
            );
        } elseif ($filters['status'] === 'ending_soon') {
            $week_from_now = date('Y-m-d', strtotime('+7 days'));
            $meta_query[] = array(
                'key' => 'deal_expiry_date',
                'value' => array($today, $week_from_now),
                'compare' => 'BETWEEN',
                'type' => 'DATE',
            );
        }
    }
    
    if (count($meta_query) > 1) {
        $query_args['meta_query'] = $meta_query;
    }
    
    // SORTING - USING deal_sale_price (the correct meta key!)
    $sort_by = isset($filters['sortBy']) ? sanitize_text_field($filters['sortBy']) : 'latest';
    
    switch ($sort_by) {
        case 'popular':
            $query_args['meta_key'] = 'deal_views';
            $query_args['orderby'] = 'meta_value_num date';
            $query_args['order'] = 'DESC';
            break;
            
        case 'discount':
            $query_args['meta_key'] = 'deal_discount_percentage';
            $query_args['orderby'] = 'meta_value_num date';
            $query_args['order'] = 'DESC';
            break;
            
        case 'price_low':
            $query_args['meta_key'] = 'deal_sale_price';  // FIXED: Using correct meta key
            $query_args['orderby'] = 'meta_value_num date';
            $query_args['order'] = 'ASC';
            break;
            
        case 'price_high':
            $query_args['meta_key'] = 'deal_sale_price';  // FIXED: Using correct meta key
            $query_args['orderby'] = 'meta_value_num date';
            $query_args['order'] = 'DESC';
            break;
            
        case 'latest':
        default:
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
            break;
    }
    
    // Execute query
    $deals_query = new WP_Query($query_args);
    
    // Generate output
    ob_start();
    
    if ($deals_query->have_posts()) {
        while ($deals_query->have_posts()) {
            $deals_query->the_post();
            get_template_part('template-parts/deal-card');
        }
        wp_reset_postdata();
    } else {
        ?>
        <div class="no-deals-message">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                <line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/>
                <line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/>
            </svg>
            <h3><?php esc_html_e('No Deals Found', 'dealsindia'); ?></h3>
            <p><?php esc_html_e('Try adjusting your filters to see more results.', 'dealsindia'); ?></p>
        </div>
        <?php
    }
    
    $html = ob_get_clean();
    
    wp_send_json_success(array(
        'html' => $html,
        'count' => $deals_query->found_posts,
        'max_pages' => $deals_query->max_num_pages,
    ));
}
