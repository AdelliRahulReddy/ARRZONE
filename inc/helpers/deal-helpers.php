<?php
if (!defined('ABSPATH')) exit; 
/**
 * Deal Helpers - Consolidated
 * 
 * Merges:
 * - deal-type-helpers.php (Deal type functions)
 * - expiry-helpers.php (Expiry/countdown logic)
 * - card-content-generator.php (Deal card content generation)
 * 
 * @package ARRZone
 * @version 1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// =====================================================
// SECTION 1: DEAL TYPE FUNCTIONS
// =====================================================

/**
 * Get deal type emoji
 */
function dealsindia_get_deal_type_emoji($term_id) {
    $term = get_term($term_id, 'deal_type');
    if (!$term || is_wp_error($term)) {
        return '';
    }
    
    // Parse emoji from name (e.g., "🎟️ Coupons")
    $name_parts = explode(' ', $term->name, 2);
    if (mb_strlen($name_parts[0]) === 1 || preg_match('/[\x{1F600}-\x{1F64F}]/u', $name_parts[0])) {
        return $name_parts[0];
    }
    
    return '';
}

/**
 * Get deal type name without emoji
 */
function dealsindia_get_deal_type_name($term_id) {
    $term = get_term($term_id, 'deal_type');
    if (!$term || is_wp_error($term)) {
        return '';
    }
    
    $name_parts = explode(' ', $term->name, 2);
    return isset($name_parts[1]) ? $name_parts[1] : $term->name;
}

/**
 * Check if deal has specific type
 */
function dealsindia_has_deal_type($deal_id, $type_slug) {
    $types = get_the_terms($deal_id, 'deal_type');
    if (!$types || is_wp_error($types)) {
        return false;
    }
    
    foreach ($types as $type) {
        if ($type->slug === $type_slug) {
            return true;
        }
    }
    
    return false;
}

// =====================================================
// SECTION 2: EXPIRY & COUNTDOWN FUNCTIONS
// =====================================================

/**
 * Check if deal is expired
 */
function dealsindia_is_deal_expired($deal_id) {
    $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
    if (empty($expiry_date)) {
        return false;
    }
    
    return (current_time('timestamp') > strtotime($expiry_date));
}

/**
 * Get time remaining for deal
 */
function dealsindia_get_time_remaining($deal_id) {
    $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
    if (empty($expiry_date)) {
        return __('No expiry', 'dealsindia');
    }
    
    $expiry_timestamp = strtotime($expiry_date);
    $current_timestamp = current_time('timestamp');
    
    if ($current_timestamp > $expiry_timestamp) {
        return __('Expired', 'dealsindia');
    }
    
    $difference = $expiry_timestamp - $current_timestamp;
    $days = floor($difference / 86400);
    $hours = floor(($difference % 86400) / 3600);
    
    if ($days > 0) {
        return sprintf(__('%d days left', 'dealsindia'), $days);
    }
    
    if ($hours > 0) {
        return sprintf(__('%d hours left', 'dealsindia'), $hours);
    }
    
    return __('Ending soon', 'dealsindia');
}

/**
 * Get expiry status with styling
 */
function dealsindia_get_expiry_status($deal_id) {
    $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
    if (empty($expiry_date)) {
        return '';
    }
    
    $expiry_timestamp = strtotime($expiry_date);
    $current_timestamp = current_time('timestamp');
    $difference = $expiry_timestamp - $current_timestamp;
    $days_left = floor($difference / 86400);
    
    if ($current_timestamp > $expiry_timestamp) {
        return '<span class="expiry-status expired">Expired</span>';
    }
    
    if ($days_left <= 1) {
        return '<span class="expiry-status expiring-soon">Expiring soon</span>';
    }
    
    if ($days_left <= 7) {
        return '<span class="expiry-status expiring-this-week">' . $days_left . ' days left</span>';
    }
    
    return '<span class="expiry-status active">' . $days_left . ' days left</span>';
}

/**
 * Format expiry date for display
 */
function dealsindia_format_expiry_date($deal_id) {
    $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
    if (empty($expiry_date)) {
        return __('No expiry', 'dealsindia');
    }
    
    return date_i18n(get_option('date_format'), strtotime($expiry_date));
}

// =====================================================
// SECTION 3: DEAL CARD CONTENT GENERATION
// =====================================================

/**
 * Generate deal discount percentage
 */
function dealsindia_get_deal_discount($deal_id) {
    $original_price = get_post_meta($deal_id, 'deal_original_price', true);
    $sale_price = get_post_meta($deal_id, 'deal_sale_price', true);
    
    if (!$original_price || !$sale_price || $original_price <= $sale_price) {
        return 0;
    }
    
    return round((($original_price - $sale_price) / $original_price) * 100);
}

/**
 * Generate deal price display HTML
 */
function dealsindia_get_price_html($deal_id) {
    $sale_price = get_post_meta($deal_id, 'deal_sale_price', true);
    $original_price = get_post_meta($deal_id, 'deal_original_price', true);
    
    if (!$sale_price) {
        return '';
    }
    
    $html = '<div class="deal-prices">';
    $html .= '<span class="sale-price">₹' . number_format($sale_price) . '</span>';
    
    if ($original_price && $original_price > $sale_price) {
        $html .= ' <del class="original-price">₹' . number_format($original_price) . '</del>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Get deal badge HTML
 */
function dealsindia_get_deal_badge($deal_id) {
    $badge_text = get_post_meta($deal_id, 'deal_badge', true);
    
    if (empty($badge_text)) {
        return '';
    }
    
    return '<span class="deal-badge">' . esc_html($badge_text) . '</span>';
}

/**
 * Get deal store logo
 */
function dealsindia_get_store_logo($deal_id, $size = 'thumbnail') {
    $stores = get_the_terms($deal_id, 'store');
    
    if (!$stores || is_wp_error($stores)) {
        return '';
    }
    
    $store = array_shift($stores);
    $logo = get_term_meta($store->term_id, 'store_logo', true);
    
    if (!$logo) {
        return '';
    }
    
    return '<img src="' . esc_url($logo) . '" alt="' . esc_attr($store->name) . '" class="store-logo">';
}

/**
 * Generate full deal card content
 */
function dealsindia_generate_deal_card_content($deal_id) {
    $discount = dealsindia_get_deal_discount($deal_id);
    $prices = dealsindia_get_price_html($deal_id);
    $badge = dealsindia_get_deal_badge($deal_id);
    $expiry = dealsindia_get_expiry_status($deal_id);
    $store_logo = dealsindia_get_store_logo($deal_id);
    
    return array(
        'discount' => $discount,
        'prices' => $prices,
        'badge' => $badge,
        'expiry' => $expiry,
        'store_logo' => $store_logo
    );
}

// =====================================================
// END OF DEAL HELPERS
// =====================================================
