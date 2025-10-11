<?php
/**
 * Search & Filter Helper Functions
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Modify search query to include custom fields
 */
function dealsindia_extend_search($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        
        // Only search in deals
        $query->set('post_type', 'deals');
        
        // Get search term
        $search_term = $query->get('s');
        
        if (!empty($search_term)) {
            // Search in title, content, and meta fields
            add_filter('posts_search', 'dealsindia_custom_search_where', 10, 2);
            add_filter('posts_join', 'dealsindia_custom_search_join', 10, 2);
            add_filter('posts_groupby', 'dealsindia_custom_search_groupby', 10, 2);
        }
        
        // Exclude expired deals from search
        $expired_ids = dealsindia_get_expired_deal_ids();
        if (!empty($expired_ids)) {
            $post__not_in = $query->get('post__not_in');
            $post__not_in = is_array($post__not_in) ? $post__not_in : array();
            $query->set('post__not_in', array_merge($post__not_in, $expired_ids));
        }
    }
    
    return $query;
}
add_action('pre_get_posts', 'dealsindia_extend_search');

/**
 * Custom WHERE clause for search
 */
function dealsindia_custom_search_where($where, $query) {
    global $wpdb;
    
    if (!is_admin() && $query->is_search()) {
        $search_term = $query->get('s');
        
        if (!empty($search_term)) {
            $where .= " OR (
                {$wpdb->postmeta}.meta_key IN ('coupon_code', 'deal_url') 
                AND {$wpdb->postmeta}.meta_value LIKE '%" . esc_sql($wpdb->esc_like($search_term)) . "%'
            )";
        }
    }
    
    return $where;
}

/**
 * Custom JOIN for search
 */
function dealsindia_custom_search_join($join, $query) {
    global $wpdb;
    
    if (!is_admin() && $query->is_search()) {
        $join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
    }
    
    return $join;
}

/**
 * Custom GROUP BY for search
 */
function dealsindia_custom_search_groupby($groupby, $query) {
    global $wpdb;
    
    if (!is_admin() && $query->is_search()) {
        $groupby = "{$wpdb->posts}.ID";
    }
    
    return $groupby;
}

/**
 * Handle AJAX filter requests
 */
function dealsindia_ajax_filter_deals() {
    check_ajax_referer('dealsindia_nonce', 'nonce');
    
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $store = isset($_POST['store']) ? sanitize_text_field($_POST['store']) : '';
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'latest';
    $min_price = isset($_POST['min_price']) ? intval($_POST['min_price']) : 0;
    $max_price = isset($_POST['max_price']) ? intval($_POST['max_price']) : 999999;
    
    $args = array(
        'post_type' => 'deals',
        'posts_per_page' => 20,
        'post_status' => 'publish'
    );
    
    // Category filter
    if (!empty($category)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'deal_category',
            'field' => 'slug',
            'terms' => $category
        );
    }
    
    // Store filter
    if (!empty($store)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'store',
            'field' => 'slug',
            'terms' => $store
        );
    }
    
    // Price range filter
    if ($min_price > 0 || $max_price < 999999) {
        $args['meta_query'][] = array(
            'key' => 'deal_price',
            'value' => array($min_price, $max_price),
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        );
    }
    
    // Sorting
    switch ($sort) {
        case 'price_low':
            $args['meta_key'] = 'deal_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'price_high':
            $args['meta_key'] = 'deal_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'discount':
            $args['meta_key'] = 'deal_discount_percentage';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'expiring':
            $args['meta_key'] = 'deal_expiry_date';
            $args['orderby'] = 'meta_value';
            $args['order'] = 'ASC';
            break;
        default: // latest
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
    }
    
    // Exclude expired deals
    $args = dealsindia_exclude_expired_deals($args);
    
    $query = new WP_Query($args);
    
    ob_start();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/deal-card');
        }
    } else {
        echo '<p class="no-deals-found">No deals found matching your criteria.</p>';
    }
    wp_reset_postdata();
    
    $html = ob_get_clean();
    
    wp_send_json_success(array(
        'html' => $html,
        'count' => $query->found_posts
    ));
}
add_action('wp_ajax_filter_deals', 'dealsindia_ajax_filter_deals');
add_action('wp_ajax_nopriv_filter_deals', 'dealsindia_ajax_filter_deals');
