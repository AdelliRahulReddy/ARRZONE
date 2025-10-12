<?php
/**
 * Deal Type Helper Functions
 * FIXED - Removed deal_active meta query
 */

if (!defined('ABSPATH')) exit;

/**
 * Get Deals by Type
 * 
 * @param string $type - Slug of deal type (deals, coupons, price-errors, etc.)
 * @param int $count - How many deals to get
 * @return WP_Query
 */
function dealsindia_get_deals_by_type($type, $count = 8) {
    
    $args = array(
        'post_type' => 'deals',
        'posts_per_page' => $count,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'deal_type',
                'field' => 'slug',
                'terms' => $type,
            ),
        ),
        // ❌ REMOVED: deal_active meta query - this field doesn't exist!
        // It was filtering out all deals on homepage
        'orderby' => 'date',
        'order' => 'DESC',
    );
    
    return new WP_Query($args);
}

/**
 * Get Deal Type Info
 * 
 * @param string $slug - deal type slug
 * @return object
 */
function dealsindia_get_deal_type_info($slug) {
    $term = get_term_by('slug', $slug, 'deal_type');
    
    if (!$term) {
        return null;
    }
    
    return (object) array(
        'id' => $term->term_id,
        'name' => $term->name,
        'slug' => $term->slug,
        'description' => $term->description,
        'count' => $term->count,
    );
}

/**
 * Get All Deal Types
 * 
 * @return array
 */
function dealsindia_get_all_deal_types() {
    return get_terms(array(
        'taxonomy' => 'deal_type',
        'hide_empty' => false,
        'orderby' => 'id',
        'order' => 'ASC',
    ));
}

/**
 * Check if deal has specific type
 * 
 * @param int $deal_id
 * @param string $type_slug
 * @return bool
 */
function dealsindia_is_deal_type($deal_id, $type_slug) {
    return has_term($type_slug, 'deal_type', $deal_id);
}
