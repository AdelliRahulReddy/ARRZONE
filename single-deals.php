<?php
/**
 * Single Deal Template
 * 
 * @package DealsIndia
 * @version 3.1 - With Social Share
 */

get_header();

while (have_posts()) : the_post();
    
    $deal_id = get_the_ID();
    
    // Get all deal meta (using CORRECT field names)
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
    
    // Deal link
    $deal_link = $affiliate_link ? $affiliate_link : get_the_permalink();
    
    // Check expiry
    $is_expired = dealsindia_is_deal_expired($deal_id);
?>

<main class="single-deal-page">
    <div class="container">
        
        <!-- Breadcrumb -->
        <div class="deal-breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <span>/</span>
            <?php if ($categories && !is_wp_error($categories)) : ?>
                <a href="<?php echo esc_url(get_term_link($categories[0])); ?>"><?php echo esc_html($categories[0]->name); ?></a>
                <span>/</span>
            <?php endif; ?>
            <span><?php the_title(); ?></span>
        </div>
        
        <!-- Deal Content Grid -->
        <div class="single-deal-grid">
            
            <!-- Left Column - Image & Details -->
            <div class="deal-left-column">
                
                <!-- Deal Image -->
                <div class="deal-featured-image">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('large'); ?>
                    <?php else : ?>
                        <div class="deal-no-image">
                            <span>📦 No Image Available</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($discount > 0 && !$is_expired) : ?>
                        <div class="deal-discount-overlay"><?php echo $discount; ?>% OFF</div>
                    <?php endif; ?>
                </div>
                
                <!-- Deal Description -->
                <div class="deal-description-section">
                    <h2>Deal Details</h2>
                    <div class="deal-description-content">
                        <?php 
                        if (has_excerpt()) {
                            the_excerpt();
                        }
                        the_content(); 
                        ?>
                    </div>
                </div>
                
                <!-- Social Share Buttons -->
                <div class="deal-social-share-section">
                    <?php echo dealsindia_social_share_buttons($deal_id); ?>
                </div>
                
                <!-- Related Deals -->
                <?php
                $related_args = array(
                    'post_type' => 'deals',
                    'posts_per_page' => 4,
                    'post__not_in' => array($deal_id),
                    'orderby' => 'rand'
                );
                
                // Get related by category
                if ($categories && !is_wp_error($categories)) {
                    $related_args['tax_query'] = array(
                        array(
                            'taxonomy' => 'deal_category',
                            'field' => 'term_id',
                            'terms' => $categories[0]->term_id
                        )
                    );
                }
                
                $related = new WP_Query($related_args);
                
                if ($related->have_posts()) :
                ?>
                <div class="related-deals-section">
                    <h2>Related Deals</h2>
                    <div class="related-deals-grid">
                        <?php while ($related->have_posts()) : $related->the_post(); ?>
                            <?php get_template_part('template-parts/deal-card'); ?>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            
            <!-- Right Column - Purchase Box -->
            <div class="deal-right-column">
                
                <!-- Sticky Purchase Box -->
                <div class="deal-purchase-box <?php echo $is_expired ? 'deal-expired' : ''; ?>">
                    
                    <!-- Store Badge -->
                    <?php if ($store) : ?>
                        <div class="deal-store-header">
                            <div class="store-logo-big">
                                <?php echo strtoupper(substr($store->name, 0, 2)); ?>
                            </div>
                            <div class="store-info">
                                <span class="store-name"><?php echo esc_html($store->name); ?></span>
                                <?php 
                                $cashback = get_term_meta($store->term_id, 'store_cashback', true);
                                if ($cashback) : ?>
                                    <span class="store-cashback">🔖 Upto <?php echo esc_html($cashback); ?> Cashback</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Deal Title -->
                    <h1 class="deal-title"><?php the_title(); ?></h1>
                    
                    <!-- Price Section -->
                    <?php if ($sale_price) : ?>
                        <div class="deal-price-section">
                            <div class="deal-price-main">₹<?php echo number_format($sale_price); ?></div>
                            <?php if ($original_price && $original_price > $sale_price) : ?>
                                <div class="deal-price-old">₹<?php echo number_format($original_price); ?></div>
                                <div class="deal-price-save">
                                    Save ₹<?php echo number_format($original_price - $sale_price); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Coupon Code -->
                    <?php if ($coupon_code && !$is_expired) : ?>
                        <div class="deal-coupon-box">
                            <label>Coupon Code:</label>
                            <div class="coupon-code-display">
                                <code id="coupon-code"><?php echo esc_html($coupon_code); ?></code>
                                <button class="copy-coupon-btn" onclick="dealsindiaCopyCode()">
                                    <span class="copy-text">Copy</span>
                                    <span class="copied-text" style="display:none;">✓ Copied!</span>
                                </button>
                            </div>
                        </div>
                        
                        <script>
                        function dealsindiaCopyCode() {
                            const code = document.getElementById('coupon-code').innerText;
                            navigator.clipboard.writeText(code);
                            document.querySelector('.copy-text').style.display = 'none';
                            document.querySelector('.copied-text').style.display = 'inline';
                            setTimeout(() => {
                                document.querySelector('.copy-text').style.display = 'inline';
                                document.querySelector('.copied-text').style.display = 'none';
                            }, 2000);
                        }
                        </script>
                    <?php endif; ?>
                    
                    <!-- Expiry Date -->
                    <?php if ($expiry_date) : ?>
                        <div class="deal-expiry <?php echo $is_expired ? 'expired' : ''; ?>">
                            <?php if ($is_expired) : ?>
                                ⚠️ This deal expired on <?php echo date('M d, Y', strtotime($expiry_date)); ?>
                            <?php else : ?>
                                ⏰ Expires on: <?php echo date('M d, Y h:i A', strtotime($expiry_date)); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- CTA Button -->
                    <?php if (!$is_expired) : ?>
                        <a href="<?php echo esc_url($deal_link); ?>" 
                           class="deal-cta-button"
                           data-deal-id="<?php echo $deal_id; ?>"
                           data-track-deal="true"
                           target="_blank"
                           rel="nofollow noopener">
                            Get This Deal →
                        </a>
                    <?php else : ?>
                        <button class="deal-cta-button deal-expired-btn" disabled>
                            Deal Expired
                        </button>
                    <?php endif; ?>
                    
                    <!-- Trust Badges -->
                    <div class="deal-trust-badges">
                        <div class="trust-badge">✓ Verified Deal</div>
                        <div class="trust-badge">🔒 Safe & Secure</div>
                        <div class="trust-badge">⚡ Instant Activation</div>
                    </div>
                    
                    <!-- Deal Meta Info -->
                    <div class="deal-meta-info">
                        <div class="meta-item">
                            <strong>Posted:</strong> <?php echo human_time_diff(get_the_time('U'), current_time('timestamp')) . ' ago'; ?>
                        </div>
                        <?php if ($categories && !is_wp_error($categories)) : ?>
                            <div class="meta-item">
                                <strong>Category:</strong> 
                                <?php 
                                $cat_links = array();
                                foreach ($categories as $cat) {
                                    $cat_links[] = '<a href="' . esc_url(get_term_link($cat)) . '">' . esc_html($cat->name) . '</a>';
                                }
                                echo implode(', ', $cat_links);
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
                
            </div>
            
        </div>
        
    </div>
</main>

<?php
endwhile;
get_footer();
?>
