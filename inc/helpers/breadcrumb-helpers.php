<?php
/**
 * Breadcrumb Helper Functions
 * Dynamic breadcrumbs for all pages
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Display Dynamic Breadcrumbs
 * Automatically detects page type and generates appropriate breadcrumb trail
 */
function dealsindia_breadcrumb() {
    // Don't show on homepage
    if (is_front_page()) {
        return;
    }
    
    $breadcrumb = array();
    $separator = '<span class="breadcrumb-separator"> › </span>';
    
    // Always start with Home
    $breadcrumb[] = '<a href="' . home_url() . '" class="breadcrumb-home">Home</a>';
    
    // Single Deal
    if (is_singular('deals')) {
        global $post;
        
        // Add Deals Archive
        $breadcrumb[] = '<a href="' . get_post_type_archive_link('deals') . '" class="breadcrumb-archive">Deals</a>';
        
        // Add Deal Type (if exists)
        $deal_types = get_the_terms($post->ID, 'deal_type');
        if ($deal_types && !is_wp_error($deal_types)) {
            $deal_type = array_shift($deal_types);
            if ($deal_type->slug !== 'deals') { // Don't show for regular deals
                $breadcrumb[] = '<a href="' . get_term_link($deal_type) . '" class="breadcrumb-type">' . esc_html($deal_type->name) . '</a>';
            }
        }
        
        // Add Deal Title (current page)
        $breadcrumb[] = '<span class="breadcrumb-current">' . get_the_title() . '</span>';
    }
    
    // Deals Archive
    elseif (is_post_type_archive('deals')) {
        $breadcrumb[] = '<span class="breadcrumb-current">Deals</span>';
    }
    
    // Deal Type Archive (Coupons, Price Errors, etc.)
    elseif (is_tax('deal_type')) {
        $term = get_queried_object();
        $breadcrumb[] = '<a href="' . get_post_type_archive_link('deals') . '" class="breadcrumb-archive">Deals</a>';
        $breadcrumb[] = '<span class="breadcrumb-current">' . esc_html($term->name) . '</span>';
    }
    
    // Deal Category Archive
    elseif (is_tax('deal_category')) {
        $term = get_queried_object();
        $breadcrumb[] = '<a href="' . get_post_type_archive_link('deals') . '" class="breadcrumb-archive">Deals</a>';
        
        // Parent category (if exists)
        if ($term->parent) {
            $parent = get_term($term->parent, 'deal_category');
            $breadcrumb[] = '<a href="' . get_term_link($parent) . '" class="breadcrumb-category">' . esc_html($parent->name) . '</a>';
        }
        
        $breadcrumb[] = '<span class="breadcrumb-current">' . esc_html($term->name) . '</span>';
    }
    
    // Store Archive
    elseif (is_tax('store')) {
        $term = get_queried_object();
        $breadcrumb[] = '<a href="' . get_post_type_archive_link('deals') . '" class="breadcrumb-archive">Deals</a>';
        $breadcrumb[] = '<span class="breadcrumb-current">' . esc_html($term->name) . '</span>';
    }
    
    // Search Results
    elseif (is_search()) {
        $breadcrumb[] = '<span class="breadcrumb-current">Search Results for: ' . get_search_query() . '</span>';
    }
    
    // 404 Page
    elseif (is_404()) {
        $breadcrumb[] = '<span class="breadcrumb-current">Page Not Found</span>';
    }
    
    // Default (other pages)
    else {
        $breadcrumb[] = '<span class="breadcrumb-current">' . get_the_title() . '</span>';
    }
    
    // Output breadcrumb
    if (!empty($breadcrumb)) {
        echo '<nav class="dealsindia-breadcrumb" aria-label="Breadcrumb">';
        echo '<div class="breadcrumb-container">';
        echo implode($separator, $breadcrumb);
        echo '</div>';
        echo '</nav>';
    }
}

/**
 * Get Breadcrumb Array (for custom usage)
 * Returns array of breadcrumb items without HTML
 */
function dealsindia_get_breadcrumb_array() {
    if (is_front_page()) {
        return array();
    }
    
    $breadcrumb = array();
    
    // Home
    $breadcrumb[] = array(
        'title' => 'Home',
        'url' => home_url(),
        'current' => false
    );
    
    // Single Deal
    if (is_singular('deals')) {
        global $post;
        
        $breadcrumb[] = array(
            'title' => 'Deals',
            'url' => get_post_type_archive_link('deals'),
            'current' => false
        );
        
        $deal_types = get_the_terms($post->ID, 'deal_type');
        if ($deal_types && !is_wp_error($deal_types)) {
            $deal_type = array_shift($deal_types);
            if ($deal_type->slug !== 'deals') {
                $breadcrumb[] = array(
                    'title' => $deal_type->name,
                    'url' => get_term_link($deal_type),
                    'current' => false
                );
            }
        }
        
        $breadcrumb[] = array(
            'title' => get_the_title(),
            'url' => '',
            'current' => true
        );
    }
    
    // Deal Type Archive
    elseif (is_tax('deal_type')) {
        $term = get_queried_object();
        $breadcrumb[] = array(
            'title' => 'Deals',
            'url' => get_post_type_archive_link('deals'),
            'current' => false
        );
        $breadcrumb[] = array(
            'title' => $term->name,
            'url' => '',
            'current' => true
        );
    }
    
    // Category Archive
    elseif (is_tax('deal_category')) {
        $term = get_queried_object();
        $breadcrumb[] = array(
            'title' => 'Deals',
            'url' => get_post_type_archive_link('deals'),
            'current' => false
        );
        $breadcrumb[] = array(
            'title' => $term->name,
            'url' => '',
            'current' => true
        );
    }
    
    // Store Archive
    elseif (is_tax('store')) {
        $term = get_queried_object();
        $breadcrumb[] = array(
            'title' => 'Deals',
            'url' => get_post_type_archive_link('deals'),
            'current' => false
        );
        $breadcrumb[] = array(
            'title' => $term->name,
            'url' => '',
            'current' => true
        );
    }
    
    return $breadcrumb;
}

/**
 * Get Breadcrumb Schema.org JSON-LD
 * For SEO enhancement
 */
function dealsindia_breadcrumb_schema() {
    $breadcrumbs = dealsindia_get_breadcrumb_array();
    
    if (empty($breadcrumbs)) {
        return '';
    }
    
    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array()
    );
    
    $position = 1;
    foreach ($breadcrumbs as $crumb) {
        $item = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $crumb['title']
        );
        
        if (!empty($crumb['url'])) {
            $item['item'] = $crumb['url'];
        }
        
        $schema['itemListElement'][] = $item;
        $position++;
    }
    
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
}
