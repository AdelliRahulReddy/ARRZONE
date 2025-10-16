<?php
if (!defined('ABSPATH')) exit; 
/**
 * Single Deal Template - MOBILE OPTIMIZED
 * 
 * Features:
 * - Perfect mobile order: Image → Deal Info → Description → Terms → Share
 * - Hero section with deal info
 * - Store details sidebar
 * - Related deals from same category
 * - More deals from same store
 * - Social share
 * - Countdown timer
 * - Mobile-first responsive
 * 
 * @package ARRZONE
 * @version 6.0 - MOBILE FIXED
 */

get_header();

// Start the loop
if (have_posts()) :
    while (have_posts()) : the_post();
        
        $post_id = get_the_ID();
        
        // Get deal meta data
        $coupon_code = get_post_meta($post_id, 'deal_coupon_code', true);
        $affiliate_link = get_post_meta($post_id, 'deal_affiliate_link', true);
        $original_price = get_post_meta($post_id, 'deal_original_price', true);
        $sale_price = get_post_meta($post_id, 'deal_sale_price', true);
        $expiry_date = get_post_meta($post_id, 'deal_expiry_date', true);
        $terms = get_post_meta($post_id, 'deal_terms', true);
        
        // Calculate discount
        $discount = 0;
        if ($original_price && $sale_price && $original_price > $sale_price) {
            $discount = round((($original_price - $sale_price) / $original_price) * 100);
        }
        
        // Check if expired
        $is_expired = false;
        if ($expiry_date) {
            $expiry_timestamp = strtotime($expiry_date);
            $is_expired = (current_time('timestamp') > $expiry_timestamp);
        }
        
        // Get taxonomies
        $categories = get_the_terms($post_id, 'deal-category');
        $stores = get_the_terms($post_id, 'store');
        $deal_types = get_the_terms($post_id, 'deal-type');
        
        // Get store meta
        $store_cashback = '';
        $store_logo_url = '';
        $store_banner_url = '';
        $store_website = '';
        
        if ($stores && !is_wp_error($stores)) {
            $first_store = reset($stores);
            $store_logo_id = get_term_meta($first_store->term_id, 'store_logo_id', true);
            $store_logo_url = $store_logo_id ? wp_get_attachment_url($store_logo_id) : '';
            $store_banner_id = get_term_meta($first_store->term_id, 'store_banner_id', true);
            $store_banner_url = $store_banner_id ? wp_get_attachment_url($store_banner_id) : '';
            $store_cashback = get_term_meta($first_store->term_id, 'store_cashback', true);
            $store_website = get_term_meta($first_store->term_id, 'store_website_url', true);
        }
?>

<main class="single-deal-page">
    
    <?php
    // =====================================================
    // HERO SECTION (Store Banner Background) - Desktop Only
    // =====================================================
    if ($store_banner_url) :
    ?>
    <section class="deal-hero-section" style="background-image: url(<?php echo esc_url($store_banner_url); ?>);">
        <div class="deal-hero-overlay"></div>
        <div class="container">
            <div class="deal-hero-content">
                <?php dealsindia_breadcrumb(); ?>
            </div>
        </div>
    </section>
    <?php else : ?>
    <div class="container">
        <div class="deal-breadcrumb-simple">
            <?php dealsindia_breadcrumb(); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="container">
        
        <!-- Main Deal Layout -->
        <div class="single-deal-grid">
            
            <!-- ⭐ FEATURED IMAGE - Will show FIRST on mobile -->
            <div class="deal-featured-image">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('large', array('class' => 'deal-main-image')); ?>
                <?php else : ?>
                    <div class="deal-no-image">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <p><?php esc_html_e('No image available', 'dealsindia'); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($discount > 0 && !$is_expired) : ?>
                    <div class="deal-discount-badge">
                        <span class="discount-value"><?php echo esc_html($discount); ?>%</span>
                        <span class="discount-label"><?php esc_html_e('OFF', 'dealsindia'); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($is_expired) : ?>
                    <div class="deal-expired-badge">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/>
                            <line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/>
                        </svg>
                        <p><?php esc_html_e('Deal Expired', 'dealsindia'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- ⭐ RIGHT SIDEBAR - Will show SECOND on mobile (after image) -->
            <aside class="deal-right-sidebar">
                
                <!-- Store Badge -->
                <?php if ($stores && !is_wp_error($stores)) : ?>
                    <div class="deal-store-badge">
                        <?php if ($store_logo_url) : ?>
                            <div class="store-logo">
                                <img src="<?php echo esc_url($store_logo_url); ?>" alt="<?php echo esc_attr($first_store->name); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="store-info">
                            <span class="store-label"><?php esc_html_e('Available at', 'dealsindia'); ?></span>
                            <h3 class="store-name"><?php echo esc_html($first_store->name); ?></h3>
                            <?php if ($store_cashback) : ?>
                                <span class="store-cashback">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <line x1="12" y1="1" x2="12" y2="23" stroke-width="2"/>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="2"/>
                                    </svg>
                                    <?php echo esc_html($store_cashback); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Deal Title -->
                <h1 class="deal-title"><?php the_title(); ?></h1>
                
                <!-- Price Section -->
                <?php if ($sale_price) : ?>
                    <div class="deal-price-section">
                        <div class="price-main">
                            <span class="currency">₹</span><?php echo number_format($sale_price); ?>
                        </div>
                        <?php if ($original_price && $original_price > $sale_price) : ?>
                            <div class="price-comparison">
                                <span class="price-old">₹<?php echo number_format($original_price); ?></span>
                                <span class="price-save">
                                    <?php esc_html_e('You Save:', 'dealsindia'); ?>
                                    ₹<?php echo number_format($original_price - $sale_price); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Countdown Timer -->
                <?php if ($expiry_date && !$is_expired) : ?>
                    <div class="deal-countdown" id="dealCountdown" data-expiry="<?php echo esc_attr($expiry_date); ?>">
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
                <?php if ($coupon_code && !$is_expired) : ?>
                    <div class="deal-coupon-box">
                        <label class="coupon-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="1" y="5" width="22" height="14" rx="2" ry="2" stroke-width="2"/>
                                <line x="1" y1="10" x2="23" y2="10" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Coupon Code:', 'dealsindia'); ?>
                        </label>
                        <div class="coupon-code-wrapper">
                            <code class="coupon-code-text" id="couponCode"><?php echo esc_html($coupon_code); ?></code>
                            <button class="copy-coupon-btn" id="copyCouponBtn">
                                <svg class="copy-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2" stroke-width="2"/>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-width="2"/>
                                </svg>
                                <span class="copy-text"><?php esc_html_e('Copy', 'dealsindia'); ?></span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- CTA Button -->
                <?php if (!$is_expired && $affiliate_link) : ?>
                    <a href="<?php echo esc_url($affiliate_link); ?>" class="btn btn-primary btn-full btn-deal-cta" target="_blank" rel="nofollow noopener" data-deal-id="<?php echo esc_attr($post_id); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" stroke-width="2"/>
                            <polyline points="10 17 15 12 10 7" stroke-width="2"/>
                            <line x1="15" y1="12" x2="3" y2="12" stroke-width="2"/>
                        </svg>
                        <?php echo $coupon_code ? esc_html__('Reveal Code & Get Deal', 'dealsindia') : esc_html__('Get This Deal', 'dealsindia'); ?>
                    </a>
                <?php elseif ($is_expired) : ?>
                    <button class="btn btn-disabled btn-full" disabled>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/>
                            <line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Deal Expired', 'dealsindia'); ?>
                    </button>
                <?php endif; ?>
                
                <!-- Deal Meta Info -->
                <div class="deal-meta-info">
                    <?php if ($categories && !is_wp_error($categories)) : ?>
                        <div class="meta-item">
                            <span class="meta-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" stroke-width="2"/>
                                    <line x1="7" y1="7" x2="7.01" y2="7" stroke-width="2"/>
                                </svg>
                                <?php esc_html_e('Category:', 'dealsindia'); ?>
                            </span>
                            <span class="meta-value">
                                <?php echo get_the_term_list($post_id, 'deal-category', '', ', '); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($deal_types && !is_wp_error($deal_types)) : ?>
                        <div class="meta-item">
                            <span class="meta-label">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/>
                                </svg>
                                <?php esc_html_e('Type:', 'dealsindia'); ?>
                            </span>
                            <span class="meta-value">
                                <?php echo get_the_term_list($post_id, 'deal-type', '', ', '); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                
            </aside><!-- .deal-right-sidebar -->
            
            <!-- ⭐ LEFT COLUMN - Will show THIRD on mobile (Description, Terms, Share) -->
            <div class="deal-left-column">
                
                <!-- Deal Description -->
                <div class="deal-description-section">
                    <h2 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M12 20h9" stroke-width="2"/>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Deal Description', 'dealsindia'); ?>
                    </h2>
                    <div class="deal-content">
                        <?php the_content(); ?>
                    </div>
                </div>
                
                <!-- Terms & Conditions -->
                <?php if ($terms) : ?>
                    <div class="deal-terms-section">
                        <h3 class="section-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                <line x1="12" y1="16" x2="12" y2="12" stroke-width="2"/>
                                <line x1="12" y1="8" x2="12.01" y2="8" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Terms & Conditions', 'dealsindia'); ?>
                        </h3>
                        <div class="terms-content">
                            <?php echo wp_kses_post($terms); ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Social Share -->
                <div class="deal-social-share">
                    <h4 class="share-title"><?php esc_html_e('Share this deal:', 'dealsindia'); ?></h4>
                    <?php dealsindia_social_share_buttons(); ?>
                </div>
                
            </div><!-- .deal-left-column -->
            
        </div><!-- .single-deal-grid -->
        
        <?php
        // =====================================================
        // RELATED DEALS (Same Category)
        // =====================================================
        $related_args = array(
            'post_type' => 'deals',
            'posts_per_page' => 4,
            'post__not_in' => array($post_id),
            'orderby' => 'rand',
        );
        
        if ($categories && !is_wp_error($categories)) {
            $related_args['tax_query'] = array(
                array(
                    'taxonomy' => 'deal-category',
                    'field' => 'term_id',
                    'terms' => wp_list_pluck($categories, 'term_id'),
                )
            );
        }
        
        $related_query = new WP_Query($related_args);
        
        if ($related_query->have_posts()) :
        ?>
        <section class="related-deals-section">
            <h2 class="section-title-main">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke-width="2"/>
                </svg>
                <?php esc_html_e('Related Deals', 'dealsindia'); ?>
            </h2>
            <div class="related-deals-grid">
                <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                    <?php get_template_part('template-parts/deal-card'); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
        <?php endif; ?>
        
        <?php
        // =====================================================
        // MORE FROM SAME STORE
        // =====================================================
        if ($stores && !is_wp_error($stores)) :
            $store_deals_args = array(
                'post_type' => 'deals',
                'posts_per_page' => 4,
                'post__not_in' => array($post_id),
                'orderby' => 'date',
                'order' => 'DESC',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'store',
                        'field' => 'term_id',
                        'terms' => $first_store->term_id,
                    )
                )
            );
            
            $store_deals_query = new WP_Query($store_deals_args);
            
            if ($store_deals_query->have_posts()) :
        ?>
        <section class="store-deals-section">
            <h2 class="section-title-main">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-width="2"/>
                    <polyline points="9 22 9 12 15 12 15 22" stroke-width="2"/>
                </svg>
                <?php printf(esc_html__('More from %s', 'dealsindia'), esc_html($first_store->name)); ?>
            </h2>
            <div class="store-deals-grid">
                <?php while ($store_deals_query->have_posts()) : $store_deals_query->the_post(); ?>
                    <?php get_template_part('template-parts/deal-card'); ?>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </section>
        <?php 
            endif;
        endif;
        ?>
        
    </div><!-- .container -->
    
</main>

<?php
    endwhile;
endif;

get_footer();
?>
