<?php
/**
 * Homepage Template - 100% Dynamic
 * All sections with complete functionality
 * 
 * @package ARRZONE
 * @version 7.0 - Fixed Banner Links
 */

get_header();
?>

<main class="homepage">
    
    <!-- ========================================
         1. WELCOME TEXT SECTION
         ======================================== -->
    <?php
    $welcome_text = get_theme_mod('welcome_text', 'Welcome to ' . get_bloginfo('name') . ' - India\'s Trusted Coupons, Offers & Cashback Website');
    if ($welcome_text) :
    ?>
    <section class="welcome-text-section">
        <div class="container">
            <div class="welcome-text-container">
                <h1 class="welcome-text"><?php echo esc_html($welcome_text); ?></h1>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- ========================================
         2. HERO BANNERS SECTION (CAROUSEL)
         ======================================== -->
    <section class="hero-slider-cd">
        <div class="container">
            <?php
            // Get hero banners
            $banners_query = new WP_Query(array(
                'post_type'      => 'hero_banner',
                'posts_per_page' => get_theme_mod('hero_banners_count', 10),
                'post_status'    => 'publish',
                'orderby'        => 'menu_order date',
                'order'          => 'ASC',
                'meta_query'     => array(
                    array(
                        'key'     => 'banner_active',
                        'value'   => '1',
                        'compare' => '=',
                    )
                )
            ));
            
            // Fallback if no active banners
            if (!$banners_query->have_posts()) {
                $banners_query = new WP_Query(array(
                    'post_type'      => 'hero_banner',
                    'posts_per_page' => get_theme_mod('hero_banners_count', 10),
                    'post_status'    => 'publish',
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                ));
            }
            
            if ($banners_query->have_posts()) :
                $banner_count = $banners_query->post_count;
                $banners_per_slide = 2;
            ?>
            
            <div class="hero-banners-carousel" data-carousel="hero" data-banner-count="<?php echo esc_attr($banner_count); ?>">
                <div class="hero-banners-wrapper">
                    <?php 
                    $slide_index = 0;
                    $banners_in_current_slide = 0;
                    $banner_index = 0;
                    
                    while ($banners_query->have_posts()) : $banners_query->the_post();
                        
                        // Open new slide div (every 2 banners)
                        if ($banners_in_current_slide === 0) {
                            $active_class = ($slide_index === 0) ? ' active' : '';
                            echo '<div class="hero-banners-slide' . $active_class . '" data-slide="' . $slide_index . '">';
                        }
                        
                        // Get banner data - FIXED: banner_button_link instead of banner_url
                        $banner_url = get_post_meta(get_the_ID(), 'banner_button_link', true);
                        $banner_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
                        
                        if (!$banner_url || $banner_url === '') {
                            $banner_url = get_post_type_archive_link('deals');
                        }
                        ?>
                        
                        <!-- Banner Item -->
                        <div class="hero-banner-item">
                            <a href="<?php echo esc_url($banner_url); ?>" class="hero-banner-link">
                                <?php if ($banner_image) : ?>
                                    <img src="<?php echo esc_url($banner_image); ?>" 
                                         alt="<?php echo esc_attr(get_the_title()); ?>" 
                                         class="hero-banner-image" 
                                         loading="<?php echo $banner_index === 0 ? 'eager' : 'lazy'; ?>">
                                <?php else : ?>
                                    <div class="hero-banner-placeholder">
                                        <h3><?php the_title(); ?></h3>
                                        <?php if (get_the_excerpt()) : ?>
                                            <p><?php echo esc_html(get_the_excerpt()); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                        
                        <?php
                        $banners_in_current_slide++;
                        $banner_index++;
                        
                        // Close slide div after 2 banners
                        if ($banners_in_current_slide === $banners_per_slide) {
                            echo '</div>';
                            $slide_index++;
                            $banners_in_current_slide = 0;
                        }
                        
                    endwhile;
                    
                    // Close last slide if incomplete
                    if ($banners_in_current_slide > 0) {
                        echo '</div>';
                    }
                    
                    wp_reset_postdata();
                    ?>
                </div>
                
                <!-- Carousel Controls -->
                <?php
                $total_slides = ceil($banner_count / $banners_per_slide);
                if ($total_slides > 1) :
                ?>
                    <div class="carousel-dots"></div>
                    <button class="carousel-arrow carousel-arrow-prev" aria-label="<?php echo esc_attr(get_theme_mod('hero_prev_label', __('Previous', 'dealsindia'))); ?>">‹</button>
                    <button class="carousel-arrow carousel-arrow-next" aria-label="<?php echo esc_attr(get_theme_mod('hero_next_label', __('Next', 'dealsindia'))); ?>">›</button>
                <?php endif; ?>
            </div>
            
            <?php else : ?>
                <!-- Default Placeholders -->
                <div class="hero-banners-carousel">
                    <div class="hero-banners-wrapper">
                        <div class="hero-banners-slide active">
                            <div class="hero-banner-item">
                                <a href="<?php echo esc_url(get_post_type_archive_link('deals')); ?>" class="hero-banner-link">
                                    <div class="hero-banner-placeholder hero-placeholder-1">
                                        <h3><?php echo esc_html(get_theme_mod('hero_placeholder_1_title', 'Welcome to ' . get_bloginfo('name'))); ?></h3>
                                        <p><?php echo esc_html(get_theme_mod('hero_placeholder_1_text', 'India\'s #1 Deals & Cashback Platform')); ?></p>
                                        <span class="placeholder-btn"><?php echo esc_html(get_theme_mod('hero_placeholder_1_btn', '🎁 Start Saving Today!')); ?></span>
                                    </div>
                                </a>
                            </div>
                            <div class="hero-banner-item">
                                <a href="<?php echo esc_url(get_post_type_archive_link('deals')); ?>" class="hero-banner-link">
                                    <div class="hero-banner-placeholder hero-placeholder-2">
                                        <h3><?php echo esc_html(get_theme_mod('hero_placeholder_2_title', '🔥 Hot Deals Live!')); ?></h3>
                                        <p><?php echo esc_html(get_theme_mod('hero_placeholder_2_text', 'Save Big with Exclusive Cashback Offers')); ?></p>
                                        <span class="placeholder-btn"><?php echo esc_html(get_theme_mod('hero_placeholder_2_btn', 'Browse Deals →')); ?></span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (current_user_can('manage_options')) : ?>
                    <div class="admin-notice">
                        <p><strong><?php echo esc_html(get_theme_mod('admin_notice_text', '⚠️ Admin Notice: No hero banners found.')); ?></strong> 
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=hero_banner')); ?>"><?php echo esc_html(get_theme_mod('admin_notice_link_text', 'Click here to add banners')); ?></a></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- ========================================
         3. THREE STEPS SECTION
         ======================================== -->
    <section class="three-steps">
        <div class="container">
            <div class="three-steps-container">
                
                <h2 class="steps-title">
                    <?php echo esc_html(get_theme_mod('three_steps_title', __('How It Works', 'dealsindia'))); ?>
                </h2>
                
                <div class="steps-grid">
                    <?php
                    $steps = get_theme_mod('three_steps_items', array());
                    
                    if (!empty($steps)) :
                        foreach ($steps as $index => $step) :
                            $number = $index + 1;
                            $icon = isset($step['icon']) ? $step['icon'] : '';
                            $title = isset($step['title']) ? $step['title'] : '';
                            $description = isset($step['description']) ? $step['description'] : '';
                            ?>
                            <div class="step-item">
                                <div class="step-number"><?php echo esc_html($number); ?></div>
                                <?php if ($icon) : ?>
                                    <div class="step-icon"><?php echo esc_html($icon); ?></div>
                                <?php endif; ?>
                                <h3><?php echo esc_html($title); ?></h3>
                                <p><?php echo esc_html($description); ?></p>
                            </div>
                            <?php
                        endforeach;
                    else :
                        // Default steps
                        ?>
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-icon">🔍</div>
                            <h3><?php echo esc_html(get_theme_mod('step_1_title', __('Browse Deals', 'dealsindia'))); ?></h3>
                            <p><?php echo esc_html(get_theme_mod('step_1_desc', __('Discover exclusive offers from top brands', 'dealsindia'))); ?></p>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-icon">🎟️</div>
                            <h3><?php echo esc_html(get_theme_mod('step_2_title', __('Get Coupon Code', 'dealsindia'))); ?></h3>
                            <p><?php echo esc_html(get_theme_mod('step_2_desc', __('Click to reveal and copy the promo code', 'dealsindia'))); ?></p>
                        </div>
                        
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-icon">💰</div>
                            <h3><?php echo esc_html(get_theme_mod('step_3_title', __('Save Money', 'dealsindia'))); ?></h3>
                            <p><?php echo esc_html(get_theme_mod('step_3_desc', __('Apply code at checkout and enjoy savings!', 'dealsindia'))); ?></p>
                        </div>
                        <?php
                    endif;
                    ?>
                </div>
                
            </div>
        </div>
    </section>
    
   <!-- ========================================
     4. HOT PICKS SECTION - CAMPAIGN AWARE
     ======================================== -->
<section class="hot-picks">
    <div class="container">
        <div class="hot-picks-container" <?php 
            $hot_picks_bg = get_theme_mod('hot_picks_bg_image');
            if ($hot_picks_bg) {
                echo 'style="background-image:url(' . esc_url($hot_picks_bg) . ');"';
            }
        ?>>
            <div class="hot-picks-content">
                <?php
                // Get active featured campaigns
                $today = current_time('Y-m-d');
                $active_campaigns = get_terms(array(
                    'taxonomy' => 'campaign',
                    'hide_empty' => true,
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'campaign_is_featured',
                            'value' => '1',
                            'compare' => '='
                        ),
                        array(
                            'relation' => 'OR',
                            array(
                                'key' => 'campaign_start_date',
                                'value' => $today,
                                'compare' => '<=',
                                'type' => 'DATE'
                            ),
                            array(
                                'key' => 'campaign_start_date',
                                'compare' => 'NOT EXISTS'
                            )
                        ),
                        array(
                            'relation' => 'OR',
                            array(
                                'key' => 'campaign_end_date',
                                'value' => $today,
                                'compare' => '>=',
                                'type' => 'DATE'
                            ),
                            array(
                                'key' => 'campaign_end_date',
                                'compare' => 'NOT EXISTS'
                            )
                        )
                    )
                ));
                
                // Build query args based on active campaigns
                $hot_picks_count = get_theme_mod('hot_picks_count', 12);
                $hot_picks_args = array(
                    'post_type' => 'deals',
                    'posts_per_page' => $hot_picks_count,
                    'post_status' => 'publish',
                    'orderby' => 'date',
                    'order' => 'DESC',
                );
                
                // Determine "See More" link and title
                $see_more_url = get_post_type_archive_link('deals');
                $section_title = get_theme_mod('hot_picks_title', __('🔥 Festival Hot Picks!', 'dealsindia'));
                
                if (!empty($active_campaigns) && !is_wp_error($active_campaigns)) {
                    // If single active campaign - show its deals and link to it
                    if (count($active_campaigns) === 1) {
                        $campaign = $active_campaigns[0];
                        $campaign_icon = get_term_meta($campaign->term_id, 'campaign_icon', true);
                        $campaign_tagline = get_term_meta($campaign->term_id, 'campaign_tagline', true);
                        
                        // Update title with campaign name
                        $section_title = ($campaign_icon ? $campaign_icon . ' ' : '🔥 ') . $campaign->name;
                        
                        // Link to specific campaign
                        $see_more_url = get_term_link($campaign);
                        
                        // Show deals from this campaign
                        $hot_picks_args['tax_query'] = array(
                            array(
                                'taxonomy' => 'campaign',
                                'field' => 'term_id',
                                'terms' => $campaign->term_id
                            )
                        );
                    } else {
                        // Multiple active campaigns - show mixed deals
                        $campaign_ids = wp_list_pluck($active_campaigns, 'term_id');
                        
                        $hot_picks_args['tax_query'] = array(
                            array(
                                'taxonomy' => 'campaign',
                                'field' => 'term_id',
                                'terms' => $campaign_ids,
                                'operator' => 'IN'
                            )
                        );
                        
                        // Link to all campaigns page
                        $see_more_url = home_url('/campaigns/');
                    }
                } else {
                    // No active campaigns - fallback to featured/trending deals
                    $hot_picks_args['meta_query'] = array(
                        'relation' => 'OR',
                        array(
                            'key' => 'deal_is_featured',
                            'value' => '1',
                            'compare' => '='
                        ),
                        array(
                            'key' => 'deal_is_trending',
                            'value' => '1',
                            'compare' => '='
                        )
                    );
                }
                
                // Run query
                $hot_picks = new WP_Query($hot_picks_args);
                
                // Fallback to latest deals if no results
                if (!$hot_picks->have_posts()) {
                    $hot_picks = new WP_Query(array(
                        'post_type' => 'deals',
                        'posts_per_page' => $hot_picks_count,
                        'post_status' => 'publish',
                        'orderby' => 'date',
                        'order' => 'DESC'
                    ));
                }
                ?>
                
                <div class="section-header-inline">
                    <h2><?php echo esc_html($section_title); ?></h2>
                    <a href="<?php echo esc_url($see_more_url); ?>" class="see-more">
                        <?php echo esc_html(get_theme_mod('hot_picks_button_text', __('See More', 'dealsindia'))); ?> ›
                    </a>
                </div>
                
                <div class="deals-scroll-wrapper">
                    <div class="deals-scroll">
                        <?php
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

    
    <!-- ========================================
     5. TOP STORES SECTION - FIXED
     ======================================== -->
<?php
$stores_count = get_theme_mod('top_stores_count', 12);
$stores = get_terms(array(
    'taxonomy'   => 'store',
    'hide_empty' => true,
    'number'     => $stores_count,
    'orderby'    => 'count',
    'order'      => 'DESC'
));

if (!empty($stores) && !is_wp_error($stores)) :
?>
<section class="top-stores-section-premium">
    <div class="container">
        <div class="top-stores-container">
            
            <div class="section-header-premium">
                <?php 
                $stores_icon = get_theme_mod('stores_section_icon', '🏪'); 
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
                    $store_index = 0;
                    foreach ($stores as $store) :
                        $logo_id = get_term_meta($store->term_id, 'store_logo_id', true);
                        $logo = $logo_id ? wp_get_attachment_url($logo_id) : '';
                        $cashback = get_term_meta($store->term_id, 'store_cashback', true);
                        $is_featured = ($store_index === 0);
                        
                        // Generate gradient colors for fallback
                        $color1 = '#' . substr(md5($store->name), 0, 6);
                        $color2 = '#' . substr(md5($store->name . 'x'), 0, 6);
                        $initials = strtoupper(substr($store->name, 0, 2));
                        ?>
                        <a href="<?php echo esc_url(get_term_link($store)); ?>" 
                           class="store-item-cd<?php echo $is_featured ? ' store-featured' : ''; ?>"
                           aria-label="<?php echo esc_attr(sprintf(__('View deals from %s', 'dealsindia'), $store->name)); ?>">
                            
                            <!-- Store Logo -->
                            <?php if ($logo) : ?>
                                <div class="store-logo-cd">
                                    <img src="<?php echo esc_url($logo); ?>" 
                                         alt="<?php echo esc_attr($store->name); ?>" 
                                         loading="lazy"
                                         width="50"
                                         height="50">
                                </div>
                            <?php else : ?>
                                <div class="store-logo-fallback" 
                                     style="background: linear-gradient(135deg, <?php echo esc_attr($color1); ?>, <?php echo esc_attr($color2); ?>);">
                                    <?php echo esc_html($initials); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Store Name -->
                            <div class="store-name-cd">
                                <?php echo esc_html($store->name); ?>
                            </div>
                            
                            <!-- Cashback (Optional) -->
                            <?php if ($cashback) : ?>
                                <div class="store-cashback-cd">
                                    <?php 
                                    $cashback_icon = get_theme_mod('cashback_icon', '💰');
                                    if ($cashback_icon) : 
                                    ?>
                                        <span class="cashback-icon"><?php echo esc_html($cashback_icon); ?></span>
                                    <?php endif; ?>
                                    <span><?php echo esc_html(sprintf(__('Upto %s', 'dealsindia'), $cashback)); ?></span>
                                </div>
                            <?php endif; ?>
                        </a>
                        <?php
                        $store_index++;
                    endforeach;
                    ?>
                    
                    <!-- View All Stores Card -->
                    <a href="<?php echo esc_url(get_post_type_archive_link('deals')); ?>" 

                       class="store-item-cd store-view-all"
                       aria-label="<?php esc_attr_e('View all stores', 'dealsindia'); ?>">
                        <div class="view-all-text">
                            <?php echo esc_html(get_theme_mod('view_all_stores_text', __('View All Stores', 'dealsindia'))); ?> →
                        </div>
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</section>
<?php endif; ?>

    
   <!-- ========================================
     6. TOP OFFERS BY CATEGORY - ENHANCED
     ======================================== -->
<?php
$categories_count = get_theme_mod('trending_categories_count', 8);
$categories = get_terms(array(
    'taxonomy'   => 'deal-category',
    'hide_empty' => true,
    'number'     => $categories_count,
    'orderby'    => 'count',
    'order'      => 'DESC'
));

if (!empty($categories) && !is_wp_error($categories)) :
?>
<section class="top-offers-section-premium">
    <div class="container">
        <div class="top-offers-container">
            
            <div class="section-header-premium">
                <?php 
                $offers_icon = get_theme_mod('offers_section_icon', '🎯');
                if ($offers_icon) : 
                ?>
                    <span class="section-icon"><?php echo esc_html($offers_icon); ?></span>
                <?php endif; ?>
                
                <h2><?php echo esc_html(get_theme_mod('top_offers_title', __('Top Offers by Category', 'dealsindia'))); ?></h2>
                <p><?php echo esc_html(get_theme_mod('top_offers_subtitle', __('Browse trending deals from popular categories', 'dealsindia'))); ?></p>
            </div>
            
            <div class="categories-offers-grid">
                <!-- Categories Sidebar with View All -->
                <aside class="trending-categories-sidebar">
                    <div class="sidebar-header-with-cta">
                        <h3 class="sidebar-title">
                            <?php echo esc_html(get_theme_mod('trending_categories_title', __('🔥 Trending Categories', 'dealsindia'))); ?>
                        </h3>
                        <a href="<?php echo esc_url(home_url('/categories/')); ?>" class="sidebar-view-all">
                            <?php echo esc_html(get_theme_mod('view_all_categories_text', __('View All', 'dealsindia'))); ?> ›
                        </a>
                    </div>
                    
                    <div class="categories-list-vertical">
                        <?php
                        foreach ($categories as $category) :
                            $icon_id = get_term_meta($category->term_id, 'category_icon_id', true);
                            $icon_url = $icon_id ? wp_get_attachment_url($icon_id) : '';
                            $emoji = get_term_meta($category->term_id, 'category_icon', true);
                            ?>
                            <a href="<?php echo esc_url(get_term_link($category)); ?>" 
                               class="category-item-horizontal"
                               aria-label="<?php echo esc_attr(sprintf(__('View %s deals', 'dealsindia'), $category->name)); ?>">
                                <div class="category-icon-left">
                                    <?php if ($icon_url) : ?>
                                        <img src="<?php echo esc_url($icon_url); ?>" 
                                             alt="<?php echo esc_attr($category->name); ?>" 
                                             class="category-icon-image" 
                                             loading="lazy"
                                             width="24"
                                             height="24">
                                    <?php elseif ($emoji) : ?>
                                        <span class="category-emoji"><?php echo esc_html($emoji); ?></span>
                                    <?php else : ?>
                                        <span class="category-emoji">📦</span>
                                    <?php endif; ?>
                                </div>
                                <span class="category-name-right">
                                    <?php echo esc_html($category->name); ?>
                                </span>
                                <span class="category-arrow">›</span>
                            </a>
                            <?php
                        endforeach;
                        ?>
                    </div>
                </aside>
                
                <!-- 🔥 FIXED: Added proper wrapper structure for scrolling -->
                <div class="offers-grid-wrapper">
                    <div class="offers-grid-header">
                        <h3 class="offers-grid-title">
                            <?php echo esc_html(get_theme_mod('featured_offers_title', __('Featured Offers', 'dealsindia'))); ?>
                        </h3>
                        <a href="<?php echo esc_url(get_post_type_archive_link('deals')); ?>" class="offers-view-all">
                            <?php echo esc_html(get_theme_mod('view_all_offers_text', __('View All Offers', 'dealsindia'))); ?> ›
                        </a>
                    </div>
                    
                    <!-- 🔥 THIS IS THE FIX: offers-grid-3col must be DIRECT CHILD of offers-grid-wrapper -->
                    <div class="offers-grid-3col">
                        <?php
                        $offers_count = get_theme_mod('top_offers_count', 6);
                        $offers_query = new WP_Query(array(
                            'post_type'      => 'deals',
                            'posts_per_page' => $offers_count,
                            'post_status'    => 'publish',
                            'orderby'        => 'date',
                            'order'          => 'DESC',
                            'meta_query'     => array(
                                array(
                                    'key'     => 'deal_is_featured',
                                    'value'   => '1',
                                    'compare' => '='
                                )
                            )
                        ));
                        
                        // Fallback to latest deals
                        if (!$offers_query->have_posts()) {
                            $offers_query = new WP_Query(array(
                                'post_type'      => 'deals',
                                'posts_per_page' => $offers_count,
                                'post_status'    => 'publish',
                                'orderby'        => 'date',
                                'order'          => 'DESC',
                            ));
                        }
                        
                        if ($offers_query->have_posts()) :
                            while ($offers_query->have_posts()) : $offers_query->the_post();
                                get_template_part('template-parts/deal-card');
                            endwhile;
                            wp_reset_postdata();
                        else :
                            ?>
                            <div class="no-offers-message">
                                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                                </svg>
                                <p><?php echo esc_html(get_theme_mod('no_offers_message', __('No featured offers available right now.', 'dealsindia'))); ?></p>
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
<?php endif; ?>

    <!-- ========================================
         7. LATEST DEALS SECTION
         ======================================== -->
    <?php
    $latest_deals_count = get_theme_mod('latest_deals_count', 12);
    $latest_deals = new WP_Query(array(
        'post_type'      => 'deals',
        'posts_per_page' => $latest_deals_count,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    ));
    
    if ($latest_deals->have_posts()) :
    ?>
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
                            while ($latest_deals->have_posts()) : $latest_deals->the_post();
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
    <?php endif; ?>
    
    <!-- ========================================
     8. GIVEAWAYS SECTION - COMPLETE FIXED
     ======================================== -->
<?php
$giveaways = new WP_Query(array(
    'post_type'      => 'giveaway',  // ✅ FIXED: Singular (not 'giveaways')
    'posts_per_page' => get_theme_mod('giveaways_count', 6),
    'post_status'    => 'publish',
    'orderby'        => 'date',
    'order'          => 'DESC'
));

if ($giveaways->have_posts()) :
?>
<section class="giveaways-section-premium">
    <div class="container">
        <div class="section-header-premium">
            <?php 
            $giveaway_icon = get_theme_mod('giveaway_section_icon', '🎁');
            if ($giveaway_icon) : 
            ?>
                <span class="section-icon"><?php echo esc_html($giveaway_icon); ?></span>
            <?php endif; ?>
            
            <h2><?php echo esc_html(get_theme_mod('giveaway_section_title', __('🎁 Active Giveaways', 'arrzone'))); ?></h2>
            <p><?php echo esc_html(get_theme_mod('giveaway_section_subtitle', __('Win amazing prizes!', 'arrzone'))); ?></p>
        </div>

        <div class="giveaways-grid">
            <?php
            while ($giveaways->have_posts()) : $giveaways->the_post();
                $prize = get_post_meta(get_the_ID(), 'giveaway_prize', true);
                $end_date = get_post_meta(get_the_ID(), 'giveaway_end_date', true);
                $bg_color = get_post_meta(get_the_ID(), 'giveaway_bg_color', true);
                if (!$bg_color) $bg_color = get_theme_mod('giveaway_default_bg', '#667eea');
                
                $prize_icon = get_theme_mod('giveaway_prize_icon', '🏆');
                $deadline_icon = get_theme_mod('giveaway_deadline_icon', '⏰');
                ?>
                <article class="giveaway-card" style="background: linear-gradient(135deg, <?php echo esc_attr($bg_color); ?> 0%, <?php echo esc_attr($bg_color); ?>dd 100%);">
                    <div class="giveaway-card-content">
                        <h3 class="giveaway-card-title"><?php the_title(); ?></h3>
                        
                        <?php if ($prize) : ?>
                            <div class="giveaway-prize">
                                <?php echo esc_html($prize_icon); ?> <?php echo esc_html($prize); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($end_date) : ?>
                            <div class="giveaway-deadline">
                                <?php echo esc_html($deadline_icon); ?> <?php echo esc_html(sprintf(__('Ends: %s', 'arrzone'), date('M d, Y', strtotime($end_date)))); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- ✅ FIXED: Using get_permalink() instead of the_permalink() -->
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="giveaway-participate-btn">
                            <?php echo esc_html(get_theme_mod('giveaway_btn_text', __('Participate Now', 'arrzone'))); ?> →
                        </a>
                    </div>
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="giveaway-card-image">
                            <?php the_post_thumbnail('medium', array('loading' => 'lazy')); ?>
                        </div>
                    <?php endif; ?>
                </article>
                <?php
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
<?php endif; ?>


    
</main>

<?php get_footer(); ?>
