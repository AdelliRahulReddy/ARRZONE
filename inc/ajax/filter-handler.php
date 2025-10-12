<?php
/**
 * AJAX Filter Handler for Enhanced Archive System
 * Handles dynamic filtering for /deals/, store archives, and category archives
 * 
 * @package DealsIndia
 * @version 1.0 - Spider-Verse Enhanced Archive
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Main AJAX Filter Handler
 * Processes filter requests and returns filtered deals HTML
 */
function dealsindia_ajax_filter_deals() {
    // Verify nonce for security
    check_ajax_referer('dealsindia_filter_nonce', 'nonce');
    
    // Get filter parameters from AJAX request
    $filters = array(
        'deal_types'   => isset($_POST['deal_types']) ? array_map('sanitize_text_field', $_POST['deal_types']) : array(),
        'categories'   => isset($_POST['categories']) ? array_map('sanitize_text_field', $_POST['categories']) : array(),
        'stores'       => isset($_POST['stores']) ? array_map('sanitize_text_field', $_POST['stores']) : array(),
        'price_min'    => isset($_POST['price_min']) ? absint($_POST['price_min']) : 0,
        'price_max'    => isset($_POST['price_max']) ? absint($_POST['price_max']) : 999999,
        'discount_min' => isset($_POST['discount_min']) ? absint($_POST['discount_min']) : 0,
        'expiry'       => isset($_POST['expiry']) ? sanitize_text_field($_POST['expiry']) : '',
        'sort_by'      => isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'latest',
        'paged'        => isset($_POST['paged']) ? absint($_POST['paged']) : 1,
        'per_page'     => isset($_POST['per_page']) ? absint($_POST['per_page']) : 20,
        
        // Context filters (pre-filtered by store/category)
        'context_type' => isset($_POST['context_type']) ? sanitize_text_field($_POST['context_type']) : '',
        'context_slug' => isset($_POST['context_slug']) ? sanitize_text_field($_POST['context_slug']) : '',
    );
    
    // Build WP_Query arguments
    $query_args = dealsindia_build_filter_query($filters);
    
    // Execute query
    $deals_query = new WP_Query($query_args);
    
    // Prepare response
    $response = array(
        'success' => true,
        'html'    => '',
        'count'   => $deals_query->found_posts,
        'pages'   => $deals_query->max_num_pages,
        'current' => $filters['paged'],
    );
    
    // Generate deals HTML
    if ($deals_query->have_posts()) {
        ob_start();
        
        while ($deals_query->have_posts()) {
            $deals_query->the_post();
            get_template_part('template-parts/deal-card');
        }
        
        $response['html'] = ob_get_clean();
        wp_reset_postdata();
    } else {
        $response['html'] = dealsindia_no_deals_message($filters);
    }
    
    wp_send_json($response);
}
add_action('wp_ajax_dealsindia_filter_deals', 'dealsindia_ajax_filter_deals');
add_action('wp_ajax_nopriv_dealsindia_filter_deals', 'dealsindia_ajax_filter_deals');


/**
 * Build WP_Query arguments from filter parameters
 * 
 * @param array $filters Filter parameters
 * @return array WP_Query arguments
 */
function dealsindia_build_filter_query($filters) {
    $args = array(
        'post_type'      => 'deals',
        'post_status'    => 'publish',
        'posts_per_page' => $filters['per_page'],
        'paged'          => $filters['paged'],
        'tax_query'      => array('relation' => 'AND'),
        'meta_query'     => array('relation' => 'AND'),
    );
    
    // Context-based pre-filtering (for store/category archives)
    if (!empty($filters['context_type']) && !empty($filters['context_slug'])) {
        $args['tax_query'][] = array(
            'taxonomy' => $filters['context_type'],
            'field'    => 'slug',
            'terms'    => $filters['context_slug'],
        );
    }
    
    // Filter by Deal Types
    if (!empty($filters['deal_types'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'deal_type',
            'field'    => 'slug',
            'terms'    => $filters['deal_types'],
            'operator' => 'IN',
        );
    }
    
    // Filter by Categories
    if (!empty($filters['categories'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'deal_category',
            'field'    => 'slug',
            'terms'    => $filters['categories'],
            'operator' => 'IN',
        );
    }
    
    // Filter by Stores
    if (!empty($filters['stores'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'store',
            'field'    => 'slug',
            'terms'    => $filters['stores'],
            'operator' => 'IN',
        );
    }
    
    // Filter by Price Range
    if ($filters['price_min'] > 0 || $filters['price_max'] < 999999) {
        $args['meta_query'][] = array(
            'key'     => 'deal_price',
            'value'   => array($filters['price_min'], $filters['price_max']),
            'type'    => 'NUMERIC',
            'compare' => 'BETWEEN',
        );
    }
    
    // Filter by Discount Percentage
    if ($filters['discount_min'] > 0) {
        $args['meta_query'][] = array(
            'key'     => 'deal_discount_percentage',
            'value'   => $filters['discount_min'],
            'type'    => 'NUMERIC',
            'compare' => '>=',
        );
    }
    
    // Filter by Expiry
    if (!empty($filters['expiry'])) {
        $expiry_date = dealsindia_get_expiry_date_range($filters['expiry']);
        if ($expiry_date) {
            $args['meta_query'][] = array(
                'key'     => 'deal_expiry_date',
                'value'   => $expiry_date,
                'compare' => '<=',
                'type'    => 'DATE',
            );
        }
    }
    
    // Sorting
    switch ($filters['sort_by']) {
        case 'popular':
            $args['meta_key'] = 'deal_click_count';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
            
        case 'discount':
            $args['meta_key'] = 'deal_discount_percentage';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
            
        case 'expiring':
            $args['meta_key'] = 'deal_expiry_date';
            $args['orderby']  = 'meta_value';
            $args['order']    = 'ASC';
            break;
            
        case 'price_low':
            $args['meta_key'] = 'deal_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'ASC';
            break;
            
        case 'price_high':
            $args['meta_key'] = 'deal_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
            
        case 'latest':
        default:
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;
    }
    
    return $args;
}


/**
 * Get expiry date range based on filter selection
 * 
 * @param string $expiry_filter Expiry filter type (today, week, month)
 * @return string|false Date string or false
 */
function dealsindia_get_expiry_date_range($expiry_filter) {
    $current_date = current_time('Y-m-d');
    
    switch ($expiry_filter) {
        case 'today':
            return $current_date;
            
        case 'week':
            return date('Y-m-d', strtotime('+7 days', current_time('timestamp')));
            
        case 'month':
            return date('Y-m-d', strtotime('+30 days', current_time('timestamp')));
            
        default:
            return false;
    }
}


/**
 * Get filter counts for sidebar (cached for performance)
 * 
 * @param string $context_type Context taxonomy (store, deal_category, or empty)
 * @param string $context_slug Context term slug
 * @return array Filter counts
 */
function dealsindia_get_filter_counts($context_type = '', $context_slug = '') {
    // Generate unique cache key based on context
    $cache_key = 'dealsindia_filter_counts_' . md5($context_type . '_' . $context_slug);
    
    // Try to get cached counts
    $cached_counts = get_transient($cache_key);
    if ($cached_counts !== false) {
        return $cached_counts;
    }
    
    $counts = array(
        'deal_types' => array(),
        'categories' => array(),
        'stores'     => array(),
        'total'      => 0,
    );
    
    // Base query args
    $base_args = array(
        'post_type'      => 'deals',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    );
    
    // Add context filter if provided
    if (!empty($context_type) && !empty($context_slug)) {
        $base_args['tax_query'] = array(
            array(
                'taxonomy' => $context_type,
                'field'    => 'slug',
                'terms'    => $context_slug,
            ),
        );
    }
    
    // Get all deal IDs in this context
    $all_deals = new WP_Query($base_args);
    $deal_ids = $all_deals->posts;
    $counts['total'] = count($deal_ids);
    
    if (empty($deal_ids)) {
        set_transient($cache_key, $counts, HOUR_IN_SECONDS);
        return $counts;
    }
    
    // Count by Deal Types
    if ($context_type !== 'deal_type') {
        $deal_type_terms = wp_get_object_terms($deal_ids, 'deal_type');
        foreach ($deal_type_terms as $term) {
            $counts['deal_types'][$term->slug] = array(
                'name'  => $term->name,
                'count' => $term->count,
                'slug'  => $term->slug,
            );
        }
    }
    
    // Count by Categories
    if ($context_type !== 'deal_category') {
        $category_terms = wp_get_object_terms($deal_ids, 'deal_category');
        foreach ($category_terms as $term) {
            $counts['categories'][$term->slug] = array(
                'name'  => $term->name,
                'count' => $term->count,
                'slug'  => $term->slug,
                'icon'  => get_term_meta($term->term_id, 'category_icon', true),
            );
        }
    }
    
    // Count by Stores
    if ($context_type !== 'store') {
        $store_terms = wp_get_object_terms($deal_ids, 'store');
        foreach ($store_terms as $term) {
            $counts['stores'][$term->slug] = array(
                'name'     => $term->name,
                'count'    => $term->count,
                'slug'     => $term->slug,
                'logo'     => get_term_meta($term->term_id, 'store_logo', true),
                'cashback' => get_term_meta($term->term_id, 'store_cashback_rate', true),
            );
        }
    }
    
    // Cache for 1 hour
    set_transient($cache_key, $counts, HOUR_IN_SECONDS);
    
    return $counts;
}


/**
 * Generate "No Deals Found" message with filter information
 * 
 * @param array $filters Applied filters
 * @return string HTML message
 */
function dealsindia_no_deals_message($filters) {
    ob_start();
    ?>
    <div class="no-deals-found">
        <div class="no-deals-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
            </svg>
        </div>
        <h3><?php esc_html_e('No Deals Found', 'dealsindia'); ?></h3>
        <p><?php esc_html_e('We couldn\'t find any deals matching your filters. Try adjusting your search criteria.', 'dealsindia'); ?></p>
        
        <?php if (!empty(array_filter($filters))): ?>
            <button class="btn btn-primary clear-all-filters" id="clearAllFilters">
                <?php esc_html_e('Clear All Filters', 'dealsindia'); ?>
            </button>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}


/**
 * Clear filter counts cache when deals are updated
 */
function dealsindia_clear_filter_cache($post_id) {
    if (get_post_type($post_id) !== 'deals') {
        return;
    }
    
    // Clear all filter count transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_dealsindia_filter_counts_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_dealsindia_filter_counts_%'");
}
add_action('save_post', 'dealsindia_clear_filter_cache');
add_action('delete_post', 'dealsindia_clear_filter_cache');
