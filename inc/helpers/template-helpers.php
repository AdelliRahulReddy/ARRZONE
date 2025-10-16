<?php
if (!defined('ABSPATH')) exit; 
/**
 * Template Helpers - Consolidated & Enhanced
 * 
 * Merges:
 * - breadcrumb-helpers.php (Breadcrumb navigation)
 * - search-helpers.php (Search functionality)
 * - social-share.php (Social sharing buttons)
 * - template-helpers.php (General template functions)
 * - menu-helpers.php (Navigation fallback)
 * 
 * @package ARRZONE
 * @version 2.0 - Production Ready
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// =====================================================
// SECTION 1: NAVIGATION & MENU FUNCTIONS (NEW)
// =====================================================

/**
 * Fallback Menu for Primary Navigation
 * Shows when no menu is assigned to primary location
 * Automatically generates menu from post types and taxonomies
 */
function dealsindia_fallback_menu() {
    echo '<ul>';
    
    // Home Link
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'dealsindia') . '</a></li>';
    
    // Deals Archive Link
    $deals_link = get_post_type_archive_link('deals');
    if ($deals_link) {
        echo '<li><a href="' . esc_url($deals_link) . '">' . esc_html__('Deals', 'dealsindia') . '</a></li>';
    }
    
    // Categories Dropdown
    $categories = get_terms(array(
        'taxonomy'   => 'deal-category',
        'number'     => 5,
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC'
    ));
    
    if (!empty($categories) && !is_wp_error($categories)) {
        echo '<li class="menu-item-has-children">';
        echo '<a href="#">' . esc_html__('Categories', 'dealsindia') . '</a>';
        echo '<ul class="sub-menu">';
        foreach ($categories as $category) {
            echo '<li><a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a></li>';
        }
        echo '</ul>';
        echo '</li>';
    }
    
    // Stores Dropdown
    $stores = get_terms(array(
        'taxonomy'   => 'store',
        'number'     => 5,
        'hide_empty' => true,
        'orderby'    => 'count',
        'order'      => 'DESC'
    ));
    
    if (!empty($stores) && !is_wp_error($stores)) {
        echo '<li class="menu-item-has-children">';
        echo '<a href="#">' . esc_html__('Stores', 'dealsindia') . '</a>';
        echo '<ul class="sub-menu">';
        foreach ($stores as $store) {
            echo '<li><a href="' . esc_url(get_term_link($store)) . '">' . esc_html($store->name) . '</a></li>';
        }
        echo '</ul>';
        echo '</li>';
    }
    
    // Giveaways Link (if post type exists)
    $giveaways_link = get_post_type_archive_link('giveaway');
    if ($giveaways_link) {
        echo '<li><a href="' . esc_url($giveaways_link) . '">' . esc_html__('Giveaways', 'dealsindia') . '</a></li>';
    }
    
    echo '</ul>';
}

/**
 * Check if menu has items
 */
function dealsindia_has_menu_items($location) {
    $locations = get_nav_menu_locations();
    if (isset($locations[$location])) {
        $menu = wp_get_nav_menu_object($locations[$location]);
        if ($menu && wp_get_nav_menu_items($menu->term_id)) {
            return true;
        }
    }
    return false;
}

// =====================================================
// SECTION 2: BREADCRUMB FUNCTIONS
// =====================================================

/**
 * Display breadcrumb navigation
 * Enhanced with schema.org markup for SEO
 */
function dealsindia_breadcrumb() {
    if (is_front_page()) {
        return;
    }
    
    $breadcrumb = array();
    $breadcrumb[] = '<a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'dealsindia') . '</a>';
    
    if (is_singular('deals')) {
        $breadcrumb[] = '<a href="' . esc_url(get_post_type_archive_link('deals')) . '">' . esc_html__('Deals', 'dealsindia') . '</a>';
        
        $categories = get_the_terms(get_the_ID(), 'deal-category');
        if ($categories && !is_wp_error($categories)) {
            $category = array_shift($categories);
            $breadcrumb[] = '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a>';
        }
        
        $breadcrumb[] = '<span>' . esc_html(get_the_title()) . '</span>';
    }
    elseif (is_singular('giveaway')) {
        $breadcrumb[] = '<a href="' . esc_url(get_post_type_archive_link('giveaway')) . '">' . esc_html__('Giveaways', 'dealsindia') . '</a>';
        $breadcrumb[] = '<span>' . esc_html(get_the_title()) . '</span>';
    }
    elseif (is_post_type_archive('deals')) {
        $breadcrumb[] = '<span>' . esc_html__('All Deals', 'dealsindia') . '</span>';
    }
    elseif (is_tax('deal-category')) {
        $term = get_queried_object();
        $breadcrumb[] = '<a href="' . esc_url(get_post_type_archive_link('deals')) . '">' . esc_html__('Deals', 'dealsindia') . '</a>';
        $breadcrumb[] = '<span>' . esc_html($term->name) . '</span>';
    }
    elseif (is_tax('store')) {
        $term = get_queried_object();
        $breadcrumb[] = '<a href="' . esc_url(home_url('/stores/')) . '">' . esc_html__('Stores', 'dealsindia') . '</a>';
        $breadcrumb[] = '<span>' . esc_html($term->name) . '</span>';
    }
    elseif (is_tax('deal-type')) {
        $term = get_queried_object();
        $breadcrumb[] = '<a href="' . esc_url(get_post_type_archive_link('deals')) . '">' . esc_html__('Deals', 'dealsindia') . '</a>';
        $breadcrumb[] = '<span>' . esc_html($term->name) . '</span>';
    }
    elseif (is_page()) {
        $breadcrumb[] = '<span>' . esc_html(get_the_title()) . '</span>';
    }
    elseif (is_search()) {
        $breadcrumb[] = '<span>' . sprintf(esc_html__('Search Results for: %s', 'dealsindia'), esc_html(get_search_query())) . '</span>';
    }
    elseif (is_404()) {
        $breadcrumb[] = '<span>' . esc_html__('Page Not Found', 'dealsindia') . '</span>';
    }
    
    echo '<nav class="breadcrumb" aria-label="' . esc_attr__('Breadcrumb', 'dealsindia') . '" itemscope itemtype="https://schema.org/BreadcrumbList">';
    echo '<ol class="breadcrumb-list">';
    
    foreach ($breadcrumb as $index => $crumb) {
        $position = $index + 1;
        $is_last = ($index === count($breadcrumb) - 1);
        
        echo '<li class="breadcrumb-item' . ($is_last ? ' active' : '') . '" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo $crumb;
        echo '<meta itemprop="position" content="' . esc_attr($position) . '">';
        echo '</li>';
    }
    
    echo '</ol>';
    echo '</nav>';
}

/**
 * Get breadcrumb array (for structured data)
 */
function dealsindia_get_breadcrumb_array() {
    $breadcrumb = array(
        array(
            'name' => __('Home', 'dealsindia'),
            'url' => home_url('/')
        )
    );
    
    if (is_singular('deals')) {
        $breadcrumb[] = array(
            'name' => __('Deals', 'dealsindia'),
            'url' => get_post_type_archive_link('deals')
        );
        
        $categories = get_the_terms(get_the_ID(), 'deal-category');
        if ($categories && !is_wp_error($categories)) {
            $category = array_shift($categories);
            $breadcrumb[] = array(
                'name' => $category->name,
                'url' => get_term_link($category)
            );
        }
        
        $breadcrumb[] = array(
            'name' => get_the_title(),
            'url' => get_permalink()
        );
    }
    
    return $breadcrumb;
}

// =====================================================
// SECTION 3: SEARCH FUNCTIONS
// =====================================================

/**
 * Custom search query for deals
 * Enhanced with meta query support
 */
function dealsindia_search_deals($search_query, $args = array()) {
    $default_args = array(
        'post_type' => 'deals',
        'post_status' => 'publish',
        's' => sanitize_text_field($search_query),
        'posts_per_page' => 20,
        'orderby' => 'relevance',
        'order' => 'DESC'
    );
    
    $args = wp_parse_args($args, $default_args);
    
    return new WP_Query($args);
}

/**
 * Search autocomplete suggestions
 * AJAX-ready function for live search
 */
function dealsindia_get_search_suggestions($query) {
    $query = sanitize_text_field($query);
    $suggestions = array();
    
    // Search deals
    $deals = get_posts(array(
        'post_type' => 'deals',
        'posts_per_page' => 5,
        's' => $query,
        'post_status' => 'publish'
    ));
    
    foreach ($deals as $deal) {
        $suggestions[] = array(
            'title' => $deal->post_title,
            'url' => get_permalink($deal->ID),
            'type' => 'deal',
            'thumbnail' => get_the_post_thumbnail_url($deal->ID, 'thumbnail')
        );
    }
    
    // Search categories
    $categories = get_terms(array(
        'taxonomy' => 'deal-category',
        'name__like' => $query,
        'number' => 3,
        'hide_empty' => true
    ));
    
    if (!is_wp_error($categories)) {
        foreach ($categories as $category) {
            $suggestions[] = array(
                'title' => $category->name,
                'url' => get_term_link($category),
                'type' => 'category',
                'count' => $category->count
            );
        }
    }
    
    // Search stores
    $stores = get_terms(array(
        'taxonomy' => 'store',
        'name__like' => $query,
        'number' => 3,
        'hide_empty' => true
    ));
    
    if (!is_wp_error($stores)) {
        foreach ($stores as $store) {
            $logo = get_term_meta($store->term_id, 'store_logo', true);
            $suggestions[] = array(
                'title' => $store->name,
                'url' => get_term_link($store),
                'type' => 'store',
                'logo' => $logo,
                'count' => $store->count
            );
        }
    }
    
    return $suggestions;
}

/**
 * Highlight search terms in content
 * Useful for search results pages
 */
function dealsindia_highlight_search_terms($text, $search_query) {
    if (empty($search_query)) {
        return $text;
    }
    
    $words = explode(' ', $search_query);
    
    foreach ($words as $word) {
        $word = trim($word);
        if (strlen($word) < 3) continue;
        
        $text = preg_replace(
            '/\b(' . preg_quote($word, '/') . ')\b/i',
            '<mark>$1</mark>',
            $text
        );
    }
    
    return $text;
}

// =====================================================
// SECTION 4: SOCIAL SHARE FUNCTIONS
// =====================================================

/**
 * Display social share buttons
 * Enhanced with more platforms and analytics tracking
 */
function dealsindia_social_share_buttons($args = array()) {
    global $post;
    if (!$post) return;
    
    $defaults = array(
        'title' => get_the_title(),
        'url' => get_permalink(),
        'show_label' => true,
        'platforms' => array('facebook', 'twitter', 'whatsapp', 'telegram', 'linkedin')
    );
    
    $args = wp_parse_args($args, $defaults);
    
    $url = urlencode($args['url']);
    $title = urlencode($args['title']);
    
    echo '<div class="social-share-buttons">';
    
    if ($args['show_label']) {
        echo '<span class="share-label">' . esc_html__('Share:', 'dealsindia') . '</span>';
    }
    
    // Facebook
    if (in_array('facebook', $args['platforms'])) {
        echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . $url . '" target="_blank" rel="noopener noreferrer" class="share-btn share-facebook" aria-label="' . esc_attr__('Share on Facebook', 'dealsindia') . '" onclick="dealsindia_track_share(\'facebook\')">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';
        echo '</a>';
    }
    
    // Twitter
    if (in_array('twitter', $args['platforms'])) {
        echo '<a href="https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title . '" target="_blank" rel="noopener noreferrer" class="share-btn share-twitter" aria-label="' . esc_attr__('Share on Twitter', 'dealsindia') . '" onclick="dealsindia_track_share(\'twitter\')">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>';
        echo '</a>';
    }
    
    // WhatsApp
    if (in_array('whatsapp', $args['platforms'])) {
        echo '<a href="https://api.whatsapp.com/send?text=' . $title . '%20' . $url . '" target="_blank" rel="noopener noreferrer" class="share-btn share-whatsapp" aria-label="' . esc_attr__('Share on WhatsApp', 'dealsindia') . '" onclick="dealsindia_track_share(\'whatsapp\')">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>';
        echo '</a>';
    }
    
    // Telegram
    if (in_array('telegram', $args['platforms'])) {
        echo '<a href="https://t.me/share/url?url=' . $url . '&text=' . $title . '" target="_blank" rel="noopener noreferrer" class="share-btn share-telegram" aria-label="' . esc_attr__('Share on Telegram', 'dealsindia') . '" onclick="dealsindia_track_share(\'telegram\')">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>';
        echo '</a>';
    }
    
    // LinkedIn
    if (in_array('linkedin', $args['platforms'])) {
        echo '<a href="https://www.linkedin.com/sharing/share-offsite/?url=' . $url . '" target="_blank" rel="noopener noreferrer" class="share-btn share-linkedin" aria-label="' . esc_attr__('Share on LinkedIn', 'dealsindia') . '" onclick="dealsindia_track_share(\'linkedin\')">';
        echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';
        echo '</a>';
    }
    
    echo '</div>';
}

/**
 * Get share count
 */
function dealsindia_get_share_count($post_id) {
    $count = get_post_meta($post_id, 'share_count', true);
    return $count ? intval($count) : 0;
}

/**
 * Increment share count
 */
function dealsindia_increment_share_count($post_id) {
    $count = dealsindia_get_share_count($post_id);
    update_post_meta($post_id, 'share_count', $count + 1);
}

// =====================================================
// SECTION 5: GENERAL TEMPLATE FUNCTIONS
// =====================================================

/**
 * Get customizer value with fallback
 */
function dealsindia_get_option($option_name, $default = '') {
    return get_theme_mod($option_name, $default);
}

/**
 * Display featured image with fallback placeholder
 */
function dealsindia_post_thumbnail($size = 'large', $class = '', $post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    if (has_post_thumbnail($post_id)) {
        echo get_the_post_thumbnail($post_id, $size, array('class' => $class));
    } else {
        echo '<div class="no-image-placeholder ' . esc_attr($class) . '">';
        echo '<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
        echo '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>';
        echo '<circle cx="8.5" cy="8.5" r="1.5"/>';
        echo '<polyline points="21 15 16 10 5 21"/>';
        echo '</svg>';
        echo '</div>';
    }
}

/**
 * Get reading time estimate
 */
function dealsindia_reading_time($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $minutes = max(1, ceil($word_count / 200));
    
    return sprintf(_n('%d min read', '%d mins read', $minutes, 'dealsindia'), $minutes);
}

/**
 * Pagination for archives with enhanced styling
 */
function dealsindia_pagination() {
    global $wp_query;
    
    if ($wp_query->max_num_pages <= 1) {
        return;
    }
    
    $current_page = max(1, get_query_var('paged'));
    
    echo '<nav class="pagination" aria-label="' . esc_attr__('Pagination', 'dealsindia') . '" role="navigation">';
    
    echo paginate_links(array(
        'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format'    => '?paged=%#%',
        'current'   => $current_page,
        'total'     => $wp_query->max_num_pages,
        'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="15 18 9 12 15 6"></polyline></svg> ' . esc_html__('Previous', 'dealsindia'),
        'next_text' => esc_html__('Next', 'dealsindia') . ' <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="9 18 15 12 9 6"></polyline></svg>',
        'type'      => 'list',
        'mid_size'  => 2,
        'end_size'  => 1,
    ));
    
    echo '</nav>';
}

/**
 * Truncate text with proper word boundary
 */
function dealsindia_truncate_text($text, $length = 150, $more = '...') {
    $text = strip_tags($text);
    
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $last_space = strrpos($text, ' ');
    
    if ($last_space !== false) {
        $text = substr($text, 0, $last_space);
    }
    
    return trim($text) . $more;
}

/**
 * Format price with currency
 */
function dealsindia_format_price($price, $currency = '₹') {
    if (empty($price)) {
        return '';
    }
    
    // Remove any non-numeric characters except dot and comma
    $price = preg_replace('/[^0-9.,]/', '', $price);
    
    // Convert to float
    $price = floatval(str_replace(',', '', $price));
    
    // Format with Indian number system
    return $currency . number_format($price, 0, '.', ',');
}

/**
 * Calculate discount percentage
 */
function dealsindia_calculate_discount($original_price, $sale_price) {
    if (empty($original_price) || empty($sale_price)) {
        return 0;
    }
    
    $original = floatval($original_price);
    $sale = floatval($sale_price);
    
    if ($original <= 0) {
        return 0;
    }
    
    $discount = (($original - $sale) / $original) * 100;
    return round($discount);
}

/**
 * Time ago function
 */
function dealsindia_time_ago($time) {
    $time_difference = time() - $time;
    
    if ($time_difference < 1) {
        return __('just now', 'dealsindia');
    }
    
    $condition = array(
        12 * 30 * 24 * 60 * 60 =>  __('year', 'dealsindia'),
        30 * 24 * 60 * 60       =>  __('month', 'dealsindia'),
        24 * 60 * 60            =>  __('day', 'dealsindia'),
        60 * 60                 =>  __('hour', 'dealsindia'),
        60                      =>  __('minute', 'dealsindia'),
        1                       =>  __('second', 'dealsindia')
    );
    
    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;
        
        if ($d >= 1) {
            $t = round($d);
            return sprintf(_n('%s %s ago', '%s %ss ago', $t, 'dealsindia'), $t, $str);
        }
    }
}

/**
 * Get view count for posts
 */
function dealsindia_get_view_count($post_id) {
    $count = get_post_meta($post_id, 'post_views_count', true);
    return $count ? intval($count) : 0;
}

/**
 * Increment view count
 */
function dealsindia_set_view_count($post_id) {
    $count = dealsindia_get_view_count($post_id);
    $count++;
    update_post_meta($post_id, 'post_views_count', $count);
}

/**
 * Format view count for display
 */
function dealsindia_format_view_count($count) {
    if ($count >= 1000000) {
        return round($count / 1000000, 1) . 'M';
    } elseif ($count >= 1000) {
        return round($count / 1000, 1) . 'K';
    }
    return $count;
}

// =====================================================
// END OF TEMPLATE HELPERS
// =====================================================
