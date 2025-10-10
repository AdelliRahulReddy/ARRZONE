<?php
/**
 * DealsIndia Theme Functions - Modular Architecture
 * Version: 4.0.0
 */

if (!defined('ABSPATH')) exit;

// Theme constants
define('DEALSINDIA_VERSION', '4.0.0');
define('DEALSINDIA_DIR', get_template_directory());
define('DEALSINDIA_URI', get_template_directory_uri());
define('DEALSINDIA_INC', DEALSINDIA_DIR . '/inc');

/**
 * Load all module files
 */

// 1. SETUP (Core theme configuration)
require_once DEALSINDIA_INC . '/setup/theme-support.php';
require_once DEALSINDIA_INC . '/setup/enqueue-assets.php';
require_once DEALSINDIA_INC . '/setup/menus.php';
require_once DEALSINDIA_INC . '/setup/customizer.php';

// 2. POST TYPES (Custom post types)
require_once DEALSINDIA_INC . '/post-types/deals.php';
require_once DEALSINDIA_INC . '/post-types/hero-banners.php';
require_once DEALSINDIA_INC . '/post-types/work-steps.php';
require_once DEALSINDIA_INC . '/post-types/giveaways.php';

// 3. TAXONOMIES (Custom taxonomies)
require_once DEALSINDIA_INC . '/taxonomies/deal-categories.php';
require_once DEALSINDIA_INC . '/taxonomies/stores.php';
require_once DEALSINDIA_INC . '/taxonomies/term-meta.php';

// 4. META BOXES (Custom fields)
require_once DEALSINDIA_INC . '/meta-boxes/deal-meta.php';
require_once DEALSINDIA_INC . '/meta-boxes/banner-meta.php';
require_once DEALSINDIA_INC . '/meta-boxes/step-meta.php';
require_once DEALSINDIA_INC . '/meta-boxes/giveaway-meta.php';

// 5. ADMIN (Admin panel features)
require_once DEALSINDIA_INC . '/admin/dealsindia-manager.php';
require_once DEALSINDIA_INC . '/admin/store-logo-upload.php';

// 6. QUERIES (Data retrieval)
require_once DEALSINDIA_INC . '/queries/deal-queries.php';
require_once DEALSINDIA_INC . '/queries/archive-sorting.php';

// 7. HELPERS (Utility functions)
require_once DEALSINDIA_INC . '/helpers/text-helpers.php';
require_once DEALSINDIA_INC . '/helpers/color-helpers.php';
require_once DEALSINDIA_INC . '/helpers/template-helpers.php';


/**
 * Theme activation
 */
function dealsindia_activation() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'dealsindia_activation');

// Debug: Show which template is loading
add_filter('template_include', function($template) {
    if (is_post_type_archive('deals')) {
        echo '<div style="position: fixed; top: 0; left: 0; background: red; color: white; padding: 10px; z-index: 9999; font-size: 12px;">';
        echo 'Template: ' . basename($template);
        echo '</div>';
    }
    return $template;
});
