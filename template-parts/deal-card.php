<?php
/**
 * Deal Card Component - PREMIUM FLIP DESIGN
 * Modern card with premium back design
 * 
 * @package DealsIndia
 */

// Get deal data
$deal_title = get_the_title();
$deal_url = get_post_meta(get_the_ID(), 'deal_url', true);
$deal_price = get_post_meta(get_the_ID(), 'deal_price', true);
$old_price = get_post_meta(get_the_ID(), 'old_price', true);
$discount = get_post_meta(get_the_ID(), 'discount_percentage', true);
$cashback = get_post_meta(get_the_ID(), 'cashback_amount', true);
$coupon_code = get_post_meta(get_the_ID(), 'coupon_code', true);
$store_terms = wp_get_post_terms(get_the_ID(), 'store');
$store_name = !empty($store_terms) ? $store_terms[0]->name : '';
$featured_image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
$is_featured = get_post_meta(get_the_ID(), 'is_featured', true);
$is_trending = get_post_meta(get_the_ID(), 'is_trending', true);
$end_date = get_post_meta(get_the_ID(), 'deal_end_date', true);

// Calculate savings
$savings = '';
if ($old_price && $deal_price) {
    $save_amount = $old_price - $deal_price;
    $savings = '₹' . number_format($save_amount, 0);
}
?>

<article class="cd-deal-card">
    <div class="cd-deal-inner">
        
        <!-- FRONT SIDE -->
        <div class="cd-deal-front">
            
            <!-- Product Image -->
            <a href="<?php the_permalink(); ?>" class="cd-product-image">
                <?php if ($featured_image) : ?>
                    <img src="<?php echo esc_url($featured_image); ?>" 
                         alt="<?php echo esc_attr($deal_title); ?>" 
                         loading="lazy">
                <?php else : ?>
                    <div class="cd-no-image">📦</div>
                <?php endif; ?>
                
                <!-- Badges on Image -->
                <?php if ($discount) : ?>
                    <span class="cd-discount-badge"><?php echo esc_html($discount); ?>% OFF</span>
                <?php endif; ?>
                
                <?php if ($is_trending) : ?>
                    <span class="cd-trending-badge">🔥 Trending</span>
                <?php endif; ?>
                
                <?php if ($store_name) : ?>
                    <div class="cd-store-text"><?php echo esc_html($store_name); ?></div>
                <?php endif; ?>
            </a>
            
            <!-- Content Area -->
            <div class="cd-deal-content">
                <h3 class="cd-deal-title">
                    <a href="<?php the_permalink(); ?>"><?php echo esc_html(wp_trim_words($deal_title, 8)); ?></a>
                </h3>
                
                <!-- Price Row -->
                <div class="cd-price-row">
                    <?php if ($deal_price) : ?>
                        <span class="cd-price-main">₹<?php echo esc_html(number_format($deal_price, 0)); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($old_price) : ?>
                        <span class="cd-price-old">₹<?php echo esc_html(number_format($old_price, 0)); ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if ($cashback) : ?>
                    <div class="cd-cashback-badge">💰 <?php echo esc_html($cashback); ?> Cashback</div>
                <?php endif; ?>
                
                <!-- Get Deal Button -->
                <a href="<?php echo esc_url($deal_url); ?>" 
                   class="cd-get-deal" 
                   target="_blank" 
                   rel="nofollow noopener">
                    Get Deal →
                </a>
            </div>
        </div>
        
        <!-- BACK SIDE - PREMIUM DESIGN -->
        <div class="cd-deal-back">
            
            <!-- Premium Back Header -->
            <div class="cd-back-header">
                <div class="cd-back-store-logo">
                    <?php if ($store_name) : ?>
                        <span class="cd-store-badge"><?php echo esc_html($store_name); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($is_featured) : ?>
                    <span class="cd-featured-star">⭐</span>
                <?php endif; ?>
            </div>
            
            <!-- Deal Title -->
            <h4 class="cd-back-title">
                <?php echo esc_html(wp_trim_words($deal_title, 6)); ?>
            </h4>
            
            <!-- Key Info Grid -->
            <div class="cd-info-grid">
                
                <!-- Price Info -->
                <?php if ($deal_price) : ?>
                <div class="cd-info-item">
                    <div class="cd-info-icon">💳</div>
                    <div class="cd-info-text">
                        <span class="cd-info-label">Deal Price</span>
                        <span class="cd-info-value">₹<?php echo esc_html(number_format($deal_price, 0)); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Savings Info -->
                <?php if ($savings) : ?>
                <div class="cd-info-item">
                    <div class="cd-info-icon">💰</div>
                    <div class="cd-info-text">
                        <span class="cd-info-label">You Save</span>
                        <span class="cd-info-value green"><?php echo esc_html($savings); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Discount Info -->
                <?php if ($discount) : ?>
                <div class="cd-info-item">
                    <div class="cd-info-icon">🏷️</div>
                    <div class="cd-info-text">
                        <span class="cd-info-label">Discount</span>
                        <span class="cd-info-value"><?php echo esc_html($discount); ?>% OFF</span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Cashback Info -->
                <?php if ($cashback) : ?>
                <div class="cd-info-item">
                    <div class="cd-info-icon">🎁</div>
                    <div class="cd-info-text">
                        <span class="cd-info-label">Cashback</span>
                        <span class="cd-info-value"><?php echo esc_html($cashback); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Coupon Code (if exists) -->
            <?php if ($coupon_code) : ?>
            <div class="cd-coupon-box">
                <div class="cd-coupon-label">Coupon Code</div>
                <div class="cd-coupon-code"><?php echo esc_html($coupon_code); ?></div>
            </div>
            <?php endif; ?>
            
            <!-- Expiry (if exists) -->
            <?php if ($end_date) : ?>
            <div class="cd-expiry-info">
                ⏰ Ends: <?php echo esc_html(date('M j', strtotime($end_date))); ?>
            </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="cd-back-actions">
                <a href="<?php the_permalink(); ?>" class="cd-details-btn">
                    View Details
                </a>
                <a href="<?php echo esc_url($deal_url); ?>" 
                   class="cd-shop-btn" 
                   target="_blank" 
                   rel="nofollow noopener">
                    Shop Now →
                </a>
            </div>
        </div>
        
    </div>
</article>
