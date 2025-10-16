<?php
if (!defined('ABSPATH')) exit; 
/**
 * Footer Template - Enhanced & 100% Dynamic
 * 
 * Features:
 * - Newsletter subscription section
 * - 4-column footer menu system
 * - Dynamic social media links
 * - Payment methods display
 * - Copyright with year replacement
 * - Mobile responsive
 * - All content from WordPress Customizer
 * 
 * @package ARRZONE
 * @version 5.0 - Enhanced Dynamic
 */
?>

<!-- Newsletter Section (Above Footer) -->
<?php if (get_theme_mod('dealsindia_show_newsletter', true)) : ?>
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-wrapper">
            <div class="newsletter-content">
                <?php if (get_theme_mod('dealsindia_newsletter_icon', '📧')) : ?>
                    <div class="newsletter-icon">
                        <?php echo esc_html(get_theme_mod('dealsindia_newsletter_icon', '📧')); ?>
                    </div>
                <?php endif; ?>
                
                <div class="newsletter-text">
                    <h3 class="newsletter-title">
                        <?php echo esc_html(get_theme_mod('dealsindia_newsletter_title', 'Never Miss a Deal!')); ?>
                    </h3>
                    <p class="newsletter-description">
                        <?php echo esc_html(get_theme_mod('dealsindia_newsletter_subtitle', 'Subscribe to get the hottest deals delivered to your inbox.')); ?>
                    </p>
                </div>
            </div>
            
            <form class="newsletter-form" id="newsletterForm" method="post">
                <input 
                    type="email" 
                    name="newsletter_email" 
                    class="newsletter-input" 
                    placeholder="<?php echo esc_attr(get_theme_mod('dealsindia_newsletter_placeholder', 'Enter your email address')); ?>" 
                    required
                >
                <button type="submit" class="newsletter-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="22" y1="2" x2="11" y2="13" stroke-width="2"/>
                        <polygon points="22 2 15 22 11 13 2 9 22 2" stroke-width="2"/>
                    </svg>
                    <?php echo esc_html(get_theme_mod('dealsindia_newsletter_button_text', 'Subscribe')); ?>
                </button>
                <?php wp_nonce_field('dealsindia_newsletter', 'newsletter_nonce'); ?>
            </form>
            
            <div class="newsletter-message" id="newsletterMessage" style="display: none;"></div>
            
            <?php if (get_theme_mod('dealsindia_newsletter_privacy_text')) : ?>
                <p class="newsletter-privacy">
                    🔒 <?php echo esc_html(get_theme_mod('dealsindia_newsletter_privacy_text', 'We respect your privacy. Unsubscribe anytime.')); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Main Footer -->
<footer class="site-footer">
    <div class="container">
        
        <!-- Footer Content Grid -->
        <div class="footer-content">
            
            <!-- Column 1: About & Social -->
            <div class="footer-section footer-about">
                <?php if (has_custom_logo()) : ?>
                    <div class="footer-logo">
                        <?php the_custom_logo(); ?>
                    </div>
                <?php else : ?>
                    <h3 class="footer-brand"><?php bloginfo('name'); ?></h3>
                <?php endif; ?>
                
                <p class="footer-description">
                    <?php echo esc_html(get_theme_mod('dealsindia_footer_about', get_bloginfo('description'))); ?>
                </p>
                
                <!-- Social Media Links - Dynamic -->
                <?php
                $facebook = get_theme_mod('dealsindia_social_facebook');
                $twitter = get_theme_mod('dealsindia_social_twitter');
                $instagram = get_theme_mod('dealsindia_social_instagram');
                $youtube = get_theme_mod('dealsindia_social_youtube');
                $telegram = get_theme_mod('dealsindia_social_telegram');
                
                if ($facebook || $twitter || $instagram || $youtube || $telegram) :
                ?>
                    <div class="footer-social">
                        <?php if ($facebook) : ?>
                            <a href="<?php echo esc_url($facebook); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($twitter) : ?>
                            <a href="<?php echo esc_url($twitter); ?>" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($instagram) : ?>
                            <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($youtube) : ?>
                            <a href="<?php echo esc_url($youtube); ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($telegram) : ?>
                            <a href="<?php echo esc_url($telegram); ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Column 2: Quick Links -->
            <div class="footer-section footer-links">
                <h4><?php echo esc_html(get_theme_mod('dealsindia_footer_links_title', 'Quick Links')); ?></h4>
                <?php
                if (has_nav_menu('footer-links')) {
                    wp_nav_menu(array(
                        'theme_location' => 'footer-links',
                        'menu_class'     => 'footer-menu',
                        'container'      => false,
                        'depth'          => 1,
                    ));
                } else {
                    // Fallback menu
                    echo '<ul class="footer-menu">';
                    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'dealsindia') . '</a></li>';
                    echo '<li><a href="' . esc_url(home_url('/about-us/')) . '">' . esc_html__('About Us', 'dealsindia') . '</a></li>';
                    echo '<li><a href="' . esc_url(home_url('/contact/')) . '">' . esc_html__('Contact', 'dealsindia') . '</a></li>';
                    echo '<li><a href="' . esc_url(home_url('/blog/')) . '">' . esc_html__('Blog', 'dealsindia') . '</a></li>';
                    echo '</ul>';
                }
                ?>
            </div>
            
            <!-- Column 3: Popular Categories -->
            <div class="footer-section footer-categories">
                <h4><?php echo esc_html(get_theme_mod('dealsindia_footer_categories_title', 'Popular Categories')); ?></h4>
                <?php
                $category_count = get_theme_mod('dealsindia_footer_categories_count', 6);
                $categories = get_terms(array(
                    'taxonomy'   => 'deal-category',
                    'number'     => $category_count,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                    'hide_empty' => true
                ));
                
                if (!empty($categories) && !is_wp_error($categories)) :
                ?>
                    <ul class="footer-menu">
                        <?php foreach ($categories as $category) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_term_link($category)); ?>">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php esc_html_e('No categories available yet.', 'dealsindia'); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Column 4: Top Stores -->
            <div class="footer-section footer-stores">
                <h4><?php echo esc_html(get_theme_mod('dealsindia_footer_stores_title', 'Top Stores')); ?></h4>
                <?php
                $store_count = get_theme_mod('dealsindia_footer_stores_count', 6);
                $stores = get_terms(array(
                    'taxonomy'   => 'store',
                    'number'     => $store_count,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                    'hide_empty' => true
                ));
                
                if (!empty($stores) && !is_wp_error($stores)) :
                ?>
                    <ul class="footer-menu">
                        <?php foreach ($stores as $store) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_term_link($store)); ?>">
                                    <?php echo esc_html($store->name); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p><?php esc_html_e('No stores available yet.', 'dealsindia'); ?></p>
                <?php endif; ?>
            </div>
            
        </div><!-- .footer-content -->
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <p class="copyright">
                    <?php
                    $copyright_text = get_theme_mod('dealsindia_copyright_text', '&copy; {year} ' . get_bloginfo('name') . '. All Rights Reserved.');
                    echo wp_kses_post(str_replace('{year}', date('Y'), $copyright_text));
                    ?>
                </p>
                
                <!-- Payment Methods -->
                <?php if (get_theme_mod('dealsindia_show_payment_methods', true)) : ?>
                    <div class="footer-payment-methods">
                        <span><?php echo esc_html(get_theme_mod('dealsindia_payment_text', 'We Accept:')); ?></span>
                        <svg width="40" height="25" viewBox="0 0 40 25" fill="none">
                            <rect width="40" height="25" rx="3" fill="#1434CB"/>
                            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="10" font-weight="bold">VISA</text>
                        </svg>
                        <svg width="40" height="25" viewBox="0 0 40 25" fill="none">
                            <rect width="40" height="25" rx="3" fill="#EB001B"/>
                            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="8" font-weight="bold">MC</text>
                        </svg>
                        <svg width="40" height="25" viewBox="0 0 40 25" fill="none">
                            <rect width="40" height="25" rx="3" fill="#00BAF2"/>
                            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="9" font-weight="bold">UPI</text>
                        </svg>
                    </div>
                <?php endif; ?>
            </div>
        </div><!-- .footer-bottom -->
        
    </div><!-- .container -->
</footer>

<?php wp_footer(); ?>

</body>
</html>
