<?php
/**
 * DealsIndia Theme Functions
 * Clean, Working Version
 * 
 * @package DealsIndia
 * @version 4.5 - Added Social Share
 */


// ===================================================== 
// THEME SETUP
// ===================================================== 
require_once get_template_directory() . '/inc/setup/theme-support.php';
require_once get_template_directory() . '/inc/setup/enqueue-assets.php';
require_once get_template_directory() . '/inc/setup/customizer.php';
require_once get_template_directory() . '/inc/setup/menus.php';


// ===================================================== 
// POST TYPES
// ===================================================== 
require_once get_template_directory() . '/inc/post-types/deals.php';
require_once get_template_directory() . '/inc/post-types/hero-banners.php';
require_once get_template_directory() . '/inc/post-types/work-steps.php';
require_once get_template_directory() . '/inc/post-types/giveaways.php';


// ===================================================== 
// TAXONOMIES
// ===================================================== 
require_once get_template_directory() . '/inc/taxonomies/stores.php';
require_once get_template_directory() . '/inc/taxonomies/deal-categories.php';
require_once get_template_directory() . '/inc/taxonomies/term-meta.php';


// ===================================================== 
// META BOXES
// ===================================================== 
require_once get_template_directory() . '/inc/meta-boxes/deal-meta.php';
require_once get_template_directory() . '/inc/meta-boxes/banner-meta.php';
require_once get_template_directory() . '/inc/meta-boxes/step-meta.php';
require_once get_template_directory() . '/inc/meta-boxes/giveaway-meta.php';


// ===================================================== 
// HELPERS
// ===================================================== 
require_once get_template_directory() . '/inc/helpers/template-helpers.php';
require_once get_template_directory() . '/inc/helpers/text-helpers.php';
require_once get_template_directory() . '/inc/helpers/color-helpers.php';


// ===================================================== 
// QUERIES
// ===================================================== 
require_once get_template_directory() . '/inc/queries/deal-queries.php';
require_once get_template_directory() . '/inc/queries/archive-sorting.php';


// ===================================================== 
// ADMIN
// ===================================================== 
require_once get_template_directory() . '/inc/admin/category-icon-upload.php';
// Store Logo Upload


// ===================================================== 
// ✅ EMERGENCY FIXES - Missing Helper Functions
// These will be moved to proper files later
// ===================================================== 

/**
 * Check if deal is expired
 */
if (!function_exists('dealsindia_is_deal_expired')) {
    function dealsindia_is_deal_expired($deal_id) {
        $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
        
        if (empty($expiry_date)) {
            return false;
        }
        
        $expiry_timestamp = strtotime($expiry_date);
        $current_timestamp = current_time('timestamp');
        
        return ($current_timestamp > $expiry_timestamp);
    }
}

/**
 * Get time remaining until deal expires
 */
if (!function_exists('dealsindia_get_time_remaining')) {
    function dealsindia_get_time_remaining($deal_id) {
        $expiry_date = get_post_meta($deal_id, 'deal_expiry_date', true);
        
        if (empty($expiry_date)) {
            return __('No expiry', 'dealsindia');
        }
        
        $expiry_timestamp = strtotime($expiry_date);
        $current_timestamp = current_time('timestamp');
        
        if ($current_timestamp > $expiry_timestamp) {
            return __('Expired', 'dealsindia');
        }
        
        $difference = $expiry_timestamp - $current_timestamp;
        $days = floor($difference / 86400);
        $hours = floor(($difference % 86400) / 3600);
        
        if ($days > 0) {
            return sprintf(__('%d days left', 'dealsindia'), $days);
        } elseif ($hours > 0) {
            return sprintf(__('%d hours left', 'dealsindia'), $hours);
        } else {
            return __('Ending soon', 'dealsindia');
        }
    }
}

/**
 * Display social share buttons
 */
if (!function_exists('dealsindia_social_share_buttons')) {
    function dealsindia_social_share_buttons() {
        global $post;
        
        if (!$post) return;
        
        $url = urlencode(get_permalink());
        $title = urlencode(get_the_title());
        
        $facebook_url = 'https://www.facebook.com/sharer/sharer.php?u=' . $url;
        $twitter_url = 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title;
        $whatsapp_url = 'https://api.whatsapp.com/send?text=' . $title . ' ' . $url;
        $telegram_url = 'https://t.me/share/url?url=' . $url . '&text=' . $title;
        
        echo '<div class="social-share-buttons">';
        echo '<span class="share-label">' . __('Share:', 'dealsindia') . '</span>';
        echo '<a href="' . esc_url($facebook_url) . '" target="_blank" rel="noopener" class="share-btn share-facebook" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a>';
        echo '<a href="' . esc_url($twitter_url) . '" target="_blank" rel="noopener" class="share-btn share-twitter" aria-label="Share on Twitter"><i class="fab fa-twitter"></i></a>';
        echo '<a href="' . esc_url($whatsapp_url) . '" target="_blank" rel="noopener" class="share-btn share-whatsapp" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>';
        echo '<a href="' . esc_url($telegram_url) . '" target="_blank" rel="noopener" class="share-btn share-telegram" aria-label="Share on Telegram"><i class="fab fa-telegram-plane"></i></a>';
        echo '</div>';
    }
}

// TEMPORARY - Remove after testing
add_action('init', function() {
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
});

// TEMPORARY: Force load homepage CSS
add_action('wp_head', function() {
    if (is_front_page() || is_home()) {
        $css_file = get_template_directory() . '/assets/css/homepage.css';
        if (file_exists($css_file)) {
            echo '<style id="force-homepage-css">';
            echo file_get_contents($css_file);
            echo '</style>';
        } else {
            echo '<!-- homepage.css NOT FOUND at: ' . $css_file . ' -->';
        }
        
        // Force inline grid fix
        echo '<style id="force-stores-grid">
        .top-stores-section-premium .stores-grid-cd {
            display: grid !important;
            grid-template-columns: repeat(6, 1fr) !important;
            gap: 16px !important;
            max-width: 1200px !important;
            margin: 0 auto !important;
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
        </style>';
    }
}, 999);
