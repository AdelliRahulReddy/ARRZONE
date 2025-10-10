<?php
/**
 * Category Archive Template
 * DealsIndia Theme
 */

get_header();

$term = get_queried_object();
$category_icon = get_term_meta($term->term_id, 'category_icon', true);
$category_icon_image_id = get_term_meta($term->term_id, 'category_icon_image_id', true);
$category_icon_url = $category_icon_image_id ? wp_get_attachment_url($category_icon_image_id) : '';
?>

<div class="category-archive-page">
    <div class="container">
        
        <!-- Breadcrumb -->
        <div class="category-breadcrumb">
            <a href="<?php echo home_url(); ?>">Home</a>
            <span>/</span>
            <a href="<?php echo get_post_type_archive_link('deals'); ?>">Deals</a>
            <span>/</span>
            <strong><?php echo esc_html($term->name); ?></strong>
        </div>
        
        <!-- Category Header -->
        <div class="category-header">
            <div class="category-icon-large">
                <?php if ($category_icon_url) : ?>
                    <img src="<?php echo esc_url($category_icon_url); ?>" alt="<?php echo esc_attr($term->name); ?>">
                <?php else : ?>
                    <?php echo $category_icon ? esc_html($category_icon) : '🏷️'; ?>
                <?php endif; ?>
            </div>
            
            <div class="category-info">
                <h1 class="category-title"><?php echo esc_html($term->name); ?></h1>
                
                <?php if ($term->description) : ?>
                    <p class="category-description"><?php echo esc_html($term->description); ?></p>
                <?php endif; ?>
                
                <div class="category-meta">
                    <span><strong><?php echo $term->count; ?></strong> Deals Available</span>
                </div>
            </div>
        </div>
        
        <!-- Toolbar -->
        <div class="category-toolbar">
            <div class="category-results">
                <strong><?php echo $wp_query->found_posts; ?></strong>
                <?php echo $wp_query->found_posts == 1 ? 'Deal' : 'Deals'; ?> Found
            </div>
            
            <div class="category-sort">
                <label for="deal-sort">Sort by:</label>
                <select id="deal-sort" onchange="window.location.href=this.value">
                    <option value="<?php echo get_term_link($term); ?>">Latest</option>
                    <option value="<?php echo add_query_arg('orderby', 'popular', get_term_link($term)); ?>">Popular</option>
                    <option value="<?php echo add_query_arg('orderby', 'ending', get_term_link($term)); ?>">Ending Soon</option>
                </select>
            </div>
        </div>
        
        <!-- Deals Grid -->
        <div class="category-deals-grid">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/deal-card-large'); ?>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="no-deals-found">
                    <div class="no-deals-found-icon">📦</div>
                    <h3>No Deals Found</h3>
                    <p>Check back soon for amazing deals in this category!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($wp_query->max_num_pages > 1) : ?>
            <div class="category-pagination">
                <?php
                echo paginate_links(array(
                    'type' => 'list',
                    'prev_text' => '←',
                    'next_text' => '→',
                ));
                ?>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php get_footer(); ?>
