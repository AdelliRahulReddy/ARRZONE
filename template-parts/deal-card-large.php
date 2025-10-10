<?php
/**
 * Large Deal Card for Offers Grid (3-column layout)
 * With Image & Placeholder Support
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

<div class="deal-card-large">
    
    <!-- Discount Badge (if exists) -->
    <?php if ($discount > 0) : ?>
        <div class="deal-discount-badge-large"><?php echo $discount; ?>% OFF</div>
    <?php endif; ?>
    
    <!-- Deal Image with Placeholder Support -->
    <div class="deal-image-large">
        <?php dealsindia_render_deal_image(get_the_ID(), 'large'); ?>
    </div>
    
    <!-- Store Logo -->
    <?php if ($store) : ?>
        <div class="deal-store-logo-large">
            <span><?php echo esc_html($store->name); ?></span>
        </div>
    <?php endif; ?>
    
    <!-- Deal Title -->
    <h3 class="deal-title-large">
        <a href="<?php echo esc_url($deal_link); ?>" <?php echo $affiliate_link ? 'target="_blank" rel="nofollow"' : ''; ?>>
            <?php the_title(); ?>
        </a>
    </h3>
    
    <!-- Price Row (if available) -->
    <?php if ($sale_price) : ?>
        <div class="deal-price-row-large">
            <span class="price-current">₹<?php echo number_format($sale_price); ?></span>
            <?php if ($original_price && $original_price > $sale_price) : ?>
                <span class="price-original">₹<?php echo number_format($original_price); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Cashback Badge -->
    <?php if ($store_cashback) : ?>
        <div class="deal-cashback-large">
            <span class="cashback-icon">🔖</span>
            <span>Upto <?php echo esc_html($store_cashback); ?></span>
        </div>
    <?php endif; ?>
    
    <!-- Get Deal Button -->
    <a href="<?php echo esc_url($deal_link); ?>" class="deal-btn-large" <?php echo $affiliate_link ? 'target="_blank" rel="nofollow"' : ''; ?>>
        <?php echo $coupon_code ? 'Get Code' : 'Get Deal'; ?>
    </a>
    
</div>
