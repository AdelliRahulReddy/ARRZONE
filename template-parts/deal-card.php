<?php
/**
 * Deal Card Component - Ultra Premium Design
 * Works everywhere: Homepage, Archives, Category, Store pages
 * 
 * @package DealsIndia
 * @version 3.5 - Data-Rich Cards
 */

// Get ALL deal data
$deal_id = get_the_ID();
$deal_title = get_the_title();
$deal_url = get_post_meta($deal_id, 'deal_url', true);
$deal_price = get_post_meta($deal_id, 'deal_price', true);
$old_price = get_post_meta($deal_id, 'old_price', true);
$discount = get_post_meta($deal_id, 'discount_percentage', true);
$cashback = get_post_meta($deal_id, 'cashback_amount', true);
$coupon_code = get_post_meta($deal_id, 'coupon_code', true);
$deal_excerpt = get_the_excerpt();

// Store info
$store_terms = wp_get_post_terms($deal_id, 'store');
$store_name = !empty($store_terms) ? $store_terms[0]->name : 'Unknown Store';
$store_logo = !empty($store_terms) ? get_term_meta($store_terms[0]->term_id, 'store_logo', true) : '';

// Category info
$category_terms = wp_get_post_terms($deal_id, 'deal_category');
$category_name = !empty($category_terms) ? $category_terms[0]->name : '';

// Deal type
$type_terms = wp_get_post_terms($deal_id, 'deal_type');
$deal_type = !empty($type_terms) ? $type_terms[0]->name : '';

// Featured image
$featured_image = get_the_post_thumbnail_url($deal_id, 'medium');

// Badges
$is_featured = get_post_meta($deal_id, 'is_featured', true);
$is_trending = get_post_meta($deal_id, 'is_trending', true);
$is_hot = get_post_meta($deal_id, 'is_hot_deal', true);

// Expiry
$end_date = get_post_meta($deal_id, 'deal_end_date', true);
$is_expired = false;
if ($end_date && strtotime($end_date) < time()) {
    $is_expired = true;
}

// Calculate savings
$savings_amount = '';
$savings_percent = $discount;
if ($old_price && $deal_price) {
    $save_amount = $old_price - $deal_price;
    $savings_amount = '₹' . number_format($save_amount, 0);
    if (!$savings_percent) {
        $savings_percent = round(($save_amount / $old_price) * 100);
    }
}

// Quick steps for regular deals (no coupon)
$quick_steps = array(
    '1' => 'Click "Shop Now" button',
    '2' => 'Visit ' . $store_name,
    '3' => 'Add to cart & checkout'
);
?>

<article class="cd-deal-card <?php echo $is_expired ? 'expired' : ''; ?>">
    <div class="cd-deal-inner">
        
        <!-- ===== FRONT SIDE ===== -->
        <div class="cd-deal-front">
            
            <!-- Product Image -->
            <a href="<?php the_permalink(); ?>" class="cd-product-image">
                <?php if ($featured_image) : ?>
                    <img src="<?php echo esc_url($featured_image); ?>" 
                         alt="<?php echo esc_attr($deal_title); ?>" 
                         loading="lazy">
                <?php else : ?>
                    <div class="cd-no-image">
                        <?php 
                        // Show category emoji or generic icon
                        if ($category_name) {
                            echo '🏷️';
                        } else {
                            echo '📦';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <!-- Top Badges -->
                <?php if ($savings_percent && $savings_percent > 0) : ?>
                    <span class="cd-discount-badge"><?php echo esc_html($savings_percent); ?>% OFF</span>
                <?php endif; ?>
                
                <?php if ($is_trending) : ?>
                    <span class="cd-trending-badge">🔥 Trending</span>
                <?php elseif ($is_hot) : ?>
                    <span class="cd-trending-badge">🔥 Hot Deal</span>
                <?php elseif ($is_featured) : ?>
                    <span class="cd-trending-badge">⭐ Featured</span>
                <?php endif; ?>
                
                <!-- Store Badge on Image -->
                <?php if ($store_logo) : ?>
                    <div class="cd-store-logo-badge">
                        <img src="<?php echo esc_url($store_logo); ?>" alt="<?php echo esc_attr($store_name); ?>">
                    </div>
                <?php else : ?>
                    <div class="cd-store-text"><?php echo esc_html($store_name); ?></div>
                <?php endif; ?>
            </a>
            
            <!-- Content Area -->
            <div class="cd-deal-content">
                
                <!-- Deal Title -->
                <h3 class="cd-deal-title">
                    <a href="<?php the_permalink(); ?>">
                        <?php echo esc_html(wp_trim_words($deal_title, 10)); ?>
                    </a>
                </h3>
                
                <!-- Price Section -->
                <?php if ($deal_price) : ?>
                <div class="cd-price-row">
                    <span class="cd-price-main">₹<?php echo esc_html(number_format($deal_price, 0)); ?></span>
                    <?php if ($old_price && $old_price > $deal_price) : ?>
                        <span class="cd-price-old">₹<?php echo esc_html(number_format($old_price, 0)); ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Extra Info -->
                <div class="cd-extra-info">
                    <?php if ($category_name) : ?>
                        <span class="cd-category-tag"><?php echo esc_html($category_name); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($cashback) : ?>
                        <span class="cd-cashback-mini">💰 <?php echo esc_html($cashback); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Get Deal Button -->
                <a href="<?php echo esc_url($deal_url ? $deal_url : get_permalink()); ?>" 
                   class="cd-get-deal" 
                   <?php echo $deal_url ? 'target="_blank" rel="nofollow noopener"' : ''; ?>>
                    <?php echo $is_expired ? '❌ Expired' : 'Get Deal →'; ?>
                </a>
            </div>
        </div>
        
        <!-- ===== BACK SIDE ===== -->
        <div class="cd-deal-back">
            
            <!-- Back Header with Store -->
            <div class="cd-back-header">
                <?php if ($store_logo) : ?>
                    <div class="cd-back-store-logo">
                        <img src="<?php echo esc_url($store_logo); ?>" alt="<?php echo esc_attr($store_name); ?>">
                    </div>
                <?php else : ?>
                    <span class="cd-store-badge"><?php echo esc_html($store_name); ?></span>
                <?php endif; ?>
                
                <?php if ($is_featured) : ?>
                    <span class="cd-featured-star">⭐</span>
                <?php endif; ?>
            </div>
            
            <!-- Deal Title -->
            <h4 class="cd-back-title">
                <?php echo esc_html(wp_trim_words($deal_title, 8)); ?>
            </h4>
            
            <!-- Info Grid - 2x2 -->
            <div class="cd-info-grid">
                
                <?php if ($deal_price) : ?>
                <div class="cd-info-item">
                    <div class="cd-info-icon">💳</div>
                    <div class="cd-info-text">
                        <span class="cd-info-label">Deal Price</span>
                        <span class="cd-info-value">₹<?php echo esc_html(number_format($deal_price, 0)); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($savings_amount) : ?>
                <div class="cd-info-item">
                    <div class="cd-info-icon">💰</div>
                    <div class="cd-info-text">
                        <span class="cd-info-label">You Save</span>
                        <span class="cd-info-value green"><?php echo esc_html($savings_amount); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($savings_percent) : ?>
                <div class="cd-info-item">
                    <div class="cd-info-icon">🏷️</div>
                    <div class="cd-info-text">
                        <span class="cd-info-label">Discount</span>
                        <span class="cd-info-value"><?php echo esc_html($savings_percent); ?>% OFF</span>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($cashback) : ?>
                <div class="cd-info-item">
                    <div class="cd-info-icon">🎁</div>
                    <div class="cd-info-text">
                        <span class="cd-info-label">Cashback</span>
                        <span class="cd-info-value"><?php echo esc_html($cashback); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($deal_type) : ?>
                <div class="cd-info-item">
                    <div class="cd-info-icon">📌</div>
                    <div class="cd-info-text">
                        <span class="cd-info-label">Type</span>
                        <span class="cd-info-value"><?php echo esc_html($deal_type); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Coupon Code OR Quick Steps -->
            <?php if ($coupon_code) : ?>
                <div class="cd-coupon-box">
                    <div class="cd-coupon-label">💎 Coupon Code</div>
                    <div class="cd-coupon-code"><?php echo esc_html($coupon_code); ?></div>
                    <div class="cd-coupon-hint">Click to copy & use at checkout</div>
                </div>
            <?php else : ?>
                <div class="cd-quick-steps">
                    <div class="cd-steps-label">📋 How to Get This Deal</div>
                    <div class="cd-steps-list">
                        <?php foreach ($quick_steps as $num => $step) : ?>
                        <div class="cd-step-item">
                            <span class="cd-step-num"><?php echo $num; ?></span>
                            <span class="cd-step-text"><?php echo esc_html($step); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Expiry Info -->
            <?php if ($end_date && !$is_expired) : ?>
            <div class="cd-expiry-info">
                ⏰ Valid until: <?php echo date('M j, Y', strtotime($end_date)); ?>
            </div>
            <?php elseif ($is_expired) : ?>
            <div class="cd-expiry-info expired">
                ❌ Deal Expired
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="cd-back-actions">
                <a href="<?php the_permalink(); ?>" class="cd-details-btn">
                    📄 Details
                </a>
                <a href="<?php echo esc_url($deal_url ? $deal_url : get_permalink()); ?>" 
                   class="cd-shop-btn" 
                   <?php echo $deal_url ? 'target="_blank" rel="nofollow noopener"' : ''; ?>>
                    <?php echo $is_expired ? '❌ Expired' : '🛒 Shop Now'; ?>
                </a>
            </div>
        </div>
        
    </div>
</article>

</article>

<!-- Coupon Copy Script (Inline for Performance) -->
<script>
(function() {
    const couponBoxes = document.querySelectorAll('.cd-coupon-box');
    
    couponBoxes.forEach(function(box) {
        box.style.cursor = 'pointer';
        
        box.addEventListener('click', function(e) {
            e.preventDefault();
            
            const couponCode = this.querySelector('.cd-coupon-code');
            const couponText = couponCode.textContent.trim();
            
            // Copy to clipboard
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(couponText).then(function() {
                    // Show success feedback
                    const originalHTML = couponCode.innerHTML;
                    couponCode.innerHTML = '✓ Copied!';
                    couponCode.style.color = '#4caf50';
                    
                    setTimeout(function() {
                        couponCode.innerHTML = originalHTML;
                        couponCode.style.color = '';
                    }, 2000);
                }).catch(function(err) {
                    console.error('Copy failed:', err);
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = couponText;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    const originalHTML = couponCode.innerHTML;
                    couponCode.innerHTML = '✓ Copied!';
                    couponCode.style.color = '#4caf50';
                    
                    setTimeout(function() {
                        couponCode.innerHTML = originalHTML;
                        couponCode.style.color = '';
                    }, 2000);
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                }
                
                document.body.removeChild(textArea);
            }
        });
    });
})();
</script>
