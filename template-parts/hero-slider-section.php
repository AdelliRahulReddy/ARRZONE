<?php
/**
 * Hero Section - Image-Only Banners
 * Shows 2 full banner images side-by-side
 */

$banners = dealsindia_get_hero_banners();

if (empty($banners)) : 
    // Fallback if no banners
    ?>
    <section class="hero-slider-cd">
        <div class="container">
            <div class="hero-banners-grid">
                <div class="hero-banner-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <a href="<?php echo get_post_type_archive_link('deals'); ?>">
                        <div class="banner-placeholder">
                            <span style="font-size: 48px;">🛍️</span>
                            <p style="color: white; margin-top: 10px;">Add Banner Image</p>
                        </div>
                    </a>
                </div>
                <div class="hero-banner-item" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <a href="<?php echo get_post_type_archive_link('deals'); ?>">
                        <div class="banner-placeholder">
                            <span style="font-size: 48px;">🎁</span>
                            <p style="color: white; margin-top: 10px;">Add Banner Image</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php
    return;
endif;

// Get first 2 banners
$banner1 = $banners[0];
$banner2 = isset($banners[1]) ? $banners[1] : $banners[0];
?>

<section class="hero-slider-cd">
    <div class="container">
        <div class="hero-banners-grid">
            
            <?php 
            foreach (array($banner1, $banner2) as $banner) :
                $featured_image = get_the_post_thumbnail_url($banner->ID, 'large');
                $button_link = get_post_meta($banner->ID, 'banner_button_link', true);
                $link = $button_link ? $button_link : get_post_type_archive_link('deals');
            ?>
            
            <div class="hero-banner-item">
                <a href="<?php echo esc_url($link); ?>" class="hero-banner-link">
                    <?php if ($featured_image) : ?>
                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($banner->post_title); ?>" class="hero-banner-image">
                    <?php else : ?>
                        <div class="banner-placeholder" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <span style="font-size: 48px; color: white;">📦</span>
                        </div>
                    <?php endif; ?>
                </a>
            </div>
            
            <?php endforeach; ?>
            
        </div>
    </div>
</section>
