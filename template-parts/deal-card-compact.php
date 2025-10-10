<?php
/**
 * Compact Deal Card for Carousel
 */

$original_price = get_post_meta(get_the_ID(), 'original_price', true);
$sale_price = get_post_meta(get_the_ID(), 'sale_price', true);
$coupon_code = get_post_meta(get_the_ID(), 'coupon_code', true);
$affiliate_link = get_post_meta(get_the_ID(), 'affiliate_link', true);

$discount = 0;
if ($original_price && $sale_price && $original_price > 0) {
    $discount = round((($original_price - $sale_price) / $original_price) * 100);
}

$stores = get_the_terms(get_the_ID(), 'store');
$store = ($stores && !is_wp_error($stores)) ? $stores[0] : null;
$store_cashback = $store ? get_term_meta($store->term_id, 'store_cashback', true) : '';

$deal_link = $affiliate_link ? $affiliate_link : get_the_permalink();
?>

<div class="deal-compact-card">
    <?php if ($discount > 0) : ?>
        <div class="compact-discount-badge"><?php echo $discount; ?>%</div>
    <?php endif; ?>
    
    <div class="compact-image">
        <?php dealsindia_render_deal_image(get_the_ID(), 'medium'); ?>
    </div>
    
    <div class="compact-content">
        <?php if ($store) : ?>
            <div class="compact-store"><?php echo esc_html($store->name); ?></div>
        <?php endif; ?>
        
        <h3 class="compact-title">
            <a href="<?php echo esc_url($deal_link); ?>" <?php echo $affiliate_link ? 'target="_blank" rel="nofollow"' : ''; ?>>
                <?php the_title(); ?>
            </a>
        </h3>
        
        <?php if ($sale_price) : ?>
            <div class="compact-price-row">
                <span class="compact-price">₹<?php echo number_format($sale_price); ?></span>
                <?php if ($original_price && $original_price > $sale_price) : ?>
                    <span class="compact-old-price">₹<?php echo number_format($original_price); ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($store_cashback) : ?>
            <div class="compact-cashback">🔖 Upto <?php echo esc_html($store_cashback); ?></div>
        <?php endif; ?>
        
        <a href="<?php echo esc_url($deal_link); ?>" class="compact-btn" <?php echo $affiliate_link ? 'target="_blank" rel="nofollow"' : ''; ?>>
            Get Deal
        </a>
    </div>
</div>
