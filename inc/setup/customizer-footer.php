<?php
/**
 * Footer Customizer Settings
 * 
 * @package DealsIndia
 */

function dealsindia_footer_customizer($wp_customize) {
    
    // Footer Section
    $wp_customize->add_section('dealsindia_footer_settings', array(
        'title' => __('Footer Settings', 'dealsindia'),
        'priority' => 130,
    ));
    
    // Footer About Text
    $wp_customize->add_setting('dealsindia_footer_about', array(
        'default' => get_bloginfo('description'),
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    $wp_customize->add_control('dealsindia_footer_about', array(
        'label' => __('About Text', 'dealsindia'),
        'section' => 'dealsindia_footer_settings',
        'type' => 'textarea',
    ));
    
    // Social Media Links
    $wp_customize->add_setting('dealsindia_social_facebook', array('sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control('dealsindia_social_facebook', array(
        'label' => __('Facebook URL', 'dealsindia'),
        'section' => 'dealsindia_footer_settings',
        'type' => 'url',
    ));
    
    $wp_customize->add_setting('dealsindia_social_twitter', array('sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control('dealsindia_social_twitter', array(
        'label' => __('Twitter URL', 'dealsindia'),
        'section' => 'dealsindia_footer_settings',
        'type' => 'url',
    ));
    
    $wp_customize->add_setting('dealsindia_social_instagram', array('sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control('dealsindia_social_instagram', array(
        'label' => __('Instagram URL', 'dealsindia'),
        'section' => 'dealsindia_footer_settings',
        'type' => 'url',
    ));
    
    $wp_customize->add_setting('dealsindia_social_youtube', array('sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control('dealsindia_social_youtube', array(
        'label' => __('YouTube URL', 'dealsindia'),
        'section' => 'dealsindia_footer_settings',
        'type' => 'url',
    ));
    
    $wp_customize->add_setting('dealsindia_social_telegram', array('sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control('dealsindia_social_telegram', array(
        'label' => __('Telegram URL', 'dealsindia'),
        'section' => 'dealsindia_footer_settings',
        'type' => 'url',
    ));
    
    $wp_customize->add_setting('dealsindia_social_whatsapp', array('sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control('dealsindia_social_whatsapp', array(
        'label' => __('WhatsApp URL', 'dealsindia'),
        'section' => 'dealsindia_footer_settings',
        'type' => 'url',
    ));
    
    // Copyright Text
    $wp_customize->add_setting('dealsindia_copyright_text', array(
        'default' => '© {year} ' . get_bloginfo('name') . '. All Rights Reserved.',
        'sanitize_callback' => 'wp_kses_post',
    ));
    $wp_customize->add_control('dealsindia_copyright_text', array(
        'label' => __('Copyright Text (use {year} for current year)', 'dealsindia'),
        'section' => 'dealsindia_footer_settings',
        'type' => 'text',
    ));
    
    // Section Titles
    $wp_customize->add_setting('dealsindia_footer_links_title', array('default' => 'Quick Links', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('dealsindia_footer_links_title', array('label' => __('Quick Links Title', 'dealsindia'), 'section' => 'dealsindia_footer_settings', 'type' => 'text'));
    
    $wp_customize->add_setting('dealsindia_footer_categories_title', array('default' => 'Popular Categories', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('dealsindia_footer_categories_title', array('label' => __('Categories Section Title', 'dealsindia'), 'section' => 'dealsindia_footer_settings', 'type' => 'text'));
    
    $wp_customize->add_setting('dealsindia_footer_stores_title', array('default' => 'Top Stores', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('dealsindia_footer_stores_title', array('label' => __('Stores Section Title', 'dealsindia'), 'section' => 'dealsindia_footer_settings', 'type' => 'text'));
    
    // Payment Methods
    $wp_customize->add_setting('dealsindia_show_payment_methods', array('default' => true, 'sanitize_callback' => 'wp_validate_boolean'));
    $wp_customize->add_control('dealsindia_show_payment_methods', array('label' => __('Show Payment Methods', 'dealsindia'), 'section' => 'dealsindia_footer_settings', 'type' => 'checkbox'));
    
    $wp_customize->add_setting('dealsindia_payment_text', array('default' => 'We Accept:', 'sanitize_callback' => 'sanitize_text_field'));
    $wp_customize->add_control('dealsindia_payment_text', array('label' => __('Payment Methods Text', 'dealsindia'), 'section' => 'dealsindia_footer_settings', 'type' => 'text'));
}
add_action('customize_register', 'dealsindia_footer_customizer');
