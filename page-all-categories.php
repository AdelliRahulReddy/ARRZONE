<?php
if (!defined('ABSPATH')) exit; 
/**
 * Template Name: All Categories Archive
 * Description: Displays all deal categories in a grid layout
 */

get_header();
?>

<div class="archive-page">
    
    <!-- Page Header -->
    <section class="section-header">
        <div class="container">
            <h1><?php echo esc_html('All Categories', 'arrzone'); ?></h1>
            <p><?php echo esc_html('Explore deals across all categories and save big', 'arrzone'); ?></p>
        </div>
    </section>

    <!-- Browse Section -->
    <section class="browse-section">
        <div class="container">
            
            <?php
            // Get all categories
            $categories_args = array(
                'taxonomy'   => 'deal-category',
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            );
            
            $all_categories = get_terms($categories_args);
            
            if (!empty($all_categories) && !is_wp_error($all_categories)):
                
                // Separate featured and regular categories
                $featured_categories = array();
                $regular_categories = array();
                
                foreach ($all_categories as $category) {
                    $is_featured = get_term_meta($category->term_id, 'category_is_featured', true);
                    if ($is_featured) {
                        $featured_categories[] = $category;
                    } else {
                        $regular_categories[] = $category;
                    }
                }
                
                // Display Featured Categories
                if (!empty($featured_categories)): ?>
                    <div class="featured-categories-section">
                        <h2 class="browse-section-title"><?php echo esc_html('Featured Categories', 'arrzone'); ?></h2>
                        <div class="browse-grid">
                            <?php foreach ($featured_categories as $category):
                                $category_icon = get_term_meta($category->term_id, 'category_icon', true);
                                $category_icon_image_id = get_term_meta($category->term_id, 'category_icon_image_id', true);
                                $category_icon_image_url = $category_icon_image_id ? wp_get_attachment_url($category_icon_image_id) : '';
                                $deals_count = $category->count;
                                $category_url = get_term_link($category);
                            ?>
                                <div class="browse-card featured">
                                    <a href="<?php echo esc_url($category_url); ?>" class="browse-card-link">
                                        <div class="browse-icon">
                                            <?php if ($category_icon_image_url): ?>
                                                <img src="<?php echo esc_url($category_icon_image_url); ?>" 
                                                     alt="<?php echo esc_attr($category->name); ?>" 
                                                     class="category-icon-img">
                                            <?php elseif ($category_icon): ?>
                                                <span class="browse-emoji"><?php echo esc_html($category_icon); ?></span>
                                            <?php else: ?>
                                                <span class="browse-emoji">🏷️</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="browse-content">
                                            <h3 class="browse-name"><?php echo esc_html($category->name); ?></h3>
                                            <p class="browse-count">
                                                <?php
                                                printf(
                                                    _n('%s Deal', '%s Deals', $deals_count, 'arrzone'),
                                                    number_format_i18n($deals_count)
                                                );
                                                ?>
                                            </p>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
                
                // Display All Categories
                if (!empty($regular_categories)): ?>
                    <div class="all-categories-section">
                        <h2 class="browse-section-title"><?php echo esc_html('All Categories', 'arrzone'); ?></h2>
                        <div class="browse-grid">
                            <?php foreach ($regular_categories as $category):
                                $category_icon = get_term_meta($category->term_id, 'category_icon', true);
                                $category_icon_image_id = get_term_meta($category->term_id, 'category_icon_image_id', true);
                                $category_icon_image_url = $category_icon_image_id ? wp_get_attachment_url($category_icon_image_id) : '';
                                $deals_count = $category->count;
                                $category_url = get_term_link($category);
                            ?>
                                <div class="browse-card">
                                    <a href="<?php echo esc_url($category_url); ?>" class="browse-card-link">
                                        <div class="browse-icon">
                                            <?php if ($category_icon_image_url): ?>
                                                <img src="<?php echo esc_url($category_icon_image_url); ?>" 
                                                     alt="<?php echo esc_attr($category->name); ?>" 
                                                     class="category-icon-img">
                                            <?php elseif ($category_icon): ?>
                                                <span class="browse-emoji"><?php echo esc_html($category_icon); ?></span>
                                            <?php else: ?>
                                                <span class="browse-emoji">🏷️</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="browse-content">
                                            <h3 class="browse-name"><?php echo esc_html($category->name); ?></h3>
                                            <p class="browse-count">
                                                <?php
                                                printf(
                                                    _n('%s Deal', '%s Deals', $deals_count, 'arrzone'),
                                                    number_format_i18n($deals_count)
                                                );
                                                ?>
                                            </p>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
                
            else: ?>
                <div class="no-categories">
                    <p><?php echo esc_html('No categories found.', 'arrzone'); ?></p>
                </div>
            <?php endif; ?>
            
        </div>
    </section>
    
</div>

<?php get_footer(); ?>
