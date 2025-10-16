<?php
if (!defined('ABSPATH')) exit; 
/**
 * Utilities - Consolidated (Deduplicated)
 * 
 * Merges:
 * - text-helpers.php (Text manipulation)
 * - performance-helpers.php (Caching & optimization)
 * - newsletter-handler.php (Newsletter subscriptions)
 * - color-helpers.php (Color manipulation)
 * - analytics-tracker.php (Click tracking & analytics)
 * 
 * @package ARRZONE
 * @version 2.0 - Deduplicated
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// =====================================================
// SECTION 1: TEXT MANIPULATION FUNCTIONS
// =====================================================

/**
 * Sanitize and format text for display
 */
function dealsindia_sanitize_text($text) {
    return wp_kses_post(wpautop($text));
}

/**
 * Excerpt with custom length
 */
function dealsindia_custom_excerpt($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = strip_tags($text);
    $text = substr($text, 0, $length);
    $last_space = strrpos($text, ' ');
    
    if ($last_space !== false) {
        $text = substr($text, 0, $last_space);
    }
    
    return $text . '...';
}

/**
 * Convert to title case
 */
function dealsindia_title_case($string) {
    return ucwords(strtolower($string));
}

/**
 * Remove special characters from string
 */
function dealsindia_clean_string($string) {
    $string = preg_replace('/[^A-Za-z0-9\s-]/', '', $string);
    $string = preg_replace('/\s+/', ' ', $string);
    return trim($string);
}

/**
 * Slugify text
 */
function dealsindia_slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    return empty($text) ? 'n-a' : $text;
}

/**
 * Extract first paragraph from content
 */
function dealsindia_first_paragraph($content) {
    $content = wpautop($content);
    $paragraphs = explode('</p>', $content);
    
    if (!empty($paragraphs[0])) {
        return $paragraphs[0] . '</p>';
    }
    
    return $content;
}

// =====================================================
// SECTION 2: PERFORMANCE & CACHING FUNCTIONS
// =====================================================

/**
 * Get cached query results
 */
function dealsindia_get_cached_query($cache_key, $callback, $expiration = 3600) {
    $cached = get_transient($cache_key);
    
    if (false !== $cached) {
        return $cached;
    }
    
    $result = call_user_func($callback);
    set_transient($cache_key, $result, $expiration);
    
    return $result;
}

/**
 * Get cached expired deal IDs
 */
function dealsindia_get_cached_expired_ids() {
    $cache_key = 'dealsindia_expired_ids';
    $cached = get_transient($cache_key);
    
    if (false !== $cached) {
        return $cached;
    }
    
    global $wpdb;
    
    // Query all expired deal IDs
    $expired_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} 
        WHERE meta_key = 'deal_expiry_date' 
        AND meta_value < %s",
        current_time('Y-m-d')
    ));
    
    // Cache for 1 hour
    set_transient($cache_key, $expired_ids, 3600);
    
    return $expired_ids ? $expired_ids : array();
}

/**
 * Clear specific cache
 */
function dealsindia_clear_cache($cache_key) {
    return delete_transient($cache_key);
}

/**
 * Clear all theme caches
 */
function dealsindia_clear_all_caches() {
    global $wpdb;
    
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_dealsindia_%' 
        OR option_name LIKE '_transient_timeout_dealsindia_%'"
    );
    
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    return true;
}

/**
 * Get cached deals query
 */
function dealsindia_get_cached_deals($args = array(), $cache_time = 1800) {
    $cache_key = 'dealsindia_deals_' . md5(serialize($args));
    
    return dealsindia_get_cached_query($cache_key, function() use ($args) {
        return new WP_Query($args);
    }, $cache_time);
}

/**
 * Optimize images on upload
 */
function dealsindia_optimize_image($file) {
    if (!function_exists('wp_get_image_editor')) {
        return $file;
    }
    
    $editor = wp_get_image_editor($file);
    
    if (!is_wp_error($editor)) {
        $editor->set_quality(85);
        $editor->save($file);
    }
    
    return $file;
}
add_filter('wp_handle_upload', 'dealsindia_optimize_image');

/**
 * Lazy load images
 */
function dealsindia_add_lazy_loading($content) {
    if (is_feed() || is_preview()) {
        return $content;
    }
    
    $content = preg_replace(
        '/<img(.*?)src=/i',
        '<img$1loading="lazy" src=',
        $content
    );
    
    return $content;
}
add_filter('the_content', 'dealsindia_add_lazy_loading');

// =====================================================
// SECTION 3: NEWSLETTER FUNCTIONS
// =====================================================

/**
 * Process newsletter subscription
 */
function dealsindia_process_newsletter_subscription() {
    if (!isset($_POST['dealsindia_newsletter_nonce']) || 
        !wp_verify_nonce($_POST['dealsindia_newsletter_nonce'], 'dealsindia_newsletter')) {
        wp_send_json_error(array('message' => __('Security check failed', 'dealsindia')));
    }
    
    $email = sanitize_email($_POST['email']);
    
    if (!is_email($email)) {
        wp_send_json_error(array('message' => __('Invalid email address', 'dealsindia')));
    }
    
    // Check if already subscribed
    $existing = get_posts(array(
        'post_type' => 'newsletter_sub',
        'title' => $email,
        'posts_per_page' => 1
    ));
    
    if (!empty($existing)) {
        wp_send_json_error(array('message' => __('You are already subscribed!', 'dealsindia')));
    }
    
    // Create subscriber post
    $subscriber_id = wp_insert_post(array(
        'post_type' => 'newsletter_sub',
        'post_title' => $email,
        'post_status' => 'publish'
    ));
    
    if ($subscriber_id) {
        update_post_meta($subscriber_id, 'subscriber_source', sanitize_text_field($_POST['source'] ?? 'footer'));
        update_post_meta($subscriber_id, 'subscriber_status', 'active');
        update_post_meta($subscriber_id, 'subscriber_ip', dealsindia_get_user_ip());
        
        wp_send_json_success(array('message' => __('Successfully subscribed!', 'dealsindia')));
    } else {
        wp_send_json_error(array('message' => __('Subscription failed. Please try again.', 'dealsindia')));
    }
}
add_action('wp_ajax_dealsindia_newsletter', 'dealsindia_process_newsletter_subscription');
add_action('wp_ajax_nopriv_dealsindia_newsletter', 'dealsindia_process_newsletter_subscription');

/**
 * Unsubscribe from newsletter
 */
function dealsindia_unsubscribe_newsletter($email) {
    $subscriber = get_posts(array(
        'post_type' => 'newsletter_sub',
        'title' => $email,
        'posts_per_page' => 1
    ));
    
    if (!empty($subscriber)) {
        update_post_meta($subscriber[0]->ID, 'subscriber_status', 'unsubscribed');
        return true;
    }
    
    return false;
}

/**
 * Get subscriber count
 */
function dealsindia_get_subscriber_count() {
    $count = wp_count_posts('newsletter_sub');
    return isset($count->publish) ? $count->publish : 0;
}

/**
 * Get user IP address
 */
function dealsindia_get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return sanitize_text_field($ip);
}

// =====================================================
// SECTION 4: COLOR MANIPULATION FUNCTIONS
// =====================================================

/**
 * Convert hex to RGB
 */
function dealsindia_hex_to_rgb($hex) {
    $hex = str_replace('#', '', $hex);
    
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    
    return array($r, $g, $b);
}

/**
 * Lighten color
 */
function dealsindia_lighten_color($hex, $percent) {
    $rgb = dealsindia_hex_to_rgb($hex);
    
    $r = min(255, $rgb[0] + ($percent / 100) * 255);
    $g = min(255, $rgb[1] + ($percent / 100) * 255);
    $b = min(255, $rgb[2] + ($percent / 100) * 255);
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

/**
 * Darken color
 */
function dealsindia_darken_color($hex, $percent) {
    $rgb = dealsindia_hex_to_rgb($hex);
    
    $r = max(0, $rgb[0] - ($percent / 100) * 255);
    $g = max(0, $rgb[1] - ($percent / 100) * 255);
    $b = max(0, $rgb[2] - ($percent / 100) * 255);
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

/**
 * Get contrasting text color (black or white)
 */
function dealsindia_get_contrast_color($hex) {
    $rgb = dealsindia_hex_to_rgb($hex);
    $brightness = (($rgb[0] * 299) + ($rgb[1] * 587) + ($rgb[2] * 114)) / 1000;
    
    return ($brightness > 155) ? '#000000' : '#ffffff';
}

// =====================================================
// SECTION 5: ANALYTICS & TRACKING FUNCTIONS
// =====================================================

/**
 * Track deal click
 */
function dealsindia_track_deal_click() {
    if (!isset($_POST['deal_id']) || !isset($_POST['nonce'])) {
        wp_send_json_error();
    }
    
    if (!wp_verify_nonce($_POST['nonce'], 'dealsindia_click_nonce')) {
        wp_send_json_error();
    }
    
    $deal_id = intval($_POST['deal_id']);
    $clicks = get_post_meta($deal_id, 'deal_clicks', true);
    $clicks = $clicks ? intval($clicks) : 0;
    
    update_post_meta($deal_id, 'deal_clicks', $clicks + 1);
    
    // Track click in database (optional - for detailed analytics)
    dealsindia_log_click($deal_id);
    
    wp_send_json_success(array('clicks' => $clicks + 1));
}
add_action('wp_ajax_dealsindia_track_click', 'dealsindia_track_deal_click');
add_action('wp_ajax_nopriv_dealsindia_track_click', 'dealsindia_track_deal_click');

/**
 * Log click to database (optional)
 */
function dealsindia_log_click($deal_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dealsindia_clicks';
    
    // Create table if not exists (run once on theme activation)
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        deal_id bigint(20) NOT NULL,
        user_ip varchar(100) DEFAULT '' NOT NULL,
        click_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Insert click record
    $wpdb->insert(
        $table_name,
        array(
            'deal_id' => $deal_id,
            'user_ip' => dealsindia_get_user_ip(),
            'click_date' => current_time('mysql')
        ),
        array('%d', '%s', '%s')
    );
}

/**
 * Get deal click count
 */
function dealsindia_get_click_count($deal_id) {
    $clicks = get_post_meta($deal_id, 'deal_clicks', true);
    return $clicks ? intval($clicks) : 0;
}

/**
 * Get popular deals (by clicks)
 */
function dealsindia_get_popular_deals($limit = 10) {
    $args = array(
        'post_type' => 'deals',
        'posts_per_page' => $limit,
        'meta_key' => 'deal_clicks',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    );
    
    return new WP_Query($args);
}

/**
 * Track page views
 */
function dealsindia_track_page_view() {
    if (!is_singular('deals')) {
        return;
    }
    
    $post_id = get_the_ID();
    $views = get_post_meta($post_id, 'deal_views', true);
    $views = $views ? intval($views) : 0;
    
    update_post_meta($post_id, 'deal_views', $views + 1);
}
add_action('wp_head', 'dealsindia_track_page_view');

/**
 * Format analytics numbers (1000 = 1K, 1000000 = 1M)
 * NOTE: Different from dealsindia_format_view_count() which is in template-helpers.php
 * This is a general formatter for any number, not just view counts
 */
function dealsindia_format_number($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    
    return $number;
}

// =====================================================
// END OF UTILITIES
// =====================================================
