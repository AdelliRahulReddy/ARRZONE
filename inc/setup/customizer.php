<?php
/**
 * Theme Customizer Settings
 * All WordPress Customizer options
 */

if (!defined('ABSPATH')) exit;

/**
 * Register customizer settings
 */
function dealsindia_customize_register($wp_customize) {
    
    // ===== SECTION: Site Settings =====
    $wp_customize->add_section('dealsindia_site_settings', array(
        'title'    => __('Site Settings', 'dealsindia'),
        'priority' => 30,
    ));
    
    // Welcome Banner Text
    $wp_customize->add_setting('dealsindia_show_welcome_banner', array(
        'default' => '1',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_show_welcome_banner', array(
        'label'   => __('Show Welcome Banner', 'dealsindia'),
        'section' => 'dealsindia_site_settings',
        'type'    => 'checkbox',
    ));
    
    $wp_customize->add_setting('dealsindia_welcome_banner_text', array(
        'default' => 'Welcome to India\'s Trusted Coupons, Offers & Cashback Website',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_welcome_banner_text', array(
        'label'   => __('Welcome Banner Text', 'dealsindia'),
        'section' => 'dealsindia_site_settings',
        'type'    => 'text',
    ));
    
    
    // ===== SECTION: Hot Picks =====
    $wp_customize->add_section('dealsindia_hot_picks', array(
        'title'    => __('Hot Picks Section', 'dealsindia'),
        'priority' => 31,
    ));
    
    // Hot Picks Background Image
    $wp_customize->add_setting('dealsindia_hot_picks_bg_image', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'dealsindia_hot_picks_bg_image', array(
        'label'    => __('Background Image', 'dealsindia'),
        'section'  => 'dealsindia_hot_picks',
    )));
    
    // Hot Picks Background Color
    $wp_customize->add_setting('dealsindia_hot_picks_bg_color', array(
        'default' => '#00897b',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'dealsindia_hot_picks_bg_color', array(
        'label'    => __('Background Color (Fallback)', 'dealsindia'),
        'section'  => 'dealsindia_hot_picks',
    )));
    
    // Border Radius
    $wp_customize->add_setting('dealsindia_hot_picks_border_radius', array(
        'default' => '24',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('dealsindia_hot_picks_border_radius', array(
        'label'   => __('Border Radius (px)', 'dealsindia'),
        'section' => 'dealsindia_hot_picks',
        'type'    => 'number',
    ));
    
    // Overlay Opacity
    $wp_customize->add_setting('dealsindia_hot_picks_overlay_opacity', array(
        'default' => '0.2',
        'sanitize_callback' => 'dealsindia_sanitize_float',
    ));
    $wp_customize->add_control('dealsindia_hot_picks_overlay_opacity', array(
        'label'   => __('Overlay Opacity (0.0 - 1.0)', 'dealsindia'),
        'section' => 'dealsindia_hot_picks',
        'type'    => 'number',
        'input_attrs' => array(
            'min'  => '0',
            'max'  => '1',
            'step' => '0.1',
        ),
    ));
    
    
    // ===== SECTION: Footer =====
    $wp_customize->add_section('dealsindia_footer_settings', array(
        'title'    => __('Footer Settings', 'dealsindia'),
        'priority' => 32,
    ));
    
    $wp_customize->add_setting('dealsindia_footer_text', array(
        'default' => '© 2024 DealsIndia. All Rights Reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('dealsindia_footer_text', array(
        'label'   => __('Footer Copyright Text', 'dealsindia'),
        'section' => 'dealsindia_footer_settings',
        'type'    => 'text',
    ));
}
add_action('customize_register', 'dealsindia_customize_register');

/**
 * Sanitize float values
 */
function dealsindia_sanitize_float($value) {
    return floatval($value);
}
