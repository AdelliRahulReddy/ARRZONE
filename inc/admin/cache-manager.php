<?php
/**
 * Cache Management for Admin
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Add Clear Cache button to admin bar
 */
function dealsindia_admin_bar_cache_button($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_node(array(
        'id' => 'dealsindia-clear-cache',
        'title' => '🔄 Clear Deals Cache',
        'href' => wp_nonce_url(admin_url('admin-post.php?action=dealsindia_clear_cache'), 'clear_cache_nonce'),
        'meta' => array(
            'class' => 'dealsindia-clear-cache-btn'
        )
    ));
}
add_action('admin_bar_menu', 'dealsindia_admin_bar_cache_button', 999);

/**
 * Handle cache clearing
 */
function dealsindia_handle_clear_cache() {
    check_admin_referer('clear_cache_nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    // Clear all deal-related caches
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_dealsindia_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_dealsindia_%'");
    
    wp_redirect(add_query_arg('cache_cleared', '1', wp_get_referer()));
    exit;
}
add_action('admin_post_dealsindia_clear_cache', 'dealsindia_handle_clear_cache');

/**
 * Show cache cleared notice
 */
function dealsindia_cache_cleared_notice() {
    if (isset($_GET['cache_cleared']) && $_GET['cache_cleared'] === '1') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>✓ All DealsIndia caches have been cleared successfully!</strong></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'dealsindia_cache_cleared_notice');
