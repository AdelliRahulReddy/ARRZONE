<?php
/**
 * Hot Picks Diagnostic Tool
 * Access: http://localhost:10010/wp-content/themes/dealsindia/hot-picks-debug.php
 */

require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied. Admin only.');
}

// Clear cache
delete_transient('dealsindia_hot_picks_12');
delete_transient('dealsindia_hot_picks_10');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hot Picks Debug Report</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #1e1e1e; color: #d4d4d4; padding: 40px; }
        .container { max-width: 1400px; margin: 0 auto; background: #252526; padding: 30px; border-radius: 8px; }
        h1 { color: #4ec9b0; font-size: 28px; margin-bottom: 30px; }
        .section { background: #2d2d30; padding: 20px; margin-bottom: 20px; border-radius: 6px; border-left: 4px solid #007acc; }
        .section-title { color: #4ec9b0; font-size: 18px; font-weight: bold; margin-bottom: 15px; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; font-weight: bold; }
        pre { background: #1e1e1e; padding: 15px; border-radius: 4px; overflow-x: auto; color: #ce9178; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #3e3e42; }
        table th { color: #4ec9b0; font-weight: bold; background: #1e1e1e; }
        .deal-card { background: #3e3e42; padding: 12px; margin: 8px 0; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 HOT PICKS DIAGNOSTIC REPORT</h1>
    
    <!-- SECTION 1: Customizer Settings -->
    <div class="section">
        <div class="section-title">1️⃣ WordPress Customizer Settings</div>
        <?php
        $hot_picks_count = get_theme_mod('hot_picks_count', 12);
        echo "<p><strong>Hot Picks Count Setting:</strong> <span class='success'>{$hot_picks_count}</span></p>";
        ?>
    </div>
    
    <!-- SECTION 2: Total Deals in Database -->
    <div class="section">
        <div class="section-title">2️⃣ Total Deals in Database</div>
        <?php
        $total_deals = new WP_Query(array(
            'post_type' => 'deals',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
        
        $total_count = $total_deals->post_count;
        $color = $total_count >= 12 ? 'success' : 'error';
        echo "<p><strong>Total Published Deals:</strong> <span class='{$color}'>{$total_count}</span></p>";
        
        if ($total_count < 12) {
            echo "<p class='warning'>⚠️ You only have {$total_count} deals. Add more deals to see 12!</p>";
        }
        ?>
    </div>
    
    <!-- SECTION 3: Hot/Featured Deals -->
    <div class="section">
        <div class="section-title">3️⃣ Deals Marked as Hot/Featured</div>
        <?php
        $hot_marked = new WP_Query(array(
            'post_type' => 'deals',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'OR',
                array('key' => 'is_hot', 'value' => '1', 'compare' => '='),
                array('key' => 'is_featured', 'value' => '1', 'compare' => '=')
            )
        ));
        
        echo "<p><strong>Deals with is_hot or is_featured = 1:</strong> <span class='success'>{$hot_marked->post_count}</span></p>";
        
        if ($hot_marked->have_posts()) {
            echo "<table>";
            echo "<tr><th>Deal Title</th><th>is_hot</th><th>is_featured</th></tr>";
            while ($hot_marked->have_posts()) {
                $hot_marked->the_post();
                $is_hot = get_post_meta(get_the_ID(), 'is_hot', true);
                $is_featured = get_post_meta(get_the_ID(), 'is_featured', true);
                echo "<tr>";
                echo "<td>" . get_the_title() . "</td>";
                echo "<td>" . ($is_hot == '1' ? '✅' : '❌') . "</td>";
                echo "<td>" . ($is_featured == '1' ? '✅' : '❌') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            wp_reset_postdata();
        } else {
            echo "<p class='warning'>⚠️ NO deals marked as hot or featured!</p>";
        }
        ?>
    </div>
    
    <!-- SECTION 4: Query Result -->
    <div class="section">
        <div class="section-title">4️⃣ Hot Picks Function Result</div>
        <?php
        $hot_picks = dealsindia_get_hot_picks(12);
        
        echo "<p><strong>Query Returned:</strong> <span class='success'>{$hot_picks->post_count} deals</span></p>";
        echo "<p><strong>Total Posts in Array:</strong> <span class='success'>" . count($hot_picks->posts) . "</span></p>";
        
        if ($hot_picks->have_posts()) {
            echo "<div style='margin-top: 20px;'>";
            $index = 1;
            while ($hot_picks->have_posts()) {
                $hot_picks->the_post();
                $is_hot = get_post_meta(get_the_ID(), 'is_hot', true);
                $is_featured = get_post_meta(get_the_ID(), 'is_featured', true);
                
                echo "<div class='deal-card'>";
                echo "<strong>#{$index}</strong> - " . get_the_title();
                echo " | Hot: " . ($is_hot == '1' ? '✅' : '❌');
                echo " | Featured: " . ($is_featured == '1' ? '✅' : '❌');
                echo "</div>";
                $index++;
            }
            echo "</div>";
            wp_reset_postdata();
        } else {
            echo "<p class='error'>❌ NO DEALS RETURNED BY FUNCTION!</p>";
        }
        ?>
    </div>
    
    <!-- SECTION 5: Cache Status -->
    <div class="section">
        <div class="section-title">5️⃣ Cache Status (Cleared on Page Load)</div>
        <p class='success'>✅ Cache was cleared when you loaded this page</p>
    </div>
    
    <!-- SECTION 6: Recommendations -->
    <div class="section">
        <div class="section-title">6️⃣ Recommendations</div>
        <?php
        if ($total_count < 12) {
            echo "<p class='warning'>⚠️ Add more deals to reach 12 total</p>";
        }
        
        if ($hot_marked->post_count == 0) {
            echo "<p class='warning'>⚠️ Mark some deals as 'Hot' or 'Featured' in the deal editor</p>";
        }
        
        if ($hot_picks->post_count >= 12) {
            echo "<p class='success'>✅ Everything looks good! 12 deals are being returned</p>";
        }
        ?>
    </div>
    
</div>
</body>
</html>
