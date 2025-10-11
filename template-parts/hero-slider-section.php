<?php
/**
 * Hero Banner Section Template
 * FIXED - Shows 2 banners per slide with auto-carousel
 * 
 * @package DealsIndia
 */

// Get hero banners
$banners_query = new WP_Query(array(
    'post_type' => 'hero_banner',
    'posts_per_page' => 10, // Get up to 10 banners
    'post_status' => 'publish',
    'orderby' => 'menu_order date',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => 'banner_active',
            'value' => '1',
            'compare' => '=',
        )
    )
));

// If no active banners, get all published banners
if (!$banners_query->have_posts()) {
    $banners_query = new WP_Query(array(
        'post_type' => 'hero_banner',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ));
}

// Check if we have banners
if ($banners_query->have_posts()) :
    $banner_count = $banners_query->post_count;
    ?>
    <div class="hero-banners-carousel" data-carousel="hero" data-banner-count="<?php echo esc_attr($banner_count); ?>">
        <div class="hero-banners-wrapper">
            <?php 
            $slide_index = 0;
            $banners_in_slide = 0;
            
            while ($banners_query->have_posts()) : $banners_query->the_post();
                // Start new slide every 2 banners
                if ($banners_in_slide === 0) {
                    echo '<div class="hero-banners-slide" data-slide="' . $slide_index . '">';
                }
                
                $banner_url = get_post_meta(get_the_ID(), 'banner_url', true);
                $banner_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
                
                if (!$banner_url || $banner_url === '') {
                    $banner_url = get_post_type_archive_link('deals');
                }
                ?>
                <div class="hero-banner-item">
                    <a href="<?php echo esc_url($banner_url); ?>" class="hero-banner-link">
                        <?php if ($banner_image) : ?>
                            <img src="<?php echo esc_url($banner_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="hero-banner-image" loading="lazy">
                        <?php else : ?>
                            <div class="hero-banner-placeholder" style="background: linear-gradient(135deg, #e53935, #ff5252); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700; min-height: 260px; text-align: center; padding: 20px;">
                                <?php the_title(); ?>
                            </div>
                        <?php endif; ?>
                    </a>
                </div>
                <?php
                
                $banners_in_slide++;
                
                // Close slide after 2 banners
                if ($banners_in_slide === 2) {
                    echo '</div>'; // Close hero-banners-slide
                    $slide_index++;
                    $banners_in_slide = 0;
                }
            endwhile; 
            
            // Close last slide if it has only 1 banner
            if ($banners_in_slide > 0) {
                echo '</div>';
            }
            
            wp_reset_postdata();
            ?>
        </div>

        <!-- Carousel Controls (only show if more than 2 banners) -->
        <?php if ($banner_count > 2) : ?>
            <div class="carousel-dots"></div>
            <button class="carousel-arrow carousel-arrow-prev" aria-label="Previous">‹</button>
            <button class="carousel-arrow carousel-arrow-next" aria-label="Next">›</button>
        <?php endif; ?>
    </div>
    <?php
else :
    // No banners found - show default placeholders
    ?>
    <div class="hero-banners-carousel">
        <div class="hero-banners-wrapper">
            <div class="hero-banners-slide" data-slide="0">
                <!-- Placeholder Banner 1 -->
                <div class="hero-banner-item">
                    <a href="<?php echo esc_url(get_post_type_archive_link('deals')); ?>" class="hero-banner-link">
                        <div class="hero-banner-placeholder" style="background: linear-gradient(135deg, #e53935 0%, #ff5252 100%); display: flex; align-items: center; justify-content: center; color: white; text-align: center; min-height: 260px; padding: 40px;">
                            <div>
                                <h3 style="font-size: 28px; margin-bottom: 12px; font-weight: 700;">Welcome to <?php bloginfo('name'); ?></h3>
                                <p style="font-size: 16px; opacity: 0.95;">India's #1 Deals & Cashback Platform</p>
                                <div style="margin-top: 20px; background: white; color: #e53935; padding: 10px 24px; border-radius: 24px; display: inline-block; font-weight: 700;">
                                    🎁 Start Saving Today!
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Placeholder Banner 2 -->
                <div class="hero-banner-item">
                    <a href="<?php echo esc_url(get_post_type_archive_link('deals')); ?>" class="hero-banner-link">
                        <div class="hero-banner-placeholder" style="background: linear-gradient(135deg, #ff6f00 0%, #ff8a50 100%); display: flex; align-items: center; justify-content: center; color: white; text-align: center; min-height: 260px; padding: 40px;">
                            <div>
                                <h3 style="font-size: 28px; margin-bottom: 12px; font-weight: 700;">🔥 Hot Deals Live!</h3>
                                <p style="font-size: 16px; opacity: 0.95;">Save Big with Exclusive Cashback Offers</p>
                                <div style="margin-top: 20px; background: white; color: #ff6f00; padding: 10px 24px; border-radius: 24px; display: inline-block; font-weight: 700;">
                                    Browse Deals →
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (current_user_can('manage_options')) : ?>
        <div style="text-align: center; margin-top: 16px; padding: 12px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px;">
            <p style="margin: 0; color: #856404; font-size: 14px;">
                <strong>⚠️ Admin Notice:</strong> No hero banners found. 
                <a href="<?php echo admin_url('edit.php?post_type=hero_banner'); ?>" style="color: #e53935; font-weight: 700;">Click here to add banners</a>
            </p>
        </div>
    <?php endif; ?>
    <?php
endif;
?>
