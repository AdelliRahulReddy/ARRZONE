<?php
/**
 * Store Archive Template - Premium Design with Banner Support
 * Full homepage-style layout with custom store banners
 */

get_header();

$term = get_queried_object();
$store_id = $term->term_id;
$cashback = get_term_meta($store_id, 'store_cashback', true);
$logo_id = get_term_meta($store_id, 'store_logo_id', true);
$logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
$banner_id = get_term_meta($store_id, 'store_banner_id', true);
$banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';

// Hero background style
$hero_style = $banner_url 
    ? "background-image: url('" . esc_url($banner_url) . "'); background-size: cover; background-position: center;" 
    : "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);";
?>

<!-- Premium Store Hero Section with Banner -->
<section class="store-hero-premium" style="<?php echo $hero_style; ?>">
    <?php if ($banner_url) : ?>
        <div class="store-hero-overlay"></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="store-hero-content-premium">
            
            <!-- Store Logo Circle -->
            <div class="store-logo-circle">
                <?php if ($logo_url) : ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($term->name); ?>">
                <?php else : ?>
                    <div class="store-logo-fallback">
                        <span><?php echo strtoupper(substr($term->name, 0, 2)); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Store Info -->
            <div class="store-info-premium">
                <div class="store-badge">Verified Store</div>
                <h1 class="store-title-premium">
                    <?php echo esc_html($term->name); ?>
                </h1>
                <p class="store-subtitle">Exclusive Coupons, Deals & Cashback Offers</p>
                
                <div class="store-stats">
                    <?php if ($cashback) : ?>
                        <div class="store-stat-item">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" fill="currentColor"/>
                            </svg>
                            <div>
                                <span class="stat-value">Upto <?php echo esc_html($cashback); ?></span>
                                <span class="stat-label">Cashback</span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="store-stat-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M20 6h-2.18c.11-.31.18-.65.18-1a2.996 2.996 0 0 0-5.5-1.65l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 12 7.4l3.38 4.6L17 10.83 14.92 8H20v6z" fill="currentColor"/>
                            </svg>
                            <div>
                                <span class="stat-value"><?php echo $wp_query->found_posts; ?>+</span>
                                <span class="stat-label">Active Deals</span>
                            </div>
                        </div>
                        
                        <div class="store-stat-item">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" fill="currentColor"/>
                            </svg>
                            <div>
                                <span class="stat-value">4.8/5</span>
                                <span class="stat-label">Rating</span>
                            </div>
                        </div>
                </div>
                
                <?php if ($term->description) : ?>
                    <p class="store-description-short"><?php echo esc_html(wp_trim_words($term->description, 20)); ?></p>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</section>

<!-- Hot Picks from Store -->
<?php
$hot_picks = new WP_Query(array(
    'post_type' => 'deals',
    'posts_per_page' => 10,
    'tax_query' => array(
        array(
            'taxonomy' => 'store',
            'field' => 'term_id',
            'terms' => $store_id,
        ),
    ),
    'meta_query' => array(
        array('key' => 'is_trending', 'value' => '1')
    )
));

if (!$hot_picks->have_posts()) {
    $hot_picks = new WP_Query(array(
        'post_type' => 'deals',
        'posts_per_page' => 10,
        'tax_query' => array(
            array(
                'taxonomy' => 'store',
                'field' => 'term_id',
                'terms' => $store_id,
            ),
        ),
        'orderby' => 'date',
        'order' => 'DESC'
    ));
}

if ($hot_picks->have_posts()) :
?>
<section class="store-hot-picks-premium">
    <div class="container">
        <div class="section-header-premium">
            <div class="section-icon">🔥</div>
            <h2>Hot Picks from <?php echo esc_html($term->name); ?></h2>
            <p>Trending deals you don't want to miss</p>
        </div>
        
        <div class="deals-scroll-wrapper">
            <div class="deals-scroll">
                <?php while ($hot_picks->have_posts()) : $hot_picks->the_post(); ?>
                    <?php get_template_part('template-parts/deal-card'); ?>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>
<?php 
wp_reset_postdata();
endif; 
?>

<!-- Categories + Deals Section -->
<section class="store-categories-offers-premium">
    <div class="container">
        <div class="categories-offers-grid-premium">
            
            <!-- LEFT: Categories Sidebar -->
            <aside class="categories-sidebar-premium">
                <div class="sidebar-header">
                    <h3>Browse by Category</h3>
                    <p>in <?php echo esc_html($term->name); ?></p>
                </div>
                
                <div class="categories-list-premium">
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'deal_category',
                        'hide_empty' => true,
                        'number' => 12,
                    ));
                    
                    foreach ($categories as $category) :
                        $cat_check = new WP_Query(array(
                            'post_type' => 'deals',
                            'posts_per_page' => 1,
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
                        
                        if (!$cat_check->have_posts()) continue;
                        
                        $cat_link = add_query_arg('category', $category->slug, get_term_link($term));
                        $icon_image_id = get_term_meta($category->term_id, 'category_icon_image_id', true);
                        $icon_image_url = $icon_image_id ? wp_get_attachment_url($icon_image_id) : '';
                        $icon_emoji = get_term_meta($category->term_id, 'category_icon', true);
                        $icon_emoji = $icon_emoji ? $icon_emoji : '🏷️';
                        $is_active = (isset($_GET['category']) && $_GET['category'] === $category->slug);
                    ?>
                        <a href="<?php echo esc_url($cat_link); ?>" class="category-item-premium <?php echo $is_active ? 'active' : ''; ?>">
                            <span class="category-icon-premium">
                                <?php if ($icon_image_url) : ?>
                                    <img src="<?php echo esc_url($icon_image_url); ?>" alt="<?php echo esc_attr($category->name); ?>">
                                <?php else : ?>
                                    <?php echo esc_html($icon_emoji); ?>
                                <?php endif; ?>
                            </span>
                            <span class="category-name-premium"><?php echo esc_html($category->name); ?></span>
                            <span class="category-count"><?php echo $cat_check->found_posts; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>
            
            <!-- RIGHT: Deals Grid -->
            <div class="deals-area-premium">
                <div class="deals-toolbar">
                    <div class="deals-count-premium">
                        <span class="count-number"><?php echo $wp_query->found_posts; ?></span>
                        <span class="count-text">Deals Available</span>
                    </div>
                    
                    <div class="deals-sort-premium">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M3 6h12M6 9h9M9 12h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        <select id="store-deals-sort">
                            <option value="date-desc">Latest First</option>
                            <option value="date-asc">Oldest First</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                        </select>
                    </div>
                </div>
                
                <div class="deals-grid-premium">
                    <?php
                    $category_filter = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
                    
                    $deals_args = array(
                        'post_type' => 'deals',
                        'posts_per_page' => 12,
                        'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'store',
                                'field' => 'term_id',
                                'terms' => $store_id,
                            ),
                        ),
                    );
                    
                    if ($category_filter) {
                        $deals_args['tax_query'][] = array(
                            'taxonomy' => 'deal_category',
                            'field' => 'slug',
                            'terms' => $category_filter,
                        );
                    }
                    
                    $deals_query = new WP_Query($deals_args);
                    
                    if ($deals_query->have_posts()) :
                        while ($deals_query->have_posts()) : $deals_query->the_post();
                            get_template_part('template-parts/deal-card-large');
                        endwhile;
                    else :
                    ?>
                        <div class="no-deals-found-premium">
                            <svg width="100" height="100" viewBox="0 0 100 100" fill="none">
                                <circle cx="50" cy="50" r="45" stroke="#e0e0e0" stroke-width="4"/>
                                <path d="M35 40h30M35 50h30M35 60h20" stroke="#e0e0e0" stroke-width="4" stroke-linecap="round"/>
                            </svg>
                            <h3>No Deals Found</h3>
                            <p>We're constantly adding new deals. Check back soon!</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($deals_query->max_num_pages > 1) : ?>
                    <div class="pagination-premium">
                        <?php
                        echo paginate_links(array(
                            'total' => $deals_query->max_num_pages,
                            'prev_text' => '← Previous',
                            'next_text' => 'Next →',
                            'type' => 'list',
                        ));
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php wp_reset_postdata(); ?>
            </div>
            
        </div>
    </div>
</section>

<!-- Similar Stores -->
<?php
$similar_stores = get_terms(array(
    'taxonomy' => 'store',
    'hide_empty' => true,
    'number' => 8,
    'exclude' => $store_id,
    'orderby' => 'count',
    'order' => 'DESC',
));

if (!empty($similar_stores)) :
?>
<section class="similar-stores-premium">
    <div class="container">
        <div class="section-header-premium">
            <div class="section-icon">🛍️</div>
            <h2>Also Check These Stores</h2>
            <p>More amazing deals from top brands</p>
        </div>
        
        <div class="stores-grid-premium">
            <?php
            $store_index = 0;
            foreach ($similar_stores as $store) :
                if ($store_index >= 8) break;
                
                $store_link = get_term_link($store);
                $store_cashback = get_term_meta($store->term_id, 'store_cashback', true);
                $store_logo_id = get_term_meta($store->term_id, 'store_logo_id', true);
                $store_logo_url = $store_logo_id ? wp_get_attachment_url($store_logo_id) : '';
            ?>
                <a href="<?php echo esc_url($store_link); ?>" class="store-card-premium">
                    <div class="store-card-logo">
                        <?php if ($store_logo_url) : ?>
                            <img src="<?php echo esc_url($store_logo_url); ?>" alt="<?php echo esc_attr($store->name); ?>">
                        <?php else : ?>
                            <span class="store-card-fallback"><?php echo strtoupper(substr($store->name, 0, 2)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="store-card-name"><?php echo esc_html($store->name); ?></div>
                    <?php if ($store_cashback) : ?>
                        <div class="store-card-cashback">Upto <?php echo esc_html($store_cashback); ?></div>
                    <?php endif; ?>
                </a>
            <?php 
                $store_index++;
            endforeach; 
            ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
