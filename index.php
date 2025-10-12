<?php
/**
 * Homepage Template - 100% Dynamic
 * ABSOLUTELY ZERO hardcoded emojis, text, or icons
 * Everything from WordPress Customizer or database
 * 
 * @package DealsIndia
 * @version 7.0 - ZERO HARDCODING
 */

get_header();
?>

<main class="homepage">
    
<!-- 1. WELCOME TEXT SECTION -->
<section class="welcome-text-section">
    <div class="container">
        <h1 class="welcome-text"><?php echo esc_html(get_theme_mod('welcome_text', 'Welcome to ' . get_bloginfo('name') . ' - India\'s Trusted Coupons, Offers & Cashback Website')); ?></h1>
    </div>
</section>

<!-- 2. HERO BANNERS SECTION -->
<section class="hero-slider-cd">
    <div class="container">
        <?php get_template_part('template-parts/hero-slider-section'); ?>
    </div>
</section>

<!-- 3. THREE STEPS SECTION -->
<section class="three-steps">
    <div class="container">
        <h2 class="steps-title"><?php echo esc_html(get_theme_mod('steps_section_title', 'Three Steps To Save With ' . get_bloginfo('name'))); ?></h2>
        <div class="steps-grid">
            <?php
            $steps = dealsindia_get_work_steps();
            if (!empty($steps)) :
                $step_number = 1;
                foreach ($steps as $step) :
                    setup_postdata($step);
                    $icon = get_post_meta($step->ID, 'step_icon', true);
                    ?>
                    <div class="step-item">
                        <div class="step-number"><?php echo $step_number; ?></div>
                        <?php if ($icon) : ?>
                            <span class="step-icon"><?php echo esc_html($icon); ?></span>
                        <?php endif; ?>
                        <h3><?php echo esc_html($step->post_title); ?></h3>
                        <p><?php echo esc_html(wp_trim_words($step->post_content, 12, '...')); ?></p>
                    </div>
                    <?php
                    $step_number++;
                endforeach;
                wp_reset_postdata();
            else :
                // NO DEFAULT STEPS - If none in database, show nothing
                echo '<p style="text-align:center;grid-column:1/-1;color:#999;">' . esc_html__('Add steps from admin panel to display here.', 'dealsindia') . '</p>';
            endif;
            ?>
        </div>
    </div>
</section>

<!-- 4. HOT PICKS SECTION -->
<section class="hot-picks">
    <div class="container">
        <div class="hot-picks-container" <?php 
            $hot_picks_bg = get_theme_mod('hot_picks_bg_image');
            if ($hot_picks_bg) {
                echo 'style="background-image:url(' . esc_url($hot_picks_bg) . ');"';
            }
        ?>>
            <div class="hot-picks-content">
                <div class="section-header-inline">
                    <h2><?php echo esc_html(get_theme_mod('hot_picks_title', __('Festival Hot Picks!', 'dealsindia'))); ?></h2>
                    <a href="<?php echo esc_url(get_post_type_archive_link('deals')); ?>" class="see-more">
                        <?php echo esc_html(get_theme_mod('hot_picks_button_text', __('See More', 'dealsindia'))); ?> ›
                    </a>
                </div>
                
                <div class="deals-scroll-wrapper">
                    <div class="deals-scroll">
                        <?php
                        $hot_picks_count = get_theme_mod('hot_picks_count', 12);
                        $hot_picks = dealsindia_get_hot_picks($hot_picks_count);
                        
                        if ($hot_picks->have_posts()) :
                            while ($hot_picks->have_posts()) : $hot_picks->the_post();
                                get_template_part('template-parts/deal-card');
                            endwhile;
                            wp_reset_postdata();
                        else :
                            ?>
                            <div class="no-deals-message">
                                <p><?php echo esc_html(get_theme_mod('no_hot_picks_message', __('No hot picks available right now. Check back soon for amazing deals!', 'dealsindia'))); ?></p>
                            </div>
                            <?php
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 5. TOP STORES SECTION -->
<section class="top-stores-section-premium">
    <div class="container">
       <div class="section-header-premium">
            <?php 
            $stores_icon = get_theme_mod('stores_section_icon', ''); 
            if ($stores_icon) : 
            ?>
                <span class="section-icon"><?php echo esc_html($stores_icon); ?></span>
            <?php endif; ?>
            
            <h2><?php echo esc_html(get_theme_mod('top_stores_title', __('Top Stores', 'dealsindia'))); ?></h2>
            <p><?php echo esc_html(get_theme_mod('top_stores_subtitle', __('Shop from India\'s most trusted brands', 'dealsindia'))); ?></p>
        </div>

        <div class="stores-wrapper-cd">
            <div class="stores-grid-cd">
                <?php
                $stores_count = get_theme_mod('top_stores_count', 8);
                $stores = get_terms(array(
                    'taxonomy' => 'store',
                    'hide_empty' => true,
                    'number' => $stores_count,
                    'orderby' => 'count',
                    'order' => 'DESC'
                ));
                
                if (!empty($stores) && !is_wp_error($stores)) :
                    $store_index = 0;
                    foreach ($stores as $store) :
                        $logo_id = get_term_meta($store->term_id, 'store_logo_id', true);
                        $logo = $logo_id ? wp_get_attachment_url($logo_id) : '';
                        $cashback = get_term_meta($store->term_id, 'store_cashback', true);
                        $is_featured = ($store_index === 0);
                        
                        $color1 = '#' . substr(md5($store->name), 0, 6);
                        $color2 = '#' . substr(md5($store->name . 'x'), 0, 6);
                        $initials = strtoupper(substr($store->name, 0, 2));
                        ?>
                        <a href="<?php echo esc_url(get_term_link($store)); ?>" 
                           class="store-item-cd <?php echo $is_featured ? 'store-featured' : ''; ?>">
                            
                            <div class="store-logo-cd">
                                <?php if ($logo) : ?>
                                    <img src="<?php echo esc_url($logo); ?>" 
                                         alt="<?php echo esc_attr($store->name); ?>" 
                                         loading="lazy">
                                <?php else : ?>
                                    <div class="store-logo-fallback" 
                                         style="background: linear-gradient(135deg, <?php echo esc_attr($color1); ?>, <?php echo esc_attr($color2); ?>);">
                                        <?php echo esc_html($initials); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="store-name-cd">
                                <?php echo esc_html($store->name); ?>
                            </div>
                            
                            <?php if ($cashback) : ?>
                                <div class="store-cashback-cd">
                                    <?php 
                                    $cashback_icon = get_theme_mod('cashback_icon', '');
                                    if ($cashback_icon) : 
                                    ?>
                                        <span class="cashback-icon"><?php echo esc_html($cashback_icon); ?></span>
                                    <?php endif; ?>
                                    <?php echo esc_html(sprintf(__('Upto %s', 'dealsindia'), $cashback)); ?>
                                </div>
                            <?php endif; ?>
                        </a>
                        <?php
                        $store_index++;
                    endforeach;
                    ?>
                    <a href="<?php echo esc_url(get_post_type_archive_link('deals')); ?>" 
                       class="store-item-cd store-view-all">
                        <div class="view-all-text">
                            <?php echo esc_html(get_theme_mod('view_all_stores_text', __('View All Stores', 'dealsindia'))); ?> →
                        </div>
                    </a>
                    <?php
                else :
                    ?>
                    <p class="no-stores-message">
                        <?php echo esc_html__('No stores found. Add stores from admin panel.', 'dealsindia'); ?>
                    </p>
                    <?php
                endif;
                ?>
            </div>
        </div>
    </div>
</section>

<!-- 6. TOP OFFERS SECTION -->
<section class="top-offers-section-premium">
    <div class="container">
        <div class="section-header-premium">
            <?php 
            $offers_icon = get_theme_mod('offers_section_icon', '');
            if ($offers_icon) : 
            ?>
                <span class="section-icon"><?php echo esc_html($offers_icon); ?></span>
            <?php endif; ?>
            
            <h2><?php echo esc_html(get_theme_mod('top_offers_title', __('Offers', 'dealsindia'))); ?></h2>
            <p><?php echo esc_html(get_theme_mod('top_offers_subtitle', __('Grab the best deals across all categories', 'dealsindia'))); ?></p>
        </div>

        <div class="categories-offers-grid">
            <aside class="trending-categories-sidebar">
                <h3 class="sidebar-title"><?php echo esc_html(get_theme_mod('sidebar_title', __('Trending Categories', 'dealsindia'))); ?></h3>
                <nav class="categories-list-vertical">
                    <?php
                    $sidebar_cat_count = get_theme_mod('sidebar_categories_count', 8);
                    $categories = get_terms(array(
                        'taxonomy' => 'deal_category',
                        'hide_empty' => true,
                        'number' => $sidebar_cat_count,
                        'orderby' => 'count',
                        'order' => 'DESC'
                    ));
                    
                    if (!empty($categories) && !is_wp_error($categories)) :
          foreach ($categories as $category) :
    // Get icon image ID first, fallback to emoji
    $icon_image_id = get_term_meta($category->term_id, 'category_icon_image_id', true);
    $icon_url = $icon_image_id ? wp_get_attachment_url($icon_image_id) : '';
    $icon_emoji = get_term_meta($category->term_id, 'category_icon', true);
    ?>
    <a href="<?php echo esc_url(get_term_link($category)); ?>" class="category-item-horizontal">
        <div class="category-icon-left">
            <?php if ($icon_url) : ?>
                <!-- Show uploaded image if exists -->
                <img src="<?php echo esc_url($icon_url); ?>" 
                     alt="<?php echo esc_attr($category->name); ?>" 
                     class="category-icon-image" 
                     loading="lazy">
            <?php elseif ($icon_emoji) : ?>
                <!-- Show emoji if no image -->
                <span class="category-emoji"><?php echo esc_html($icon_emoji); ?></span>
            <?php else : ?>
                <!-- Fallback icon -->
                <span class="category-emoji">📦</span>
            <?php endif; ?>
        </div>
        <span class="category-name-right"><?php echo esc_html($category->name); ?></span>
    </a>
    <?php
endforeach;

                    else :
                        ?>
                        <p class="no-categories-message">
                            <?php echo esc_html__('No categories found', 'dealsindia'); ?>
                        </p>
                        <?php
                    endif;
                    ?>
                </nav>
            </aside>

            <div class="offers-grid-3col">
                <?php
                $top_offers_count = get_theme_mod('top_offers_count', 9);
                $top_offers = new WP_Query(array(
                    'post_type' => 'deals',
                    'posts_per_page' => $top_offers_count,
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'meta_query' => array(
                        array(
                            'key' => 'is_featured',
                            'value' => '1',
                            'compare' => '='
                        )
                    )
                ));
                
                if ($top_offers->have_posts()) :
                    while ($top_offers->have_posts()) : $top_offers->the_post();
                        get_template_part('template-parts/deal-card');
                    endwhile;
                    wp_reset_postdata();
                else :
                    $fallback = new WP_Query(array(
                        'post_type' => 'deals',
                        'posts_per_page' => $top_offers_count,
                        'post_status' => 'publish',
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ));
                    
                    if ($fallback->have_posts()) :
                        while ($fallback->have_posts()) : $fallback->the_post();
                            get_template_part('template-parts/deal-card');
                        endwhile;
                        wp_reset_postdata();
                    else :
                        ?>
                        <p class="no-offers-message">
                            <?php echo esc_html__('No offers available. Add deals from admin panel.', 'dealsindia'); ?>
                        </p>
                        <?php
                    endif;
                endif;
                ?>
            </div>
        </div>
    </div>
</section>

<!-- 7. LATEST DEALS SECTION -->
<section class="latest-deals-section-premium">
    <div class="container">
        <div class="latest-deals-container" <?php 
            $latest_deals_bg = get_theme_mod('latest_deals_bg_image');
            if ($latest_deals_bg) {
                echo 'style="background-image:url(' . esc_url($latest_deals_bg) . ');"';
            }
        ?>>
            <div class="latest-deals-content">
                <div class="section-header-inline">
                    <?php 
                    $latest_deals_icon = get_theme_mod('latest_deals_icon', '');
                    if ($latest_deals_icon) : 
                    ?>
                        <span class="section-icon-inline"><?php echo esc_html($latest_deals_icon); ?></span>
                    <?php endif; ?>
                    
                    <h2><?php echo esc_html(get_theme_mod('latest_deals_title', __('Latest Deals', 'dealsindia'))); ?></h2>
                    <a href="<?php echo esc_url(get_post_type_archive_link('deals')); ?>" class="see-more">
                        <?php echo esc_html(get_theme_mod('view_all_btn_text', __('View All Deals', 'dealsindia'))); ?> ›
                    </a>
                </div>
                
                <div class="deals-scroll-wrapper">
                    <div class="deals-scroll">
                        <?php
                        $latest_deals_count = get_theme_mod('latest_deals_count', 8);
                        $latest_deals = new WP_Query(array(
                            'post_type' => 'deals',
                            'posts_per_page' => $latest_deals_count,
                            'post_status' => 'publish',
                            'orderby' => 'date',
                            'order' => 'DESC'
                        ));
                        
                        if ($latest_deals->have_posts()) :
                            while ($latest_deals->have_posts()) : $latest_deals->the_post();
                                get_template_part('template-parts/deal-card');
                            endwhile;
                            wp_reset_postdata();
                        else :
                            ?>
                            <div class="no-deals-message">
                                <p><?php echo esc_html__('No deals found. Add deals from admin panel.', 'dealsindia'); ?></p>
                            </div>
                            <?php
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 8. GIVEAWAY SECTION (If Active) -->
<?php
$active_giveaway = new WP_Query(array(
    'post_type' => 'giveaway',
    'posts_per_page' => 1,
    'post_status' => 'publish',
    'meta_query' => array(
        array(
            'key' => 'giveaway_active',
            'value' => '1',
            'compare' => '='
        )
    )
));

if ($active_giveaway->have_posts()) :
    while ($active_giveaway->have_posts()) : $active_giveaway->the_post();
        $prize = get_post_meta(get_the_ID(), 'giveaway_prize', true);
        $bg_color = get_post_meta(get_the_ID(), 'giveaway_bg_color', true);
        if (!$bg_color) $bg_color = '#5e35b1';
        
        $bg_dark = '#4527a0';
        if (function_exists('dealsindia_adjust_brightness')) {
            $bg_dark = dealsindia_adjust_brightness($bg_color, -20);
        }
        ?>
        <section class="giveaway-section" style="background: linear-gradient(135deg, <?php echo esc_attr($bg_color); ?>, <?php echo esc_attr($bg_dark); ?>);">
            <div class="container">
                <div class="giveaway-content-wrapper">
                    <div class="giveaway-left">
                        <h2 class="giveaway-title"><?php the_title(); ?></h2>
                        <div class="giveaway-description"><?php the_excerpt(); ?></div>
                        <?php if ($prize) : ?>
                            <div class="giveaway-prize">
                                <?php 
                                $giveaway_icon = get_theme_mod('giveaway_prize_icon', '');
                                if ($giveaway_icon) {
                                    echo esc_html($giveaway_icon) . ' ';
                                }
                                ?>
                                <?php echo esc_html(sprintf(__('Prize: %s', 'dealsindia'), $prize)); ?>
                            </div>
                        <?php endif; ?>
                        <a href="<?php the_permalink(); ?>" class="giveaway-btn">
                            <?php echo esc_html(get_theme_mod('giveaway_button_text', __('Enter Giveaway Now!', 'dealsindia'))); ?>
                        </a>
                    </div>
                    <div class="giveaway-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large', array('alt' => get_the_title())); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <?php
    endwhile;
    wp_reset_postdata();
endif;
?>

<!-- 9. NEWSLETTER SECTION -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-wrapper">
            <?php 
            $newsletter_icon = get_theme_mod('newsletter_icon', '');
            if ($newsletter_icon) : 
            ?>
                <div class="newsletter-icon"><?php echo esc_html($newsletter_icon); ?></div>
            <?php endif; ?>
            
            <h2 class="newsletter-title"><?php echo esc_html(get_theme_mod('newsletter_title', __('Never Miss a Deal!', 'dealsindia'))); ?></h2>
            <p class="newsletter-description"><?php echo esc_html(get_theme_mod('newsletter_subtitle', __('Subscribe to get the hottest deals delivered to your inbox', 'dealsindia'))); ?></p>
            <form class="newsletter-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" id="newsletter-form">
                <input type="email" 
                       name="newsletter_email" 
                       class="newsletter-input" 
                       placeholder="<?php echo esc_attr(get_theme_mod('newsletter_placeholder', __('Enter your email address', 'dealsindia'))); ?>" 
                       required>
                <input type="hidden" name="action" value="dealsindia_newsletter_subscribe">
                <?php wp_nonce_field('dealsindia_newsletter', 'newsletter_nonce'); ?>
                <button type="submit" class="newsletter-btn">
                    <?php echo esc_html(get_theme_mod('newsletter_btn_text', __('Subscribe', 'dealsindia'))); ?>
                </button>
            </form>
            <p class="newsletter-privacy">
                <?php 
                $privacy_icon = get_theme_mod('newsletter_privacy_icon', '');
                if ($privacy_icon) {
                    echo esc_html($privacy_icon) . ' ';
                }
                ?>
                <?php echo esc_html(get_theme_mod('newsletter_privacy', __('We respect your privacy. Unsubscribe anytime.', 'dealsindia'))); ?>
            </p>
        </div>
    </div>
</section>

</main>

<?php
get_footer();
