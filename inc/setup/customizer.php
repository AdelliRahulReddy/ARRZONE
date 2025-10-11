<?php
/**
 * Theme Customizer Settings - COMPLETE
 * All Homepage Sections with Full Control
 * 
 * @package DealsIndia
 * @version 5.0 - COMPLETE
 */

if (!defined('ABSPATH')) exit;

function dealsindia_customize_register($wp_customize) {
    
    // ===================================================== 
    // SECTION: Site Settings
    // ===================================================== 
    $wp_customize->add_section('dealsindia_site_settings', array(
        'title' => __('Site Settings', 'dealsindia'),
        'priority' => 30,
    ));

    // Welcome Banner
    $wp_customize->add_setting('dealsindia_show_welcome_banner', array(
        'default' => '1',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_show_welcome_banner', array(
        'label' => __('Show Welcome Banner', 'dealsindia'),
        'section' => 'dealsindia_site_settings',
        'type' => 'checkbox',
    ));

    $wp_customize->add_setting('welcome_text', array(
        'default' => 'Welcome to ' . get_bloginfo('name') . ' - India\'s Trusted Coupons, Offers & Cashback Website',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('welcome_text', array(
        'label' => __('Welcome Banner Text', 'dealsindia'),
        'section' => 'dealsindia_site_settings',
        'type' => 'text',
    ));

    // ===================================================== 
    // SECTION: How It Works
    // ===================================================== 
    $wp_customize->add_section('dealsindia_how_it_works', array(
        'title' => __('How It Works Section', 'dealsindia'),
        'priority' => 35,
    ));

    $wp_customize->add_setting('steps_section_title', array(
        'default' => 'Three Steps To Save With ' . get_bloginfo('name'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('steps_section_title', array(
        'label' => __('Section Title', 'dealsindia'),
        'section' => 'dealsindia_how_it_works',
        'type' => 'text',
    ));

    // ===================================================== 
    // SECTION: Hot Picks
    // ===================================================== 
    $wp_customize->add_section('dealsindia_hot_picks', array(
        'title' => __('Hot Picks Section', 'dealsindia'),
        'priority' => 40,
        'description' => __('Customize the Hot Picks section on your homepage', 'dealsindia'),
    ));

    // Show/Hide Hot Picks
    $wp_customize->add_setting('dealsindia_show_hot_picks', array(
        'default' => '1',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_show_hot_picks', array(
        'label' => __('Show Hot Picks Section', 'dealsindia'),
        'section' => 'dealsindia_hot_picks',
        'type' => 'checkbox',
    ));

    // Background Image
    $wp_customize->add_setting('hot_picks_bg_image', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hot_picks_bg_image', array(
        'label'    => __('Background Image', 'dealsindia'),
        'section'  => 'dealsindia_hot_picks',
        'settings' => 'hot_picks_bg_image',
        'description' => __('Upload background image (1920x500px recommended). Leave empty for gradient.', 'dealsindia'),
    )));

    // Section Title
    $wp_customize->add_setting('hot_picks_title', array(
        'default' => 'Festival Hot Picks!',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hot_picks_title', array(
        'label' => __('Section Title', 'dealsindia'),
        'section' => 'dealsindia_hot_picks',
        'type' => 'text',
    ));

    // Button Text
    $wp_customize->add_setting('hot_picks_button_text', array(
        'default' => 'See More',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('hot_picks_button_text', array(
        'label' => __('Button Text', 'dealsindia'),
        'section' => 'dealsindia_hot_picks',
        'type' => 'text',
    ));

    // Number of Deals
    $wp_customize->add_setting('hot_picks_count', array(
        'default' => 12,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('hot_picks_count', array(
        'label' => __('Number of Deals to Show', 'dealsindia'),
        'section' => 'dealsindia_hot_picks',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 6,
            'max' => 20,
            'step' => 1,
        ),
    ));

    // No Hot Picks Message
    $wp_customize->add_setting('no_hot_picks_message', array(
        'default' => 'No hot picks available right now. Check back soon for amazing deals!',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('no_hot_picks_message', array(
        'label' => __('No Deals Message', 'dealsindia'),
        'section' => 'dealsindia_hot_picks',
        'type' => 'text',
    ));

    // ===================================================== 
    // SECTION: Latest Deals
    // ===================================================== 
    $wp_customize->add_section('dealsindia_latest_deals', array(
        'title' => __('Latest Deals Section', 'dealsindia'),
        'priority' => 45,
    ));

    $wp_customize->add_setting('dealsindia_show_latest_deals', array(
        'default' => '1',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_show_latest_deals', array(
        'label' => __('Show Latest Deals Section', 'dealsindia'),
        'section' => 'dealsindia_latest_deals',
        'type' => 'checkbox',
    ));

    // Section Icon
    $wp_customize->add_setting('latest_deals_icon', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('latest_deals_icon', array(
        'label' => __('Section Icon (Emoji)', 'dealsindia'),
        'section' => 'dealsindia_latest_deals',
        'type' => 'text',
        'description' => __('Leave empty for no icon. Example: ⚡', 'dealsindia'),
    ));

    $wp_customize->add_setting('latest_deals_title', array(
        'default' => 'Latest Deals',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('latest_deals_title', array(
        'label' => __('Section Title', 'dealsindia'),
        'section' => 'dealsindia_latest_deals',
        'type' => 'text',
    ));

    $wp_customize->add_setting('latest_deals_subtitle', array(
        'default' => 'Fresh deals added daily',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('latest_deals_subtitle', array(
        'label' => __('Section Subtitle', 'dealsindia'),
        'section' => 'dealsindia_latest_deals',
        'type' => 'text',
    ));

    $wp_customize->add_setting('latest_deals_count', array(
        'default' => 8,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('latest_deals_count', array(
        'label' => __('Number of Deals to Show', 'dealsindia'),
        'section' => 'dealsindia_latest_deals',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 4,
            'max' => 16,
            'step' => 2,
        ),
    ));

    $wp_customize->add_setting('view_all_btn_text', array(
        'default' => 'View All Deals',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('view_all_btn_text', array(
        'label' => __('View All Button Text', 'dealsindia'),
        'section' => 'dealsindia_latest_deals',
        'type' => 'text',
    ));

    // ===================================================== 
    // SECTION: Top Offers
    // ===================================================== 
    $wp_customize->add_section('dealsindia_top_offers', array(
        'title' => __('Top Offers Section', 'dealsindia'),
        'priority' => 50,
    ));

    $wp_customize->add_setting('dealsindia_show_top_offers', array(
        'default' => '1',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_show_top_offers', array(
        'label' => __('Show Top Offers Section', 'dealsindia'),
        'section' => 'dealsindia_top_offers',
        'type' => 'checkbox',
    ));

    // Section Icon
    $wp_customize->add_setting('offers_section_icon', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('offers_section_icon', array(
        'label' => __('Section Icon (Emoji)', 'dealsindia'),
        'section' => 'dealsindia_top_offers',
        'type' => 'text',
        'description' => __('Leave empty for no icon. Example: 🔥', 'dealsindia'),
    ));

    $wp_customize->add_setting('top_offers_title', array(
        'default' => 'Offers',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('top_offers_title', array(
        'label' => __('Section Title', 'dealsindia'),
        'section' => 'dealsindia_top_offers',
        'type' => 'text',
    ));

    $wp_customize->add_setting('top_offers_subtitle', array(
        'default' => 'Grab the best deals across all categories',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('top_offers_subtitle', array(
        'label' => __('Section Subtitle', 'dealsindia'),
        'section' => 'dealsindia_top_offers',
        'type' => 'text',
    ));

    $wp_customize->add_setting('top_offers_count', array(
        'default' => 9,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('top_offers_count', array(
        'label' => __('Number of Offers to Show', 'dealsindia'),
        'section' => 'dealsindia_top_offers',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 3,
            'max' => 12,
            'step' => 3,
        ),
    ));

    // Sidebar Settings
    $wp_customize->add_setting('sidebar_title', array(
        'default' => 'Trending Categories',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('sidebar_title', array(
        'label' => __('Sidebar Title', 'dealsindia'),
        'section' => 'dealsindia_top_offers',
        'type' => 'text',
    ));

    $wp_customize->add_setting('sidebar_categories_count', array(
        'default' => 8,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('sidebar_categories_count', array(
        'label' => __('Sidebar Categories Count', 'dealsindia'),
        'section' => 'dealsindia_top_offers',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 5,
            'max' => 15,
        ),
    ));

    $wp_customize->add_setting('category_default_icon', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('category_default_icon', array(
        'label' => __('Default Category Icon', 'dealsindia'),
        'section' => 'dealsindia_top_offers',
        'type' => 'text',
        'description' => __('Default icon if category has no icon. Example: 📦', 'dealsindia'),
    ));

    // ===================================================== 
    // SECTION: Top Stores
    // ===================================================== 
    $wp_customize->add_section('dealsindia_top_stores', array(
        'title' => __('Top Stores Section', 'dealsindia'),
        'priority' => 55,
    ));

    $wp_customize->add_setting('dealsindia_show_top_stores', array(
        'default' => '1',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_show_top_stores', array(
        'label' => __('Show Top Stores Section', 'dealsindia'),
        'section' => 'dealsindia_top_stores',
        'type' => 'checkbox',
    ));

    // Section Icon
    $wp_customize->add_setting('stores_section_icon', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('stores_section_icon', array(
        'label' => __('Section Icon (Emoji)', 'dealsindia'),
        'section' => 'dealsindia_top_stores',
        'type' => 'text',
        'description' => __('Leave empty for no icon. Example: 🏬', 'dealsindia'),
    ));

    $wp_customize->add_setting('top_stores_title', array(
        'default' => 'Top Stores',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('top_stores_title', array(
        'label' => __('Section Title', 'dealsindia'),
        'section' => 'dealsindia_top_stores',
        'type' => 'text',
    ));

    $wp_customize->add_setting('top_stores_subtitle', array(
        'default' => 'Shop from India\'s most trusted brands',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('top_stores_subtitle', array(
        'label' => __('Section Subtitle', 'dealsindia'),
        'section' => 'dealsindia_top_stores',
        'type' => 'text',
    ));

    $wp_customize->add_setting('top_stores_count', array(
        'default' => 5,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('top_stores_count', array(
        'label' => __('Number of Stores', 'dealsindia'),
        'section' => 'dealsindia_top_stores',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 5,
            'max' => 20,
        ),
        'description' => __('5 stores + 1 View All = 6 cards total', 'dealsindia'),
    ));

    $wp_customize->add_setting('cashback_icon', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('cashback_icon', array(
        'label' => __('Cashback Icon (Emoji)', 'dealsindia'),
        'section' => 'dealsindia_top_stores',
        'type' => 'text',
        'description' => __('Icon before cashback text. Example: 💰', 'dealsindia'),
    ));

    $wp_customize->add_setting('view_all_stores_text', array(
        'default' => 'View All Stores',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('view_all_stores_text', array(
        'label' => __('View All Stores Text', 'dealsindia'),
        'section' => 'dealsindia_top_stores',
        'type' => 'text',
    ));

    // ===================================================== 
    // SECTION: Newsletter
    // ===================================================== 
    $wp_customize->add_section('dealsindia_newsletter', array(
        'title' => __('Newsletter Section', 'dealsindia'),
        'priority' => 60,
    ));

    $wp_customize->add_setting('dealsindia_show_newsletter_cta', array(
        'default' => '1',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_show_newsletter_cta', array(
        'label' => __('Show Newsletter Section', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type' => 'checkbox',
    ));

    $wp_customize->add_setting('newsletter_icon', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_icon', array(
        'label' => __('Newsletter Icon (Emoji)', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type' => 'text',
        'description' => __('Example: 📧', 'dealsindia'),
    ));

    $wp_customize->add_setting('newsletter_title', array(
        'default' => 'Never Miss a Deal!',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_title', array(
        'label' => __('Newsletter Title', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type' => 'text',
    ));

    $wp_customize->add_setting('newsletter_subtitle', array(
        'default' => 'Subscribe to get the hottest deals delivered to your inbox',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('newsletter_subtitle', array(
        'label' => __('Newsletter Subtitle', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type' => 'textarea',
    ));

    $wp_customize->add_setting('newsletter_placeholder', array(
        'default' => 'Enter your email address',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_placeholder', array(
        'label' => __('Input Placeholder', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type' => 'text',
    ));

    $wp_customize->add_setting('newsletter_btn_text', array(
        'default' => 'Subscribe',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_btn_text', array(
        'label' => __('Button Text', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type' => 'text',
    ));

    $wp_customize->add_setting('newsletter_privacy_icon', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_privacy_icon', array(
        'label' => __('Privacy Icon (Emoji)', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type' => 'text',
        'description' => __('Example: 🔒', 'dealsindia'),
    ));

    $wp_customize->add_setting('newsletter_privacy', array(
        'default' => 'We respect your privacy. Unsubscribe anytime.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('newsletter_privacy', array(
        'label' => __('Privacy Text', 'dealsindia'),
        'section' => 'dealsindia_newsletter',
        'type' => 'text',
    ));

    // ===================================================== 
    // SECTION: Giveaway
    // ===================================================== 
    $wp_customize->add_section('dealsindia_giveaway', array(
        'title' => __('Giveaway Section', 'dealsindia'),
        'priority' => 58,
    ));

    $wp_customize->add_setting('giveaway_prize_icon', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('giveaway_prize_icon', array(
        'label' => __('Prize Icon (Emoji)', 'dealsindia'),
        'section' => 'dealsindia_giveaway',
        'type' => 'text',
        'description' => __('Example: 🎁', 'dealsindia'),
    ));

    $wp_customize->add_setting('giveaway_button_text', array(
        'default' => 'Enter Giveaway Now!',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('giveaway_button_text', array(
        'label' => __('Button Text', 'dealsindia'),
        'section' => 'dealsindia_giveaway',
        'type' => 'text',
    ));

    // ===================================================== 
    // SECTION: Footer
    // ===================================================== 
    $wp_customize->add_section('dealsindia_footer', array(
        'title' => __('Footer Settings', 'dealsindia'),
        'priority' => 80,
    ));

    $wp_customize->add_setting('dealsindia_footer_about', array(
        'default' => get_bloginfo('name') . ' is India\'s leading deals and cashback platform, helping millions save money every day.',
        'sanitize_callback' => 'wp_kses_post',
    ));
    $wp_customize->add_control('dealsindia_footer_about', array(
        'label' => __('Footer About Text', 'dealsindia'),
        'section' => 'dealsindia_footer',
        'type' => 'textarea',
    ));

    $wp_customize->add_setting('dealsindia_copyright_text', array(
        'default' => '© ' . date('Y') . ' ' . get_bloginfo('name') . '. All Rights Reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_copyright_text', array(
        'label' => __('Copyright Text', 'dealsindia'),
        'section' => 'dealsindia_footer',
        'type' => 'text',
    ));

    // ===================================================== 
    // SECTION: Social Media
    // ===================================================== 
    $wp_customize->add_section('dealsindia_social', array(
        'title' => __('Social Media Links', 'dealsindia'),
        'priority' => 85,
    ));

    $social_networks = array(
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'linkedin' => 'LinkedIn',
        'telegram' => 'Telegram',
    );

    foreach ($social_networks as $network => $label) {
        $wp_customize->add_setting('dealsindia_social_' . $network, array(
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
        ));
        $wp_customize->add_control('dealsindia_social_' . $network, array(
            'label' => sprintf(__('%s URL', 'dealsindia'), $label),
            'section' => 'dealsindia_social',
            'type' => 'url',
        ));
    }
}
add_action('customize_register', 'dealsindia_customize_register');
