<?php
/**
 * ARRZone Theme Functions
 * Consolidated Structure - Clean & Minimal
 * 
 * This file only loads other files - NO logic here!
 * All functionality is organized in inc/ folder
 * 
 * @package ARRZone
 * @version 8.0 - Refactored & Optimized
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// ===================================================== 
// THEME SETUP
// ===================================================== 
require_once get_template_directory() . '/inc/setup/theme-config.php';
require_once get_template_directory() . '/inc/setup/enqueue-assets.php';

// ===================================================== 
// POST TYPES
// ===================================================== 
require_once get_template_directory() . '/inc/post-types/deals.php';
require_once get_template_directory() . '/inc/post-types/hero-banners.php';
require_once get_template_directory() . '/inc/post-types/work-steps.php';
require_once get_template_directory() . '/inc/post-types/giveaways.php';
require_once get_template_directory() . '/inc/post-types/money-earning.php';

// ===================================================== 
// TAXONOMIES
// ===================================================== 
require_once get_template_directory() . '/inc/taxonomies/deal-categories.php';
require_once get_template_directory() . '/inc/taxonomies/stores.php';
require_once get_template_directory() . '/inc/taxonomies/deal-types.php';
require_once get_template_directory() . '/inc/taxonomies/term-meta.php';
require_once get_template_directory() . '/inc/taxonomies/campaigns.php';
// ===================================================== 
// META BOXES
// ===================================================== 
require_once get_template_directory() . '/inc/meta-boxes/deal-meta.php';
require_once get_template_directory() . '/inc/meta-boxes/banner-meta.php';
require_once get_template_directory() . '/inc/meta-boxes/step-meta.php';
require_once get_template_directory() . '/inc/meta-boxes/giveaway-meta.php';

// ===================================================== 
// CONSOLIDATED HELPERS
// ===================================================== 
require_once get_template_directory() . '/inc/helpers/deal-helpers.php';
require_once get_template_directory() . '/inc/helpers/template-helpers.php';
require_once get_template_directory() . '/inc/helpers/utilities.php';

// ===================================================== 
// QUERIES
// ===================================================== 
require_once get_template_directory() . '/inc/queries/deal-queries.php';
require_once get_template_directory() . '/inc/queries/archive-sorting.php';

// ===================================================== 
// AJAX HANDLERS
// ===================================================== 
require_once get_template_directory() . '/inc/ajax/filter-handler.php';

// ===================================================== 
// ADMIN
// ===================================================== 
require_once get_template_directory() . '/inc/admin/admin-setup.php';

// Enable WordPress sitemap
add_filter('wp_sitemaps_enabled', '__return_true');


// ===================================================== 
// END OF FUNCTIONS.PHP
// All logic is now in organized inc/ files
// ===================================================== 
