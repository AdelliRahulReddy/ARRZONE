<?php
/**
 * Deal Card Component - Production Ready
 * 
 * Reusable deal card used in:
 * - Archive pages (stores, categories, deal types)
 * - Homepage sections
 * - Single deal page (related deals)
 * - Search results
 * 
 * Features:
 * - Clean markup (no inline CSS)
 * - Image with discount badge
 * - Store logo overlay
 * - Price comparison
 * - Expiry status
 * - Coupon code indicator
 * - Click tracking ready
 * - Responsive design
 * 
 * @package ARRZONE
 * @version 5.0 - Production Ready
 */

// Get deal ID and data
$deal_id = get_the_ID();
$deal_title = get_the_title();
$deal_url = get_permalink();

// Get deal meta
$coupon_code = get_post_meta($deal_id, 'deal_coupon_code', true);
$affiliate_link = get_post_meta($deal_id, 'deal_affiliate_link', true);
$original_price = get_post_meta($deal_id, 'deal_original_price', true);
$sale_price = get_post_meta($deal_id, 'deal_sale_price', true);
$expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);

// Calculate discount
$discount = 0;
if ($original_price && $sale_price && $original_price > $sale_price) {
    $discount = round((($original_price - $sale_price) / $original_price) * 100);
}

// Check if expired
$is_expired = false;
$time_remaining = '';
if ($expiry_date) {
    $expiry_timestamp = strtotime($expiry_date);
    $current_timestamp = current_time('timestamp');
    $is_expired = ($current_timestamp > $expiry_timestamp);
    
    if (!$is_expired) {
        $difference = $expiry_timestamp - $current_timestamp;
        $days = floor($difference / 86400);
        $hours = floor(($difference % 86400) / 3600);
        
        if ($days > 0) {
            $time_remaining = sprintf(__('%d days left', 'dealsindia'), $days);
        } elseif ($hours > 0) {
            $time_remaining = sprintf(__('%d hours left', 'dealsindia'), $hours);
        } else {
            $time_remaining = __('Ending soon', 'dealsindia');
        }
    }
}

// Get taxonomies
$stores = get_the_terms($deal_id, 'store');
$categories = get_the_terms($deal_id, 'deal-category');
$deal_types = get_the_terms($deal_id, 'deal-type');

// Get store info
$store_name = '';
$store_logo_url = '';
$store_cashback = '';

if ($stores && !is_wp_error($stores)) {
    $first_store = reset($stores);
    $store_name = $first_store->name;
    $store_logo_id = get_term_meta($first_store->term_id, 'store_logo_id', true);
    $store_logo_url = $store_logo_id ? wp_get_attachment_url($store_logo_id) : '';
    $store_cashback = get_term_meta($first_store->term_id, 'store_cashback', true);
}

// Get featured image
$featured_image = get_the_post_thumbnail_url($deal_id, 'medium');
?>

<article class="deal-card <?php echo $is_expired ? 'deal-expired' : ''; ?>" data-deal-id="<?php echo esc_attr($deal_id); ?>">
    
    <a href="<?php echo esc_url($deal_url); ?>" class="deal-card-link">
        
        <!-- Deal Image -->
        <div class="deal-card-image">
            <?php if ($featured_image) : ?>
                <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($deal_title); ?>" loading="lazy">
            <?php else : ?>
                <div class="deal-no-image">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                    </svg>
                </div>
            <?php endif; ?>
            
            <!-- Store Logo Badge -->
            <?php if ($store_logo_url) : ?>
                <div class="deal-store-badge">
                    <img src="<?php echo esc_url($store_logo_url); ?>" alt="<?php echo esc_attr($store_name); ?>">
                </div>
            <?php endif; ?>
            
            <!-- Discount Badge -->
            <?php if ($discount > 0 && !$is_expired) : ?>
                <div class="deal-discount-badge">
                    <span class="discount-value"><?php echo esc_html($discount); ?>%</span>
                    <span class="discount-label"><?php esc_html_e('OFF', 'dealsindia'); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Expired Overlay -->
            <?php if ($is_expired) : ?>
                <div class="deal-expired-overlay">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/>
                        <line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/>
                    </svg>
                    <p><?php esc_html_e('Expired', 'dealsindia'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Deal Content -->
        <div class="deal-card-content">
            
            <!-- Store Name -->
            <?php if ($store_name) : ?>
                <div class="deal-store-name">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-width="2"/>
                        <polyline points="9 22 9 12 15 12 15 22" stroke-width="2"/>
                    </svg>
                    <?php echo esc_html($store_name); ?>
                </div>
            <?php endif; ?>
            
            <!-- Deal Title -->
            <h3 class="deal-card-title"><?php echo esc_html(wp_trim_words($deal_title, 12)); ?></h3>
            
            <!-- Price Section -->
            <?php if ($sale_price) : ?>
                <div class="deal-card-prices">
                    <span class="deal-price-sale">₹<?php echo number_format($sale_price); ?></span>
                    <?php if ($original_price && $original_price > $sale_price) : ?>
                        <span class="deal-price-original">₹<?php echo number_format($original_price); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Deal Meta (Bottom) -->
            <div class="deal-card-meta">
                
                <!-- Coupon Indicator -->
                <?php if ($coupon_code && !$is_expired) : ?>
                    <span class="deal-meta-coupon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="1" y="5" width="22" height="14" rx="2" ry="2" stroke-width="2"/>
                            <line x1="1" y1="10" x2="23" y2="10" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Code', 'dealsindia'); ?>
                    </span>
                <?php endif; ?>
                
                <!-- Cashback Badge -->
                <?php if ($store_cashback && !$is_expired) : ?>
                    <span class="deal-meta-cashback">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <line x1="12" y1="1" x2="12" y2="23" stroke-width="2"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="2"/>
                        </svg>
                        <?php echo esc_html($store_cashback); ?>
                    </span>
                <?php endif; ?>
                
                <!-- Expiry Timer -->
                <?php if ($time_remaining && !$is_expired) : ?>
                    <span class="deal-meta-expiry">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                        </svg>
                        <?php echo esc_html($time_remaining); ?>
                    </span>
                <?php endif; ?>
                
            </div>
            
        </div>
        
    </a>
    
    <!-- Quick Action Button -->
    <?php if (!$is_expired) : ?>
        <div class="deal-card-actions">
            <a href="<?php echo esc_url($affiliate_link ? $affiliate_link : $deal_url); ?>" 
               class="deal-btn-action" 
               <?php echo $affiliate_link ? 'target="_blank" rel="nofollow noopener"' : ''; ?>
               data-deal-id="<?php echo esc_attr($deal_id); ?>"
               onclick="event.stopPropagation();">
                <?php if ($coupon_code) : ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="1" y="5" width="22" height="14" rx="2" ry="2" stroke-width="2"/>
                        <line x1="1" y1="10" x2="23" y2="10" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Get Code', 'dealsindia'); ?>
                <?php else : ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" stroke-width="2"/>
                        <polyline points="10 17 15 12 10 7" stroke-width="2"/>
                        <line x1="15" y1="12" x2="3" y2="12" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Get Deal', 'dealsindia'); ?>
                <?php endif; ?>
            </a>
        </div>
    <?php endif; ?>
    
</article>
