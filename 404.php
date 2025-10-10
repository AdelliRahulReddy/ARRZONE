<?php get_header(); ?>

<div class="container mt-40">
    <div class="error-404">
        <h1 class="error-title">404</h1>
        <h2>Oops! Page Not Found</h2>
        <p>The page you're looking for doesn't exist or has been moved.</p>
        
        <div class="error-actions">
            <a href="<?php echo home_url(); ?>" class="view-deal-btn" style="display: inline-block; max-width: 250px;">← Back to Homepage</a>
            
            <div class="error-search">
                <p style="margin-top: 30px; margin-bottom: 15px; font-size: 16px; color: #666;">Or search for deals:</p>
                <form role="search" method="get" action="<?php echo home_url('/'); ?>" style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                    <input type="search" 
                           name="s" 
                           placeholder="Search deals..." 
                           class="search-input"
                           style="max-width: 400px; margin: 0;">
                    <button type="submit" class="search-btn">🔍 Search</button>
                </form>
            </div>
        </div>
        
        <div class="popular-categories" style="margin-top: 50px;">
            <h3 style="font-size: 20px; margin-bottom: 20px; color: #333;">Browse Popular Categories:</h3>
            <div class="category-filters" style="justify-content: center;">
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'deal_category',
                    'hide_empty' => true,
                ));
                
                if ($categories && !is_wp_error($categories)) :
                    foreach ($categories as $category) : ?>
                        <a href="<?php echo get_term_link($category); ?>" class="filter-btn">
                            <?php echo $category->name; ?>
                        </a>
                    <?php endforeach;
                else : ?>
                    <p style="color: #999;">No categories available yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
