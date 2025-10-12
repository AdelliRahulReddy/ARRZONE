<?php
/**
 * Deal Type Archive Template - Enhanced Spider-Verse
 * Shows deals filtered by type with AJAX filtering
 * URL: /deals/coupons/, /deals/price-errors/, etc.
 * 
 * @package DealsIndia
 * @version 2.0 - Enhanced Archive System
 */

get_header();

// Get current deal type term
$term = get_queried_object();

// Parse emoji from name (e.g., "🎟️ Coupons" → emoji: "🎟️", name: "Coupons")
$name_parts = explode(' ', $term->name, 2);
$emoji = (mb_strlen($name_parts[0]) === 1 || preg_match('/[\x{1F600}-\x{1F64F}]/u', $name_parts[0])) ? $name_parts[0] : '';
$display_name = isset($name_parts[1]) ? $name_parts[1] : $term->name;

// Get term description
$term_description = term_description($term->term_id);

// Query setup
global $wp_query;
$total_deals = $wp_query->found_posts;
?>

<div class="deals-archive-page deal-type-archive-enhanced">
    
    <!-- Deal Type Hero Header -->
    <div class="deal-type-hero-header">
        <div class="container">
            <div class="deal-type-hero-content">
                <?php if ($emoji): ?>
                <div class="deal-type-hero-icon">
                    <span class="deal-type-emoji-large"><?php echo esc_html($emoji); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="deal-type-hero-info">
                    <h1 class="deal-type-hero-title"><?php echo esc_html($display_name); ?></h1>
                    
                    <?php if ($term_description): ?>
                    <p class="deal-type-hero-description"><?php echo wp_kses_post($term_description); ?></p>
                    <?php endif; ?>
                    
                    <div class="deal-type-hero-stats">
                        <span class="deal-type-stat-item">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" stroke-width="2"/>
                                <line x1="9" y1="2" x2="9" y2="7" stroke-width="2"/>
                                <line x1="15" y1="2" x2="15" y2="7" stroke-width="2"/>
                            </svg>
                            <strong><?php echo number_format($total_deals); ?></strong>
                            <?php echo $total_deals == 1 ? esc_html__('Deal', 'dealsindia') : esc_html__('Deals', 'dealsindia'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        
        <!-- Breadcrumb -->
        <div class="archive-header">
            <?php dealsindia_breadcrumb(); ?>
        </div>

        <!-- Enhanced Layout: Sidebar + Content -->
        <div class="archive-layout-enhanced">
            
            <!-- Filter Sidebar (Context: Deal Type Archive) -->
            <?php get_template_part('template-parts/filter-sidebar'); ?>

            <!-- Main Content Area -->
            <div class="archive-main-content">
                
                <!-- Results Info -->
                <div class="archive-results-info">
                    <div id="dealsResultsCount">
                        <?php 
                        printf(
                            esc_html__('Showing %d of %d %s', 'dealsindia'),
                            $wp_query->post_count,
                            $total_deals,
                            esc_html($display_name)
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
                            <h3><?php esc_html_e('No Deals Available', 'dealsindia'); ?></h3>
                            <p>
                                <?php 
                                printf(
                                    esc_html__('No %s are available right now. Check back soon!', 'dealsindia'),
                                    '<strong>' . esc_html($display_name) . '</strong>'
                                ); 
                                ?>
                            </p>
                            <a href="<?php echo esc_url(home_url('/deals/')); ?>" class="btn-primary">
                                <?php esc_html_e('Browse All Deals', 'dealsindia'); ?>
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
                $type_categories = get_terms(array(
                    'taxonomy'   => 'deal_category',
                    'hide_empty' => true,
                    'object_ids' => wp_list_pluck($wp_query->posts, 'ID'),
                    'number'     => 12,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ));

                if ($type_categories && !is_wp_error($type_categories) && count($type_categories) > 0) :
                ?>
                <div class="archive-section-browse">
                    <div class="browse-section-header">
                        <h2 class="browse-section-title">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z" stroke-width="2"/>
                            </svg>
                            <?php 
                            printf(
                                esc_html__('Browse %s by Category', 'dealsindia'),
                                esc_html($display_name)
                            ); 
                            ?>
                        </h2>
                    </div>
                    <div class="browse-categories-grid">
                        <?php foreach ($type_categories as $category): 
                            $icon = get_term_meta($category->term_id, 'category_icon', true);
                            $category_link = add_query_arg('deal_type', $term->slug, get_term_link($category));
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
                $type_stores = get_terms(array(
                    'taxonomy'   => 'store',
                    'hide_empty' => true,
                    'object_ids' => wp_list_pluck($wp_query->posts, 'ID'),
                    'number'     => 12,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ));

                if ($type_stores && !is_wp_error($type_stores) && count($type_stores) > 0) :
                ?>
                <div class="archive-section-browse">
                    <div class="browse-section-header">
                        <h2 class="browse-section-title">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-width="2"/>
                                <polyline points="9 22 9 12 15 12 15 22" stroke-width="2"/>
                            </svg>
                            <?php 
                            printf(
                                esc_html__('Top Stores for %s', 'dealsindia'),
                                esc_html($display_name)
                            ); 
                            ?>
                        </h2>
                    </div>
                    <div class="browse-stores-grid">
                        <?php foreach ($type_stores as $store): 
                            $logo = get_term_meta($store->term_id, 'store_logo', true);
                            $cashback = get_term_meta($store->term_id, 'store_cashback_rate', true);
                            $store_link = add_query_arg('deal_type', $term->slug, get_term_link($store));
                        ?>
                        <a href="<?php echo esc_url($store_link); ?>" class="store-card-browse">
                            <?php if ($logo): ?>
                            <div class="store-logo-browse">
                                <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($store->name); ?>">
                            </div>
                            <?php endif; ?>
                            <h3 class="store-name-browse"><?php echo esc_html($store->name); ?></h3>
                            <div class="store-meta-browse">
                                <span class="store-deals-count"><?php echo esc_html($store->count); ?> <?php esc_html_e('deals', 'dealsindia'); ?></span>
                                <?php if ($cashback): ?>
                                <span class="store-cashback-badge"><?php echo esc_html($cashback); ?>% 🎁</span>
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
