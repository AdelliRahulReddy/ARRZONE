<?php
/**
 * Newsletter Subscription Handler
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

// ===================================================== 
// CREATE SUBSCRIBERS TABLE ON THEME ACTIVATION
// ===================================================== 
function dealsindia_create_subscribers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dealsindia_subscribers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        email varchar(255) NOT NULL,
        subscribed_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        status varchar(20) DEFAULT 'active' NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'dealsindia_create_subscribers_table');

// ===================================================== 
// AJAX HANDLER FOR NEWSLETTER SUBSCRIPTION
// ===================================================== 
function dealsindia_newsletter_subscribe_ajax() {
    // Verify nonce
    if (!isset($_POST['newsletter_nonce']) || !wp_verify_nonce($_POST['newsletter_nonce'], 'dealsindia_newsletter_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
        wp_die();
    }

    // Validate email
    if (!isset($_POST['subscriber_email']) || !is_email($_POST['subscriber_email'])) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        wp_die();
    }

    $email = sanitize_email($_POST['subscriber_email']);

    // Insert into database
    global $wpdb;
    $table_name = $wpdb->prefix . 'dealsindia_subscribers';

    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE email = %s",
        $email
    ));

    if ($existing) {
        wp_send_json_error(array('message' => 'This email is already subscribed!'));
        wp_die();
    }

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'email' => $email,
            'status' => 'active'
        ),
        array('%s', '%s')
    );

    if ($inserted) {
        wp_send_json_success(array('message' => '🎉 Successfully subscribed! Check your inbox for exclusive deals.'));
    } else {
        wp_send_json_error(array('message' => 'Subscription failed. Please try again.'));
    }

    wp_die();
}
add_action('wp_ajax_dealsindia_newsletter_subscribe', 'dealsindia_newsletter_subscribe_ajax');
add_action('wp_ajax_nopriv_dealsindia_newsletter_subscribe', 'dealsindia_newsletter_subscribe_ajax');
