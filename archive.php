<?php
if (!defined('ABSPATH')) exit; 
/**
 * Archive Template - Enhanced "Every Page is a Homepage" Architecture
 * 
 * @package ARRZONE
 * @version 7.2 - Fixed Hero Background Images
 */


get_header();


// Detect page type
$page_type = 'deals_archive';
$current_term = null;
$term_name = '';
$term_description = '';
$term_meta = array();


if (is_tax('campaign')) {
    $page_type = 'campaign';
    $current_term = get_queried_object();
    $term_name = $current_term->name;
    $term_description = term_description($current_term->term_id);
    
    $term_meta['icon'] = get_term_meta($current_term->term_id, 'campaign_icon', true);
    $term_meta['banner_id'] = get_term_meta($current_term->term_id, 'campaign_banner_id', true);
    $term_meta['banner_url'] = $term_meta['banner_id'] ? wp_get_attachment_url($term_meta['banner_id']) : '';
    $term_meta['color'] = get_term_meta($current_term->term_id, 'campaign_color', true);
    $term_meta['start_date'] = get_term_meta($current_term->term_id, 'campaign_start_date', true);
    $term_meta['end_date'] = get_term_meta($current_term->term_id, 'campaign_end_date', true);
    $term_meta['tagline'] = get_term_meta($current_term->term_id, 'campaign_tagline', true);
    $term_meta['is_featured'] = get_term_meta($current_term->term_id, 'campaign_is_featured', true);
    
    $today = current_time('Y-m-d');
    $term_meta['status'] = 'Active';
    if ($term_meta['start_date'] && $today < $term_meta['start_date']) {
        $term_meta['status'] = 'Upcoming';
    } elseif ($term_meta['end_date'] && $today > $term_meta['end_date']) {
        $term_meta['status'] = 'Expired';
    }
    
} elseif (is_tax('deal-category')) {
    $page_type = 'category';
    $current_term = get_queried_object();
    $term_name = $current_term->name;
    $term_description = term_description($current_term->term_id);
    
    $term_meta['icon'] = get_term_meta($current_term->term_id, 'category_icon', true);
    $term_meta['icon_image_id'] = get_term_meta($current_term->term_id, 'category_icon_image_id', true);
    $term_meta['icon_image_url'] = $term_meta['icon_image_id'] ? wp_get_attachment_url($term_meta['icon_image_id']) : '';
    $term_meta['color'] = get_term_meta($current_term->term_id, 'category_color', true);
    $term_meta['banner_id'] = get_term_meta($current_term->term_id, 'category_banner_id', true);
    $term_meta['banner_url'] = $term_meta['banner_id'] ? wp_get_attachment_url($term_meta['banner_id']) : '';
    $term_meta['is_featured'] = get_term_meta($current_term->term_id, 'category_is_featured', true);
    $term_meta['featured_deals'] = get_term_meta($current_term->term_id, 'category_featured_deals', true);
    $term_meta['bg_gradient'] = get_term_meta($current_term->term_id, 'category_bg_gradient', true);
    
} elseif (is_tax('store')) {
    $page_type = 'store';
    $current_term = get_queried_object();
    $term_name = $current_term->name;
    $term_description = term_description($current_term->term_id);
    
    $term_meta['logo_id'] = get_term_meta($current_term->term_id, 'store_logo_id', true);
    $term_meta['logo_url'] = $term_meta['logo_id'] ? wp_get_attachment_url($term_meta['logo_id']) : '';
    $term_meta['banner_id'] = get_term_meta($current_term->term_id, 'store_banner_id', true);
    $term_meta['banner_url'] = $term_meta['banner_id'] ? wp_get_attachment_url($term_meta['banner_id']) : '';
    $term_meta['cashback'] = get_term_meta($current_term->term_id, 'store_cashback', true);
    $term_meta['is_featured'] = get_term_meta($current_term->term_id, 'store_is_featured', true);
    $term_meta['website_url'] = get_term_meta($current_term->term_id, 'store_website_url', true);
    $term_meta['bg_color'] = get_term_meta($current_term->term_id, 'store_bg_color', true);
    $term_meta['featured_deals'] = get_term_meta($current_term->term_id, 'store_featured_deals', true);
    
} elseif (is_tax('deal-type')) {
    $page_type = 'dealtype';
    $current_term = get_queried_object();
    $term_name = $current_term->name;
    $term_description = term_description($current_term->term_id);
    
    $term_meta['icon'] = get_term_meta($current_term->term_id, 'dealtype_icon', true);
    $term_meta['icon_image_id'] = get_term_meta($current_term->term_id, 'dealtype_icon_image_id', true);
    $term_meta['icon_image_url'] = $term_meta['icon_image_id'] ? wp_get_attachment_url($term_meta['icon_image_id']) : '';
    $term_meta['color'] = get_term_meta($current_term->term_id, 'dealtype_color', true);
    $term_meta['banner_id'] = get_term_meta($current_term->term_id, 'dealtype_banner_id', true);
    $term_meta['banner_url'] = $term_meta['banner_id'] ? wp_get_attachment_url($term_meta['banner_id']) : '';
    $term_meta['featured_deals'] = get_term_meta($current_term->term_id, 'dealtype_featured_deals', true);
    $term_meta['bg_gradient'] = get_term_meta($current_term->term_id, 'dealtype_bg_gradient', true);
}


global $wp_query;
$total_deals = $wp_query->found_posts;


?>


<div class="archive-deals <?php echo esc_attr($page_type); ?>-page">


<?php
// Hero Sections
if ($page_type === 'campaign') :
?>
    <section class="campaign-hero-section campaign-status-<?php echo esc_attr(strtolower($term_meta['status'])); ?>">
        <?php if (!empty($term_meta['banner_url'])) : ?>
            <div class="hero-bg-image hero-bg-<?php echo esc_attr($page_type); ?>" style="background-image: url('<?php echo esc_url($term_meta['banner_url']); ?>');"></div>
        <?php endif; ?>
        
        <div class="container">
            <div class="campaign-hero-content">
                <div class="campaign-status-badge campaign-<?php echo esc_attr(strtolower($term_meta['status'])); ?>">
                    <?php 
                    if ($term_meta['status'] === 'Active') {
                        echo '🟢 ' . esc_html__('Live Now', 'dealsindia');
                    } elseif ($term_meta['status'] === 'Upcoming') {
                        echo '🟡 ' . esc_html__('Coming Soon', 'dealsindia');
                    } else {
                        echo '🔴 ' . esc_html__('Ended', 'dealsindia');
                    }
                    ?>
                </div>
                
                <div class="campaign-header">
                    <?php if (!empty($term_meta['icon'])) : ?>
                        <div class="campaign-icon-large"><?php echo esc_html($term_meta['icon']); ?></div>
                    <?php endif; ?>
                    <h1 class="campaign-hero-title"><?php echo esc_html($term_name); ?></h1>
                </div>
                
                <?php if (!empty($term_meta['tagline'])) : ?>
                    <p class="campaign-tagline"><?php echo esc_html($term_meta['tagline']); ?></p>
                <?php endif; ?>
                
                <?php if ($term_description) : ?>
                    <div class="campaign-description"><?php echo wp_kses_post($term_description); ?></div>
                <?php endif; ?>
                
                <div class="campaign-hero-meta">
                    <div class="hero-stat">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/><line x1="16" y1="2" x2="16" y2="6" stroke-width="2"/><line x1="8" y1="2" x2="8" y2="6" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/></svg>
                        <strong><?php echo number_format($total_deals); ?></strong>
                        <?php echo $total_deals == 1 ? esc_html__('Deal', 'dealsindia') : esc_html__('Deals', 'dealsindia'); ?>
                    </div>
                    
                    <?php if (!empty($term_meta['start_date']) && !empty($term_meta['end_date'])) : ?>
                        <div class="hero-stat campaign-dates">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><polyline points="12 6 12 12 16 14" stroke-width="2"/></svg>
                            <?php 
                            if ($term_meta['status'] === 'Active') {
                                echo esc_html__('Ends: ', 'dealsindia') . date_i18n('M d, Y', strtotime($term_meta['end_date']));
                            } elseif ($term_meta['status'] === 'Upcoming') {
                                echo esc_html__('Starts: ', 'dealsindia') . date_i18n('M d, Y', strtotime($term_meta['start_date']));
                            } else {
                                echo esc_html__('Ended: ', 'dealsindia') . date_i18n('M d, Y', strtotime($term_meta['end_date']));
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>


<?php elseif ($page_type === 'store') : ?>
    <section class="store-hero-section" <?php if (!empty($term_meta['bg_color'])) : ?>style="background: <?php echo esc_attr($term_meta['bg_color']); ?>;"<?php endif; ?>>
        <?php if (!empty($term_meta['banner_url'])) : ?>
            <div class="hero-bg-image hero-bg-<?php echo esc_attr($page_type); ?>" style="background-image: url('<?php echo esc_url($term_meta['banner_url']); ?>');"></div>
        <?php endif; ?>
        <div class="container">
            <div class="store-hero-content">
                <?php if (!empty($term_meta['logo_url'])) : ?>
                    <div class="store-logo-hero">
                        <img src="<?php echo esc_url($term_meta['logo_url']); ?>" alt="<?php echo esc_attr($term_name); ?>" loading="eager">
                    </div>
                <?php endif; ?>
                <h1 class="store-hero-title"><?php echo esc_html($term_name); ?></h1>
                <?php if ($term_description) : ?>
                    <p class="store-hero-description"><?php echo wp_kses_post($term_description); ?></p>
                <?php endif; ?>
                <div class="store-hero-meta">
                    <div class="hero-stat">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 16V8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2z" stroke-width="2"/><polyline points="7 10 12 14 17 10" stroke-width="2"/></svg>
                        <strong><?php echo number_format($total_deals); ?></strong>
                        <?php echo $total_deals == 1 ? esc_html__('Deal', 'dealsindia') : esc_html__('Deals', 'dealsindia'); ?>
                    </div>
                    <?php if (!empty($term_meta['cashback'])) : ?>
                        <div class="hero-stat hero-cashback">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23" stroke-width="2"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="2"/></svg>
                            <?php echo esc_html($term_meta['cashback']); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($term_meta['website_url'])) : ?>
                        <a href="<?php echo esc_url($term_meta['website_url']); ?>" target="_blank" rel="nofollow noopener" class="hero-store-link">
                            <?php esc_html_e('Visit Store', 'dealsindia'); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" stroke-width="2"/><polyline points="15 3 21 3 21 9" stroke-width="2"/><line x1="10" y1="14" x2="21" y2="3" stroke-width="2"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>


<?php elseif ($page_type === 'category') : ?>
    <section class="category-hero-section">
        <?php if (!empty($term_meta['banner_url'])) : ?>
            <div class="hero-bg-image hero-bg-<?php echo esc_attr($page_type); ?>" style="background-image: url('<?php echo esc_url($term_meta['banner_url']); ?>');"></div>
        <?php endif; ?>
        <div class="container">
            <div class="category-hero-content">
                <?php if (!empty($term_meta['icon_image_url'])) : ?>
                    <div class="category-icon-hero">
                        <img src="<?php echo esc_url($term_meta['icon_image_url']); ?>" alt="<?php echo esc_attr($term_name); ?>">
                    </div>
                <?php elseif (!empty($term_meta['icon'])) : ?>
                    <div class="category-emoji-hero"><?php echo esc_html($term_meta['icon']); ?></div>
                <?php endif; ?>
                <h1 class="category-hero-title"><?php echo esc_html($term_name); ?></h1>
                <?php if ($term_description) : ?>
                    <p class="category-hero-description"><?php echo wp_kses_post($term_description); ?></p>
                <?php endif; ?>
                <div class="category-hero-stats">
                    <div class="hero-stat-box">
                        <strong><?php echo number_format($total_deals); ?></strong>
                        <span><?php echo $total_deals == 1 ? esc_html__('Deal', 'dealsindia') : esc_html__('Deals', 'dealsindia'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>


<?php elseif ($page_type === 'dealtype') : ?>
    <section class="dealtype-hero-section">
        <?php if (!empty($term_meta['banner_url'])) : ?>
            <div class="hero-bg-image hero-bg-<?php echo esc_attr($page_type); ?>" style="background-image: url('<?php echo esc_url($term_meta['banner_url']); ?>');"></div>
        <?php endif; ?>
        <div class="container">
            <div class="dealtype-hero-content">
                <?php if (!empty($term_meta['icon_image_url'])) : ?>
                    <div class="dealtype-icon-hero">
                        <img src="<?php echo esc_url($term_meta['icon_image_url']); ?>" alt="<?php echo esc_attr($term_name); ?>">
                    </div>
                <?php elseif (!empty($term_meta['icon'])) : ?>
                    <div class="dealtype-emoji-hero"><?php echo esc_html($term_meta['icon']); ?></div>
                <?php endif; ?>
                <h1 class="dealtype-hero-title"><?php echo esc_html($term_name); ?></h1>
                <?php if ($term_description) : ?>
                    <p class="dealtype-hero-description"><?php echo wp_kses_post($term_description); ?></p>
                <?php endif; ?>
                <div class="dealtype-hero-stats">
                    <div class="hero-stat-box">
                        <strong><?php echo number_format($total_deals); ?></strong>
                        <span><?php echo esc_html__('Available', 'dealsindia'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>


<?php
// Featured Deals Section
if (($page_type === 'store' || $page_type === 'category' || $page_type === 'dealtype') && !empty($term_meta['featured_deals'])) :
    $featured_deal_ids = array_map('trim', explode(',', $term_meta['featured_deals']));
    $featured_deal_ids = array_filter($featured_deal_ids, 'is_numeric');
    
    if (!empty($featured_deal_ids)) :
        $featured_query = new WP_Query(array(
            'post_type' => 'deals',
            'post__in' => $featured_deal_ids,
            'orderby' => 'post__in',
            'posts_per_page' => count($featured_deal_ids),
            'post_status' => 'publish'
        ));
        
        if ($featured_query->have_posts()) :
?>
<section class="featured-deals-section">
    <div class="container">
        <div class="section-header-inline">
            <h2><?php printf(esc_html__('Featured %s Deals', 'dealsindia'), esc_html($term_name)); ?></h2>
        </div>
        <div class="deals-scroll-wrapper">
            <div class="deals-scroll">
                <?php while ($featured_query->have_posts()) : $featured_query->the_post(); ?>
                    <?php get_template_part('template-parts/deal-card'); ?>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</section>
<?php
        wp_reset_postdata();
        endif;
    endif;
endif;
?>


<div class="container">
    <div class="archive-breadcrumb">
        <?php dealsindia_breadcrumb(); ?>
    </div>
</div>


<!-- HORIZONTAL FILTERS (FULL WIDTH) -->
<?php 
set_query_var('show_horizontal_only', true);
get_template_part('template-parts/filter-sidebar');
set_query_var('show_horizontal_only', false);
?>


<div class="container">
    <div class="archive-layout">
        
        <aside class="archive-sidebar" id="dealFiltersSidebar">

            <?php 
            set_query_var('show_sidebar_only', true);
            get_template_part('template-parts/filter-sidebar');
            set_query_var('show_sidebar_only', false);
            ?>
        </aside>
        
        <div class="archive-content">
            
            <?php if ($page_type === 'deals_archive') : ?>
                <div class="archive-header-simple">
                    <h1 class="archive-title"><?php esc_html_e('All Deals', 'dealsindia'); ?></h1>
                    <p class="archive-count"><?php echo number_format($total_deals); ?> <?php echo $total_deals == 1 ? esc_html__('deal', 'dealsindia') : esc_html__('deals', 'dealsindia'); ?> <?php esc_html_e('available', 'dealsindia'); ?></p>
                </div>
            <?php else : ?>
                <div class="archive-header-simple">
                    <p class="archive-count"><?php echo number_format($total_deals); ?> <?php echo $total_deals == 1 ? esc_html__('deal found', 'dealsindia') : esc_html__('deals found', 'dealsindia'); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="deals-grid" id="dealsGridContainer">
                <?php if (have_posts()) : ?>
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/deal-card'); ?>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="no-deals-message">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <line x1="15" y1="9" x2="9" y2="15" stroke-width="2"/>
                            <line x1="9" y1="9" x2="15" y2="15" stroke-width="2"/>
                        </svg>
                        <h3><?php esc_html_e('No Deals Found', 'dealsindia'); ?></h3>
                        <p><?php esc_html_e('We couldn\'t find any deals at the moment. Check back soon!', 'dealsindia'); ?></p>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary"><?php esc_html_e('Back to Homepage', 'dealsindia'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($wp_query->max_num_pages > 1) : ?>
                <div class="archive-pagination">
                    <?php dealsindia_pagination(); ?>
                </div>
            <?php endif; ?>
            
        </div>
        
    </div>
    
    <?php
    // Browse Sections
    if ($page_type === 'store' || $page_type === 'dealtype' || $page_type === 'campaign') :
        $browse_categories = get_terms(array(
            'taxonomy' => 'deal-category',
            'hide_empty' => true,
            'number' => 12,
            'orderby' => 'count',
            'order' => 'DESC'
        ));
        
        if (!is_wp_error($browse_categories) && count($browse_categories) > 0) :
    ?>
    <section class="browse-section browse-categories">
        <div class="section-header">
            <h2>
                <?php 
                if ($page_type === 'store') {
                    printf(esc_html__('Browse %s by Category', 'dealsindia'), esc_html($term_name));
                } elseif ($page_type === 'campaign') {
                    printf(esc_html__('%s Deals by Category', 'dealsindia'), esc_html($term_name));
                } else {
                    esc_html_e('Browse by Category', 'dealsindia');
                }
                ?>
            </h2>
        </div>
        <div class="browse-grid">
            <?php foreach ($browse_categories as $category) : 
                $icon = get_term_meta($category->term_id, 'category_icon', true);
                $icon_image_id = get_term_meta($category->term_id, 'category_icon_image_id', true);
                $icon_image_url = $icon_image_id ? wp_get_attachment_url($icon_image_id) : '';
                $category_link = get_term_link($category);
            ?>
                <a href="<?php echo esc_url($category_link); ?>" class="browse-card">
                    <div class="browse-icon">
                        <?php if ($icon_image_url) : ?>
                            <img src="<?php echo esc_url($icon_image_url); ?>" alt="<?php echo esc_attr($category->name); ?>">
                        <?php elseif ($icon) : ?>
                            <span class="browse-emoji"><?php echo esc_html($icon); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="browse-name"><?php echo esc_html($category->name); ?></h3>
                    <span class="browse-count"><?php echo number_format($category->count); ?> <?php echo $category->count == 1 ? esc_html__('deal', 'dealsindia') : esc_html__('deals', 'dealsindia'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php 
        endif;
    endif;
    
    if ($page_type === 'category' || $page_type === 'dealtype' || $page_type === 'campaign' || $page_type === 'deals_archive') :
        $browse_stores = get_terms(array(
            'taxonomy' => 'store',
            'hide_empty' => true,
            'number' => 12,
            'orderby' => 'count',
            'order' => 'DESC'
        ));
        
        if (!is_wp_error($browse_stores) && count($browse_stores) > 0) :
    ?>
    <section class="browse-section browse-stores">
        <div class="section-header">
            <h2>
                <?php 
                if ($page_type === 'category') {
                    printf(esc_html__('Top Stores for %s', 'dealsindia'), esc_html($term_name));
                } elseif ($page_type === 'dealtype') {
                    printf(esc_html__('Top Stores for %s', 'dealsindia'), esc_html($term_name));
                } elseif ($page_type === 'campaign') {
                    printf(esc_html__('Top Stores in %s', 'dealsindia'), esc_html($term_name));
                } else {
                    esc_html_e('Browse by Store', 'dealsindia');
                }
                ?>
            </h2>
        </div>
        <div class="browse-grid">
            <?php foreach ($browse_stores as $store) : 
                $logo_id = get_term_meta($store->term_id, 'store_logo_id', true);
                $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
                $cashback = get_term_meta($store->term_id, 'store_cashback', true);
                $store_link = get_term_link($store);
            ?>
                <a href="<?php echo esc_url($store_link); ?>" class="browse-card browse-store">
                    <?php if ($logo_url) : ?>
                        <div class="browse-store-logo">
                            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($store->name); ?>">
                        </div>
                    <?php else : ?>
                        <div class="browse-store-fallback">
                            <?php echo esc_html(strtoupper(substr($store->name, 0, 2))); ?>
                        </div>
                    <?php endif; ?>
                    <h3 class="browse-name"><?php echo esc_html($store->name); ?></h3>
                    <div class="browse-meta">
                        <span class="browse-count"><?php echo number_format($store->count); ?> <?php echo $store->count == 1 ? esc_html__('deal', 'dealsindia') : esc_html__('deals', 'dealsindia'); ?></span>
                        <?php if ($cashback) : ?>
                            <span class="browse-cashback"><?php echo esc_html($cashback); ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php 
        endif;
    endif;
    ?>
    
</div>


</div>


<button class="mobile-filter-toggle-btn" id="mobileFilterToggle">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke-width="2"/>
    </svg>
    <?php esc_html_e('Filters', 'dealsindia'); ?>
</button>


<?php get_footer(); ?>
