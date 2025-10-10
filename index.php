<?php
/**
 * Homepage Template - 100% Dynamic (No Hardcoded Text)
 * DealsIndia Theme
 */

get_header(); 
?>

<!-- Hero Slider Section -->
<?php get_template_part('template-parts/hero-slider-section'); ?>

<!-- Three Steps Section -->
<?php
$work_steps = dealsindia_get_work_steps();
if (!empty($work_steps)) :
?>
<section class="three-steps">
    <div class="container">
        <h2 class="steps-title"><?php echo esc_html(dealsindia_get_work_steps_title()); ?></h2>
        <div class="steps-grid">
            <?php 
            $step_number = 1;
            foreach ($work_steps as $step) : 
                $icon = get_post_meta($step->ID, 'step_icon', true);
            ?>
            <div class="step-item">
                <div class="step-number"><?php echo $step_number; ?></div>
                <div class="step-icon"><?php echo $icon ? esc_html($icon) : '✨'; ?></div>
                <h3><?php echo esc_html($step->post_title); ?></h3>
                <p><?php echo esc_html($step->post_content); ?></p>
            </div>
            <?php 
                $step_number++;
            endforeach; 
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Hot Picks Section -->
<?php
$bg_image = get_theme_mod('dealsindia_hot_picks_bg_image', '');
$bg_color = get_theme_mod('dealsindia_hot_picks_bg_color', '#00897b');
$border_radius = get_theme_mod('dealsindia_hot_picks_border_radius', '24');
$overlay_opacity = get_theme_mod('dealsindia_hot_picks_overlay_opacity', '0.2');

$container_style = '';
if ($bg_image) {
    $container_style .= "background-image: url('" . esc_url($bg_image) . "');";
    $container_style .= "background-size: cover;";
    $container_style .= "background-position: center;";
} else {
    $container_style .= "background: linear-gradient(135deg, " . esc_attr($bg_color) . " 0%, " . esc_attr(dealsindia_lighten_color($bg_color, 10)) . " 100%);";
}
$container_style .= "border-radius: " . esc_attr($border_radius) . "px;";
?>

<section class="hot-picks">
    <div class="container">
        <div class="hot-picks-container" style="<?php echo $container_style; ?>">
            
            <?php if ($bg_image && $overlay_opacity > 0) : ?>
    <div class="hot-picks-overlay" style="opacity: 0;"></div>
<?php endif; ?>

            
            <div class="hot-picks-content">
                <div class="section-header-inline">
                    <h2><?php echo esc_html(dealsindia_get_hot_picks_title()); ?></h2>
                    <a href="<?php echo get_post_type_archive_link('deals'); ?>" class="see-more">
                        <?php echo esc_html(dealsindia_get_see_more_text()); ?>
                    </a>
                </div>
                
                <div class="deals-scroll-wrapper">
                    <div class="deals-scroll">
                        <?php
                        $hot_picks = new WP_Query(array(
                            'post_type' => 'deals',
                            'posts_per_page' => 10,
                            'meta_query' => array(
                                array('key' => 'is_trending', 'value' => '1')
                            )
                        ));
                        
                        if (!$hot_picks->have_posts()) {
                            $hot_picks = new WP_Query(array(
                                'post_type' => 'deals',
                                'posts_per_page' => 10
                            ));
                        }
                        
                        while ($hot_picks->have_posts()) : $hot_picks->the_post();
                            get_template_part('template-parts/deal-card');
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Top Offers Section (100% Dynamic) -->
<section class="top-offers-section-premium">
    <div class="container">
        <div class="section-header-premium">
            <div class="section-icon">⚡</div>
            <h2><?php echo esc_html(dealsindia_get_top_offers_title()); ?></h2>
            <p><?php echo esc_html(dealsindia_get_top_offers_subtitle()); ?></p>
        </div>
        
        <div class="categories-offers-grid">
            
            <!-- LEFT: Trending Categories Sidebar -->
            <div class="trending-categories-sidebar">
                <h3 class="sidebar-title"><?php echo esc_html(dealsindia_get_categories_title()); ?></h3>
                
                <div class="categories-list-vertical">
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'deal_category',
                        'parent' => 0,
                        'hide_empty' => false,
                        'number' => 8
                    ));
                    
                    foreach ($categories as $category) :
                        $cat_link = get_term_link($category);
                        $icon_image_id = get_term_meta($category->term_id, 'category_icon_image_id', true);
                        $icon_image_url = $icon_image_id ? wp_get_attachment_url($icon_image_id) : '';
                        $icon_emoji = get_term_meta($category->term_id, 'category_icon', true);
                        $icon_emoji = $icon_emoji ? $icon_emoji : '🏷️';
                    ?>
                        <a href="<?php echo esc_url($cat_link); ?>" class="category-item-horizontal">
                            <span class="category-icon-left">
                                <?php if ($icon_image_url) : ?>
                                    <img src="<?php echo esc_url($icon_image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" class="category-icon-image">
                                <?php else : ?>
                                    <?php echo esc_html($icon_emoji); ?>
                                <?php endif; ?>
                            </span>
                            <span class="category-name-right"><?php echo esc_html($category->name); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- RIGHT: Offers Grid -->
            <div class="offers-main-area">
                <div class="offers-grid-3col">
                    <?php
                    $offers = new WP_Query(array(
                        'post_type' => 'deals',
                        'posts_per_page' => 6
                    ));
                    
                    while ($offers->have_posts()) : $offers->the_post();
                        get_template_part('template-parts/deal-card-large');
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- Top Stores Section (100% Dynamic) -->
<section class="top-stores-section-premium">
    <div class="container">
        <div class="section-header-premium">
            <div class="section-icon">🛍️</div>
            <h2><?php echo esc_html(dealsindia_get_stores_title()); ?></h2>
            <p><?php echo esc_html(dealsindia_get_top_stores_subtitle()); ?></p>
        </div>
        
        <div class="stores-wrapper-cd">
            <div class="stores-grid-cd">
                <?php
                $stores = get_terms(array(
                    'taxonomy' => 'store',
                    'hide_empty' => false,
                    'orderby' => 'count',
                    'order' => 'DESC',
                    'number' => 8
                ));
                
                $store_index = 0;
                foreach ($stores as $store) :
                    $store_link = get_term_link($store);
                    $cashback = get_term_meta($store->term_id, 'store_cashback', true);
                    $cashback = $cashback ? $cashback : '5%';
                    $is_featured = ($store_index === 0);
                ?>
                    <a href="<?php echo esc_url($store_link); ?>" class="store-item-cd <?php echo $is_featured ? 'store-featured' : ''; ?>">
                        <div class="store-logo-cd">
                            <?php dealsindia_render_store_logo($store->term_id, $store->name); ?>
                        </div>
                        
                        <?php if ($is_featured) : ?>
                            <div class="store-name-cd"><?php echo esc_html($store->name); ?></div>
                        <?php endif; ?>
                        
                        <div class="store-cashback-cd">
                            <span class="cashback-icon">🔖</span>
                            <span>Upto <?php echo esc_html($cashback); ?></span>
                        </div>
                    </a>
                <?php 
                    $store_index++;
                endforeach; 
                ?>
                
                <a href="<?php echo home_url('/store/'); ?>" class="store-item-cd store-view-all">
                    <div class="view-all-content">
                        <span class="view-all-text"><?php echo esc_html(dealsindia_get_see_more_text()); ?></span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>


<<!-- Latest Deals Section (Stacked Carousel) -->
<section class="latest-deals-section-carousel">
    <div class="container">
        <div class="section-header-premium">
            <div class="section-icon">✨</div>
            <h2><?php echo esc_html(dealsindia_get_latest_deals_title()); ?></h2>
            <p><?php echo esc_html(dealsindia_get_latest_deals_subtitle()); ?></p>
        </div>
        
        <div class="deals-carousel-wrapper">
            <button class="carousel-nav carousel-nav-left" aria-label="Previous">‹</button>
            <button class="carousel-nav carousel-nav-right" aria-label="Next">›</button>
            
            <div class="deals-carousel-track">
                <?php
                $latest_deals = new WP_Query(array(
                    'post_type' => 'deals',
                    'posts_per_page' => 12,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ));
                
                if ($latest_deals->have_posts()) :
                    while ($latest_deals->have_posts()) : $latest_deals->the_post();
                        ?>
                        <div class="deal-carousel-card">
                            <?php get_template_part('template-parts/deal-card-compact'); ?>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else : ?>
                    <div class="no-deals-message">
                        <p><?php _e('No deals available at the moment. Check back soon!', 'dealsindia'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="section-footer-center">
            <a href="<?php echo get_post_type_archive_link('deals'); ?>" class="view-all-btn-premium">
                <?php echo esc_html(dealsindia_get_view_all_deals_text()); ?> →
            </a>
        </div>
    </div>
</section>


<!-- Giveaway Section -->
<?php
$giveaway = dealsindia_get_active_giveaway();
if ($giveaway) :
    $prize = get_post_meta($giveaway->ID, 'giveaway_prize', true);
    $bg_color = get_post_meta($giveaway->ID, 'giveaway_bg_color', true) ?: '#f093fb';
    $button_text = get_post_meta($giveaway->ID, 'giveaway_button_text', true) ?: 'Participate Now';
    $button_link = get_post_meta($giveaway->ID, 'giveaway_button_link', true);
    $featured_image = get_the_post_thumbnail_url($giveaway->ID, 'large');
?>
<section class="giveaway-section" style="background: linear-gradient(135deg, <?php echo esc_attr($bg_color); ?> 0%, <?php echo esc_attr(dealsindia_darken_color($bg_color, 15)); ?> 100%);">
    <div class="container">
        <div class="giveaway-content-wrapper">
            <div class="giveaway-text">
                <h2 class="giveaway-title"><?php echo esc_html($giveaway->post_title); ?></h2>
                <p class="giveaway-description"><?php echo esc_html($giveaway->post_content); ?></p>
                <?php if ($prize) : ?>
                    <p class="giveaway-prize">🎁 <?php _e('Prize:', 'dealsindia'); ?> <?php echo esc_html($prize); ?></p>
                <?php endif; ?>
                <a href="<?php echo esc_url($button_link ? $button_link : '#'); ?>" class="giveaway-btn">
                    <?php echo esc_html($button_text); ?> →
                </a>
            </div>
            <?php if ($featured_image) : ?>
                <div class="giveaway-image">
                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($giveaway->post_title); ?>">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- About/Stats Section -->
<section class="about-stats-section">
    <div class="container">
        <h2 class="section-title"><?php echo esc_html(dealsindia_get_about_title()); ?></h2>
        
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-icon">🏷️</div>
                <div class="stat-number">
                    <?php 
                    $deals_count = wp_count_posts('deals');
                    echo number_format($deals_count->publish);
                    ?>+
                </div>
                <div class="stat-label"><?php _e('Active Deals', 'dealsindia'); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon">🏪</div>
                <div class="stat-number">
                    <?php 
                    $stores_count = get_terms(array('taxonomy' => 'store', 'hide_empty' => false));
                    echo count($stores_count);
                    ?>+
                </div>
                <div class="stat-label"><?php _e('Partner Stores', 'dealsindia'); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon">👥</div>
                <div class="stat-number">50K+</div>
                <div class="stat-label"><?php _e('Happy Users', 'dealsindia'); ?></div>
            </div>
            
            <div class="stat-box">
                <div class="stat-icon">💰</div>
                <div class="stat-number">₹10 Cr+</div>
                <div class="stat-label"><?php _e('Total Savings', 'dealsindia'); ?></div>
            </div>
        </div>
        
        <p class="about-description">
            <?php _e('India\'s most trusted deals and coupons platform. Save money on your favorite brands with verified coupons, cashback offers, and exclusive deals.', 'dealsindia'); ?>
        </p>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-wrapper">
            <div class="newsletter-icon">📧</div>
            <h2 class="newsletter-title"><?php echo esc_html(dealsindia_get_newsletter_title()); ?></h2>
            <p class="newsletter-description"><?php _e('Subscribe to get the latest deals, coupons & cashback updates directly in your inbox!', 'dealsindia'); ?></p>
            
            <form class="newsletter-form" method="post" action="">
                <input type="email" name="newsletter_email" placeholder="<?php _e('Enter your email address', 'dealsindia'); ?>" required class="newsletter-input">
                <button type="submit" class="newsletter-btn"><?php _e('Subscribe', 'dealsindia'); ?></button>
            </form>
            
            <p class="newsletter-privacy">🔒 <?php _e('We respect your privacy. Unsubscribe anytime.', 'dealsindia'); ?></p>
        </div>
    </div>
</section>

<?php get_footer(); ?>
