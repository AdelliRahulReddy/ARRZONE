<?php
/**
 * Frontend Display Debug
 * Access: http://localhost:10010/wp-content/themes/dealsindia/frontend-debug.php
 */

require_once('../../../wp-load.php');
get_header();
?>

<style>
body { background: #1e1e1e !important; color: #d4d4d4 !important; }
.debug-container { max-width: 1400px; margin: 50px auto; background: #252526; padding: 40px; }
.debug-title { color: #4ec9b0; font-size: 24px; margin-bottom: 30px; }
.test-section { background: #2d2d30; padding: 30px; margin: 20px 0; border-radius: 8px; }
.test-title { color: #4ec9b0; font-size: 18px; margin-bottom: 20px; }
</style>

<div class="debug-container">
    <h1 class="debug-title">🔍 FRONTEND DISPLAY DEBUG</h1>
    
    <!-- TEST 1: Raw HTML Output -->
    <div class="test-section">
        <h2 class="test-title">1️⃣ Hot Picks HTML Output Test</h2>
        <div class="deals-scroll">
            <?php
            delete_transient('dealsindia_hot_picks_12');
            $hot_picks_count = 12;
            $hot_picks = dealsindia_get_hot_picks($hot_picks_count);
            
            echo "<p style='color:#4ec9b0;'>Query returned: " . $hot_picks->post_count . " deals</p>";
            
            if ($hot_picks->have_posts()) :
                $card_index = 1;
                while ($hot_picks->have_posts()) : $hot_picks->the_post();
                    echo "<div style='background:#3e3e42;padding:20px;margin:10px;border-radius:8px;'>";
                    echo "<strong style='color:#dcdcaa;'>Card #{$card_index}</strong><br>";
                    echo "<span style='color:#ce9178;'>" . get_the_title() . "</span>";
                    echo "</div>";
                    $card_index++;
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </div>
    
    <!-- TEST 2: Using Template Part -->
    <div class="test-section">
        <h2 class="test-title">2️⃣ Using deal-card Template Part</h2>
        <div class="deals-scroll" style="display:flex;gap:16px;overflow-x:auto;padding:20px 0;">
            <?php
            $hot_picks2 = dealsindia_get_hot_picks(12);
            
            if ($hot_picks2->have_posts()) :
                while ($hot_picks2->have_posts()) : $hot_picks2->the_post();
                    get_template_part('template-parts/deal-card');
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
        
        <p style="color:#4ec9b0;margin-top:20px;">
            Total cards rendered by template-parts/deal-card.php: 
            <strong><?php echo $hot_picks2->post_count; ?></strong>
        </p>
    </div>
    
    <!-- TEST 3: CSS Files Loaded -->
    <div class="test-section">
        <h2 class="test-title">3️⃣ CSS Files Loaded</h2>
        <?php
        global $wp_styles;
        echo "<ul style='color:#ce9178;'>";
        foreach ($wp_styles->queue as $handle) {
            $src = isset($wp_styles->registered[$handle]->src) ? $wp_styles->registered[$handle]->src : 'N/A';
            if (strpos($src, 'dealsindia') !== false || strpos($src, 'homepage') !== false) {
                echo "<li><strong>{$handle}</strong> → {$src}</li>";
            }
        }
        echo "</ul>";
        ?>
    </div>
    
    <!-- TEST 4: JavaScript Files Loaded -->
    <div class="test-section">
        <h2 class="test-title">4️⃣ JavaScript Files Loaded</h2>
        <?php
        global $wp_scripts;
        echo "<ul style='color:#ce9178;'>";
        foreach ($wp_scripts->queue as $handle) {
            $src = isset($wp_scripts->registered[$handle]->src) ? $wp_scripts->registered[$handle]->src : 'N/A';
            if (strpos($src, 'dealsindia') !== false || strpos($src, 'banner') !== false || strpos($src, 'main') !== false) {
                echo "<li><strong>{$handle}</strong> → {$src}</li>";
            }
        }
        echo "</ul>";
        ?>
    </div>
    
</div>

<script>
// Count visible cards after page load
window.addEventListener('load', function() {
    setTimeout(function() {
        const cards = document.querySelectorAll('.deals-scroll .cd-deal-card, .deals-scroll > div');
        const visible = Array.from(cards).filter(card => {
            const style = window.getComputedStyle(card);
            return style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
        });
        
        const results = document.createElement('div');
        results.style.cssText = 'position:fixed;top:20px;right:20px;background:#4ec9b0;color:#1e1e1e;padding:20px;border-radius:8px;z-index:99999;font-weight:bold;';
        results.innerHTML = 'Total Cards: ' + cards.length + '<br>Visible Cards: ' + visible.length;
        document.body.appendChild(results);
    }, 1000);
});
</script>

<?php 
wp_reset_postdata();
get_footer();
