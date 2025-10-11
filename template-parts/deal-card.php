<?php
/**
 * Deal Card - Simple & Working Version
 * No extra functions needed
 */

$deal_id = get_the_ID();
$original_price = get_post_meta($deal_id, 'deal_original_price', true);
$sale_price = get_post_meta($deal_id, 'deal_price', true);
$coupon_code = get_post_meta($deal_id, 'coupon_code', true);
$affiliate_link = get_post_meta($deal_id, 'deal_url', true);
$is_featured = get_post_meta($deal_id, 'is_featured', true);

// Calculate discount
$discount = 0;
if ($original_price && $sale_price && $original_price > 0) {
    $discount = round((($original_price - $sale_price) / $original_price) * 100);
}

// Get store
$stores = get_the_terms($deal_id, 'store');
$store = ($stores && !is_wp_error($stores)) ? $stores[0] : null;
$store_cashback = $store ? get_term_meta($store->term_id, 'store_cashback', true) : '';

// Deal link
$deal_link = $affiliate_link ? $affiliate_link : get_the_permalink();
?>

<div class="cd-deal-card" data-deal-id="<?php echo $deal_id; ?>">
    <div class="cd-deal-inner">
        
        <!-- FRONT FACE -->
        <div class="cd-deal-front">
            
            <!-- Product Image -->
            <div class="cd-product-image">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('medium'); ?>
                <?php else : ?>
                    <div style="width:100%;height:100%;background:#f5f5f5;display:flex;align-items:center;justify-content:center;color:#999;">
                        No Image
                    </div>
                <?php endif; ?>
                
                <!-- Store Badge -->
                <?php if ($store) : ?>
                    <div class="cd-store-text"><?php echo esc_html($store->name); ?></div>
                <?php endif; ?>
                
                <!-- Discount Badge -->
                <?php if ($discount > 0) : ?>
                    <div class="cd-discount-badge"><?php echo $discount; ?>% OFF</div>
                <?php endif; ?>
                
                <!-- Featured Badge -->
                <?php if ($is_featured) : ?>
                    <div class="cd-trending-badge">⭐ FEATURED</div>
                <?php endif; ?>
            </div>
            
            <!-- Content -->
            <div class="cd-deal-content">
                
                <!-- Title -->
                <h3 class="cd-deal-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h3>
                
                <!-- Price -->
                <?php if ($sale_price) : ?>
                    <div class="cd-price-row">
                        <span class="cd-price-main">₹<?php echo number_format($sale_price); ?></span>
                        <?php if ($original_price && $original_price > $sale_price) : ?>
                            <span class="cd-price-old">₹<?php echo number_format($original_price); ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="cd-price-note">*Best Price</p>
                <?php endif; ?>
                
                <!-- Cashback -->
                <?php if ($store_cashback) : ?>
                    <div class="cd-cashback-badge">
                        <span class="cd-cashback-icon">💰</span>
                        Upto <?php echo esc_html($store_cashback); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Button -->
                <a href="<?php echo esc_url($deal_link); ?>" 
                   class="cd-get-deal" 
                   <?php echo $affiliate_link ? 'target="_blank" rel="nofollow noopener"' : ''; ?>>
                    <?php echo $coupon_code ? 'Get Code' : 'Get Deal'; ?>
                </a>
            </div>
        </div>
        
        <!-- BACK FACE -->
        <div class="cd-deal-back">
            <h4 class="cd-back-title">Deal Details</h4>
            
            <div class="cd-back-details">
                
                <!-- Coupon -->
                <?php if ($coupon_code) : ?>
                    <div class="cd-detail-item">
                        <strong>Coupon:</strong> <code><?php echo esc_html($coupon_code); ?></code>
                    </div>
                <?php endif; ?>
                
                <!-- Store -->
                <?php if ($store) : ?>
                    <div class="cd-detail-item">
                        <span class="cd-detail-icon">🏪</span>
                        <span>Store: <strong><?php echo esc_html($store->name); ?></strong></span>
                    </div>
                <?php endif; ?>
                
                <!-- Discount -->
                <?php if ($discount > 0) : ?>
                    <div class="cd-detail-item">
                        <span class="cd-detail-icon">💸</span>
                        <span>Save <?php echo esc_html($discount); ?>%</span>
                    </div>
                <?php endif; ?>
                
                <!-- Verified -->
                <div class="cd-detail-item">
                    <span class="cd-detail-icon">✓</span>
                    <span>Verified Deal</span>
                </div>
                
                <!-- Limited -->
                <div class="cd-detail-item">
                    <span class="cd-detail-icon">⚡</span>
                    <span>Limited Time Offer</span>
                </div>
            </div>
            
            <!-- Button -->
            <a href="<?php echo esc_url($deal_link); ?>" 
               class="cd-back-button" 
               <?php echo $affiliate_link ? 'target="_blank" rel="nofollow noopener"' : ''; ?>>
                Get This Deal →
            </a>
        </div>
        
    </div>
</div>
