<?php
/**
 * Template Helper Functions
 * Reusable functions for templates - SVG Version
 */

if (!defined('ABSPATH')) exit;

/**
 * Get solid color for placeholder (no gradients)
 */
function dealsindia_get_placeholder_color($id) {
    $colors = array(
        '#667eea', // Purple
        '#f093fb', // Pink
        '#4facfe', // Blue
        '#43e97b', // Green
        '#fa709a', // Orange
        '#30cfd0', // Teal
        '#a8edea', // Light Blue
        '#ff9a9e', // Rose
    );
    
    $index = $id % count($colors);
    return $colors[$index];
}

/**
 * Render deal image with SVG placeholder fallback
 */
function dealsindia_render_deal_image($post_id = null, $size = 'medium') {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    if (has_post_thumbnail($post_id)) {
        echo get_the_post_thumbnail($post_id, $size);
    } else {
        $bg_color = dealsindia_get_placeholder_color($post_id);
        $min_height = ($size === 'large') ? '220px' : '180px';
        $icon_size = ($size === 'large') ? '100' : '80';
        ?>
        <div class="deal-image-placeholder" style="background: <?php echo esc_attr($bg_color); ?>; min-height: <?php echo esc_attr($min_height); ?>;">
            <svg class="placeholder-icon" width="<?php echo esc_attr($icon_size); ?>" height="<?php echo esc_attr($icon_size); ?>" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" fill="white" fill-opacity="0.9"/>
            </svg>
        </div>
        <?php
    }
}

/**
 * Premium section titles and subtitles
 */
function dealsindia_get_top_offers_title() {
    return get_theme_mod('dealsindia_top_offers_title', 'Top Offers Today');
}

function dealsindia_get_top_offers_subtitle() {
    return get_theme_mod('dealsindia_top_offers_subtitle', 'Handpicked deals just for you');
}

function dealsindia_get_top_stores_subtitle() {
    return get_theme_mod('dealsindia_top_stores_subtitle', 'Shop from India\'s most trusted brands');
}

function dealsindia_get_latest_deals_subtitle() {
    return get_theme_mod('dealsindia_latest_deals_subtitle', 'Fresh deals added every day');
}

function dealsindia_get_view_all_deals_text() {
    return get_theme_mod('dealsindia_view_all_deals_text', 'View All Deals');
}

/**
 * Get solid color for store placeholder
 */
function dealsindia_get_store_placeholder_color($term_id) {
    $colors = array(
        '#667eea', // Purple
        '#f093fb', // Pink
        '#4facfe', // Blue
        '#43e97b', // Green
        '#fa709a', // Orange
        '#30cfd0', // Teal
        '#a8edea', // Light Blue
        '#ff9a9e', // Rose
    );
    
    $index = $term_id % count($colors);
    return $colors[$index];
}

/**
 * Render store logo with SVG placeholder fallback
 */
function dealsindia_render_store_logo($term_id, $store_name = '', $size = 'medium') {
    $logo_id = get_term_meta($term_id, 'store_logo_id', true);
    
    if ($logo_id) {
        $logo_url = wp_get_attachment_url($logo_id);
        if ($logo_url) {
            echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($store_name) . '">';
            return;
        }
    }
    
    // Fallback: Show first 2 letters with gradient background
    $bg_color = dealsindia_get_store_placeholder_color($term_id);
    $letters = strtoupper(substr($store_name, 0, 2));
    ?>
    <span class="store-logo-fallback" style="background: <?php echo esc_attr($bg_color); ?>;">
        <?php echo esc_html($letters); ?>
    </span>
    <?php
}

/**
 * Render store logo with shopping bag icon (alternate)
 */
function dealsindia_render_store_logo_with_icon($term_id, $store_name = '', $icon_size = 40) {
    $logo_id = get_term_meta($term_id, 'store_logo_id', true);
    
    if ($logo_id) {
        $logo_url = wp_get_attachment_url($logo_id);
        if ($logo_url) {
            echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($store_name) . '" class="store-logo-image">';
            return;
        }
    }
    
    // Fallback: Show shopping bag SVG icon
    $bg_color = dealsindia_get_store_placeholder_color($term_id);
    ?>
    <div class="store-logo-icon-placeholder" style="background: <?php echo esc_attr($bg_color); ?>;">
        <svg width="<?php echo esc_attr($icon_size); ?>" height="<?php echo esc_attr($icon_size); ?>" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 7h-4V5a4 4 0 00-8 0v2H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM10 5a2 2 0 114 0v2h-4V5zm10 14H4V9h2v2a1 1 0 002 0V9h8v2a1 1 0 002 0V9h2v10z" fill="white" fill-opacity="0.9"/>
        </svg>
    </div>
    <?php
}
