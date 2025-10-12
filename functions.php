<?php
/**
 * DealsIndia Theme Functions
 * Spider-Verse Permalink System - 100% Dynamic
 * 
 * @package DealsIndia
 * @version 7.0 - Production Ready
 */

// ===================================================== 
// THEME SETUP
// ===================================================== 
require_once get_template_directory() . '/inc/setup/theme-support.php';
require_once get_template_directory() . '/inc/setup/enqueue-assets.php';
require_once get_template_directory() . '/inc/setup/customizer.php';
require_once get_template_directory() . '/inc/setup/menus.php';

// ===================================================== 
// POST TYPES
// ===================================================== 
require_once get_template_directory() . '/inc/post-types/deals.php';
require_once get_template_directory() . '/inc/post-types/hero-banners.php';
require_once get_template_directory() . '/inc/post-types/work-steps.php';
require_once get_template_directory() . '/inc/post-types/giveaways.php';
require_once get_template_directory() . '/inc/post-types/money-earning.php';

// ===================================================== 
// TAXONOMIES
// ===================================================== 
require_once get_template_directory() . '/inc/taxonomies/deal-categories.php';
require_once get_template_directory() . '/inc/taxonomies/stores.php';
require_once get_template_directory() . '/inc/taxonomies/deal-types.php';
require_once get_template_directory() . '/inc/taxonomies/term-meta.php';

// ===================================================== 
// META BOXES
// ===================================================== 
require_once get_template_directory() . '/inc/meta-boxes/deal-meta.php';
require_once get_template_directory() . '/inc/meta-boxes/banner-meta.php';
require_once get_template_directory() . '/inc/meta-boxes/step-meta.php';
require_once get_template_directory() . '/inc/meta-boxes/giveaway-meta.php';

// ===================================================== 
// HELPERS
// ===================================================== 
require_once get_template_directory() . '/inc/helpers/template-helpers.php';
require_once get_template_directory() . '/inc/helpers/text-helpers.php';
require_once get_template_directory() . '/inc/helpers/color-helpers.php';
require_once get_template_directory() . '/inc/helpers/expiry-helpers.php';
require_once get_template_directory() . '/inc/helpers/social-share.php';
require_once get_template_directory() . '/inc/helpers/analytics-tracker.php';
require_once get_template_directory() . '/inc/helpers/newsletter-handler.php';
require_once get_template_directory() . '/inc/helpers/search-helpers.php';
require_once get_template_directory() . '/inc/helpers/performance-helpers.php';
require_once get_template_directory() . '/inc/helpers/deal-type-helpers.php';
require_once get_template_directory() . '/inc/helpers/breadcrumb-helpers.php';

// ===================================================== 
// QUERIES
// ===================================================== 
require_once get_template_directory() . '/inc/queries/deal-queries.php';
require_once get_template_directory() . '/inc/queries/archive-sorting.php';

// ===================================================== 
// AJAX HANDLERS
// ===================================================== 
require_once get_template_directory() . '/inc/ajax/filter-handler.php';

// ===================================================== 
// FALLBACK HELPER FUNCTIONS
// ===================================================== 

if (!function_exists('dealsindia_is_deal_expired')) {
    function dealsindia_is_deal_expired($deal_id) {
        $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
        if (empty($expiry_date)) return false;
        return (current_time('timestamp') > strtotime($expiry_date));
    }
}

if (!function_exists('dealsindia_get_time_remaining')) {
    function dealsindia_get_time_remaining($deal_id) {
        $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
        if (empty($expiry_date)) return __('No expiry', 'dealsindia');
        
        $expiry_timestamp = strtotime($expiry_date);
        $current_timestamp = current_time('timestamp');
        
        if ($current_timestamp > $expiry_timestamp) return __('Expired', 'dealsindia');
        
        $difference = $expiry_timestamp - $current_timestamp;
        $days = floor($difference / 86400);
        $hours = floor(($difference % 86400) / 3600);
        
        if ($days > 0) return sprintf(__('%d days left', 'dealsindia'), $days);
        if ($hours > 0) return sprintf(__('%d hours left', 'dealsindia'), $hours);
        return __('Ending soon', 'dealsindia');
    }
}

if (!function_exists('dealsindia_social_share_buttons')) {
    function dealsindia_social_share_buttons() {
        global $post;
        if (!$post) return;
        
        $url = urlencode(get_permalink());
        $title = urlencode(get_the_title());
        
        echo '<div class="social-share-buttons">';
        echo '<span class="share-label">' . __('Share:', 'dealsindia') . '</span>';
        echo '<a href="https://www.facebook.com/sharer/sharer.php?u=' . $url . '" target="_blank" rel="noopener" class="share-btn share-facebook" aria-label="Share on Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>';
        echo '<a href="https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title . '" target="_blank" rel="noopener" class="share-btn share-twitter" aria-label="Share on Twitter"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg></a>';
        echo '<a href="https://api.whatsapp.com/send?text=' . $title . ' ' . $url . '" target="_blank" rel="noopener" class="share-btn share-whatsapp" aria-label="Share on WhatsApp"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></a>';
        echo '<a href="https://t.me/share/url?url=' . $url . '&text=' . $title . '" target="_blank" rel="noopener" class="share-btn share-telegram" aria-label="Share on Telegram"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg></a>';
        echo '</div>';
    }
}

// ===================================================== 
// SPIDER-VERSE PERMALINK SYSTEM
// ===================================================== 

/**
 * Spider-Verse Permalink Structure
 * Special types: /deals/coupons/deal-name/
 * Regular deals: /deals/deal-name/
 */
function dealsindia_spider_permalink($post_link, $post) {
    if ($post->post_type !== 'deals' || strpos($post_link, '%deal_type%') === false) {
        return $post_link;
    }
    
    $deal_types = get_the_terms($post->ID, 'deal_type');
    
    if ($deal_types && !is_wp_error($deal_types)) {
        $deal_type = array_shift($deal_types);
        if ($deal_type->slug === 'deals') {
            return str_replace('/%deal_type%', '', $post_link);
        }
        return str_replace('%deal_type%', $deal_type->slug, $post_link);
    }
    
    return str_replace('/%deal_type%', '', $post_link);
}
add_filter('post_type_link', 'dealsindia_spider_permalink', 10, 2);

/**
 * Spider-Verse Rewrite Rules
 */
function dealsindia_spider_rewrite_rules() {
    add_rewrite_rule('^deals/?$', 'index.php?post_type=deals', 'top');
    add_rewrite_rule('^deals/([^/]+)/([^/]+)/?$', 'index.php?dealsindia_deal_name=$matches[2]&dealsindia_deal_type=$matches[1]', 'top');
    add_rewrite_rule('^deals/([^/]+)/?$', 'index.php?dealsindia_deals_or_type=$matches[1]', 'top');
}
add_action('init', 'dealsindia_spider_rewrite_rules');

/**
 * Category & Store Archive Rewrite Rules
 */
function dealsindia_taxonomy_archive_rewrites() {
    add_rewrite_rule(
        '^deals-category/?$',
        'index.php?dealsindia_all_categories=1',
        'top'
    );
    
    add_rewrite_rule(
        '^deals-store/?$',
        'index.php?dealsindia_all_stores=1',
        'top'
    );
}
add_action('init', 'dealsindia_taxonomy_archive_rewrites');

/**
 * Parse ambiguous deal URLs
 */
function dealsindia_parse_request($wp) {
    if (isset($wp->query_vars['dealsindia_deals_or_type'])) {
        $slug = $wp->query_vars['dealsindia_deals_or_type'];
        $term = get_term_by('slug', $slug, 'deal_type');
        
        if ($term) {
            $wp->query_vars['deal_type'] = $slug;
            $wp->query_vars['post_type'] = 'deals';
        } else {
            $wp->query_vars['name'] = $slug;
            $wp->query_vars['post_type'] = 'deals';
        }
        
        unset($wp->query_vars['dealsindia_deals_or_type']);
    }
    
    if (isset($wp->query_vars['dealsindia_deal_name']) && isset($wp->query_vars['dealsindia_deal_type'])) {
        $wp->query_vars['name'] = $wp->query_vars['dealsindia_deal_name'];
        $wp->query_vars['post_type'] = 'deals';
        $wp->query_vars['deal_type'] = $wp->query_vars['dealsindia_deal_type'];
        
        unset($wp->query_vars['dealsindia_deal_name']);
        unset($wp->query_vars['dealsindia_deal_type']);
    }
    
    return $wp;
}
add_filter('parse_request', 'dealsindia_parse_request');

/**
 * Register custom query vars
 */
function dealsindia_query_vars($vars) {
    $vars[] = 'dealsindia_deals_or_type';
    $vars[] = 'dealsindia_deal_name';
    $vars[] = 'dealsindia_deal_type';
    $vars[] = 'dealsindia_all_categories';
    $vars[] = 'dealsindia_all_stores';
    return $vars;
}
add_filter('query_vars', 'dealsindia_query_vars');

/**
 * Template redirect for taxonomy archives
 */
function dealsindia_taxonomy_archive_template() {
    if (get_query_var('dealsindia_all_categories')) {
        $template = locate_template('archive-categories.php');
        if ($template) {
            include($template);
            exit;
        }
    }
    
    if (get_query_var('dealsindia_all_stores')) {
        $template = locate_template('archive-stores.php');
        if ($template) {
            include($template);
            exit;
        }
    }
}
add_action('template_redirect', 'dealsindia_taxonomy_archive_template');

/**
 * Flush on theme activation
 */
function dealsindia_spider_activation() {
    dealsindia_spider_rewrite_rules();
    dealsindia_taxonomy_archive_rewrites();
    flush_rewrite_rules(false);
}
add_action('after_switch_theme', 'dealsindia_spider_activation');

/**
 * Auto-detect and flush permalinks when taxonomy slugs change
 */
function dealsindia_auto_flush_on_taxonomy_change() {
    if (!is_admin()) {
        return;
    }
    
    $deal_cat_tax = get_taxonomy('deal_category');
    $store_tax = get_taxonomy('store');
    
    if (!$deal_cat_tax || !$store_tax) {
        return;
    }
    
    $actual_category_slug = isset($deal_cat_tax->rewrite['slug']) ? $deal_cat_tax->rewrite['slug'] : '';
    $actual_store_slug = isset($store_tax->rewrite['slug']) ? $store_tax->rewrite['slug'] : '';
    
    $stored_signature = get_option('dealsindia_taxonomy_signature', '');
    $current_signature = $actual_category_slug . '|' . $actual_store_slug;
    
    if ($stored_signature !== $current_signature) {
        flush_rewrite_rules(false);
        update_option('dealsindia_taxonomy_signature', $current_signature);
    }
}
add_action('admin_init', 'dealsindia_auto_flush_on_taxonomy_change');

/**
 * Auto-assign deal type to new deals
 */
function dealsindia_auto_assign_deal_type($post_id, $post, $update) {
    if ($post->post_type !== 'deals' || $post->post_status !== 'publish' || 
        defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || wp_is_post_revision($post_id)) {
        return;
    }
    
    $existing_types = get_the_terms($post_id, 'deal_type');
    if (!$existing_types || is_wp_error($existing_types)) {
        $default_type = get_term_by('slug', 'deals', 'deal_type');
        if ($default_type) {
            wp_set_object_terms($post_id, $default_type->term_id, 'deal_type', false);
        }
    }
}
add_action('save_post', 'dealsindia_auto_assign_deal_type', 10, 3);

// ===================================================== 
// ENHANCED ARCHIVE SYSTEM - LOCALIZATION
// ===================================================== 

/**
 * Localize AJAX data for filter scripts
 */
function dealsindia_localize_filter_data() {
    if (!is_post_type_archive('deals') && !is_tax('store') && !is_tax('deal_category')) {
        return;
    }
    
    $context_type = '';
    $context_slug = '';
    
    if (is_tax('store')) {
        $context_type = 'store';
        $context_slug = get_queried_object()->slug;
    } elseif (is_tax('deal_category')) {
        $context_type = 'deal_category';
        $context_slug = get_queried_object()->slug;
    }
    
    wp_localize_script('dealsindia-filter-ajax', 'dealsindiaFilterData', array(
        'ajax_url'     => admin_url('admin-ajax.php'),
        'nonce'        => wp_create_nonce('dealsindia_filter_nonce'),
        'context_type' => $context_type,
        'context_slug' => $context_slug,
        'per_page'     => 20,
        'translations' => array(
            'loading'       => __('Loading deals...', 'dealsindia'),
            'load_more'     => __('Load More Deals', 'dealsindia'),
            'no_more'       => __('No More Deals', 'dealsindia'),
            'error'         => __('Error loading deals. Please try again.', 'dealsindia'),
            'showing'       => __('Showing', 'dealsindia'),
            'of'            => __('of', 'dealsindia'),
            'deals'         => __('deals', 'dealsindia'),
        ),
    ));
}
add_action('wp_enqueue_scripts', 'dealsindia_localize_filter_data', 20);
