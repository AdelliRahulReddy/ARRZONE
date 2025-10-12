<?php
/**
 * Store Archive Template - Full Redesign v2.0
 * Complete premium design with tabs, filters, and advanced features
 * 
 * @package DealsIndia
 * @version 2.0
 */

get_header();

$term = get_queried_object();
$store_id = $term->term_id;
$cashback = get_term_meta($store_id, 'store_cashback', true);
$logo_id = get_term_meta($store_id, 'store_logo_id', true);
$logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
$banner_id = get_term_meta($store_id, 'store_banner_id', true);
$banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';

// Get current tab and filters
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'all';
$category_filter = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
$sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'latest';

// Calculate store statistics
$total_deals_query = new WP_Query(array(
    'post_type' => 'deals',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'tax_query' => array(
        array(
            'taxonomy' => 'store',
            'field' => 'term_id',
            'terms' => $store_id,
        ),
    ),
));
$total_deals = $total_deals_query->found_posts;

// Count active coupons
$coupon_deals = new WP_Query(array(
    'post_type' => 'deals',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'tax_query' => array(
        array(
            'taxonomy' => 'store',
            'field' => 'term_id',
            'terms' => $store_id,
        ),
    ),
    'meta_query' => array(
        array(
            'key' => 'coupon_code',
            'value' => '',
            'compare' => '!=',
        ),
    ),
));
$total_coupons = $coupon_deals->found_posts;

// Count expiring soon deals (next 7 days)
$expiring_soon = new WP_Query(array(
    'post_type' => 'deals',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'tax_query' => array(
        array(
            'taxonomy' => 'store',
            'field' => 'term_id',
            'terms' => $store_id,
        ),
    ),
    'meta_query' => array(
        array(
            'key' => 'deal_expiry_date',
            'value' => array(
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s', strtotime('+7 days'))
            ),
            'compare' => 'BETWEEN',
            'type' => 'DATETIME',
        ),
    ),
));
$expiring_count = $expiring_soon->found_posts;

// Calculate dynamic rating (based on deal count and freshness)
function calculate_store_rating($total_deals) {
    if ($total_deals >= 100) return 4.8;
    if ($total_deals >= 50) return 4.5;
    if ($total_deals >= 25) return 4.3;
    if ($total_deals >= 10) return 4.0;
    return 3.8;
}
$store_rating = calculate_store_rating($total_deals);

// Hero background style
$hero_style = $banner_url 
    ? "background-image: url('" . esc_url($banner_url) . "'); background-size: cover; background-position: center;" 
    : "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);";
?>

<!-- Breadcrumb Navigation -->
<div class="breadcrumb-container">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Home
            </a>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo esc_url(home_url('/store/')); ?>">Stores</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current"><?php echo esc_html($term->name); ?></span>
        </nav>
    </div>
</div>

<!-- Enhanced Store Hero Section -->
<section class="store-hero-premium-v2" style="<?php echo $hero_style; ?>">
    <?php if ($banner_url) : ?>
        <div class="store-hero-overlay-v2"></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="store-hero-content-v2">
            
            <!-- Store Logo Circle -->
            <div class="store-logo-circle-v2">
                <?php if ($logo_url) : ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($term->name); ?>" loading="lazy">
                <?php else : ?>
                    <div class="store-logo-fallback-v2">
                        <span><?php echo strtoupper(substr($term->name, 0, 2)); ?></span>
                    </div>
                <?php endif; ?>
                <div class="store-verified-badge">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
            
            <!-- Store Info -->
            <div class="store-info-v2">
                <div class="store-badges-row">
                    <span class="store-badge-primary">✓ Verified Partner</span>
                    <?php if ($cashback) : ?>
                        <span class="store-badge-cashback">⚡ <?php echo esc_html($cashback); ?> Cashback</span>
                    <?php endif; ?>
                </div>
                
                <h1 class="store-title-v2"><?php echo esc_html($term->name); ?></h1>
                
                <div class="store-rating-row">
                    <div class="store-rating-stars">
                        <?php
                        $full_stars = floor($store_rating);
                        $half_star = ($store_rating - $full_stars) >= 0.5;
                        
                        for ($i = 0; $i < $full_stars; $i++) {
                            echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
                        }
                        if ($half_star) {
                            echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2v15.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
                        }
                        ?>
                    </div>
                    <span class="store-rating-text"><?php echo number_format($store_rating, 1); ?>/5</span>
                    <span class="store-rating-count">(<?php echo number_format($total_deals); ?> deals)</span>
                </div>
                
                <!-- Store Statistics Cards -->
                <div class="store-stats-cards">
                    <div class="stat-card">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M20 6h-2.18c.11-.31.18-.65.18-1a2.996 2.996 0 0 0-5.5-1.65l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2z" fill="currentColor"/>
                        </svg>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo number_format($total_deals); ?></span>
                            <span class="stat-label">Active Deals</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo number_format($total_coupons); ?></span>
                            <span class="stat-label">Coupon Codes</span>
                        </div>
                    </div>
                    
                    <?php if ($expiring_count > 0) : ?>
                    <div class="stat-card stat-card-urgent">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="stat-info">
                            <span class="stat-value"><?php echo number_format($expiring_count); ?></span>
                            <span class="stat-label">Expiring Soon</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Store Description with Read More -->
                <?php if ($term->description) : ?>
                    <div class="store-description-v2">
                        <div class="description-short" id="desc-short">
                            <?php echo esc_html(wp_trim_words($term->description, 20, '...')); ?>
                            <?php if (str_word_count($term->description) > 20) : ?>
                                <button class="read-more-btn" onclick="toggleDescription()">Read More</button>
                            <?php endif; ?>
                        </div>
                        <?php if (str_word_count($term->description) > 20) : ?>
                        <div class="description-full" id="desc-full" style="display: none;">
                            <?php echo esc_html($term->description); ?>
                            <button class="read-more-btn" onclick="toggleDescription()">Show Less</button>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Tabbed Navigation Section -->
<section class="store-tabs-section">
    <div class="container">
        <nav class="store-tabs-nav" role="tablist">
            <a href="<?php echo esc_url(remove_query_arg(array('tab', 'paged'))); ?>" 
               class="tab-link <?php echo $current_tab === 'all' ? 'active' : ''; ?>"
               role="tab">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                All Deals
                <span class="tab-count"><?php echo $total_deals; ?></span>
            </a>
            
            <a href="<?php echo esc_url(add_query_arg('tab', 'hot')); ?>" 
               class="tab-link <?php echo $current_tab === 'hot' ? 'active' : ''; ?>"
               role="tab">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Hot Picks
            </a>
            
            <?php if ($expiring_count > 0) : ?>
            <a href="<?php echo esc_url(add_query_arg('tab', 'expiring')); ?>" 
               class="tab-link <?php echo $current_tab === 'expiring' ? 'active' : ''; ?>"
               role="tab">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Expiring Soon
                <span class="tab-badge"><?php echo $expiring_count; ?></span>
            </a>
            <?php endif; ?>
            
            <a href="<?php echo esc_url(add_query_arg('tab', 'coupons')); ?>" 
               class="tab-link <?php echo $current_tab === 'coupons' ? 'active' : ''; ?>"
               role="tab">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Coupons Only
                <span class="tab-count"><?php echo $total_coupons; ?></span>
            </a>
        </nav>
    </div>
</section>

<!-- Main Content: Categories + Deals Grid -->
<section class="store-content-section-v2">
    <div class="container">
        <div class="store-content-grid-v2">
            
            <!-- LEFT: Sticky Categories Sidebar -->
            <aside class="categories-sidebar-v2">
                <div class="sidebar-sticky">
                    <div class="sidebar-header-v2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <h3>Browse by Category</h3>
                    </div>
                    
                    <!-- All Categories Link -->
                    <a href="<?php echo esc_url(remove_query_arg('category')); ?>" 
                       class="category-item-v2 <?php echo empty($category_filter) ? 'active' : ''; ?>">
                        <span class="category-icon-v2">🏷️</span>
                        <span class="category-name-v2">All Categories</span>
                        <span class="category-count-v2"><?php echo $total_deals; ?></span>
                    </a>
                    
                    <div class="categories-list-v2">
                        <?php
                        // Get categories that have deals from this store
                        $categories = get_terms(array(
                            'taxonomy' => 'deal_category',
                            'hide_empty' => true,
                            'number' => 15,
                            'orderby' => 'count',
                            'order' => 'DESC',
                        ));
                        
                        foreach ($categories as $category) :
                            // Check if this store has deals in this category
                            $cat_deal_count = new WP_Query(array(
                                'post_type' => 'deals',
                                'posts_per_page' => 1,
                                'fields' => 'ids',
                                'tax_query' => array(
                                    'relation' => 'AND',
                                    array(
                                        'taxonomy' => 'store',
                                        'field' => 'term_id',
                                        'terms' => $store_id,
                                    ),
                                    array(
                                        'taxonomy' => 'deal_category',
                                        'field' => 'term_id',
                                        'terms' => $category->term_id,
                                    ),
                                ),
                            ));
                            
                            if ($cat_deal_count->found_posts === 0) continue;
                            
                            $cat_link = add_query_arg('category', $category->slug);
                            $icon_image_id = get_term_meta($category->term_id, 'category_icon_image_id', true);
                            $icon_image_url = $icon_image_id ? wp_get_attachment_url($icon_image_id) : '';
                            $icon_emoji = get_term_meta($category->term_id, 'category_icon', true);
                            $icon_emoji = $icon_emoji ? $icon_emoji : '🏷️';
                            $is_active = ($category_filter === $category->slug);
                        ?>
                            <a href="<?php echo esc_url($cat_link); ?>" 
                               class="category-item-v2 <?php echo $is_active ? 'active' : ''; ?>">
                                <span class="category-icon-v2">
                                    <?php if ($icon_image_url) : ?>
                                        <img src="<?php echo esc_url($icon_image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" loading="lazy">
                                    <?php else : ?>
                                        <?php echo esc_html($icon_emoji); ?>
                                    <?php endif; ?>
                                </span>
                                <span class="category-name-v2"><?php echo esc_html($category->name); ?></span>
                                <span class="category-count-v2"><?php echo $cat_deal_count->found_posts; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
            
            <!-- RIGHT: Deals Grid with Toolbar -->
            <div class="deals-area-v2">
                
                <!-- Deals Toolbar -->
                <div class="deals-toolbar-v2">
                    <div class="toolbar-left">
                        <h2 class="deals-count-title">
                            <span class="count-number"><?php echo number_format($total_deals); ?></span>
                            Deals Available
                        </h2>
                    </div>
                    
                    <div class="toolbar-right">
                        <label for="store-deals-sort" class="sort-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M3 6h18M7 12h14M11 18h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            Sort by:
                        </label>
                        <form method="get" class="sort-form">
                            <?php if ($current_tab !== 'all') : ?>
                                <input type="hidden" name="tab" value="<?php echo esc_attr($current_tab); ?>">
                            <?php endif; ?>
                            <?php if ($category_filter) : ?>
                                <input type="hidden" name="category" value="<?php echo esc_attr($category_filter); ?>">
                            <?php endif; ?>
                            <select id="store-deals-sort" name="sort" onchange="this.form.submit()" class="sort-select">
                                <option value="latest" <?php selected($sort_by, 'latest'); ?>>Latest First</option>
                                <option value="popular" <?php selected($sort_by, 'popular'); ?>>Most Popular</option>
                                <option value="ending" <?php selected($sort_by, 'ending'); ?>>Ending Soon</option>
                                <option value="discount" <?php selected($sort_by, 'discount'); ?>>Best Discount</option>
                            </select>
                        </form>
                    </div>
                </div>
                
                <!-- Deals Grid -->
                <div class="deals-grid-v2">
                    <?php
                    // Build query args based on current tab and filters
                    $deals_args = array(
                        'post_type' => 'deals',
                        'posts_per_page' => 16,
                        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'store',
                                'field' => 'term_id',
                                'terms' => $store_id,
                            ),
                        ),
                    );
                    
                    // Add category filter
                    if ($category_filter) {
                        $deals_args['tax_query'][] = array(
                            'taxonomy' => 'deal_category',
                            'field' => 'slug',
                            'terms' => $category_filter,
                        );
                    }
                    
                    // Add tab-specific filters
                    switch ($current_tab) {
                        case 'hot':
                            $deals_args['meta_query'] = array(
                                array(
                                    'key' => 'is_trending',
                                    'value' => '1',
                                ),
                            );
                            break;
                        
                        case 'expiring':
                            $deals_args['meta_query'] = array(
                                array(
                                    'key' => 'deal_expiry_date',
                                    'value' => array(
                                        date('Y-m-d H:i:s'),
                                        date('Y-m-d H:i:s', strtotime('+7 days'))
                                    ),
                                    'compare' => 'BETWEEN',
                                    'type' => 'DATETIME',
                                ),
                            );
                            $deals_args['orderby'] = 'meta_value';
                            $deals_args['meta_key'] = 'deal_expiry_date';
                            $deals_args['order'] = 'ASC';
                            break;
                        
                        case 'coupons':
                            $deals_args['meta_query'] = array(
                                array(
                                    'key' => 'coupon_code',
                                    'value' => '',
                                    'compare' => '!=',
                                ),
                            );
                            break;
                    }
                    
                    // Add sorting
                    if ($current_tab !== 'expiring') { // Don't override expiring sort
                        switch ($sort_by) {
                            case 'popular':
                                $deals_args['meta_key'] = 'deal_clicks_count';
                                $deals_args['orderby'] = 'meta_value_num';
                                $deals_args['order'] = 'DESC';
                                break;
                            
                            case 'ending':
                                $deals_args['meta_key'] = 'deal_expiry_date';
                                $deals_args['orderby'] = 'meta_value';
                                $deals_args['order'] = 'ASC';
                                $deals_args['meta_query'] = array(
                                    array(
                                        'key' => 'deal_expiry_date',
                                        'value' => date('Y-m-d H:i:s'),
                                        'compare' => '>=',
                                        'type' => 'DATETIME',
                                    ),
                                );
                                break;
                            
                            case 'discount':
                                $deals_args['meta_key'] = 'deal_discount_percentage';
                                $deals_args['orderby'] = 'meta_value_num';
                                $deals_args['order'] = 'DESC';
                                break;
                            
                            default: // latest
                                $deals_args['orderby'] = 'date';
                                $deals_args['order'] = 'DESC';
                        }
                    }
                    
                    $deals_query = new WP_Query($deals_args);
                    
                    if ($deals_query->have_posts()) :
                        while ($deals_query->have_posts()) : $deals_query->the_post();
                            get_template_part('template-parts/deal-card-large');
                        endwhile;
                    else :
                    ?>
                        <div class="no-deals-found-v2">
                            <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
                                <circle cx="60" cy="60" r="50" stroke="#e0e0e0" stroke-width="4"/>
                                <path d="M45 50h30M45 60h30M45 70h20" stroke="#e0e0e0" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                            <h3>No Deals Found</h3>
                            <p>
                                <?php
                                if ($current_tab !== 'all') {
                                    echo 'No deals available in this category. ';
                                }
                                ?>
                                We're constantly adding new deals from <?php echo esc_html($term->name); ?>. Check back soon!
                            </p>
                            <a href="<?php echo esc_url(remove_query_arg(array('tab', 'category', 'sort'))); ?>" class="btn-view-all-deals">
                                View All <?php echo esc_html($term->name); ?> Deals
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($deals_query->max_num_pages > 1) : ?>
                    <div class="pagination-v2">
                        <?php
                        $pagination_args = array(
                            'total' => $deals_query->max_num_pages,
                            'current' => max(1, get_query_var('paged')),
                            'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Previous',
                            'next_text' => 'Next <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                            'type' => 'list',
                            'end_size' => 2,
                            'mid_size' => 2,
                        );
                        
                        // Preserve query args in pagination
                        if ($current_tab !== 'all') {
                            $pagination_args['add_args'] = array('tab' => $current_tab);
                        }
                        if ($category_filter) {
                            $pagination_args['add_args']['category'] = $category_filter;
                        }
                        if ($sort_by !== 'latest') {
                            $pagination_args['add_args']['sort'] = $sort_by;
                        }
                        
                        echo paginate_links($pagination_args);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php wp_reset_postdata(); ?>
                
            </div>
        </div>
    </div>
</section>

<!-- Similar Stores Section -->
<?php
$similar_stores = get_terms(array(
    'taxonomy' => 'store',
    'hide_empty' => true,
    'number' => 12,
    'exclude' => $store_id,
    'orderby' => 'count',
    'order' => 'DESC',
));

if (!empty($similar_stores)) :
?>
<section class="similar-stores-v2">
    <div class="container">
        <div class="section-header-v2">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h2>Also Check These Stores</h2>
            <p>More amazing deals from top brands</p>
        </div>
        
        <div class="stores-grid-v2">
            <?php
            $store_count = 0;
            foreach ($similar_stores as $store) :
                if ($store_count >= 12) break;
                
                $store_link = get_term_link($store);
                $store_cashback = get_term_meta($store->term_id, 'store_cashback', true);
                $store_logo_id = get_term_meta($store->term_id, 'store_logo_id', true);
                $store_logo_url = $store_logo_id ? wp_get_attachment_url($store_logo_id) : '';
                
                // Count deals for this store
                $store_deal_count = new WP_Query(array(
                    'post_type' => 'deals',
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'store',
                            'field' => 'term_id',
                            'terms' => $store->term_id,
                        ),
                    ),
                ));
            ?>
                <a href="<?php echo esc_url($store_link); ?>" class="store-card-v2">
                    <div class="store-card-logo-v2">
                        <?php if ($store_logo_url) : ?>
                            <img src="<?php echo esc_url($store_logo_url); ?>" alt="<?php echo esc_attr($store->name); ?>" loading="lazy">
                        <?php else : ?>
                            <span class="store-card-fallback-v2">
                                <?php echo strtoupper(substr($store->name, 0, 2)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="store-card-info-v2">
                        <h3 class="store-card-name-v2"><?php echo esc_html($store->name); ?></h3>
                        <p class="store-card-deals-v2"><?php echo number_format($store_deal_count->found_posts); ?> Deals</p>
                    </div>
                    <?php if ($store_cashback) : ?>
                        <div class="store-card-cashback-v2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/>
                            </svg>
                            <?php echo esc_html($store_cashback); ?>
                        </div>
                    <?php endif; ?>
                </a>
            <?php 
                $store_count++;
            endforeach; 
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- JavaScript for Description Toggle -->
<script>
function toggleDescription() {
    var short = document.getElementById('desc-short');
    var full = document.getElementById('desc-full');
    
    if (full.style.display === 'none') {
        short.style.display = 'none';
        full.style.display = 'block';
    } else {
        short.style.display = 'block';
        full.style.display = 'none';
    }
}
</script>

<?php get_footer(); ?>
