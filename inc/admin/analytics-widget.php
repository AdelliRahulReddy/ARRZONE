<?php
/**
 * Admin Dashboard Analytics Widget
 * 
 * @package DealsIndia
 */

if (!defined('ABSPATH')) exit;

/**
 * Add dashboard widget
 */
function dealsindia_add_analytics_widget() {
    wp_add_dashboard_widget(
        'dealsindia_analytics_widget',
        '📊 Deals Analytics - Last 30 Days',
        'dealsindia_analytics_widget_content'
    );
}
add_action('wp_dashboard_setup', 'dealsindia_add_analytics_widget');

/**
 * Widget content
 */
function dealsindia_analytics_widget_content() {
    global $wpdb;
    
    $clicks_table = $wpdb->prefix . 'dealsindia_clicks';
    $views_table = $wpdb->prefix . 'dealsindia_views';
    
    // Get stats for last 30 days
    $date_30_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));
    
    // Total clicks
    $total_clicks = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $clicks_table WHERE clicked_at > %s",
        $date_30_days_ago
    ));
    
    // Total views
    $total_views = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $views_table WHERE viewed_at > %s",
        $date_30_days_ago
    ));
    
    // CTR
    $ctr = $total_views > 0 ? round(($total_clicks / $total_views) * 100, 2) : 0;
    
    // Total active deals
    $active_deals = wp_count_posts('deals')->publish;
    
    // Expiring soon (next 7 days)
    $expiring_soon = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT pm.post_id) 
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
         WHERE pm.meta_key = 'deal_expiry_date'
         AND pm.meta_value BETWEEN %s AND %s
         AND p.post_status = 'publish'
         AND p.post_type = 'deals'",
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s', strtotime('+7 days'))
    ));
    
    // Top 5 performing deals
    $top_deals = $wpdb->get_results($wpdb->prepare(
        "SELECT deal_id, COUNT(*) as clicks
         FROM $clicks_table
         WHERE clicked_at > %s
         GROUP BY deal_id
         ORDER BY clicks DESC
         LIMIT 5",
        $date_30_days_ago
    ), ARRAY_A);
    
    ?>
    <div class="dealsindia-analytics-widget">
        
        <div class="analytics-stats">
            <div class="stat-box">
                <div class="stat-value"><?php echo number_format($total_clicks); ?></div>
                <div class="stat-label">Total Clicks</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo number_format($total_views); ?></div>
                <div class="stat-label">Total Views</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $ctr; ?>%</div>
                <div class="stat-label">Click-Through Rate</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $active_deals; ?></div>
                <div class="stat-label">Active Deals</div>
            </div>
        </div>
        
        <?php if ($expiring_soon > 0) : ?>
        <div class="analytics-alert">
            <strong>⚠️ <?php echo $expiring_soon; ?> deal(s) expiring in next 7 days!</strong>
        </div>
        <?php endif; ?>
        
        <div class="analytics-top-deals">
            <h4>🔥 Top Performing Deals</h4>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Deal</th>
                        <th>Clicks</th>
                        <th>Views</th>
                        <th>CTR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($top_deals)) : ?>
                        <?php foreach ($top_deals as $deal) : ?>
                            <?php 
                            $deal_post = get_post($deal['deal_id']);
                            if ($deal_post) :
                                $views = dealsindia_get_deal_views($deal['deal_id']);
                                $ctr_deal = dealsindia_get_deal_ctr($deal['deal_id']);
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo get_edit_post_link($deal['deal_id']); ?>" target="_blank">
                                        <?php echo esc_html($deal_post->post_title); ?>
                                    </a>
                                </td>
                                <td><?php echo number_format($deal['clicks']); ?></td>
                                <td><?php echo number_format($views); ?></td>
                                <td><?php echo $ctr_deal; ?>%</td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">No click data available yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <style>
        .analytics-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
        .stat-box { background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #e9ecef; }
        .stat-value { font-size: 24px; font-weight: 700; color: #1e40af; }
        .stat-label { font-size: 12px; color: #6b7280; margin-top: 5px; }
        .analytics-alert { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin-bottom: 20px; }
        .analytics-top-deals h4 { margin-bottom: 10px; }
        .analytics-top-deals table { margin-top: 10px; }
        </style>
    </div>
    <?php
}
