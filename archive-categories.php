<?php
/**
 * All Categories Archive
 * URL: /deals-category/
 * 
 * @package DealsIndia
 */

get_header();
?>

<div class="all-categories-page">
    <div class="container">
        
        <!-- Breadcrumb -->
        <?php dealsindia_breadcrumb(); ?>
        
        <!-- Page Header -->
        <div class="page-header" style="text-align: center; padding: 40px 0;">
            <h1 style="font-size: 48px; margin-bottom: 10px;">Browse by Category</h1>
            <p style="font-size: 18px; color: #718096;">Find the best deals in your favorite categories</p>
        </div>
        
        <!-- Categories Grid -->
        <div class="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 24px; margin-top: 40px;">
            <?php
            $categories = get_terms(array(
                'taxonomy' => 'deal_category',
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ));
            
            if ($categories && !is_wp_error($categories)) :
                foreach ($categories as $category) :
                    $icon = get_term_meta($category->term_id, 'category_icon', true);
                    $icon_image_id = get_term_meta($category->term_id, 'category_icon_image_id', true);
                    $icon_url = $icon_image_id ? wp_get_attachment_url($icon_image_id) : '';
                    $category_link = get_term_link($category);
                    ?>
                    <a href="<?php echo esc_url($category_link); ?>" class="category-card" style="background: white; border: 2px solid #e2e8f0; border-radius: 12px; padding: 30px; text-align: center; text-decoration: none; transition: all 0.3s ease; display: block;">
                        <div class="category-icon" style="font-size: 48px; margin-bottom: 15px;">
                            <?php if ($icon_url) : ?>
                                <img src="<?php echo esc_url($icon_url); ?>" alt="<?php echo esc_attr($category->name); ?>" style="width: 64px; height: 64px; object-fit: contain;">
                            <?php else : ?>
                                <?php echo $icon ? esc_html($icon) : '🏷️'; ?>
                            <?php endif; ?>
                        </div>
                        <h3 style="font-size: 20px; color: #2d3748; margin-bottom: 8px;"><?php echo esc_html($category->name); ?></h3>
                        <p style="font-size: 14px; color: #718096; margin: 0;">
                            <strong><?php echo $category->count; ?></strong> <?php echo $category->count == 1 ? 'Deal' : 'Deals'; ?>
                        </p>
                    </a>
                    <?php
                endforeach;
            else :
                ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <p style="font-size: 18px; color: #718096;">No categories found.</p>
                </div>
                <?php
            endif;
            ?>
        </div>
        
    </div>
</div>

<style>
.category-card:hover {
    border-color: #00897B !important;
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 137, 123, 0.15);
}
</style>

<?php get_footer(); ?>
