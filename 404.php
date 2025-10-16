<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

get_header(); 
?>

<div class="container">
    <div class="error-404-container">
        <h1 class="error-title">404</h1>
        <h2>Oops! Page Not Found</h2>
        <p>The page you're looking for doesn't exist or has been moved.</p>
        
        <div class="error-actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">← Back to Homepage</a>
            
            <div class="error-search">
                <p>Or search for deals:</p>
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search" 
                           name="s" 
                           placeholder="Search deals..." 
                           class="search-input">
                    <button type="submit" class="btn btn-secondary">🔍 Search</button>
                </form>
            </div>
        </div>
        
        <div class="popular-categories">
            <h3>Browse Popular Categories:</h3>
            <div class="category-filters">
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'deal_category',
                    'hide_empty' => true,
                    'number'     => 6,
                    'orderby'    => 'count',
                    'order'      => 'DESC'
                ));
                
                if ($categories && !is_wp_error($categories)) :
                    foreach ($categories as $category) : ?>
                        <a href="<?php echo esc_url(get_term_link($category)); ?>" class="btn btn-tag">
                            <?php echo esc_html($category->name); ?>
                        </a>
                    <?php endforeach;
                endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
