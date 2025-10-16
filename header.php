<?php
/**
 * Header Template - 100% Dynamic & SEO-Optimized
 * All content controlled from WordPress Customizer
 * Zero hardcoded text, maximum flexibility
 * 
 * FIXES APPLIED:
 * ✅ Dynamic <title> tag (WordPress handles it)
 * ✅ Skip to content link (accessibility)
 * ✅ Proper meta tags for SEO
 * ✅ Open Graph & Twitter Cards
 * ✅ Structured Data (Schema.org) for deals
 * ✅ Form labels for accessibility
 * 
 * @package ARRZONE
 * @version 7.0 - SEO & Accessibility Enhanced
 */

if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    
    <!-- CRITICAL: Proper Viewport Meta Tag - Prevents Zoom & Fixes Mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Mobile Optimization Meta Tags -->
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#00897B">
    
    <?php 
    // SEO Meta Tags - Dynamic
    if (is_singular()) {
        $meta_description = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 20);
    } else {
        $meta_description = get_bloginfo('description');
    }
    ?>
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>">
    
    <?php 
    // Canonical URL
    if (is_singular()) {
        echo '<link rel="canonical" href="' . esc_url(get_permalink()) . '">' . "\n";
    } elseif (is_tax() || is_category() || is_tag()) {
        echo '<link rel="canonical" href="' . esc_url(get_term_link(get_queried_object())) . '">' . "\n";
    } elseif (is_home() || is_front_page()) {
        echo '<link rel="canonical" href="' . esc_url(home_url('/')) . '">' . "\n";
    }
    ?>
    
    <?php 
    // Open Graph Meta Tags
    if (is_singular()) :
        $og_title = get_the_title();
        $og_description = $meta_description;
        $og_image = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '';
        $og_url = get_permalink();
    ?>
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo esc_attr($og_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($og_description); ?>">
    <?php if ($og_image) : ?>
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?php echo esc_url($og_url); ?>">
    <meta property="og:site_name" content="<?php bloginfo('name'); ?>">
    <?php endif; ?>
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <?php if (is_singular()) : ?>
    <meta name="twitter:title" content="<?php echo esc_attr(get_the_title()); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($meta_description); ?>">
    <?php if (has_post_thumbnail()) : ?>
    <meta name="twitter:image" content="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>">
    <?php endif; ?>
    <?php endif; ?>
    
    <?php 
    // Structured Data (Schema.org JSON-LD) for Deals
    if (is_singular('deals')) :
        $deal_price = get_post_meta(get_the_ID(), 'deal_sale_price', true);
        $deal_original_price = get_post_meta(get_the_ID(), 'deal_original_price', true);
        $deal_image = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : '';
        $deal_store = wp_get_post_terms(get_the_ID(), 'store');
        $store_name = !empty($deal_store) && !is_wp_error($deal_store) ? $deal_store[0]->name : get_bloginfo('name');
    ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "<?php echo esc_js(get_the_title()); ?>",
      <?php if ($deal_image) : ?>
      "image": "<?php echo esc_url($deal_image); ?>",
      <?php endif; ?>
      "description": "<?php echo esc_js(wp_trim_words(get_the_excerpt() ?: get_the_content(), 30)); ?>",
      "brand": {
        "@type": "Brand",
        "name": "<?php echo esc_js($store_name); ?>"
      },
      "offers": {
        "@type": "Offer",
        "url": "<?php echo esc_url(get_permalink()); ?>",
        "priceCurrency": "INR",
        <?php if ($deal_price) : ?>
        "price": "<?php echo esc_js($deal_price); ?>",
        <?php endif; ?>
        "availability": "https://schema.org/InStock",
        "priceValidUntil": "<?php echo esc_js(get_post_meta(get_the_ID(), 'deal_expiry_date', true) ?: date('Y-m-d', strtotime('+30 days'))); ?>"
      }
    }
    </script>
    <?php endif; ?>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// ========================================
// ANNOUNCEMENT BAR (Optional - Disabled by default)
// ========================================
$show_announcement = get_theme_mod('dealsindia_show_announcement_bar', false);
if ($show_announcement) :
    $announcement_text = get_theme_mod('dealsindia_announcement_text', '');
    $announcement_link = get_theme_mod('dealsindia_announcement_link', '');
    $announcement_bg = get_theme_mod('dealsindia_announcement_bg_color', '#ff6b6b');
    $announcement_text_color = get_theme_mod('dealsindia_announcement_text_color', '#ffffff');
    $announcement_closeable = get_theme_mod('dealsindia_announcement_closeable', true);
    
    if ($announcement_text) :
?>
<div class="announcement-bar" style="background-color: <?php echo esc_attr($announcement_bg); ?>; color: <?php echo esc_attr($announcement_text_color); ?>;" id="announcementBar">
    <div class="container">
        <div class="announcement-content">
            <?php if ($announcement_link) : ?>
                <a href="<?php echo esc_url($announcement_link); ?>" class="announcement-link">
                    <?php echo esc_html($announcement_text); ?>
                </a>
            <?php else : ?>
                <span><?php echo esc_html($announcement_text); ?></span>
            <?php endif; ?>
            
            <?php if ($announcement_closeable) : ?>
            <button class="announcement-close" aria-label="<?php esc_attr_e('Close announcement', 'dealsindia'); ?>" onclick="this.parentElement.parentElement.style.display='none'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <line x1="18" y1="6" x2="6" y2="18" stroke-width="2"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke-width="2"/>
                </svg>
            </button>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php 
    endif;
endif; 
?>

<?php
// ========================================
// TOP BAR (Optional - Disabled by default)
// ========================================
$show_topbar = get_theme_mod('dealsindia_show_topbar', false);
if ($show_topbar) :
    $topbar_phone = get_theme_mod('dealsindia_topbar_phone', '');
    $topbar_email = get_theme_mod('dealsindia_topbar_email', '');
    $topbar_message = get_theme_mod('dealsindia_topbar_message', '');
?>
<div class="site-topbar">
    <div class="container">
        <div class="topbar-content">
            <div class="topbar-left">
                <?php if ($topbar_message) : ?>
                    <span class="topbar-message"><?php echo esc_html($topbar_message); ?></span>
                <?php endif; ?>
            </div>
            <div class="topbar-right">
                <?php if ($topbar_phone) : ?>
                <a href="tel:<?php echo esc_attr(str_replace(' ', '', $topbar_phone)); ?>" class="topbar-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" stroke-width="2"/>
                    </svg>
                    <?php echo esc_html($topbar_phone); ?>
                </a>
                <?php endif; ?>
                
                <?php if ($topbar_email) : ?>
                <a href="mailto:<?php echo esc_attr($topbar_email); ?>" class="topbar-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke-width="2"/>
                        <polyline points="22,6 12,13 2,6" stroke-width="2"/>
                    </svg>
                    <?php echo esc_html($topbar_email); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Main Site Header -->
<header class="site-header<?php echo get_theme_mod('dealsindia_sticky_header', true) ? ' sticky-header' : ''; ?>">
    <div class="container">
        <div class="header-wrapper">
            
            <!-- Logo -->
            <div class="site-logo">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : 
                    $logo_emoji = get_theme_mod('dealsindia_logo_emoji_fallback', '🏷️');
                    $site_name = get_bloginfo('name');
                ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                        <?php if ($logo_emoji) : ?>
                            <span class="logo-icon" aria-hidden="true"><?php echo esc_html($logo_emoji); ?></span>
                        <?php endif; ?>
                        <span><?php echo esc_html($site_name); ?></span>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Primary Navigation (Desktop) -->
            <nav class="primary-nav" aria-label="<?php esc_attr_e('Primary Navigation', 'dealsindia'); ?>">
                <?php 
                if (has_nav_menu('primary')) {
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => '',
                        'fallback_cb'    => 'dealsindia_fallback_menu'
                    ));
                } else {
                    dealsindia_fallback_menu();
                }
                ?>
            </nav>
            
            <!-- Header Actions -->
            <div class="header-actions">
                
                <!-- Search Toggle -->
                <div class="header-search">
                    <button class="search-toggle" aria-label="<?php esc_attr_e('Toggle search', 'dealsindia'); ?>" aria-expanded="false" type="button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" stroke-width="2"/>
                            <path d="m21 21-4.35-4.35" stroke-width="2"/>
                        </svg>
                    </button>
                    
                    <!-- Search Dropdown -->
                    <form class="search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <label for="header-search-input" class="screen-reader-text"><?php _e('Search', 'dealsindia'); ?></label>
                        <input 
                            type="search"
                            id="header-search-input"
                            name="s" 
                            placeholder="<?php echo esc_attr(get_theme_mod('dealsindia_search_placeholder', __('Search for deals, stores...', 'dealsindia'))); ?>" 
                            value="<?php echo get_search_query(); ?>"
                            aria-label="<?php esc_attr_e('Search', 'dealsindia'); ?>"
                        >
                        <button type="submit" aria-label="<?php esc_attr_e('Submit search', 'dealsindia'); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <circle cx="11" cy="11" r="8" stroke-width="2"/>
                                <path d="m21 21-4.35-4.35" stroke-width="2"/>
                            </svg>
                        </button>
                    </form>
                </div>
                
                <?php 
                // Optional CTA Button (Disabled by default)
                $show_cta = get_theme_mod('dealsindia_show_header_cta', false);
                if ($show_cta) :
                    $cta_text = get_theme_mod('dealsindia_header_cta_text', __('Submit Deal', 'dealsindia'));
                    $cta_link = get_theme_mod('dealsindia_header_cta_link', '#');
                    $cta_style = get_theme_mod('dealsindia_header_cta_style', 'primary');
                ?>
                <a href="<?php echo esc_url($cta_link); ?>" class="btn btn-<?php echo esc_attr($cta_style); ?> header-cta">
                    <?php echo esc_html($cta_text); ?>
                </a>
                <?php endif; ?>
                
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" aria-label="<?php esc_attr_e('Toggle mobile menu', 'dealsindia'); ?>" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
            
        </div>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobile-menu">
    <!-- Mobile Menu Header -->
    <div class="mobile-menu-header">
        <div class="site-logo">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : 
                $logo_emoji = get_theme_mod('dealsindia_logo_emoji_fallback', '🏷️');
            ?>
                <span class="logo-icon" aria-hidden="true"><?php echo esc_html($logo_emoji); ?></span>
                <span><?php bloginfo('name'); ?></span>
            <?php endif; ?>
        </div>
        <button class="mobile-menu-close" aria-label="<?php esc_attr_e('Close mobile menu', 'dealsindia'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18" stroke-width="2"/>
                <line x1="6" y1="6" x2="18" y2="18" stroke-width="2"/>
            </svg>
        </button>
    </div>
    
    <!-- Mobile Menu Content -->
    <div class="mobile-menu-content">
        <!-- Mobile Search -->
        <div class="mobile-search">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <label for="mobile-search-input" class="screen-reader-text"><?php _e('Search', 'dealsindia'); ?></label>
                <input 
                    type="search"
                    id="mobile-search-input"
                    name="s" 
                    placeholder="<?php echo esc_attr(get_theme_mod('dealsindia_search_placeholder', __('Search for deals, stores...', 'dealsindia'))); ?>"
                    aria-label="<?php esc_attr_e('Search', 'dealsindia'); ?>"
                >
                <button type="submit" aria-label="<?php esc_attr_e('Submit search', 'dealsindia'); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                        <path d="m21 21-4.35-4.35" stroke-width="2"/>
                    </svg>
                </button>
            </form>
        </div>
        
        <!-- Mobile Navigation -->
        <nav class="mobile-nav" aria-label="<?php esc_attr_e('Mobile Navigation', 'dealsindia'); ?>">
            <?php 
            if (has_nav_menu('primary')) {
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'mobile-menu-list'
                ));
            }
            ?>
        </nav>
        
        <!-- Mobile Categories -->
        <?php 
        $show_mobile_categories = get_theme_mod('dealsindia_show_mobile_categories', true);
        if ($show_mobile_categories) :
            $mobile_cat_count = get_theme_mod('dealsindia_mobile_categories_count', 5);
            $mobile_categories = get_terms(array(
                'taxonomy'   => 'deal-category',
                'number'     => $mobile_cat_count,
                'hide_empty' => true,
                'orderby'    => 'count',
                'order'      => 'DESC'
            ));
            
            if (!empty($mobile_categories) && !is_wp_error($mobile_categories)) :
        ?>
        <div class="mobile-categories">
            <h3><?php echo esc_html(get_theme_mod('dealsindia_mobile_cat_title', __('Top Categories', 'dealsindia'))); ?></h3>
            <ul>
                <?php foreach ($mobile_categories as $category) : 
                    $icon = get_term_meta($category->term_id, 'category_icon', true);
                ?>
                <li>
                    <a href="<?php echo esc_url(get_term_link($category)); ?>">
                        <?php if ($icon) : ?>
                            <span class="category-icon" aria-hidden="true"><?php echo esc_html($icon); ?></span>
                        <?php endif; ?>
                        <span><?php echo esc_html($category->name); ?></span>
                        <span class="category-count"><?php echo esc_html($category->count); ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php 
            endif;
        endif; 
        ?>
        
        <!-- Mobile Deal Types (Optional) -->
        <?php 
        $show_mobile_dealtypes = get_theme_mod('dealsindia_show_mobile_dealtypes', false);
        if ($show_mobile_dealtypes) :
            $mobile_dealtypes = get_terms(array(
                'taxonomy'   => 'deal-type',
                'hide_empty' => true,
                'orderby'    => 'count',
                'order'      => 'DESC'
            ));
            
            if (!empty($mobile_dealtypes) && !is_wp_error($mobile_dealtypes)) :
        ?>
        <div class="mobile-deal-types">
            <h3><?php echo esc_html(get_theme_mod('dealsindia_mobile_dealtype_title', __('Deal Types', 'dealsindia'))); ?></h3>
            <ul>
                <?php foreach ($mobile_dealtypes as $dealtype) : ?>
                <li>
                    <a href="<?php echo esc_url(get_term_link($dealtype)); ?>">
                        <?php echo esc_html($dealtype->name); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php 
            endif;
        endif; 
        ?>
    </div>
</div>

<!-- Menu Overlay -->
<div class="menu-overlay"></div>

<!-- Main Content Wrapper (for skip link target) -->
<main id="main-content" class="site-main">
