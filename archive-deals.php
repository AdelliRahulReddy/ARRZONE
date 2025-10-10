<?php
/**
 * Deals Archive Template
 * DealsIndia Theme - Premium Design
 */

get_header();
?>

<div class="deals-archive-page">
    <div class="container">
        
        <!-- Archive Header -->
        <div class="archive-header">
            <h1 class="archive-title">All Deals</h1>
            <p class="archive-description">
                Browse through our collection of amazing deals and offers
            </p>
            <div class="archive-count">
                <strong><?php echo $wp_query->found_posts; ?></strong>
                <?php echo ($wp_query->found_posts == 1) ? 'Deal' : 'Deals'; ?> Available
            </div>
        </div>
        
        <!-- Toolbar -->
        <div class="archive-toolbar">
            <div class="archive-filters">
                <a href="<?php echo get_post_type_archive_link('deals'); ?>" class="filter-tab <?php echo !isset($_GET['filter']) ? 'active' : ''; ?>">
                    All Deals
                </a>
                <a href="<?php echo add_query_arg('filter', 'featured', get_post_type_archive_link('deals')); ?>" class="filter-tab <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'featured') ? 'active' : ''; ?>">
                    Featured
                </a>
                <a href="<?php echo add_query_arg('filter', 'trending', get_post_type_archive_link('deals')); ?>" class="filter-tab <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'trending') ? 'active' : ''; ?>">
                    Trending
                </a>
                <a href="<?php echo add_query_arg('filter', 'ending', get_post_type_archive_link('deals')); ?>" class="filter-tab <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'ending') ? 'active' : ''; ?>">
                    Ending Soon
                </a>
            </div>
            
            <div class="archive-sort">
                <label for="deal-sort">Sort by:</label>
                <select id="deal-sort" onchange="window.location.href=this.value">
                    <option value="<?php echo get_post_type_archive_link('deals'); ?>" <?php echo !isset($_GET['orderby']) ? 'selected' : ''; ?>>Latest</option>
                    <option value="<?php echo add_query_arg('orderby', 'popular', get_post_type_archive_link('deals')); ?>" <?php echo (isset($_GET['orderby']) && $_GET['orderby'] == 'popular') ? 'selected' : ''; ?>>Popular</option>
                    <option value="<?php echo add_query_arg('orderby', 'discount', get_post_type_archive_link('deals')); ?>" <?php echo (isset($_GET['orderby']) && $_GET['orderby'] == 'discount') ? 'selected' : ''; ?>>Best Discount</option>
                </select>
            </div>
        </div>
        
        <!-- Deals Grid -->
        <div class="archive-deals-grid">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/deal-card-large'); ?>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="no-deals-found">
                    <div class="no-deals-icon">📦</div>
                    <h3>No Deals Found</h3>
                    <p>We couldn't find any deals matching your criteria.</p>
                    <a href="<?php echo get_post_type_archive_link('deals'); ?>">View All Deals</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($wp_query->max_num_pages > 1) : ?>
            <div class="archive-pagination">
                <?php
                echo paginate_links(array(
                    'type' => 'list',
                    'prev_text' => '← Previous',
                    'next_text' => 'Next →',
                ));
                ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php get_footer(); ?>
