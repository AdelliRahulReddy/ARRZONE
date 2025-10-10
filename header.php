<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- Premium Header -->
<header class="site-header">
    
    <!-- Top Bar (Logo + Search) -->
    <div class="header-main">
        <div class="container">
            <div class="header-content">
                
                <!-- Logo -->
                <div class="header-logo">
                    <?php if (has_custom_logo()) : ?>
                        <?php the_custom_logo(); ?>
                    <?php else : ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-text">
                            <span class="logo-icon">🛍️</span>
                            <span class="logo-name"><?php bloginfo('name'); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Search Bar -->
                <div class="header-search">
                    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search-form">
                        <input type="search" 
                               name="s" 
                               placeholder="Search for deals, stores, categories..." 
                               class="search-input"
                               value="<?php echo get_search_query(); ?>">
                        <button type="submit" class="search-button">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </form>
                </div>
                
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <div class="header-nav">
        <div class="container">
            <nav class="main-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'nav-menu-items',
                    'fallback_cb'    => 'dealsindia_fallback_menu',
                ));
                ?>
            </nav>
        </div>
    </div>
    
</header>

<!-- Welcome Banner (Optional) -->
<?php if (dealsindia_show_welcome_banner()) : ?>
<div class="welcome-banner">
    <div class="container">
        <p><?php echo esc_html(dealsindia_get_welcome_banner_text()); ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Mobile Navigation Overlay -->
<div class="mobile-nav-overlay"></div>
<nav class="mobile-nav-menu">
    <button class="mobile-nav-close" aria-label="Close menu">✕</button>
    <?php
    wp_nav_menu(array(
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'mobile-menu-items',
        'fallback_cb'    => 'dealsindia_fallback_menu',
    ));
    ?>
</nav>

<main class="site-content">
