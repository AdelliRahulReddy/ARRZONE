<?php
/**
 * Expiry Helper Functions
 * Deal expiration checking
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Check if deal is expired
 * @param int $deal_id The deal post ID
 * @return bool True if expired, false otherwise
 */
function dealsindia_is_deal_expired($deal_id) {
    // Get expiry date
    $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
    
    // If no expiry date set, deal never expires
    if (empty($expiry_date)) {
        return false;
    }
    
    // Convert expiry date to timestamp
    $expiry_timestamp = strtotime($expiry_date);
    $current_timestamp = current_time('timestamp');
    
    // Check if expired
    if ($current_timestamp > $expiry_timestamp) {
        return true;
    }
    
    return false;
}

/**
 * Get time remaining until deal expires
 * @param int $deal_id The deal post ID
 * @return string Human readable time remaining
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
    
    // Calculate days, hours, minutes
    $days = floor($difference / 86400);
    $hours = floor(($difference % 86400) / 3600);
    $minutes = floor(($difference % 3600) / 60);
    
    if ($days > 0) {
        return sprintf(__('%d days left', 'dealsindia'), $days);
    } elseif ($hours > 0) {
        return sprintf(__('%d hours left', 'dealsindia'), $hours);
    } else {
        return sprintf(__('%d minutes left', 'dealsindia'), $minutes);
    }
}

/**
 * Display expiry badge
 * @param int $deal_id The deal post ID
 * @return string HTML for expiry badge
 */
function dealsindia_expiry_badge($deal_id) {
    if (dealsindia_is_deal_expired($deal_id)) {
        return '<span class="deal-badge deal-expired-badge">Expired</span>';
    }
    
    $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
    
    if (empty($expiry_date)) {
        return '';
    }
    
    $time_remaining = dealsindia_get_time_remaining($deal_id);
    return '<span class="deal-badge deal-expiry-badge">' . esc_html($time_remaining) . '</span>';
}

/**
 * Check if deal should be shown when expired
 * @param int $deal_id The deal post ID
 * @return bool True if should show, false otherwise
 */
function dealsindia_show_when_expired($deal_id) {
    $show_when_expired = get_post_meta($deal_id, 'show_when_expired', true);
    return ($show_when_expired === '1');
}
