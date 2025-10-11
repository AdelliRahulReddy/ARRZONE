<?php
/**
 * Header Template
 * 
 * @package DealsIndia
 * @version 3.1 - With Stores Grid Fix
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <?php if (is_front_page() || is_home()) : ?>
    <style id="stores-grid-fix">
    /* STORES GRID FIX - INJECTED IN HEAD */
    .top-stores-section-premium .stores-grid-cd {
        display: grid !important;
        grid-template-columns: repeat(6, 1fr) !important;
        gap: 16px !important;
        max-width: 1200px !important;
        margin: 0 auto !important;
    }
    @media (max-width: 1199px) {
        .top-stores-section-premium .stores-grid-cd {
            grid-template-columns: repeat(6, 1fr) !important;
        }
    }
    @media (max-width: 967px) {
        .top-stores-section-premium .stores-grid-cd {
            grid-template-columns: repeat(4, 1fr) !important;
        }
    }
    @media (max-width: 767px) {
        .top-stores-section-premium .stores-grid-cd {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }
    @media (max-width: 639px) {
        .top-stores-section-premium .stores-grid-cd {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    </style>
    <?php endif; ?>
    
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Header -->
<header class="site-header">
    
    <!-- Main Header -->
    <div class="header-main">
        <div class="header-content">
            
            <!-- Logo -->
            <div class="header-logo">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-text">
                        <span class="logo-icon">🏷️</span>
                        <span><?php bloginfo('name'); ?></span>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Search Form -->
            <form class="header-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input 
                    type="search" 
                    name="s" 
                    placeholder="<?php esc_attr_e('Search for deals, stores...', 'dealsindia'); ?>" 
                    value="<?php echo get_search_query(); ?>"
                    class="search-input"
                />
                <button type="submit" class="search-btn" aria-label="Search">
                    🔍
                </button>
            </form>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" aria-label="Toggle Menu">
                <span class="hamburger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
            
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="header-nav">
        <div class="container">
            <?php
            if (has_nav_menu('primary')) {
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class' => 'nav-menu',
                    'container' => false,
                    'fallback_cb' => 'dealsindia_fallback_menu'
                ));
            } else {
                dealsindia_fallback_menu();
            }
            ?>
        </div>
    </nav>
    
</header>

<!-- Mobile Navigation Overlay -->
<div class="mobile-nav-overlay"></div>

<!-- Mobile Navigation Menu -->
<div class="mobile-nav-menu">
    
    <!-- Mobile Nav Header -->
    <div class="mobile-nav-header">
        <div class="mobile-nav-logo">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <span class="logo-icon">🏷️</span>
                <span><?php bloginfo('name'); ?></span>
            <?php endif; ?>
        </div>
        <button class="mobile-nav-close" aria-label="Close Menu">
            ✕
        </button>
    </div>
    
    <!-- Mobile Search -->
    <div class="mobile-search">
        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
            <input 
                type="search" 
                name="s" 
                placeholder="<?php esc_attr_e('Search deals...', 'dealsindia'); ?>" 
                value="<?php echo get_search_query(); ?>"
            />
            <button type="submit" aria-label="Search">🔍</button>
        </form>
    </div>
    
    <!-- Mobile Menu Items -->
    <div class="mobile-nav-items">
        <?php
        if (has_nav_menu('primary')) {
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_class' => 'mobile-menu',
                'container' => false,
                'fallback_cb' => 'dealsindia_fallback_menu'
            ));
        } else {
            dealsindia_fallback_menu();
        }
        ?>
    </div>
    
    <!-- Mobile Quick Links -->
    <div class="mobile-quick-links">
        <h4><?php _e('Popular Categories', 'dealsindia'); ?></h4>
        <?php
        $mobile_categories = get_terms(array(
            'taxonomy' => 'deal_category',
            'number' => 5,
            'orderby' => 'count',
            'order' => 'DESC',
            'hide_empty' => true
        ));
        
        if (!empty($mobile_categories) && !is_wp_error($mobile_categories)) :
        ?>
        <ul>
            <?php foreach ($mobile_categories as $cat) : ?>
                <li>
                    <a href="<?php echo esc_url(get_term_link($cat)); ?>">
                        <?php echo esc_html($cat->name); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    
    <!-- Mobile Social Links -->
    <?php 
    $facebook = get_theme_mod('dealsindia_social_facebook');
    $twitter = get_theme_mod('dealsindia_social_twitter');
    $instagram = get_theme_mod('dealsindia_social_instagram');
    
    if ($facebook || $twitter || $instagram) :
    ?>
    <div class="mobile-social">
        <?php if ($facebook) : ?>
            <a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener" aria-label="Facebook">📘</a>
        <?php endif; ?>
        
        <?php if ($twitter) : ?>
            <a href="<?php echo esc_url($twitter); ?>" target="_blank" rel="noopener" aria-label="Twitter">🐦</a>
        <?php endif; ?>
        
        <?php if ($instagram) : ?>
            <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener" aria-label="Instagram">📷</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
</div>
