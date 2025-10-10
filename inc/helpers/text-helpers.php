<?php
/**
 * Text Helper Functions
 * Get dynamic text from customizer and provide defaults
 */

if (!defined('ABSPATH')) exit;

/**
 * Get customizer text values with fallbacks
 */

function dealsindia_get_welcome_banner_text() {
    return get_theme_mod('dealsindia_welcome_banner_text', 'Welcome to India\'s Trusted Coupons, Offers & Cashback Website');
}

function dealsindia_show_welcome_banner() {
    return get_theme_mod('dealsindia_show_welcome_banner', '1') === '1';
}

function dealsindia_get_work_steps_title() {
    return __('Three Steps to Save with DealsIndia', 'dealsindia');
}

function dealsindia_get_hot_picks_title() {
    return __('🔥 Hot Picks!', 'dealsindia');
}

function dealsindia_get_categories_title() {
    return __('Trending Categories', 'dealsindia');
}

function dealsindia_get_offers_title() {
    return __('Top Offers', 'dealsindia');
}

function dealsindia_get_stores_title() {
    return __('Top Stores', 'dealsindia');
}

function dealsindia_get_latest_deals_title() {
    return __('Latest Deals', 'dealsindia');
}

function dealsindia_get_about_title() {
    return __('Why Choose DealsIndia?', 'dealsindia');
}

function dealsindia_get_newsletter_title() {
    return __('Never Miss a Deal!', 'dealsindia');
}

function dealsindia_get_see_more_text() {
    return __('See More', 'dealsindia');
}

function dealsindia_get_show_more_text() {
    return __('Show More →', 'dealsindia');
}

function dealsindia_get_footer_text() {
    return get_theme_mod('dealsindia_footer_text', '© 2024 DealsIndia. All Rights Reserved.');
}
