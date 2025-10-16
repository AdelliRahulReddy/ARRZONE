<?php
if (!defined('ABSPATH')) exit; 
/**
 * Theme Configuration - WordPress Customizer Settings
 * 100% Dynamic - All theme elements controlled from admin
 * 
 * @package ARRZONE
 * @version 6.0 - Complete with All Sections
 */

if (!defined('ABSPATH')) exit;

/**
 * Register WordPress Customizer Settings
 */
function dealsindia_customize_register($wp_customize) {
    
    // ========================================
    // ANNOUNCEMENT BAR SETTINGS
    // ========================================
    $wp_customize->add_section('dealsindia_announcement_bar', array(
        'title'    => __('Announcement Bar', 'dealsindia'),
        'priority' => 25,
    ));
    
    $wp_customize->add_setting('dealsindia_show_announcement_bar', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('dealsindia_show_announcement_bar', array(
        'label'   => __('Enable Announcement Bar', 'dealsindia'),
        'section' => 'dealsindia_announcement_bar',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('dealsindia_announcement_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_announcement_text', array(
        'label'   => __('Announcement Text', 'dealsindia'),
        'section' => 'dealsindia_announcement_bar',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_announcement_link', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('dealsindia_announcement_link', array(
        'label'   => __('Announcement Link (Optional)', 'dealsindia'),
        'section' => 'dealsindia_announcement_bar',
        'type'    => 'url',
    ));
    
    $wp_customize->add_setting('dealsindia_announcement_bg_color', array(
        'default'           => '#ff6b6b',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'dealsindia_announcement_bg_color', array(
        'label'    => __('Background Color', 'dealsindia'),
        'section'  => 'dealsindia_announcement_bar',
        'settings' => 'dealsindia_announcement_bg_color',
    )));
    
    $wp_customize->add_setting('dealsindia_announcement_text_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'dealsindia_announcement_text_color', array(
        'label'    => __('Text Color', 'dealsindia'),
        'section'  => 'dealsindia_announcement_bar',
        'settings' => 'dealsindia_announcement_text_color',
    )));
    
    $wp_customize->add_setting('dealsindia_announcement_closeable', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('dealsindia_announcement_closeable', array(
        'label'   => __('Show Close Button', 'dealsindia'),
        'section' => 'dealsindia_announcement_bar',
        'type'    => 'checkbox',
    ));
    
    // ========================================
    // TOP BAR SETTINGS
    // ========================================
    $wp_customize->add_section('dealsindia_topbar', array(
        'title'    => __('Top Bar', 'dealsindia'),
        'priority' => 26,
    ));
    
    $wp_customize->add_setting('dealsindia_show_topbar', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('dealsindia_show_topbar', array(
        'label'   => __('Enable Top Bar', 'dealsindia'),
        'section' => 'dealsindia_topbar',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('dealsindia_topbar_message', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_topbar_message', array(
        'label'   => __('Top Bar Message', 'dealsindia'),
        'section' => 'dealsindia_topbar',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_topbar_phone', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_topbar_phone', array(
        'label'   => __('Phone Number', 'dealsindia'),
        'section' => 'dealsindia_topbar',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_topbar_email', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_email',
    ));
    $wp_customize->add_control('dealsindia_topbar_email', array(
        'label'   => __('Email Address', 'dealsindia'),
        'section' => 'dealsindia_topbar',
        'type'    => 'email',
    ));
    
    // ========================================
    // HEADER SETTINGS
    // ========================================
    $wp_customize->add_section('dealsindia_header', array(
        'title'    => __('Header Settings', 'dealsindia'),
        'priority' => 27,
    ));
    
    $wp_customize->add_setting('dealsindia_sticky_header', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('dealsindia_sticky_header', array(
        'label'   => __('Enable Sticky Header', 'dealsindia'),
        'section' => 'dealsindia_header',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('dealsindia_logo_emoji_fallback', array(
        'default'           => '🏷️',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_logo_emoji_fallback', array(
        'label'       => __('Logo Emoji Fallback', 'dealsindia'),
        'description' => __('Used when no custom logo is uploaded', 'dealsindia'),
        'section'     => 'dealsindia_header',
        'type'        => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_search_placeholder', array(
        'default'           => __('Search for deals, stores...', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_search_placeholder', array(
        'label'   => __('Search Placeholder Text', 'dealsindia'),
        'section' => 'dealsindia_header',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_show_header_cta', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('dealsindia_show_header_cta', array(
        'label'   => __('Enable Header CTA Button', 'dealsindia'),
        'section' => 'dealsindia_header',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('dealsindia_header_cta_text', array(
        'default'           => __('Submit Deal', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_header_cta_text', array(
        'label'   => __('CTA Button Text', 'dealsindia'),
        'section' => 'dealsindia_header',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_header_cta_link', array(
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('dealsindia_header_cta_link', array(
        'label'   => __('CTA Button Link', 'dealsindia'),
        'section' => 'dealsindia_header',
        'type'    => 'url',
    ));
    
    $wp_customize->add_setting('dealsindia_header_cta_style', array(
        'default'           => 'primary',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_header_cta_style', array(
        'label'   => __('CTA Button Style', 'dealsindia'),
        'section' => 'dealsindia_header',
        'type'    => 'select',
        'choices' => array(
            'primary'   => __('Primary', 'dealsindia'),
            'secondary' => __('Secondary', 'dealsindia'),
            'outline'   => __('Outline', 'dealsindia'),
        ),
    ));
    
    // ========================================
    // MOBILE MENU SETTINGS
    // ========================================
    $wp_customize->add_section('dealsindia_mobile_menu', array(
        'title'    => __('Mobile Menu Settings', 'dealsindia'),
        'priority' => 28,
    ));
    
    $wp_customize->add_setting('dealsindia_show_mobile_categories', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('dealsindia_show_mobile_categories', array(
        'label'   => __('Show Categories in Mobile Menu', 'dealsindia'),
        'section' => 'dealsindia_mobile_menu',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('dealsindia_mobile_cat_title', array(
        'default'           => __('Top Categories', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_mobile_cat_title', array(
        'label'   => __('Categories Section Title', 'dealsindia'),
        'section' => 'dealsindia_mobile_menu',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_mobile_categories_count', array(
        'default'           => 5,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_mobile_categories_count', array(
        'label'   => __('Number of Categories to Show', 'dealsindia'),
        'section' => 'dealsindia_mobile_menu',
        'type'    => 'number',
        'input_attrs' => array(
            'min'  => 1,
            'max'  => 20,
            'step' => 1,
        ),
    ));
    
    $wp_customize->add_setting('dealsindia_show_mobile_dealtypes', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('dealsindia_show_mobile_dealtypes', array(
        'label'   => __('Show Deal Types in Mobile Menu', 'dealsindia'),
        'section' => 'dealsindia_mobile_menu',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('dealsindia_mobile_dealtype_title', array(
        'default'           => __('Deal Types', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_mobile_dealtype_title', array(
        'label'   => __('Deal Types Section Title', 'dealsindia'),
        'section' => 'dealsindia_mobile_menu',
        'type'    => 'text',
    ));
    
    // ========================================
    // HOMEPAGE HERO SECTION
    // ========================================
    $wp_customize->add_section('dealsindia_hero', array(
        'title'    => __('Homepage Hero Section', 'dealsindia'),
        'priority' => 30,
    ));
    
    $wp_customize->add_setting('hero_title', array(
        'default'           => __('Find Amazing Deals & Discounts', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_title', array(
        'label'   => __('Hero Title', 'dealsindia'),
        'section' => 'dealsindia_hero',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('hero_subtitle', array(
        'default'           => __('Save money with exclusive coupons, offers & cashback from 3000+ stores', 'dealsindia'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('hero_subtitle', array(
        'label'   => __('Hero Subtitle', 'dealsindia'),
        'section' => 'dealsindia_hero',
        'type'    => 'textarea',
    ));
    
    $wp_customize->add_setting('hero_cta_text', array(
        'default'           => __('Browse All Deals', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hero_cta_text', array(
        'label'   => __('Hero Button Text', 'dealsindia'),
        'section' => 'dealsindia_hero',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('hero_cta_link', array(
        'default'           => '/deals',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('hero_cta_link', array(
        'label'   => __('Hero Button Link', 'dealsindia'),
        'section' => 'dealsindia_hero',
        'type'    => 'url',
    ));
    
    // ========================================
    // WORK STEPS SECTION
    // ========================================
    $wp_customize->add_section('dealsindia_work_steps', array(
        'title'    => __('How It Works Section', 'dealsindia'),
        'priority' => 40,
    ));
    
    $wp_customize->add_setting('work_steps_title', array(
        'default'           => __('How It Works', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('work_steps_title', array(
        'label'   => __('Section Title', 'dealsindia'),
        'section' => 'dealsindia_work_steps',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('step1_title', array(
        'default'           => __('Browse Deals', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('step1_title', array(
        'label'   => __('Step 1 Title', 'dealsindia'),
        'section' => 'dealsindia_work_steps',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('step1_desc', array(
        'default'           => __('Find verified deals from your favorite stores', 'dealsindia'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('step1_desc', array(
        'label'   => __('Step 1 Description', 'dealsindia'),
        'section' => 'dealsindia_work_steps',
        'type'    => 'textarea',
    ));
    
    $wp_customize->add_setting('step2_title', array(
        'default'           => __('Get Coupon Code', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('step2_title', array(
        'label'   => __('Step 2 Title', 'dealsindia'),
        'section' => 'dealsindia_work_steps',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('step2_desc', array(
        'default'           => __('Click to reveal and copy the promo code', 'dealsindia'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('step2_desc', array(
        'label'   => __('Step 2 Description', 'dealsindia'),
        'section' => 'dealsindia_work_steps',
        'type'    => 'textarea',
    ));
    
    $wp_customize->add_setting('step3_title', array(
        'default'           => __('Save Money', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('step3_title', array(
        'label'   => __('Step 3 Title', 'dealsindia'),
        'section' => 'dealsindia_work_steps',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('step3_desc', array(
        'default'           => __('Apply code at checkout and enjoy savings!', 'dealsindia'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('step3_desc', array(
        'label'   => __('Step 3 Description', 'dealsindia'),
        'section' => 'dealsindia_work_steps',
        'type'    => 'textarea',
    ));
    
    // ========================================
    // HOT PICKS SECTION
    // ========================================
    $wp_customize->add_section('dealsindia_hot_picks', array(
        'title'    => __('Hot Picks Section', 'dealsindia'),
        'priority' => 45,
    ));
    
    $wp_customize->add_setting('hot_picks_title', array(
        'default'           => __('🔥 Festival Hot Picks!', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hot_picks_title', array(
        'label'   => __('Section Title', 'dealsindia'),
        'section' => 'dealsindia_hot_picks',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('hot_picks_button_text', array(
        'default'           => __('See More', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hot_picks_button_text', array(
        'label'   => __('Button Text', 'dealsindia'),
        'section' => 'dealsindia_hot_picks',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('hot_picks_bg_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hot_picks_bg_image', array(
        'label'    => __('Background Image', 'dealsindia'),
        'section'  => 'dealsindia_hot_picks',
        'settings' => 'hot_picks_bg_image',
    )));
    
    // ========================================
    // TOP STORES SECTION
    // ========================================
    $wp_customize->add_section('dealsindia_top_stores', array(
        'title'    => __('Top Stores Section', 'dealsindia'),
        'priority' => 50,
    ));
    
    $wp_customize->add_setting('top_stores_title', array(
        'default'           => __('Top Stores', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('top_stores_title', array(
        'label'   => __('Section Title', 'dealsindia'),
        'section' => 'dealsindia_top_stores',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('top_stores_button_text', array(
        'default'           => __('View All Stores', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('top_stores_button_text', array(
        'label'   => __('Button Text', 'dealsindia'),
        'section' => 'dealsindia_top_stores',
        'type'    => 'text',
    ));
    
    // ========================================
    // NEWSLETTER SECTION (HOMEPAGE)
    // ========================================
    $wp_customize->add_section('dealsindia_newsletter', array(
        'title'    => __('Newsletter Section (Homepage)', 'dealsindia'),
        'priority' => 55,
    ));
    
    $wp_customize->add_setting('newsletter_title', array(
        'default'           => __('Never Miss a Deal!', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_title', array(
        'label'   => __('Newsletter Title', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('newsletter_subtitle', array(
        'default'           => __('Subscribe to get the best deals delivered to your inbox', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_subtitle', array(
        'label'   => __('Newsletter Subtitle', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('newsletter_btn_text', array(
        'default'           => __('Subscribe', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_btn_text', array(
        'label'   => __('Button Text', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('newsletter_placeholder', array(
        'default'           => __('Enter your email address', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_placeholder', array(
        'label'   => __('Input Placeholder', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type'    => 'text',
    ));
    
    // ========================================
    // NEWSLETTER SECTION (FOOTER)
    // ========================================
    $wp_customize->add_section('dealsindia_newsletter_footer', array(
        'title'    => __('Newsletter (Footer)', 'dealsindia'),
        'priority' => 115,
    ));
    
    $wp_customize->add_setting('dealsindia_show_newsletter', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('dealsindia_show_newsletter', array(
        'label'   => __('Show Newsletter Section', 'dealsindia'),
        'section' => 'dealsindia_newsletter_footer',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('dealsindia_newsletter_icon', array(
        'default'           => '📧',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_newsletter_icon', array(
        'label'   => __('Newsletter Icon (Emoji)', 'dealsindia'),
        'section' => 'dealsindia_newsletter_footer',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_newsletter_title', array(
        'default'           => __('Never Miss a Deal!', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_newsletter_title', array(
        'label'   => __('Newsletter Title', 'dealsindia'),
        'section' => 'dealsindia_newsletter_footer',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_newsletter_subtitle', array(
        'default'           => __('Subscribe to get the hottest deals delivered to your inbox.', 'dealsindia'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('dealsindia_newsletter_subtitle', array(
        'label'   => __('Newsletter Subtitle', 'dealsindia'),
        'section' => 'dealsindia_newsletter_footer',
        'type'    => 'textarea',
    ));
    
    $wp_customize->add_setting('dealsindia_newsletter_placeholder', array(
        'default'           => __('Enter your email address', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_newsletter_placeholder', array(
        'label'   => __('Email Input Placeholder', 'dealsindia'),
        'section' => 'dealsindia_newsletter_footer',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_newsletter_button_text', array(
        'default'           => __('Subscribe', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_newsletter_button_text', array(
        'label'   => __('Button Text', 'dealsindia'),
        'section' => 'dealsindia_newsletter_footer',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_newsletter_privacy_text', array(
        'default'           => __('We respect your privacy. Unsubscribe anytime.', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_newsletter_privacy_text', array(
        'label'   => __('Privacy Text', 'dealsindia'),
        'section' => 'dealsindia_newsletter_footer',
        'type'    => 'text',
    ));
    
    // ========================================
    // FOOTER - ABOUT SECTION
    // ========================================
    $wp_customize->add_section('dealsindia_footer_about', array(
        'title'    => __('Footer - About Section', 'dealsindia'),
        'priority' => 116,
    ));
    
    $wp_customize->add_setting('dealsindia_footer_about', array(
        'default'           => get_bloginfo('description'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('dealsindia_footer_about', array(
        'label'   => __('About Description', 'dealsindia'),
        'section' => 'dealsindia_footer_about',
        'type'    => 'textarea',
    ));
    
    // ========================================
    // FOOTER - SOCIAL MEDIA LINKS
    // ========================================
    $wp_customize->add_section('dealsindia_footer_social_links', array(
        'title'    => __('Footer - Social Media', 'dealsindia'),
        'priority' => 117,
    ));
    
    $social_networks = array(
        'facebook'  => __('Facebook', 'dealsindia'),
        'twitter'   => __('Twitter', 'dealsindia'),
        'instagram' => __('Instagram', 'dealsindia'),
        'youtube'   => __('YouTube', 'dealsindia'),
        'telegram'  => __('Telegram', 'dealsindia'),
    );
    
    foreach ($social_networks as $network => $label) {
        $wp_customize->add_setting('dealsindia_social_' . $network, array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control('dealsindia_social_' . $network, array(
            'label'       => sprintf(__('%s URL', 'dealsindia'), $label),
            'description' => sprintf(__('Enter your %s profile URL', 'dealsindia'), $label),
            'section'     => 'dealsindia_footer_social_links',
            'type'        => 'url',
        ));
    }
    
    // ========================================
    // FOOTER - COLUMN SETTINGS
    // ========================================
    $wp_customize->add_section('dealsindia_footer_columns', array(
        'title'    => __('Footer - Column Settings', 'dealsindia'),
        'priority' => 118,
    ));
    
    $wp_customize->add_setting('dealsindia_footer_links_title', array(
        'default'           => __('Quick Links', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_footer_links_title', array(
        'label'   => __('Quick Links Column Title', 'dealsindia'),
        'section' => 'dealsindia_footer_columns',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_footer_categories_title', array(
        'default'           => __('Popular Categories', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_footer_categories_title', array(
        'label'   => __('Categories Column Title', 'dealsindia'),
        'section' => 'dealsindia_footer_columns',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_footer_categories_count', array(
        'default'           => 6,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_footer_categories_count', array(
        'label'       => __('Number of Categories to Display', 'dealsindia'),
        'description' => __('Maximum 12', 'dealsindia'),
        'section'     => 'dealsindia_footer_columns',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 1,
            'max'  => 12,
            'step' => 1,
        ),
    ));
    
    $wp_customize->add_setting('dealsindia_footer_stores_title', array(
        'default'           => __('Top Stores', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_footer_stores_title', array(
        'label'   => __('Stores Column Title', 'dealsindia'),
        'section' => 'dealsindia_footer_columns',
        'type'    => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_footer_stores_count', array(
        'default'           => 6,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_footer_stores_count', array(
        'label'       => __('Number of Stores to Display', 'dealsindia'),
        'description' => __('Maximum 12', 'dealsindia'),
        'section'     => 'dealsindia_footer_columns',
        'type'        => 'number',
        'input_attrs' => array(
            'min'  => 1,
            'max'  => 12,
            'step' => 1,
        ),
    ));
    
    // ========================================
    // FOOTER - BOTTOM SECTION
    // ========================================
    $wp_customize->add_section('dealsindia_footer_bottom', array(
        'title'    => __('Footer - Bottom Bar', 'dealsindia'),
        'priority' => 119,
    ));
    
    $wp_customize->add_setting('dealsindia_copyright_text', array(
        'default'           => '&copy; {year} ' . get_bloginfo('name') . '. All Rights Reserved.',
        'sanitize_callback' => 'wp_kses_post',
    ));
    $wp_customize->add_control('dealsindia_copyright_text', array(
        'label'       => __('Copyright Text', 'dealsindia'),
        'description' => __('Use {year} to automatically insert current year', 'dealsindia'),
        'section'     => 'dealsindia_footer_bottom',
        'type'        => 'text',
    ));
    
    $wp_customize->add_setting('dealsindia_show_payment_methods', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('dealsindia_show_payment_methods', array(
        'label'   => __('Show Payment Methods', 'dealsindia'),
        'section' => 'dealsindia_footer_bottom',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('dealsindia_payment_text', array(
        'default'           => __('We Accept:', 'dealsindia'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_payment_text', array(
        'label'   => __('Payment Methods Label', 'dealsindia'),
        'section' => 'dealsindia_footer_bottom',
        'type'    => 'text',
    ));
    
}
add_action('customize_register', 'dealsindia_customize_register');

// ===================================================== 
// SEO ENHANCEMENTS
// ===================================================== 

/**
 * Enable WordPress Title Tag Support
 * Automatically generates <title> tags for all pages
 */
add_action('after_setup_theme', 'arrzone_seo_setup');
function arrzone_seo_setup() {
    // Enable automatic title tag generation
    add_theme_support('title-tag');
    
    // Enable HTML5 support for better markup
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style'
    ));
}

/**
 * Add custom post types to sitemap
 */
add_filter('wp_sitemaps_post_types', 'arrzone_add_sitemap_post_types');
function arrzone_add_sitemap_post_types($post_types) {
    // Add deals to sitemap
    if (isset($post_types['deals'])) {
        $post_types['deals']->name = 'deals';
    }
    
    // Add giveaways to sitemap
    if (isset($post_types['giveaway'])) {
        $post_types['giveaway']->name = 'giveaway';
    }
    
    return $post_types;
}

/**
 * Add taxonomies to sitemap
 */
add_filter('wp_sitemaps_taxonomies', 'arrzone_add_sitemap_taxonomies');
function arrzone_add_sitemap_taxonomies($taxonomies) {
    // Add deal categories
    if (isset($taxonomies['deal-category'])) {
        $taxonomies['deal-category']->name = 'deal-category';
    }
    
    // Add stores
    if (isset($taxonomies['store'])) {
        $taxonomies['store']->name = 'store';
    }
    
    return $taxonomies;
}
