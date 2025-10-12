<?php
/**
 * Deals Archive - Enhanced Spider-Verse Archive
 * URL: /deals/
 * Features: AJAX Filtering, Sidebar, Category/Store Grids
 * 
 * @package DealsIndia
 * @version 2.1 - Fixed Store Archive Link
 */

get_header();

// Get initial query for total count
global $wp_query;
$total_deals = $wp_query->found_posts;
?>

<div class="deals-archive-page">
    <div class="container">
        
        <!-- Archive Header -->
        <div class="archive-header">
            <?php dealsindia_breadcrumb(); ?>
            <h1 class="archive-title"><?php esc_html_e('Browse All Deals', 'dealsindia'); ?></h1>
            <p class="archive-description">
                <?php esc_html_e('Discover exclusive deals, coupons, and offers from 3000+ stores across India', 'dealsindia'); ?>
            </p>
            <div class="archive-count">
                <strong><?php echo number_format($total_deals); ?></strong>
                <?php esc_html_e('deals available right now', 'dealsindia'); ?>
            </div>
        </div>

        <!-- Enhanced Layout: Sidebar + Content -->
        <div class="archive-layout-enhanced">
            
            <!-- Filter Sidebar -->
            <?php get_template_part('template-parts/filter-sidebar'); ?>

            <!-- Main Content Area -->
            <div class="archive-main-content">
                
                <!-- Results Info -->
                <div class="archive-results-info">
                    <div id="dealsResultsCount">
                        <?php 
                        printf(
                            esc_html__('Showing %d of %d deals', 'dealsindia'),
                            $wp_query->post_count,
                            $total_deals
                        ); 
                        ?>
                    </div>
                </div>

                <!-- Deals Grid Container (AJAX Target) -->
                <div class="archive-deals-grid" id="dealsGridContainer">
                    <?php
                    if (have_posts()) :
                        while (have_posts()) : the_post();
                            get_template_part('template-parts/deal-card');
                        endwhile;
                    else :
                        ?>
                        <div class="no-deals-found">
                            <div class="no-deals-icon">
                                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                    <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                                </svg>
                            </div>
                            <h3><?php esc_html_e('No Deals Found', 'dealsindia'); ?></h3>
                            <p><?php esc_html_e('We couldn\'t find any deals at the moment. Check back soon!', 'dealsindia'); ?></p>
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary">
                                <?php esc_html_e('Back to Homepage', 'dealsindia'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Load More Button (AJAX Pagination) -->
                <?php if ($wp_query->max_num_pages > 1): ?>
                <button id="loadMoreDealsBtn" class="btn-load-more" data-page="1" data-max-pages="<?php echo esc_attr($wp_query->max_num_pages); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Load More Deals', 'dealsindia'); ?>
                </button>
                <?php endif; ?>

                <!-- Browse by Category Section -->
                <?php
                $categories = get_terms(array(
                    'taxonomy'   => 'deal_category',
                    'hide_empty' => true,
                    'number'     => 12,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ));

                if ($categories && !is_wp_error($categories) && count($categories) > 0) :
                ?>
                <div class="archive-section-browse">
                    <div class="browse-section-header">
                        <h2 class="browse-section-title">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Browse by Category', 'dealsindia'); ?>
                        </h2>
                        <a href="<?php echo esc_url(home_url('/deals-category/')); ?>" class="browse-view-all">
                            <?php esc_html_e('View All Categories', 'dealsindia'); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="5" y1="12" x2="19" y2="12" stroke-width="2"/>
                                <polyline points="12 5 19 12 12 19" stroke-width="2"/>
                            </svg>
                        </a>
                    </div>
                    <div class="browse-categories-grid">
                        <?php foreach ($categories as $category): 
                            $icon = get_term_meta($category->term_id, 'category_icon', true);
                            $category_link = get_term_link($category);
                        ?>
                        <a href="<?php echo esc_url($category_link); ?>" class="category-card-browse">
                            <?php if ($icon): ?>
                            <div class="category-icon-browse">
                                <?php if (filter_var($icon, FILTER_VALIDATE_URL)): ?>
                                    <img src="<?php echo esc_url($icon); ?>" alt="<?php echo esc_attr($category->name); ?>">
                                <?php else: ?>
                                    <span class="category-emoji"><?php echo esc_html($icon); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <h3 class="category-name-browse"><?php echo esc_html($category->name); ?></h3>
                            <span class="category-count-browse"><?php echo esc_html($category->count); ?> <?php esc_html_e('deals', 'dealsindia'); ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Browse by Store Section -->
                <?php
                $stores = get_terms(array(
                    'taxonomy'   => 'store',
                    'hide_empty' => true,
                    'number'     => 12,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ));

                if ($stores && !is_wp_error($stores) && count($stores) > 0) :
                ?>
                <div class="archive-section-browse">
                    <div class="browse-section-header">
                        <h2 class="browse-section-title">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-width="2"/>
                                <polyline points="9 22 9 12 15 12 15 22" stroke-width="2"/>
                            </svg>
                            <?php esc_html_e('Browse by Store', 'dealsindia'); ?>
                        </h2>
                        <!-- ✅ FIXED: Changed from /deals-store/ to /store/ -->
                        <a href="<?php echo esc_url(home_url('/deals-store/')); ?>" class="browse-view-all">

                            <?php esc_html_e('View All Stores', 'dealsindia'); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <line x1="5" y1="12" x2="19" y2="12" stroke-width="2"/>
                                <polyline points="12 5 19 12 12 19" stroke-width="2"/>
                            </svg>
                        </a>
                    </div>
                    <div class="browse-stores-grid">
                        <?php foreach ($stores as $store): 
                            $logo = get_term_meta($store->term_id, 'store_logo', true);
                            $cashback = get_term_meta($store->term_id, 'store_cashback_rate', true);
                            $store_link = get_term_link($store);
                        ?>
                        <a href="<?php echo esc_url($store_link); ?>" class="store-card-browse">
                            <?php if ($logo): ?>
                            <div class="store-logo-browse">
                                <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($store->name); ?>">
                            </div>
                            <?php else: ?>
                            <div class="store-logo-browse store-logo-fallback">
                                <?php echo esc_html(strtoupper(substr($store->name, 0, 1))); ?>
                            </div>
                            <?php endif; ?>
                            <h3 class="store-name-browse"><?php echo esc_html($store->name); ?></h3>
                            <div class="store-meta-browse">
                                <span class="store-deals-count"><?php echo esc_html($store->count); ?> <?php esc_html_e('deals', 'dealsindia'); ?></span>
                                <?php if ($cashback): ?>
                                <span class="store-cashback-badge">💰 <?php echo esc_html($cashback); ?>%</span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- .archive-main-content -->

        </div><!-- .archive-layout-enhanced -->

    </div><!-- .container -->
</div><!-- .deals-archive-page -->

<?php get_footer(); ?>
