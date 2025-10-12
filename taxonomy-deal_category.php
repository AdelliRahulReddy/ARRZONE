<?php
/**
 * Category Archive Template - Premium Design with Filters
 * Shows all deals in a specific category
 * URL: /deals-category/{category-slug}/
 * 
 * @package DealsIndia
 * @version 3.0 - With Spider-Verse Filters
 */

get_header();

// Get current category term
$term = get_queried_object();

// Get category meta
$category_icon = get_term_meta($term->term_id, 'category_icon', true);
$category_description = term_description($term->term_id);

// Query setup
global $wp_query;
$total_deals = $wp_query->found_posts;
?>

<div class="category-archive-page">
    
    <!-- Category Hero Header -->
    <div class="category-hero-header">
        <div class="container">
            <div class="category-hero-content">
                <div class="category-hero-icon">
                    <?php if ($category_icon): ?>
                        <?php if (filter_var($category_icon, FILTER_VALIDATE_URL)): ?>
                            <img src="<?php echo esc_url($category_icon); ?>" alt="<?php echo esc_attr($term->name); ?>">
                        <?php else: ?>
                            <span class="category-emoji-large"><?php echo esc_html($category_icon); ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="category-emoji-large">🏷️</span>
                    <?php endif; ?>
                </div>
                
                <div class="category-hero-info">
                    <h1 class="category-hero-title"><?php echo esc_html($term->name); ?></h1>
                    
                    <?php if ($category_description): ?>
                    <p class="category-hero-description"><?php echo wp_kses_post($category_description); ?></p>
                    <?php endif; ?>
                    
                    <div class="category-hero-stats">
                        <span class="category-stat-item">
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
        <div class="category-breadcrumb">
            <?php dealsindia_breadcrumb(); ?>
        </div>

        <!-- SPIDER-VERSE LAYOUT: Sidebar + Content -->
        <div class="archive-layout-enhanced">
            
            <!-- ✅ FILTER SIDEBAR (Spider-Verse Hierarchy) -->
            <?php get_template_part('template-parts/filter-sidebar'); ?>

            <!-- MAIN CONTENT AREA -->
            <div class="archive-main-content">
                
                <!-- Toolbar -->
                <div class="category-toolbar">
                    <div class="category-results">
                        <strong><?php echo number_format($total_deals); ?></strong>
                        <?php 
                        printf(
                            esc_html__('%s deals found', 'dealsindia'),
                            esc_html($term->name)
                        ); 
                        ?>
                    </div>
                    
                    <div class="category-sort">
                        <label for="sortDeals"><?php esc_html_e('Sort by:', 'dealsindia'); ?></label>
                        <select id="sortDeals" name="orderby">
                            <option value="date"><?php esc_html_e('Latest', 'dealsindia'); ?></option>
                            <option value="popularity"><?php esc_html_e('Popular', 'dealsindia'); ?></option>
                            <option value="discount"><?php esc_html_e('Discount', 'dealsindia'); ?></option>
                            <option value="price_low"><?php esc_html_e('Price: Low to High', 'dealsindia'); ?></option>
                            <option value="price_high"><?php esc_html_e('Price: High to Low', 'dealsindia'); ?></option>
                        </select>
                    </div>
                </div>

                <!-- Deals Grid -->
                <div class="category-deals-grid" id="dealsGridContainer">
                    <?php
                    if (have_posts()) :
                        while (have_posts()) : the_post();
                            get_template_part('template-parts/deal-card');
                        endwhile;
                    else :
                        ?>
                        <div class="no-deals-found">
                            <div class="no-deals-found-icon">📭</div>
                            <h3><?php esc_html_e('No Deals Available', 'dealsindia'); ?></h3>
                            <p>
                                <?php 
                                printf(
                                    esc_html__('No %s deals are available right now. Check back soon!', 'dealsindia'),
                                    esc_html($term->name)
                                ); 
                                ?>
                            </p>
                            <a href="<?php echo esc_url(home_url('/deals/')); ?>">
                                <?php esc_html_e('Browse All Deals', 'dealsindia'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($wp_query->max_num_pages > 1): ?>
                <div class="category-pagination">
                    <?php
                    echo paginate_links(array(
                        'total'     => $wp_query->max_num_pages,
                        'current'   => max(1, get_query_var('paged')),
                        'format'    => '?paged=%#%',
                        'show_all'  => false,
                        'end_size'  => 1,
                        'mid_size'  => 2,
                        'prev_next' => true,
                        'prev_text' => '←',
                        'next_text' => '→',
                        'type'      => 'list',
                    ));
                    ?>
                </div>
                <?php endif; ?>

                <!-- Browse by Store Section -->
                <?php
                $category_stores = get_terms(array(
                    'taxonomy'   => 'store',
                    'hide_empty' => true,
                    'object_ids' => wp_list_pluck($wp_query->posts, 'ID'),
                    'number'     => 12,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                ));

                if ($category_stores && !is_wp_error($category_stores) && count($category_stores) > 0) :
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
                                esc_html($term->name)
                            ); 
                            ?>
                        </h2>
                        <a href="<?php echo esc_url(home_url('/stores/')); ?>" class="browse-view-all">
                            <?php esc_html_e('View All Stores', 'dealsindia'); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="9 18 15 12 9 6" stroke-width="2"/>
                            </svg>
                        </a>
                    </div>
                    
                    <div class="browse-stores-grid">
                        <?php foreach ($category_stores as $store): 
                            $logo = get_term_meta($store->term_id, 'store_logo', true);
                            $cashback = get_term_meta($store->term_id, 'store_cashback_rate', true);
                            $store_link = add_query_arg('category', $term->slug, get_term_link($store));
                        ?>
                        <a href="<?php echo esc_url($store_link); ?>" class="store-card-browse">
                            <div class="store-logo-browse">
                                <?php if ($logo): ?>
                                    <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($store->name); ?>">
                                <?php else: ?>
                                    <span><?php echo esc_html(substr($store->name, 0, 1)); ?></span>
                                <?php endif; ?>
                            </div>
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
</div><!-- .category-archive-page -->

<!-- Mobile Filter Toggle Button -->
<button class="mobile-filter-toggle-btn" id="mobileFilterToggle">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <line x1="4" y1="6" x2="20" y2="6" stroke-width="2"/>
        <line x1="4" y1="12" x2="20" y2="12" stroke-width="2"/>
        <line x1="4" y1="18" x2="14" y2="18" stroke-width="2"/>
    </svg>
    <?php esc_html_e('Filters', 'dealsindia'); ?>
</button>

<?php get_footer(); ?>
