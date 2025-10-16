<?php
if (!defined('ABSPATH')) exit;
/**
 * Search Results Template
 * Displays search results for deals, stores, categories
 * 
 * @package ARRZONE
 * @version 1.0
 */

get_header();

// Get search query
$search_query = get_search_query();
$search_query_escaped = esc_html($search_query);

// Get total results
global $wp_query;
$total_results = $wp_query->found_posts;

?>

<div class="archive-deals search-results-page">

    <!-- Search Hero Section -->
    <section class="search-hero-section">
        <div class="container">
            <div class="search-hero-content">
                <div class="search-icon-large">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <h1 class="search-hero-title">
                    <?php 
                    if (!empty($search_query)) {
                        printf(esc_html__('Search Results for "%s"', 'dealsindia'), $search_query_escaped);
                    } else {
                        esc_html_e('Search Results', 'dealsindia');
                    }
                    ?>
                </h1>
                <p class="search-hero-description">
                    <?php 
                    if ($total_results > 0) {
                        printf(
                            esc_html(_n('Found %s result', 'Found %s results', $total_results, 'dealsindia')),
                            '<strong>' . number_format($total_results) . '</strong>'
                        );
                    } else {
                        esc_html_e('No results found', 'dealsindia');
                    }
                    ?>
                </p>
                
                <!-- Search Form in Hero -->
                <div class="search-hero-form">
                    <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                        <input 
                            type="search" 
                            name="s" 
                            placeholder="<?php esc_attr_e('Try another search...', 'dealsindia'); ?>" 
                            value="<?php echo $search_query_escaped; ?>"
                            required
                        >
                        <button type="submit" class="search-submit-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                            <?php esc_html_e('Search', 'dealsindia'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="archive-breadcrumb">
            <?php dealsindia_breadcrumb(); ?>
        </div>
    </div>

    <div class="container">
        <div class="archive-layout">
            
            <!-- Sidebar Filters -->
            <aside class="archive-sidebar" id="dealFiltersSidebar">
                <?php 
                set_query_var('show_sidebar_only', true);
                get_template_part('template-parts/filter-sidebar');
                set_query_var('show_sidebar_only', false);
                ?>
            </aside>
            
            <!-- Search Results Content -->
            <div class="archive-content">
                
                <!-- Results Header -->
                <div class="search-results-header">
                    <?php if (!empty($search_query)): ?>
                        <p class="search-query-display">
                            <?php esc_html_e('Showing results for:', 'dealsindia'); ?>
                            <span class="search-query-term"><?php echo $search_query_escaped; ?></span>
                        </p>
                    <?php endif; ?>
                    
                    <div class="archive-header-simple">
                        <p class="archive-count">
                            <?php 
                            echo number_format($total_results) . ' ';
                            echo $total_results == 1 
                                ? esc_html__('result found', 'dealsindia') 
                                : esc_html__('results found', 'dealsindia');
                            ?>
                        </p>
                    </div>
                </div>
                
                <!-- Results Grid -->
                <div class="deals-grid" id="dealsGridContainer">
                    <?php if (have_posts()) : ?>
                        <?php while (have_posts()) : the_post(); ?>
                            <?php get_template_part('template-parts/deal-card'); ?>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <!-- No Results Message -->
                        <div class="no-deals-message search-no-results">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                                <line x1="11" y1="8" x2="11" y2="14" stroke-width="2"/>
                            </svg>
                            <h3><?php esc_html_e('No Results Found', 'dealsindia'); ?></h3>
                            <p>
                                <?php 
                                printf(
                                    esc_html__('Sorry, we couldn\'t find any results for "%s". Try searching with different keywords.', 'dealsindia'),
                                    $search_query_escaped
                                );
                                ?>
                            </p>
                            
                            <!-- Search Suggestions -->
                            <div class="search-suggestions">
                                <h4><?php esc_html_e('Search Tips:', 'dealsindia'); ?></h4>
                                <ul>
                                    <li><?php esc_html_e('Check your spelling', 'dealsindia'); ?></li>
                                    <li><?php esc_html_e('Try more general keywords', 'dealsindia'); ?></li>
                                    <li><?php esc_html_e('Try different keywords', 'dealsindia'); ?></li>
                                    <li><?php esc_html_e('Browse popular categories below', 'dealsindia'); ?></li>
                                </ul>
                            </div>
                            
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
                                <?php esc_html_e('Back to Homepage', 'dealsindia'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($wp_query->max_num_pages > 1) : ?>
                    <div class="archive-pagination">
                        <?php dealsindia_pagination(); ?>
                    </div>
                <?php endif; ?>
                
            </div>
            
        </div>
        
        <!-- Popular Categories (Show when no results) -->
        <?php if (!have_posts()): 
            $popular_categories = get_terms(array(
                'taxonomy' => 'deal-category',
                'hide_empty' => true,
                'number' => 8,
                'orderby' => 'count',
                'order' => 'DESC'
            ));
            
            if (!is_wp_error($popular_categories) && !empty($popular_categories)) :
        ?>
        <section class="browse-section browse-categories search-suggestions-section">
            <div class="section-header">
                <h2><?php esc_html_e('Popular Categories', 'dealsindia'); ?></h2>
                <p class="section-subtitle"><?php esc_html_e('Browse deals by category', 'dealsindia'); ?></p>
            </div>
            <div class="browse-grid">
                <?php foreach ($popular_categories as $category) : 
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
                        <span class="browse-count">
                            <?php 
                            echo number_format($category->count) . ' ';
                            echo $category->count == 1 
                                ? esc_html__('deal', 'dealsindia') 
                                : esc_html__('deals', 'dealsindia');
                            ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php 
            endif;
        endif; 
        ?>
        
        <!-- Popular Stores (Show when no results) -->
        <?php if (!have_posts()): 
            $popular_stores = get_terms(array(
                'taxonomy' => 'store',
                'hide_empty' => true,
                'number' => 8,
                'orderby' => 'count',
                'order' => 'DESC'
            ));
            
            if (!is_wp_error($popular_stores) && !empty($popular_stores)) :
        ?>
        <section class="browse-section browse-stores search-suggestions-section">
            <div class="section-header">
                <h2><?php esc_html_e('Popular Stores', 'dealsindia'); ?></h2>
                <p class="section-subtitle"><?php esc_html_e('Find deals from top stores', 'dealsindia'); ?></p>
            </div>
            <div class="browse-grid">
                <?php foreach ($popular_stores as $store) : 
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
                            <span class="browse-count">
                                <?php 
                                echo number_format($store->count) . ' ';
                                echo $store->count == 1 
                                    ? esc_html__('deal', 'dealsindia') 
                                    : esc_html__('deals', 'dealsindia');
                                ?>
                            </span>
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

<!-- Mobile Filter Toggle Button -->
<button class="mobile-filter-toggle-btn" id="mobileFilterToggle">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
    </svg>
    <?php esc_html_e('Filters', 'dealsindia'); ?>
</button>

<?php get_footer(); ?>
