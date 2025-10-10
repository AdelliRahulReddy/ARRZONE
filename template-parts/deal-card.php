<?php
/**
 * Deal Card Component - CouponDunia Style with 3D Flip + Placeholder Support
 * Reusable template for displaying deals
 */

$original_price = get_post_meta(get_the_ID(), 'original_price', true);
$sale_price = get_post_meta(get_the_ID(), 'sale_price', true);
$coupon_code = get_post_meta(get_the_ID(), 'coupon_code', true);
$affiliate_link = get_post_meta(get_the_ID(), 'affiliate_link', true);
$expiry_date = get_post_meta(get_the_ID(), 'expiry_date', true);
$is_featured = get_post_meta(get_the_ID(), 'is_featured', true);
$is_trending = get_post_meta(get_the_ID(), 'is_trending', true);

$discount = 0;
if ($original_price && $sale_price && $original_price > 0) {
    $discount = round((($original_price - $sale_price) / $original_price) * 100);
}

$stores = get_the_terms(get_the_ID(), 'store');
$store = ($stores && !is_wp_error($stores)) ? $stores[0] : null;
$store_cashback = $store ? get_term_meta($store->term_id, 'store_cashback', true) : '';

$deal_link = $affiliate_link ? $affiliate_link : get_the_permalink();

$is_expired = false;
if ($expiry_date) {
    $is_expired = (strtotime($expiry_date) < current_time('timestamp'));
}
?>

<div class="cd-deal-card">
    <div class="cd-deal-inner">
        
        <!-- FRONT FACE -->
        <div class="cd-deal-front">
            
            <!-- Discount Badge (Top Right) -->
            <?php if ($discount > 0) : ?>
                <div class="cd-discount-badge"><?php echo $discount; ?>%</div>
            <?php endif; ?>
            
            <!-- Store Logo -->
            <?php if ($store) : ?>
                <div class="cd-store-logo">
                    <span class="cd-store-text"><?php echo esc_html($store->name); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Product Image with Placeholder Support -->
            <div class="cd-product-image">
                <?php dealsindia_render_deal_image(get_the_ID(), 'medium'); ?>
            </div>
            
            <!-- Deal Title -->
            <h3 class="cd-deal-title">
                <a href="<?php echo esc_url($deal_link); ?>" <?php echo $affiliate_link ? 'target="_blank" rel="nofollow"' : ''; ?>>
                    <?php the_title(); ?>
                </a>
            </h3>
            
            <!-- Cashback Badge -->
            <?php if ($store_cashback) : ?>
                <div class="cd-cashback-badge">
                    <span class="cd-cashback-icon">🔖</span>
                    <span>Upto <?php echo esc_html($store_cashback); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Price Row -->
            <?php if ($sale_price) : ?>
                <div class="cd-price-row">
                    <span class="cd-price-main">₹<?php echo number_format($sale_price); ?>*</span>
                    <?php if ($original_price && $original_price > $sale_price) : ?>
                        <span class="cd-price-old">₹<?php echo number_format($original_price); ?></span>
                    <?php endif; ?>
                </div>
                <p class="cd-price-note">*After Discount<?php echo $store_cashback ? ' & Cashback' : ''; ?></p>
            <?php endif; ?>
            
            <!-- Get Deal Link -->
            <a href="<?php echo esc_url($deal_link); ?>" class="cd-get-deal" <?php echo $affiliate_link ? 'target="_blank" rel="nofollow"' : ''; ?>>
                Get Deal
            </a>
            
        </div>
        
        <!-- BACK FACE (Flip on Hover) -->
        <div class="cd-deal-back">
            <div class="cd-back-content">
                <h4>Offer Info</h4>
                
                <?php if ($coupon_code) : ?>
                    <div class="cd-back-coupon">
                        <strong>Coupon Code:</strong>
                        <code><?php echo esc_html($coupon_code); ?></code>
                    </div>
                <?php endif; ?>
                
                <div class="cd-back-desc">
                    <?php 
                    $excerpt = get_the_excerpt() ? get_the_excerpt() : get_the_content();
                    echo wp_trim_words($excerpt, 15);
                    ?>
                </div>
                
                <?php if ($expiry_date && !$is_expired) : ?>
                    <div class="cd-back-expiry">
                        ⏰ Expires: <?php echo date('M d, Y', strtotime($expiry_date)); ?>
                    </div>
                <?php endif; ?>
                
                <a href="<?php echo esc_url($deal_link); ?>" class="cd-back-button" <?php echo $affiliate_link ? 'target="_blank" rel="nofollow"' : ''; ?>>
                    View Deal →
                </a>
            </div>
        </div>
        
    </div>
</div>
