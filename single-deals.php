<?php
/**
 * Single Deal Template - Enhanced Spider-Verse
 * Features: Countdown timer, analytics, schema markup, animated coupon reveal
 * 
 * @package DealsIndia
 * @version 4.0 - Enhanced Single Deal
 */

get_header();

while (have_posts()) : the_post();
    
    $deal_id = get_the_ID();
    
    // Get all deal meta
    $original_price = get_post_meta($deal_id, 'deal_original_price', true);
    $sale_price = get_post_meta($deal_id, 'deal_price', true);
    $coupon_code = get_post_meta($deal_id, 'coupon_code', true);
    $affiliate_link = get_post_meta($deal_id, 'deal_url', true);
    $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
    
    // Calculate discount
    $discount = 0;
    if ($original_price && $sale_price && $original_price > 0) {
        $discount = round((($original_price - $sale_price) / $original_price) * 100);
    }
    
    // Get taxonomies
    $stores = get_the_terms($deal_id, 'store');
    $store = ($stores && !is_wp_error($stores)) ? $stores[0] : null;
    $categories = get_the_terms($deal_id, 'deal_category');
    $deal_types = get_the_terms($deal_id, 'deal_type');
    $deal_type = ($deal_types && !is_wp_error($deal_types)) ? $deal_types[0] : null;
    
    // Deal link
    $deal_link = $affiliate_link ? $affiliate_link : get_permalink();
    
    // Check expiry
    $is_expired = dealsindia_is_deal_expired($deal_id);
    
    // Get store meta
    $store_logo = $store ? get_term_meta($store->term_id, 'store_logo', true) : '';
    $store_cashback = $store ? get_term_meta($store->term_id, 'store_cashback_rate', true) : '';
    
    // Increment view count
    $view_count = get_post_meta($deal_id, 'deal_view_count', true);
    $view_count = $view_count ? intval($view_count) + 1 : 1;
    update_post_meta($deal_id, 'deal_view_count', $view_count);
?>

<main class="single-deal-page-enhanced">
    <div class="container">
        
        <!-- Breadcrumb -->
        <?php dealsindia_breadcrumb(); ?>
        
        <!-- Deal Content Grid -->
        <div class="single-deal-grid-enhanced">
            
            <!-- Left Column - Image & Content -->
            <div class="deal-left-column-enhanced">
                
                <!-- Deal Image Gallery -->
                <div class="deal-featured-image-enhanced">
                    <?php if (has_post_thumbnail()): ?>
                        <?php the_post_thumbnail('large', array('class' => 'deal-main-image')); ?>
                    <?php else: ?>
                        <div class="deal-no-image">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5" stroke-width="2"/>
                                <polyline points="21 15 16 10 5 21" stroke-width="2"/>
                            </svg>
                            <p><?php esc_html_e('No Image Available', 'dealsindia'); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($discount > 0 && !$is_expired): ?>
                        <div class="deal-discount-badge-overlay">
                            <span class="discount-value"><?php echo $discount; ?>%</span>
                            <span class="discount-label"><?php esc_html_e('OFF', 'dealsindia'); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($is_expired): ?>
                        <div class="deal-expired-overlay">
                            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="white">
                                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                <line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/>
                                <line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/>
                            </svg>
                            <p><?php esc_html_e('Deal Expired', 'dealsindia'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Deal Highlights -->
                <?php if (!$is_expired): ?>
                <div class="deal-highlights-section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Deal Highlights', 'dealsindia'); ?>
                    </h3>
                    <ul class="highlights-list">
                        <?php if ($discount > 0): ?>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12" stroke-width="2"/>
                            </svg>
                            <?php printf(esc_html__('Save %d%% on this deal', 'dealsindia'), $discount); ?>
                        </li>
                        <?php endif; ?>
                        <?php if ($coupon_code): ?>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Coupon code available', 'dealsindia'); ?>
                        </li>
                        <?php endif; ?>
                        <?php if ($store_cashback): ?>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12" stroke-width="2"/>
                            </svg>
                            <?php printf(esc_html__('Get %s%% cashback', 'dealsindia'), $store_cashback); ?>
                        </li>
                        <?php endif; ?>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('100% verified deal', 'dealsindia'); ?>
                        </li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Deal Description -->
                <div class="deal-description-section-enhanced">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-width="2"/>
                            <polyline points="14 2 14 8 20 8" stroke-width="2"/>
                            <line x1="16" y1="13" x2="8" y2="13" stroke-width="2"/>
                            <line x1="16" y1="17" x2="8" y2="17" stroke-width="2"/>
                            <polyline points="10 9 9 9 8 9" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Deal Description', 'dealsindia'); ?>
                    </h2>
                    <div class="deal-description-content">
                        <?php 
                        if (has_excerpt()) {
                            echo '<div class="deal-excerpt">' . get_the_excerpt() . '</div>';
                        }
                        the_content(); 
                        ?>
                    </div>
                </div>
                
                <!-- Deal Details Table -->
                <div class="deal-details-table-section">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                            <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Deal Information', 'dealsindia'); ?>
                    </h2>
                    <table class="deal-info-table">
                        <tbody>
                            <?php if ($store): ?>
                            <tr>
                                <th><?php esc_html_e('Store', 'dealsindia'); ?></th>
                                <td><a href="<?php echo esc_url(get_term_link($store)); ?>"><?php echo esc_html($store->name); ?></a></td>
                            </tr>
                            <?php endif; ?>
                            
                            <?php if ($categories && !is_wp_error($categories)): ?>
                            <tr>
                                <th><?php esc_html_e('Category', 'dealsindia'); ?></th>
                                <td>
                                    <?php 
                                    $cat_links = array();
                                    foreach ($categories as $cat) {
                                        $cat_links[] = '<a href="' . esc_url(get_term_link($cat)) . '">' . esc_html($cat->name) . '</a>';
                                    }
                                    echo implode(', ', $cat_links);
                                    ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            
                            <?php if ($deal_type): ?>
                            <tr>
                                <th><?php esc_html_e('Deal Type', 'dealsindia'); ?></th>
                                <td><a href="<?php echo esc_url(get_term_link($deal_type)); ?>"><?php echo esc_html($deal_type->name); ?></a></td>
                            </tr>
                            <?php endif; ?>
                            
                            <tr>
                                <th><?php esc_html_e('Posted', 'dealsindia'); ?></th>
                                <td><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ' . esc_html__('ago', 'dealsindia'); ?></td>
                            </tr>
                            
                            <?php if ($expiry_date && !$is_expired): ?>
                            <tr>
                                <th><?php esc_html_e('Valid Until', 'dealsindia'); ?></th>
                                <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($expiry_date)); ?></td>
                            </tr>
                            <?php endif; ?>
                            
                            <tr>
                                <th><?php esc_html_e('Views', 'dealsindia'); ?></th>
                                <td><?php echo number_format($view_count); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Social Share -->
                <div class="deal-social-share-section-enhanced">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" stroke-width="2"/>
                            <polyline points="16 6 12 2 8 6" stroke-width="2"/>
                            <line x1="12" y1="2" x2="12" y2="15" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Share This Deal', 'dealsindia'); ?>
                    </h3>
                    <?php dealsindia_social_share_buttons(); ?>
                </div>
                
                <!-- Related Deals -->
                <?php
                $related_args = array(
                    'post_type' => 'deals',
                    'posts_per_page' => 4,
                    'post__not_in' => array($deal_id),
                    'orderby' => 'rand',
                    'meta_query' => array(
                        array(
                            'key' => 'deal_expiry_date',
                            'value' => current_time('Y-m-d'),
                            'compare' => '>=',
                            'type' => 'DATE',
                        ),
                    ),
                );
                
                if ($categories && !is_wp_error($categories)) {
                    $related_args['tax_query'] = array(
                        array(
                            'taxonomy' => 'deal_category',
                            'field' => 'term_id',
                            'terms' => $categories[0]->term_id,
                        ),
                    );
                }
                
                $related = new WP_Query($related_args);
                
                if ($related->have_posts()):
                ?>
                <div class="related-deals-section-enhanced">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" stroke-width="2"/>
                            <line x1="9" y1="2" x2="9" y2="7" stroke-width="2"/>
                            <line x1="15" y1="2" x2="15" y2="7" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Similar Deals', 'dealsindia'); ?>
                    </h2>
                    <div class="related-deals-grid-enhanced">
                        <?php while ($related->have_posts()): $related->the_post(); ?>
                            <?php get_template_part('template-parts/deal-card'); ?>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Right Column - Sticky Purchase Box -->
            <div class="deal-right-column-enhanced">
                
                <div class="deal-purchase-box-enhanced <?php echo $is_expired ? 'is-expired' : ''; ?>" id="dealPurchaseBox">
                    
                    <!-- Store Header -->
                    <?php if ($store): ?>
                    <div class="store-header-box">
                        <?php if ($store_logo): ?>
                            <div class="store-logo-box">
                                <img src="<?php echo esc_url($store_logo); ?>" alt="<?php echo esc_attr($store->name); ?>">
                            </div>
                        <?php else: ?>
                            <div class="store-logo-box store-logo-fallback">
                                <?php echo esc_html(strtoupper(substr($store->name, 0, 2))); ?>
                            </div>
                        <?php endif; ?>
                        <div class="store-info-box">
                            <span class="store-name-box"><?php echo esc_html($store->name); ?></span>
                            <?php if ($store_cashback): ?>
                            <span class="store-cashback-box">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M20 12V22H4V12" stroke-width="2"/>
                                    <path d="M22 7H2v5h20V7z" stroke-width="2"/>
                                    <path d="M12 22V7" stroke-width="2"/>
                                </svg>
                                <?php echo esc_html($store_cashback); ?>% <?php esc_html_e('Cashback', 'dealsindia'); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Deal Title -->
                    <h1 class="deal-title-box"><?php the_title(); ?></h1>
                    
                    <!-- Price Section -->
                    <?php if ($sale_price): ?>
                    <div class="deal-price-section-box">
                        <div class="price-main-box">
                            <span class="currency">₹</span><?php echo number_format($sale_price); ?>
                        </div>
                        <?php if ($original_price && $original_price > $sale_price): ?>
                        <div class="price-comparison-box">
                            <span class="price-old-box">₹<?php echo number_format($original_price); ?></span>
                            <span class="price-save-box">
                                <?php esc_html_e('You Save', 'dealsindia'); ?>: ₹<?php echo number_format($original_price - $sale_price); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Countdown Timer -->
                    <?php if ($expiry_date && !$is_expired): ?>
                    <div class="deal-countdown-box" id="dealCountdown" data-expiry="<?php echo esc_attr($expiry_date); ?>">
                        <div class="countdown-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Deal Ends In:', 'dealsindia'); ?>
                        </div>
                        <div class="countdown-timer">
                            <div class="countdown-item">
                                <span class="countdown-value" id="days">00</span>
                                <span class="countdown-unit"><?php esc_html_e('Days', 'dealsindia'); ?></span>
                            </div>
                            <div class="countdown-separator">:</div>
                            <div class="countdown-item">
                                <span class="countdown-value" id="hours">00</span>
                                <span class="countdown-unit"><?php esc_html_e('Hrs', 'dealsindia'); ?></span>
                            </div>
                            <div class="countdown-separator">:</div>
                            <div class="countdown-item">
                                <span class="countdown-value" id="minutes">00</span>
                                <span class="countdown-unit"><?php esc_html_e('Mins', 'dealsindia'); ?></span>
                            </div>
                            <div class="countdown-separator">:</div>
                            <div class="countdown-item">
                                <span class="countdown-value" id="seconds">00</span>
                                <span class="countdown-unit"><?php esc_html_e('Secs', 'dealsindia'); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Coupon Code -->
                    <?php if ($coupon_code && !$is_expired): ?>
                    <div class="deal-coupon-box-enhanced">
                        <label class="coupon-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="1" y="5" width="22" height="14" rx="2" ry="2" stroke-width="2"/>
                                <line x1="1" y1="10" x2="23" y2="10" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Coupon Code', 'dealsindia'); ?>
                        </label>
                        <div class="coupon-code-wrapper">
                            <code class="coupon-code-text" id="couponCode"><?php echo esc_html($coupon_code); ?></code>
                            <button class="copy-coupon-btn-enhanced" id="copyCouponBtn">
                                <svg class="copy-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2" stroke-width="2"/>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-width="2"/>
                                </svg>
                                <span class="copy-text"><?php esc_html_e('Copy', 'dealsindia'); ?></span>
                                <span class="copied-text" style="display:none;">✓ <?php esc_html_e('Copied!', 'dealsindia'); ?></span>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- CTA Button -->
                    <?php if (!$is_expired): ?>
                    <a href="<?php echo esc_url($deal_link); ?>" 
                       class="deal-cta-button-enhanced"
                       id="getDealBtn"
                       data-deal-id="<?php echo esc_attr($deal_id); ?>"
                       target="_blank"
                       rel="nofollow noopener sponsored">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M9 18l6-6-6-6" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Get This Deal Now', 'dealsindia'); ?>
                    </a>
                    <?php else: ?>
                    <button class="deal-cta-button-enhanced deal-expired-btn" disabled>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/>
                            <line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Deal Expired', 'dealsindia'); ?>
                    </button>
                    <?php endif; ?>
                    
                    <!-- Trust Badges -->
                    <div class="trust-badges-box">
                        <div class="trust-badge-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20 6 9 17 4 12" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Verified', 'dealsindia'); ?>
                        </div>
                        <div class="trust-badge-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Secure', 'dealsindia'); ?>
                        </div>
                        <div class="trust-badge-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Instant', 'dealsindia'); ?>
                        </div>
                    </div>
                    
                </div>
                
            </div>
            
        </div>
        
    </div>
</main>

<!-- Countdown Timer Script -->
<?php if ($expiry_date && !$is_expired): ?>
<script>
(function() {
    const expiryDate = new Date('<?php echo date('Y-m-d H:i:s', strtotime($expiry_date)); ?>').getTime();
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = expiryDate - now;
        
        if (distance < 0) {
            document.getElementById('dealCountdown').innerHTML = '<p style="color:#f44336;font-weight:700;">⚠️ Deal Expired</p>';
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById('days').textContent = String(days).padStart(2, '0');
        document.getElementById('hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
})();
</script>
<?php endif; ?>

<!-- Coupon Copy Script -->
<?php if ($coupon_code && !$is_expired): ?>
<script>
document.getElementById('copyCouponBtn').addEventListener('click', function() {
    const code = document.getElementById('couponCode').innerText;
    
    navigator.clipboard.writeText(code).then(function() {
        const btn = document.getElementById('copyCouponBtn');
        btn.querySelector('.copy-text').style.display = 'none';
        btn.querySelector('.copied-text').style.display = 'inline';
        btn.classList.add('copied');
        
        setTimeout(function() {
            btn.querySelector('.copy-text').style.display = 'inline';
            btn.querySelector('.copied-text').style.display = 'none';
            btn.classList.remove('copied');
        }, 2000);
    });
});
</script>
<?php endif; ?>

<!-- Click Tracking Script -->
<script>
document.getElementById('getDealBtn')?.addEventListener('click', function() {
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=track_deal_click&deal_id=<?php echo $deal_id; ?>&nonce=<?php echo wp_create_nonce('track_deal_click'); ?>'
    });
});
</script>

<?php
// Schema Markup for SEO
$schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'Offer',
    'name' => get_the_title(),
    'description' => get_the_excerpt(),
    'url' => get_permalink(),
    'price' => $sale_price ? $sale_price : $original_price,
    'priceCurrency' => 'INR',
);

if ($expiry_date) {
    $schema['priceValidUntil'] = date('Y-m-d', strtotime($expiry_date));
}

if ($store) {
    $schema['seller'] = array(
        '@type' => 'Organization',
        'name' => $store->name,
    );
}
?>
<script type="application/ld+json">
<?php echo json_encode($schema, JSON_UNESCAPED_SLASHES); ?>
</script>

<?php
endwhile;
get_footer();
?>
