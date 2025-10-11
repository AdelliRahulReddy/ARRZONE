<?php
/**
 * Newsletter Subscribers Admin Page
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Add subscribers menu page
 */
function dealsindia_add_subscribers_menu() {
    add_submenu_page(
        'edit.php?post_type=deals',
        'Newsletter Subscribers',
        '📧 Subscribers',
        'manage_options',
        'dealsindia-subscribers',
        'dealsindia_subscribers_page'
    );
}
add_action('admin_menu', 'dealsindia_add_subscribers_menu');

/**
 * Subscribers page content
 */
function dealsindia_subscribers_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'dealsindia_subscribers';
    
    // Handle export
    if (isset($_GET['action']) && $_GET['action'] === 'export') {
        dealsindia_export_subscribers();
        exit;
    }
    
    // Get all subscribers
    $subscribers = $wpdb->get_results("SELECT * FROM $table ORDER BY subscribed_date DESC");
    $total = count($subscribers);
    ?>
    <div class="wrap">
        <h1>📧 Newsletter Subscribers</h1>
        
        <div class="subscriber-stats" style="margin: 20px 0;">
            <div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:5px;display:inline-block;">
                <h2 style="margin:0 0 10px 0;">Total Subscribers</h2>
                <p style="font-size:48px;font-weight:700;color:#1e40af;margin:0;"><?php echo number_format($total); ?></p>
            </div>
            
            <a href="<?php echo admin_url('edit.php?post_type=deals&page=dealsindia-subscribers&action=export'); ?>" 
               class="button button-primary" 
               style="margin-left:20px;height:50px;line-height:48px;">
                Download CSV
            </a>
        </div>
        
        <?php if ($subscribers) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email Address</th>
                    <th>Subscribed Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $sub) : ?>
                <tr>
                    <td><?php echo $sub->id; ?></td>
                    <td><strong><?php echo esc_html($sub->email); ?></strong></td>
                    <td><?php echo date('M d, Y h:i A', strtotime($sub->subscribed_date)); ?></td>
                    <td>
                        <span style="background:#10b981;color:#fff;padding:3px 10px;border-radius:3px;font-size:11px;">
                            <?php echo strtoupper($sub->status); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else : ?>
        <p>No subscribers yet.</p>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Export subscribers as CSV
 */
function dealsindia_export_subscribers() {
    global $wpdb;
    $table = $wpdb->prefix . 'dealsindia_subscribers';
    
    $subscribers = $wpdb->get_results("SELECT * FROM $table ORDER BY subscribed_date DESC", ARRAY_A);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="dealsindia-subscribers-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Headers
    fputcsv($output, array('ID', 'Email', 'Subscribed Date', 'Status'));
    
    // Data
    foreach ($subscribers as $sub) {
        fputcsv($output, array(
            $sub['id'],
            $sub['email'],
            $sub['subscribed_date'],
            $sub['status']
        ));
    }
    
    fclose($output);
}
