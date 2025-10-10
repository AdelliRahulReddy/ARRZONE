<?php
/**
 * DealsIndia Theme Functions - Complete Management System
 * 100% Dynamic | Zero Hardcoded Content | Professional Grade
 * Version: 3.0.1
 */

// =====================================================
// ENQUEUE STYLES
// =====================================================
function dealsindia_enqueue_styles() {
    $version = '3.0.2';
    // Enqueue main.js
    wp_enqueue_style('dealsindia-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap', array(), null);
    wp_enqueue_script('dealsindia-main', get_template_directory_uri() . '/assets/js/main.js', array(), '3.0.1', true);
    wp_enqueue_style('dealsindia-base', get_template_directory_uri() . '/assets/css/base.css', array(), $version);
    wp_enqueue_style('dealsindia-header', get_template_directory_uri() . '/assets/css/header.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-footer', get_template_directory_uri() . '/assets/css/footer.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-deal-card', get_template_directory_uri() . '/assets/css/deal-card.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-homepage', get_template_directory_uri() . '/assets/css/homepage.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-single-deal', get_template_directory_uri() . '/assets/css/single-deal.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-category-page', get_template_directory_uri() . '/assets/css/category-page.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-responsive', get_template_directory_uri() . '/assets/css/responsive.css', array('dealsindia-base'), $version);
    wp_enqueue_style('dealsindia-main', get_stylesheet_uri(), array(), $version);
}
add_action('wp_enqueue_scripts', 'dealsindia_enqueue_styles');

function dealsindia_admin_styles() {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'dealsindia_admin_styles');

// =====================================================
// THEME SUPPORT
// =====================================================
function dealsindia_theme_support() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo', array(
        'height' => 60,
        'width' => 240,
        'flex-height' => true,
        'flex-width' => true,
    ));
    
    add_image_size('deal-thumbnail', 400, 400, true);
    add_image_size('deal-large', 800, 600, true);
    add_image_size('banner-image', 1200, 500, true);
}
add_action('after_setup_theme', 'dealsindia_theme_support');

// =====================================================
// NAVIGATION MENUS
// =====================================================
function register_nav_menus_theme() {
    register_nav_menus(array(
        'primary' => __('Primary Menu (Header)', 'dealsindia'),
        'footer' => __('Footer Menu', 'dealsindia'),
    ));
}
add_action('init', 'register_nav_menus_theme');

// =====================================================
// CUSTOM POST TYPES
// =====================================================

// 1. DEALS CPT
function create_deals_post_type() {
    $labels = array(
        'name' => 'Deals',
        'singular_name' => 'Deal',
        'add_new' => 'Add New Deal',
        'add_new_item' => 'Add New Deal',
        'edit_item' => 'Edit Deal',
        'new_item' => 'New Deal',
        'view_item' => 'View Deal',
        'search_items' => 'Search Deals',
        'not_found' => 'No deals found',
        'not_found_in_trash' => 'No deals found in trash'
    );
    
    register_post_type('deals', array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-tag',
        'menu_position' => 5,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
        'rewrite' => array('slug' => 'deal'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'create_deals_post_type');

// 2. HERO BANNERS CPT
function create_hero_banners_cpt() {
    register_post_type('hero_banner', array(
        'labels' => array(
            'name' => 'Hero Banners',
            'singular_name' => 'Hero Banner',
            'add_new' => 'Add New Banner',
            'add_new_item' => 'Create Banner',
            'edit_item' => 'Edit Banner',
        ),
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-slides',
        'menu_position' => 21,
        'supports' => array('title', 'thumbnail'),
        'show_in_menu' => 'dealsindia-manager',
    ));
}
add_action('init', 'create_hero_banners_cpt');

// 3. STEPS CPT
function create_steps_cpt() {
    register_post_type('work_step', array(
        'labels' => array(
            'name' => 'How It Works Steps',
            'singular_name' => 'Step',
            'add_new' => 'Add New Step',
            'edit_item' => 'Edit Step',
        ),
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-list-view',
        'menu_position' => 22,
        'supports' => array('title', 'editor'),
        'show_in_menu' => 'dealsindia-manager',
    ));
}
add_action('init', 'create_steps_cpt');

// 4. GIVEAWAYS CPT
function create_giveaways_cpt() {
    register_post_type('giveaway', array(
        'labels' => array(
            'name' => 'Giveaways',
            'singular_name' => 'Giveaway',
            'add_new' => 'Create Giveaway',
            'edit_item' => 'Edit Giveaway',
        ),
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-awards',
        'menu_position' => 23,
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_menu' => 'dealsindia-manager',
        'rewrite' => array('slug' => 'giveaway'),
    ));
}
add_action('init', 'create_giveaways_cpt');

// =====================================================
// TAXONOMIES
// =====================================================
function create_deal_categories() {
    register_taxonomy('deal_category', 'deals', array(
        'label' => 'Deal Categories',
        'labels' => array(
            'name' => 'Deal Categories',
            'singular_name' => 'Category',
            'menu_name' => 'Categories',
            'all_items' => 'All Categories',
            'edit_item' => 'Edit Category',
            'add_new_item' => 'Add New Category',
            'parent_item' => 'Parent Category',
        ),
        'public' => true,
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'show_in_nav_menus' => true,
        'rewrite' => array(
            'slug' => 'deals',
            'with_front' => false,
            'hierarchical' => true,
        ),
    ));
}
add_action('init', 'create_deal_categories');

function create_stores_taxonomy() {
    register_taxonomy('store', 'deals', array(
        'label' => 'Stores',
        'labels' => array(
            'name' => 'Stores',
            'singular_name' => 'Store',
            'menu_name' => 'Stores',
            'all_items' => 'All Stores',
            'edit_item' => 'Edit Store',
            'add_new_item' => 'Add New Store',
        ),
        'public' => true,
        'hierarchical' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'store', 'with_front' => false),
        'show_in_rest' => true,
    ));
}
add_action('init', 'create_stores_taxonomy');

// =====================================================
// META BOXES FOR DEALS
// =====================================================
function add_deal_meta_boxes() {
    add_meta_box('deal_details', 'Deal Details', 'deal_details_callback', 'deals', 'normal', 'high');
    add_meta_box('deal_featured', 'Deal Options', 'deal_featured_callback', 'deals', 'side', 'default');
    add_meta_box('deal_store_select', 'Select Store', 'deal_store_select_callback', 'deals', 'side', 'high');
}
add_action('add_meta_boxes', 'add_deal_meta_boxes');

function deal_details_callback($post) {
    wp_nonce_field('save_deal_details', 'deal_details_nonce');
    
    $original_price = get_post_meta($post->ID, 'original_price', true);
    $sale_price = get_post_meta($post->ID, 'sale_price', true);
    $coupon_code = get_post_meta($post->ID, 'coupon_code', true);
    $affiliate_link = get_post_meta($post->ID, 'affiliate_link', true);
    $expiry_date = get_post_meta($post->ID, 'expiry_date', true);
    
    ?>
    <table class="form-table" style="margin-top: 10px;">
        <tr>
            <th><label for="original_price">Original Price (â‚¹)</label></th>
            <td>
                <input type="number" id="original_price" name="original_price" value="<?php echo esc_attr($original_price); ?>" style="width: 100%;" placeholder="e.g., 10000">
                <p class="description">Original price before discount</p>
            </td>
        </tr>
        <tr>
            <th><label for="sale_price">Sale Price (â‚¹)</label></th>
            <td>
                <input type="number" id="sale_price" name="sale_price" value="<?php echo esc_attr($sale_price); ?>" style="width: 100%;" placeholder="e.g., 7500">
                <p class="description">Discounted price after offer</p>
            </td>
        </tr>
        <tr>
            <th><label for="coupon_code">Coupon Code</label></th>
            <td>
                <input type="text" id="coupon_code" name="coupon_code" value="<?php echo esc_attr($coupon_code); ?>" style="width: 100%;" placeholder="e.g., SAVE25">
                <p class="description">Leave blank if no coupon code needed</p>
            </td>
        </tr>
        <tr>
            <th><label for="affiliate_link">Affiliate Link</label></th>
            <td>
                <input type="url" id="affiliate_link" name="affiliate_link" value="<?php echo esc_attr($affiliate_link); ?>" style="width: 100%;" placeholder="https://amazon.in/...">
                <p class="description">Your affiliate link to the deal</p>
            </td>
        </tr>
        <tr>
            <th><label for="expiry_date">Expiry Date</label></th>
            <td>
                <input type="date" id="expiry_date" name="expiry_date" value="<?php echo esc_attr($expiry_date); ?>" style="width: 100%;">
                <p class="description">When does this deal expire?</p>
            </td>
        </tr>
    </table>
    <?php
}

function deal_featured_callback($post) {
    wp_nonce_field('save_deal_featured', 'deal_featured_nonce');
    
    $is_featured = get_post_meta($post->ID, 'is_featured', true);
    $is_trending = get_post_meta($post->ID, 'is_trending', true);
    
    ?>
    <p>
        <label>
            <input type="checkbox" name="is_featured" value="1" <?php checked($is_featured, '1'); ?>>
            <strong>Mark as Featured Deal</strong>
        </label>
        <br><small>Shows in hero section on homepage</small>
    </p>
    
    <p>
        <label>
            <input type="checkbox" name="is_trending" value="1" <?php checked($is_trending, '1'); ?>>
            <strong>Mark as Trending</strong>
        </label>
        <br><small>Shows in trending section</small>
    </p>
    <?php
}

function deal_store_select_callback($post) {
    wp_nonce_field('save_deal_store_select', 'deal_store_select_nonce');
    
    $current_stores = wp_get_post_terms($post->ID, 'store', array('fields' => 'ids'));
    $current_store = !empty($current_stores) ? $current_stores[0] : '';
    
    $stores = get_terms(array(
        'taxonomy' => 'store',
        'hide_empty' => false,
    ));
    
    ?>
    <p><strong>Where is this deal available?</strong></p>
    <select name="deal_store" id="deal_store" style="width: 100%; padding: 8px; font-size: 14px;">
        <option value="">-- Select Store --</option>
        <?php foreach ($stores as $store) : ?>
            <option value="<?php echo esc_attr($store->term_id); ?>" <?php selected($current_store, $store->term_id); ?>>
                <?php echo esc_html($store->name); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="description" style="margin-top: 10px;">
        Select the store where this deal is available.<br>
        Don't see your store? <a href="<?php echo admin_url('edit-tags.php?taxonomy=store&post_type=deals'); ?>" target="_blank">Add new store</a>
    </p>
    <?php
}

function save_deal_details($post_id) {
    if (isset($_POST['deal_details_nonce']) && wp_verify_nonce($_POST['deal_details_nonce'], 'save_deal_details')) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        $fields = array('original_price', 'sale_price', 'coupon_code', 'affiliate_link', 'expiry_date');
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if ($field === 'affiliate_link') {
                    update_post_meta($post_id, $field, esc_url_raw($_POST[$field]));
                } else {
                    update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
                }
            }
        }
    }
    
    if (isset($_POST['deal_featured_nonce']) && wp_verify_nonce($_POST['deal_featured_nonce'], 'save_deal_featured')) {
        update_post_meta($post_id, 'is_featured', isset($_POST['is_featured']) ? '1' : '0');
        update_post_meta($post_id, 'is_trending', isset($_POST['is_trending']) ? '1' : '0');
    }
    
    if (isset($_POST['deal_store_select_nonce']) && wp_verify_nonce($_POST['deal_store_select_nonce'], 'save_deal_store_select')) {
        if (isset($_POST['deal_store']) && !empty($_POST['deal_store'])) {
            wp_set_post_terms($post_id, array(intval($_POST['deal_store'])), 'store', false);
        } else {
            wp_set_post_terms($post_id, array(), 'store', false);
        }
    }
}
add_action('save_post', 'save_deal_details');

add_post_type_support('deals', 'thumbnail');

// =====================================================
// SEARCH FUNCTIONALITY
// =====================================================
function custom_search_query($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        $query->set('post_type', 'deals');
    }
    return $query;
}
add_action('pre_get_posts', 'custom_search_query');

function custom_search_join($join) {
    global $wpdb;
    
    if (is_search() && !is_admin()) {
        $join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
        $join .= " LEFT JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id ";
        $join .= " LEFT JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id ";
        $join .= " LEFT JOIN {$wpdb->terms} ON {$wpdb->term_taxonomy}.term_id = {$wpdb->terms}.term_id ";
    }
    
    return $join;
}
add_filter('posts_join', 'custom_search_join');

function custom_search_where($where) {
    global $wpdb;
    
    if (is_search() && !is_admin()) {
        $where = preg_replace(
            "/\(\s*{$wpdb->posts}.post_title\s+LIKE\s*(\\'[^\\']+\\')\s*\)/",
            "({$wpdb->posts}.post_title LIKE $1) 
            OR ({$wpdb->postmeta}.meta_value LIKE $1) 
            OR ({$wpdb->terms}.name LIKE $1)",
            $where
        );
    }
    
    return $where;
}
add_filter('posts_where', 'custom_search_where');

function custom_search_distinct($distinct) {
    if (is_search() && !is_admin()) {
        return "DISTINCT";
    }
    return $distinct;
}
add_filter('posts_distinct', 'custom_search_distinct');

function fix_search_form_submit() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.querySelector('header .header-search form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const searchInput = this.querySelector('input[name="s"]');
                if (searchInput && searchInput.value.trim() !== '') {
                    window.location.href = '<?php echo home_url(); ?>/?s=' + encodeURIComponent(searchInput.value.trim());
                }
                return false;
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'fix_search_form_submit');

// =====================================================
// SEO ENHANCEMENTS
// =====================================================
function add_seo_meta_tags() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
    
    if (is_singular('deals')) {
        global $post;
        
        $excerpt = get_the_excerpt();
        $stores = get_the_terms(get_the_ID(), 'store');
        $store_name = ($stores && !is_wp_error($stores)) ? $stores[0]->name : '';
        $sale_price = get_post_meta(get_the_ID(), 'sale_price', true);
        
        $description = $excerpt ? $excerpt : 'Get amazing deals on ' . get_the_title();
        if ($store_name && $sale_price) {
            $description .= ' at ' . $store_name . ' starting from â‚¹' . number_format($sale_price);
        }
        
        echo '<meta name="description" content="' . esc_attr(wp_trim_words($description, 25)) . '">' . "\n";
        
        $categories = get_the_terms(get_the_ID(), 'deal_category');
        if ($categories && !is_wp_error($categories)) {
            $cat_names = array();
            foreach ($categories as $cat) {
                $cat_names[] = $cat->name;
            }
            echo '<meta name="keywords" content="' . esc_attr(implode(', ', $cat_names) . ', deals, coupons, discounts, ' . $store_name) . '">' . "\n";
        }
        
    } elseif (is_home() || is_front_page()) {
        echo '<meta name="description" content="' . esc_attr(get_bloginfo('description')) . ' - Find the best deals and coupons in India.">' . "\n";
        echo '<meta name="keywords" content="deals, coupons, discounts, offers, India, online shopping">' . "\n";
    }
}
add_action('wp_head', 'add_seo_meta_tags', 1);

function add_og_tags() {
    if (is_singular('deals')) {
        global $post;
        $stores = get_the_terms(get_the_ID(), 'store');
        $store_name = ($stores && !is_wp_error($stores)) ? $stores[0]->name : '';
        $sale_price = get_post_meta(get_the_ID(), 'sale_price', true);
        ?>
        <meta property="og:type" content="product">
        <meta property="og:title" content="<?php echo esc_attr(get_the_title()); ?>">
        <meta property="og:description" content="<?php echo esc_attr(get_the_excerpt()); ?>">
        <meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <meta property="og:image" content="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
        <?php endif; ?>
        <meta property="og:site_name" content="<?php bloginfo('name'); ?>">
        <meta property="product:price:amount" content="<?php echo esc_attr($sale_price); ?>">
        <meta property="product:price:currency" content="INR">
        
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo esc_attr(get_the_title()); ?>">
        <meta name="twitter:description" content="<?php echo esc_attr(get_the_excerpt()); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <meta name="twitter:image" content="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>">
        <?php endif; ?>
        <?php
    }
}
add_action('wp_head', 'add_og_tags', 5);

function add_deal_schema() {
    if (is_singular('deals')) {
        global $post;
        $original_price = get_post_meta(get_the_ID(), 'original_price', true);
        $sale_price = get_post_meta(get_the_ID(), 'sale_price', true);
        $stores = get_the_terms(get_the_ID(), 'store');
        $store_name = ($stores && !is_wp_error($stores)) ? $stores[0]->name : '';
        $affiliate_link = get_post_meta(get_the_ID(), 'affiliate_link', true);
        $expiry_date = get_post_meta(get_the_ID(), 'expiry_date', true);
        
        if ($sale_price && $store_name) {
            $schema = array(
                "@context" => "https://schema.org",
                "@type" => "Offer",
                "name" => get_the_title(),
                "description" => get_the_excerpt(),
                "price" => $sale_price,
                "priceCurrency" => "INR",
                "availability" => "https://schema.org/InStock",
                "url" => get_permalink(),
                "seller" => array(
                    "@type" => "Organization",
                    "name" => $store_name
                )
            );
            
            if ($expiry_date) {
                $schema['priceValidUntil'] = $expiry_date;
            }
            
            if (has_post_thumbnail()) {
                $schema['image'] = get_the_post_thumbnail_url(get_the_ID(), 'large');
            }
            
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            echo "\n" . '</script>' . "\n";
        }
    }
    
    if (is_home() || is_front_page()) {
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "WebSite",
            "name" => get_bloginfo('name'),
            "description" => get_bloginfo('description'),
            "url" => home_url(),
            "potentialAction" => array(
                "@type" => "SearchAction",
                "target" => home_url('/?s={search_term_string}'),
                "query-input" => "required name=search_term_string"
            )
        );
        
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo "\n" . '</script>' . "\n";
    }
}
add_action('wp_head', 'add_deal_schema', 10);

function add_canonical_url() {
    if (is_singular()) {
        echo '<link rel="canonical" href="' . esc_url(get_permalink()) . '">' . "\n";
    } elseif (is_home() || is_front_page()) {
        echo '<link rel="canonical" href="' . esc_url(home_url('/')) . '">' . "\n";
    } elseif (is_tax('deal_category') || is_tax('store')) {
        echo '<link rel="canonical" href="' . esc_url(get_term_link(get_queried_object())) . '">' . "\n";
    }
}
add_action('wp_head', 'add_canonical_url', 15);

function optimize_title($title) {
    if (is_singular('deals')) {
        $stores = get_the_terms(get_the_ID(), 'store');
        $store_name = ($stores && !is_wp_error($stores)) ? $stores[0]->name : '';
        if ($store_name) {
            return $title . ' - ' . $store_name . ' Deal';
        }
    }
    return $title;
}
add_filter('the_title', 'optimize_title', 10, 1);

function add_sitemap_link() {
    echo '<link rel="sitemap" type="application/xml" title="Sitemap" href="' . esc_url(home_url('/wp-sitemap.xml')) . '">' . "\n";
}
add_action('wp_head', 'add_sitemap_link', 20);

// =====================================================
// DEALSINDIA MANAGER MENU
// =====================================================
function dealsindia_manager_menu() {
    add_menu_page(
        'DealsIndia Manager',
        'DealsIndia Manager',
        'manage_options',
        'dealsindia-manager',
        'dealsindia_homepage_builder',
        'dashicons-admin-home',
        20
    );
    
    add_submenu_page(
        'dealsindia-manager',
        'Homepage Builder',
        'ðŸ  Homepage Builder',
        'manage_options',
        'dealsindia-manager',
        'dealsindia_homepage_builder'
    );
    
    add_submenu_page(
        'dealsindia-manager',
        'Hot Picks Manager',
        'ðŸ”¥ Hot Picks',
        'manage_options',
        'dealsindia-hot-picks',
        'dealsindia_hot_picks_page'
    );
    
    add_submenu_page(
        'dealsindia-manager',
        'Site Settings',
        'âš™ï¸ Site Settings',
        'manage_options',
        'dealsindia-settings',
        'dealsindia_settings_page'
    );
}
add_action('admin_menu', 'dealsindia_manager_menu');

// =====================================================
// HOMEPAGE BUILDER PAGE
// =====================================================
function dealsindia_homepage_builder() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_POST['save_homepage_settings']) && check_admin_referer('dealsindia_homepage_settings')) {
        update_option('dealsindia_welcome_text', sanitize_text_field($_POST['welcome_text']));
        update_option('dealsindia_hot_picks_title', sanitize_text_field($_POST['hot_picks_title']));
        update_option('dealsindia_stores_title', sanitize_text_field($_POST['stores_title']));
        update_option('dealsindia_latest_deals_title', sanitize_text_field($_POST['latest_deals_title']));
        update_option('dealsindia_categories_title', sanitize_text_field($_POST['categories_title']));
        echo '<div class="updated"><p>Settings saved successfully!</p></div>';
    }
    
    $welcome_text = get_option('dealsindia_welcome_text', 'Welcome to ' . get_bloginfo('name') . ' - India\'s Trusted Coupons, Offers & Cashback Website');
    $hot_picks_title = get_option('dealsindia_hot_picks_title', 'ðŸ”¥ Hot Picks!');
    $stores_title = get_option('dealsindia_stores_title', 'ðŸª Top Stores');
    $latest_deals_title = get_option('dealsindia_latest_deals_title', 'ðŸ“° Latest Deals');
    $categories_title = get_option('dealsindia_categories_title', 'ðŸ“± Browse by Category');
    
    ?>
    <div class="wrap">
        <h1>ðŸ  Homepage Builder</h1>
        <p class="description">Manage all homepage sections and content from here. All text is editable!</p>
        
        <div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #c3c4c7;">
            <h2>Quick Actions</h2>
            <p>
                <a href="<?php echo admin_url('edit.php?post_type=hero_banner'); ?>" class="button button-primary">ðŸŽª Manage Hero Banners</a>
                <a href="<?php echo admin_url('edit.php?post_type=work_step'); ?>" class="button button-primary">ðŸ“– Manage Steps</a>
                <a href="<?php echo admin_url('admin.php?page=dealsindia-hot-picks'); ?>" class="button button-primary">ðŸ”¥ Manage Hot Picks</a>
                <a href="<?php echo admin_url('edit.php?post_type=giveaway'); ?>" class="button button-primary">ðŸŽ Manage Giveaways</a>
                <a href="<?php echo home_url(); ?>" target="_blank" class="button">ðŸ‘ï¸ Preview Homepage</a>
            </p>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('dealsindia_homepage_settings'); ?>
            
            <h2>Section Titles (100% Editable)</h2>
            <table class="form-table">
                <tr>
                    <th><label for="welcome_text">Welcome Banner Text</label></th>
                    <td>
                        <input type="text" id="welcome_text" name="welcome_text" value="<?php echo esc_attr($welcome_text); ?>" class="large-text">
                        <p class="description">Text shown in the welcome banner below header</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="hot_picks_title">Hot Picks Section Title</label></th>
                    <td>
                        <input type="text" id="hot_picks_title" name="hot_picks_title" value="<?php echo esc_attr($hot_picks_title); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="stores_title">Stores Section Title</label></th>
                    <td>
                        <input type="text" id="stores_title" name="stores_title" value="<?php echo esc_attr($stores_title); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="latest_deals_title">Latest Deals Section Title</label></th>
                    <td>
                        <input type="text" id="latest_deals_title" name="latest_deals_title" value="<?php echo esc_attr($latest_deals_title); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="categories_title">Categories Section Title</label></th>
                    <td>
                        <input type="text" id="categories_title" name="categories_title" value="<?php echo esc_attr($categories_title); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" name="save_homepage_settings" class="button button-primary button-hero">
                    ðŸ’¾ Save All Settings
                </button>
            </p>
        </form>
    </div>
    <?php
}

function dealsindia_hot_picks_page() {
    echo '<div class="wrap"><h1>ðŸ”¥ Hot Picks Manager</h1><p>Hot Picks management coming soon!</p></div>';
}

function dealsindia_settings_page() {
    echo '<div class="wrap"><h1>âš™ï¸ Site Settings</h1><p>Site settings coming soon!</p></div>';
}

// =====================================================
// META BOXES FOR HERO BANNERS
// =====================================================
function add_hero_banner_meta_boxes() {
    add_meta_box('hero_banner_details', 'Banner Settings', 'hero_banner_details_callback', 'hero_banner', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_hero_banner_meta_boxes');

function hero_banner_details_callback($post) {
    wp_nonce_field('save_hero_banner', 'hero_banner_nonce');
    
    $heading = get_post_meta($post->ID, 'banner_heading', true);
    $subheading = get_post_meta($post->ID, 'banner_subheading', true);
    $store_name = get_post_meta($post->ID, 'banner_store', true);
    $bg_color = get_post_meta($post->ID, 'banner_bg_color', true) ?: '#e74c3c';
    $cashback_text = get_post_meta($post->ID, 'banner_cashback', true);
    $button_text = get_post_meta($post->ID, 'banner_button_text', true) ?: 'Shop Now';
    $button_link = get_post_meta($post->ID, 'banner_button_link', true);
    $banner_order = get_post_meta($post->ID, 'banner_order', true) ?: 1;
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="banner_heading">Main Heading</label></th>
            <td>
                <input type="text" id="banner_heading" name="banner_heading" value="<?php echo esc_attr($heading); ?>" class="large-text" placeholder="Upto 80% Off">
                <p class="description">Large text displayed on banner (e.g., "Upto 80% Off")</p>
            </td>
        </tr>
        <tr>
            <th><label for="banner_subheading">Subheading</label></th>
            <td>
                <input type="text" id="banner_subheading" name="banner_subheading" value="<?php echo esc_attr($subheading); ?>" class="large-text" placeholder="Across all categories">
                <p class="description">Small text below heading</p>
            </td>
        </tr>
        <tr>
            <th><label for="banner_store">Store Name</label></th>
            <td>
                <input type="text" id="banner_store" name="banner_store" value="<?php echo esc_attr($store_name); ?>" class="regular-text" placeholder="Amazon">
                <p class="description">Store name shown as logo badge</p>
            </td>
        </tr>
        <tr>
            <th><label for="banner_bg_color">Background Color</label></th>
            <td>
                <input type="text" id="banner_bg_color" name="banner_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="color-picker">
                <p class="description">Background color if no featured image is set</p>
            </td>
        </tr>
        <tr>
            <th><label for="banner_cashback">Cashback Text</label></th>
            <td>
                <input type="text" id="banner_cashback" name="banner_cashback" value="<?php echo esc_attr($cashback_text); ?>" class="large-text" placeholder="Upto 6.5% Voucher Rewards">
                <p class="description">Optional cashback badge text</p>
            </td>
        </tr>
        <tr>
            <th><label for="banner_button_text">Button Text</label></th>
            <td>
                <input type="text" id="banner_button_text" name="banner_button_text" value="<?php echo esc_attr($button_text); ?>" class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="banner_button_link">Button Link</label></th>
            <td>
                <input type="url" id="banner_button_link" name="banner_button_link" value="<?php echo esc_attr($button_link); ?>" class="large-text" placeholder="https://">
                <p class="description">Where should the button take users?</p>
            </td>
        </tr>
        <tr>
            <th><label for="banner_order">Display Order</label></th>
            <td>
                <input type="number" id="banner_order" name="banner_order" value="<?php echo esc_attr($banner_order); ?>" min="1" max="10" class="small-text">
                <p class="description">Lower numbers appear first (1, 2, 3...)</p>
            </td>
        </tr>
    </table>
    
    <script>
    jQuery(document).ready(function($){
        $('.color-picker').wpColorPicker();
    });
    </script>
    <?php
}

function save_hero_banner_meta($post_id) {
    if (!isset($_POST['hero_banner_nonce']) || !wp_verify_nonce($_POST['hero_banner_nonce'], 'save_hero_banner')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    $fields = array('banner_heading', 'banner_subheading', 'banner_store', 'banner_bg_color', 'banner_cashback', 'banner_button_text', 'banner_button_link', 'banner_order');
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
}
add_action('save_post_hero_banner', 'save_hero_banner_meta');

// =====================================================
// META BOXES FOR WORK STEPS
// =====================================================
function add_work_steps_meta_boxes() {
    add_meta_box('step_details', 'Step Details', 'step_details_callback', 'work_step', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_work_steps_meta_boxes');

function step_details_callback($post) {
    wp_nonce_field('save_work_step', 'work_step_nonce');
    
    $icon = get_post_meta($post->ID, 'step_icon', true) ?: 'ðŸ”';
    $step_order = get_post_meta($post->ID, 'step_order', true) ?: 1;
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="step_icon">Step Icon (Emoji)</label></th>
            <td>
                <input type="text" id="step_icon" name="step_icon" value="<?php echo esc_attr($icon); ?>" class="regular-text" maxlength="4">
                <p class="description">Single emoji (e.g., ðŸ” ðŸ›’ ðŸ’°). <a href="https://emojipedia.org/" target="_blank">Find emojis here</a></p>
            </td>
        </tr>
        <tr>
            <th><label for="step_order">Step Number</label></th>
            <td>
                <input type="number" id="step_order" name="step_order" value="<?php echo esc_attr($step_order); ?>" min="1" max="10" class="small-text">
                <p class="description">Step order (1 = first, 2 = second, etc.)</p>
            </td>
        </tr>
    </table>
    
    <p><strong>Step Title:</strong> Enter in the title field above</p>
    <p><strong>Step Description:</strong> Enter in the content editor above</p>
    <?php
}

function save_work_step_meta($post_id) {
    if (!isset($_POST['work_step_nonce']) || !wp_verify_nonce($_POST['work_step_nonce'], 'save_work_step')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (isset($_POST['step_icon'])) {
        update_post_meta($post_id, 'step_icon', sanitize_text_field($_POST['step_icon']));
    }
    
    if (isset($_POST['step_order'])) {
        update_post_meta($post_id, 'step_order', intval($_POST['step_order']));
    }
}
add_action('save_post_work_step', 'save_work_step_meta');

// =====================================================
// META BOXES FOR GIVEAWAYS
// =====================================================
function add_giveaway_meta_boxes() {
    add_meta_box('giveaway_details', 'Giveaway Settings', 'giveaway_details_callback', 'giveaway', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_giveaway_meta_boxes');

function giveaway_details_callback($post) {
    wp_nonce_field('save_giveaway', 'giveaway_nonce');
    
    $prize = get_post_meta($post->ID, 'giveaway_prize', true);
    $bg_color = get_post_meta($post->ID, 'giveaway_bg_color', true) ?: '#ff9800';
    $button_text = get_post_meta($post->ID, 'giveaway_button_text', true) ?: 'Enter Giveaway';
    $button_link = get_post_meta($post->ID, 'giveaway_button_link', true);
    $start_date = get_post_meta($post->ID, 'giveaway_start_date', true);
    $end_date = get_post_meta($post->ID, 'giveaway_end_date', true);
    $is_active = get_post_meta($post->ID, 'giveaway_active', true);
    
    ?>
    <table class="form-table">
        <tr>
            <th><label for="giveaway_prize">Prize Description</label></th>
            <td>
                <input type="text" id="giveaway_prize" name="giveaway_prize" value="<?php echo esc_attr($prize); ?>" class="large-text" placeholder="iPhone 15 Pro Max + Accessories">
                <p class="description">Brief description of what they can win</p>
            </td>
        </tr>
        <tr>
            <th><label for="giveaway_bg_color">Background Color</label></th>
            <td>
                <input type="text" id="giveaway_bg_color" name="giveaway_bg_color" value="<?php echo esc_attr($bg_color); ?>" class="color-picker">
            </td>
        </tr>
        <tr>
            <th><label for="giveaway_button_text">Button Text</label></th>
            <td>
                <input type="text" id="giveaway_button_text" name="giveaway_button_text" value="<?php echo esc_attr($button_text); ?>" class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="giveaway_button_link">Entry Link</label></th>
            <td>
                <input type="url" id="giveaway_button_link" name="giveaway_button_link" value="<?php echo esc_attr($button_link); ?>" class="large-text" placeholder="https://forms.google.com/...">
                <p class="description">Link to Google Form, contest page, etc.</p>
            </td>
        </tr>
        <tr>
            <th><label for="giveaway_start_date">Start Date</label></th>
            <td>
                <input type="date" id="giveaway_start_date" name="giveaway_start_date" value="<?php echo esc_attr($start_date); ?>" class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="giveaway_end_date">End Date</label></th>
            <td>
                <input type="date" id="giveaway_end_date" name="giveaway_end_date" value="<?php echo esc_attr($end_date); ?>" class="regular-text">
                <p class="description">Giveaway will auto-hide after this date</p>
            </td>
        </tr>
        <tr>
            <th><label for="giveaway_active">Status</label></th>
            <td>
                <label>
                    <input type="checkbox" id="giveaway_active" name="giveaway_active" value="1" <?php checked($is_active, '1'); ?>>
                    <strong>Active</strong> (Show on homepage)
                </label>
            </td>
        </tr>
    </table>
    
    <script>
    jQuery(document).ready(function($){
        $('.color-picker').wpColorPicker();
    });
    </script>
    <?php
}

function save_giveaway_meta($post_id) {
    if (!isset($_POST['giveaway_nonce']) || !wp_verify_nonce($_POST['giveaway_nonce'], 'save_giveaway')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    $fields = array('giveaway_prize', 'giveaway_bg_color', 'giveaway_button_text', 'giveaway_button_link', 'giveaway_start_date', 'giveaway_end_date');
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    update_post_meta($post_id, 'giveaway_active', isset($_POST['giveaway_active']) ? '1' : '0');
}
add_action('save_post_giveaway', 'save_giveaway_meta');

// =====================================================
// HELPER FUNCTIONS
// =====================================================

function dealsindia_get_welcome_text() {
    return get_option('dealsindia_welcome_text', 'Welcome to ' . get_bloginfo('name') . ' - India\'s Trusted Coupons, Offers & Cashback Website');
}

function dealsindia_get_section_title($section) {
    $defaults = array(
        'hot_picks' => 'ðŸ”¥ Hot Picks!',
        'stores' => 'ðŸª Top Stores',
        'latest_deals' => 'ðŸ“° Latest Deals',
        'categories' => 'ðŸ“± Browse by Category',
    );
    
    return get_option('dealsindia_' . $section . '_title', $defaults[$section]);
}

function dealsindia_get_hero_banners() {
    return get_posts(array(
        'post_type' => 'hero_banner',
        'posts_per_page' => 5,
        'post_status' => 'publish',
        'meta_key' => 'banner_order',
        'orderby' => 'meta_value_num',
        'order' => 'ASC'
    ));
}

function dealsindia_get_work_steps() {
    return get_posts(array(
        'post_type' => 'work_step',
        'posts_per_page' => 10,
        'post_status' => 'publish',
        'meta_key' => 'step_order',
        'orderby' => 'meta_value_num',
        'order' => 'ASC'
    ));
}

function dealsindia_get_active_giveaway() {
    $giveaways = get_posts(array(
        'post_type' => 'giveaway',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'giveaway_active',
                'value' => '1'
            )
        )
    ));
    
    if (!empty($giveaways)) {
        $giveaway = $giveaways[0];
        $end_date = get_post_meta($giveaway->ID, 'giveaway_end_date', true);
        
        if (!$end_date || strtotime($end_date) >= time()) {
            return $giveaway;
        }
    }
    
    return null;
}

// =====================================================
// DYNAMIC SECTION TITLES
// =====================================================
function dealsindia_get_work_steps_title() {
    return get_option('dealsindia_work_steps_title', 'Three Steps to Save with ' . get_bloginfo('name'));
}

function dealsindia_get_hot_picks_title() {
    return get_option('dealsindia_hot_picks_title', 'ðŸ”¥ Hot Picks!');
}

function dealsindia_get_categories_title() {
    return get_option('dealsindia_categories_title', 'Trending Categories');
}

function dealsindia_get_stores_title() {
    return get_option('dealsindia_stores_title', 'Top Stores');
}

function dealsindia_get_offers_title() {
    return get_option('dealsindia_offers_title', 'Offers');
}

function dealsindia_get_see_more_text() {
    return get_option('dealsindia_see_more_text', 'See More');
}

function dealsindia_get_show_more_text() {
    return get_option('dealsindia_show_more_text', 'Show More');
}

// =====================================================
// CATEGORY & STORE TERM META
// =====================================================
function dealsindia_add_term_meta_fields() {
    add_action('deal_category_add_form_fields', 'dealsindia_category_icon_field_add');
    add_action('deal_category_edit_form_fields', 'dealsindia_category_icon_field_edit');
    add_action('created_deal_category', 'dealsindia_save_category_icon');
    add_action('edited_deal_category', 'dealsindia_save_category_icon');
    
    add_action('store_add_form_fields', 'dealsindia_store_cashback_field_add');
    add_action('store_edit_form_fields', 'dealsindia_store_cashback_field_edit');
    add_action('created_store', 'dealsindia_save_store_cashback');
    add_action('edited_store', 'dealsindia_save_store_cashback');
}
add_action('admin_init', 'dealsindia_add_term_meta_fields');

function dealsindia_category_icon_field_add() {
    ?>
    <div class="form-field">
        <label for="category_icon">Category Icon (Emoji)</label>
        <input type="text" name="category_icon" id="category_icon" value="ðŸ·ï¸" maxlength="2">
        <p class="description">Enter an emoji icon for this category (e.g., ðŸ“±, ðŸ‘•, ðŸ”)</p>
    </div>
    <?php
}

function dealsindia_category_icon_field_edit($term) {
    $icon = get_term_meta($term->term_id, 'category_icon', true);
    ?>
    <tr class="form-field">
        <th><label for="category_icon">Category Icon</label></th>
        <td>
            <input type="text" name="category_icon" id="category_icon" value="<?php echo esc_attr($icon ? $icon : 'ðŸ·ï¸'); ?>" maxlength="2">
            <p class="description">Enter an emoji icon for this category</p>
        </td>
    </tr>
    <?php
}

function dealsindia_save_category_icon($term_id) {
    if (isset($_POST['category_icon'])) {
        update_term_meta($term_id, 'category_icon', sanitize_text_field($_POST['category_icon']));
    }
}

function dealsindia_store_cashback_field_add() {
    ?>
    <div class="form-field">
        <label for="store_cashback">Cashback Percentage</label>
        <input type="text" name="store_cashback" id="store_cashback" value="5%" placeholder="5%">
        <p class="description">Enter cashback percentage (e.g., 5%, 10%)</p>
    </div>
    <?php
}

function dealsindia_store_cashback_field_edit($term) {
    $cashback = get_term_meta($term->term_id, 'store_cashback', true);
    ?>
    <tr class="form-field">
        <th><label for="store_cashback">Cashback Percentage</label></th>
        <td>
            <input type="text" name="store_cashback" id="store_cashback" value="<?php echo esc_attr($cashback ? $cashback : '5%'); ?>">
            <p class="description">Enter cashback percentage (e.g., 5%, 10%)</p>
        </td>
    </tr>
    <?php
}

function dealsindia_save_store_cashback($term_id) {
    if (isset($_POST['store_cashback'])) {
        update_term_meta($term_id, 'store_cashback', sanitize_text_field($_POST['store_cashback']));
    }
}

// =====================================================
// COLOR MANIPULATION HELPERS
// =====================================================
function dealsindia_lighten_color($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = min(255, $r + ($r * $percent / 100));
    $g = min(255, $g + ($g * $percent / 100));
    $b = min(255, $b + ($b * $percent / 100));
    
    return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
}

function dealsindia_darken_color($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, $r - ($r * $percent / 100));
    $g = max(0, $g - ($g * $percent / 100));
    $b = max(0, $b - ($b * $percent / 100));
    
    return '#' . sprintf('%02x%02x%02x', $r, $g, $b);
}

// =====================================================
// DEAL CARD TEXT HELPERS
// =====================================================
function dealsindia_get_off_text() {
    return get_option('dealsindia_off_text', 'OFF');
}

function dealsindia_get_featured_text() {
    return get_option('dealsindia_featured_text', 'Featured');
}

function dealsindia_get_trending_text() {
    return get_option('dealsindia_trending_text', 'Trending');
}

function dealsindia_get_no_image_text() {
    return get_option('dealsindia_no_image_text', 'No Image');
}

function dealsindia_get_expired_text() {
    return get_option('dealsindia_expired_text', 'Expired');
}

function dealsindia_get_code_text() {
    return get_option('dealsindia_code_text', 'Code');
}

function dealsindia_get_expires_text() {
    return get_option('dealsindia_expires_text', 'Expires');
}

function dealsindia_get_deal_btn_text() {
    return get_option('dealsindia_deal_btn_text', 'Get Deal');
}

function dealsindia_get_deal_expired_btn_text() {
    return get_option('dealsindia_deal_expired_btn_text', 'Deal Expired');
}

// =====================================================
// NAVIGATION MENU REGISTRATION
// =====================================================
function dealsindia_register_menus() {
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'dealsindia'),
        'footer' => __('Footer Menu', 'dealsindia')
    ));
}
add_action('init', 'dealsindia_register_menus');

function dealsindia_fallback_menu() {
    echo '<ul class="nav-menu-items">';
    echo '<li><a href="' . home_url('/deals/') . '">All Deals</a></li>';
    
    $categories = get_terms(array(
        'taxonomy' => 'deal_category',
        'number' => 4,
        'hide_empty' => false
    ));
    
    foreach ($categories as $cat) {
        echo '<li><a href="' . get_term_link($cat) . '">' . esc_html($cat->name) . '</a></li>';
    }
    
    echo '</ul>';
}

// =====================================================
// HOT PICKS SECTION CUSTOMIZATION
// =====================================================
function dealsindia_hot_picks_customizer($wp_customize) {
    $wp_customize->add_section('dealsindia_hot_picks_section', array(
        'title' => 'Hot Picks Section',
        'priority' => 30,
    ));
    
    $wp_customize->add_setting('dealsindia_hot_picks_bg_image', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw'
    ));
    
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'dealsindia_hot_picks_bg_image', array(
        'label' => 'Background Image',
        'description' => 'Upload decorative background (festivals, events, etc.)',
        'section' => 'dealsindia_hot_picks_section',
        'settings' => 'dealsindia_hot_picks_bg_image',
    )));
    
    $wp_customize->add_setting('dealsindia_hot_picks_bg_color', array(
        'default' => '#00897b',
        'sanitize_callback' => 'sanitize_hex_color'
    ));
    
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'dealsindia_hot_picks_bg_color', array(
        'label' => 'Background Color (if no image)',
        'section' => 'dealsindia_hot_picks_section',
        'settings' => 'dealsindia_hot_picks_bg_color',
    )));
    
    $wp_customize->add_setting('dealsindia_hot_picks_border_radius', array(
        'default' => '24',
        'sanitize_callback' => 'absint'
    ));
    
    $wp_customize->add_control('dealsindia_hot_picks_border_radius', array(
        'label' => 'Container Border Radius (px)',
        'section' => 'dealsindia_hot_picks_section',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 0,
            'max' => 50,
            'step' => 1,
        ),
    ));
    
    $wp_customize->add_setting('dealsindia_hot_picks_overlay_opacity', array(
        'default' => '0.2',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    $wp_customize->add_control('dealsindia_hot_picks_overlay_opacity', array(
        'label' => 'Background Overlay Opacity',
        'description' => '0 = no overlay, 1 = full overlay',
        'section' => 'dealsindia_hot_picks_section',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 0,
            'max' => 1,
            'step' => 0.1,
        ),
    ));
}
add_action('customize_register', 'dealsindia_hot_picks_customizer');

// =====================================================
// LOGO CUSTOMIZER SUPPORT
// =====================================================
function dealsindia_customize_register($wp_customize) {
    $wp_customize->add_setting('dealsindia_logo_icon', array(
        'default' => 'ðŸ›ï¸',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    $wp_customize->add_control('dealsindia_logo_icon', array(
        'label' => 'Logo Icon (Emoji)',
        'section' => 'title_tagline',
        'type' => 'text'
    ));
    
    $wp_customize->add_setting('dealsindia_logo_text', array(
        'default' => get_bloginfo('name'),
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    $wp_customize->add_control('dealsindia_logo_text', array(
        'label' => 'Logo Text',
        'section' => 'title_tagline',
        'type' => 'text'
    ));
    
    $wp_customize->add_setting('dealsindia_search_placeholder', array(
        'default' => 'Search for deals, stores, categories...',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    
    $wp_customize->add_control('dealsindia_search_placeholder', array(
        'label' => 'Search Placeholder Text',
        'section' => 'title_tagline',
        'type' => 'text'
    ));
}
add_action('customize_register', 'dealsindia_customize_register');

// =====================================================
// FOOTER CUSTOMIZATION OPTIONS
// =====================================================
function dealsindia_footer_settings() {
    add_settings_section(
        'dealsindia_footer_section',
        'Footer Settings',
        null,
        'dealsindia_settings'
    );
    
    $footer_fields = array(
        'dealsindia_footer_logo_text' => 'Footer Logo Text',
        'dealsindia_footer_description' => 'Footer Description',
        'dealsindia_footer_links_title' => 'Quick Links Title',
        'dealsindia_footer_categories_title' => 'Categories Title',
        'dealsindia_footer_stores_title' => 'Stores Title',
        'dealsindia_footer_category_count' => 'Number of Categories',
        'dealsindia_footer_store_count' => 'Number of Stores',
        'dealsindia_copyright_text' => 'Copyright Text',
        'dealsindia_footer_credit' => 'Footer Credit Text',
        'dealsindia_facebook_url' => 'Facebook URL',
        'dealsindia_twitter_url' => 'Twitter URL',
        'dealsindia_instagram_url' => 'Instagram URL',
        'dealsindia_youtube_url' => 'YouTube URL',
        'dealsindia_telegram_url' => 'Telegram URL'
    );
    
    foreach ($footer_fields as $key => $label) {
        register_setting('dealsindia_settings_group', $key);
    }
}
add_action('admin_init', 'dealsindia_footer_settings');

require_once get_template_directory() . '/dealsindia-demo-content.php';

// =====================================================
// STORE LOGO UPLOAD FUNCTIONALITY
// =====================================================

// Add logo field to Store ADD form
function dealsindia_store_logo_field_add() {
    ?>
    <div class="form-field">
        <label>Store Logo</label>
        <input type="hidden" id="store_logo_id" name="store_logo_id" value="">
        <button type="button" class="upload-store-logo-btn button">Upload Store Logo</button>
        <div class="store-logo-preview" style="margin-top: 10px;"></div>
        <p class="description">Upload a logo image for this store (PNG, JPG, or SVG recommended)</p>
    </div>
    <?php
}
add_action('store_add_form_fields', 'dealsindia_store_logo_field_add');

// Add logo field to Store EDIT form
function dealsindia_store_logo_field_edit($term) {
    $logo_id = get_term_meta($term->term_id, 'store_logo_id', true);
    $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
    ?>
    <tr class="form-field">
        <th><label>Store Logo</label></th>
        <td>
            <input type="hidden" id="store_logo_id" name="store_logo_id" value="<?php echo esc_attr($logo_id); ?>">
            <button type="button" class="upload-store-logo-btn button">
                <?php echo $logo_url ? 'Change Logo' : 'Upload Logo'; ?>
            </button>
            <button type="button" class="remove-store-logo-btn button" style="<?php echo $logo_url ? '' : 'display:none;'; ?>">Remove Logo</button>
            <div class="store-logo-preview" style="margin-top: 10px;">
                <?php if ($logo_url) : ?>
                    <img src="<?php echo esc_url($logo_url); ?>" style="max-width: 150px; max-height: 150px; display: block; border: 1px solid #ddd; padding: 5px; background: white;">
                <?php endif; ?>
            </div>
            <p class="description">Upload a square logo (recommended: 200x200px, PNG with transparent background)</p>
        </td>
    </tr>
    <?php
}
add_action('store_edit_form_fields', 'dealsindia_store_logo_field_edit');

// Save store logo
function dealsindia_save_store_logo($term_id) {
    if (isset($_POST['store_logo_id'])) {
        update_term_meta($term_id, 'store_logo_id', sanitize_text_field($_POST['store_logo_id']));
    }
}
add_action('created_store', 'dealsindia_save_store_logo');
add_action('edited_store', 'dealsindia_save_store_logo');

// Enqueue media uploader script for stores
function dealsindia_store_logo_scripts($hook) {
    if ('edit-tags.php' !== $hook && 'term.php' !== $hook) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || $screen->taxonomy !== 'store') {
        return;
    }
    
    wp_enqueue_media();
    wp_enqueue_script('dealsindia-store-logo-upload', get_template_directory_uri() . '/assets/js/store-logo-upload.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'dealsindia_store_logo_scripts');

function dealsindia_get_latest_deals_title() {
    return get_option('dealsindia_latest_deals_title', 'Latest Deals');
}

function dealsindia_get_giveaway_title() {
    return get_option('dealsindia_giveaway_title', 'ðŸŽ Active Giveaway');
}

function dealsindia_get_about_title() {
    return get_option('dealsindia_about_title', 'Why Choose Us');
}

function dealsindia_get_newsletter_title() {
    return get_option('dealsindia_newsletter_title', 'ðŸ“§ Get Latest Deals');
}

// =====================================================
// STORE ARCHIVE SORTING
// =====================================================
add_action('pre_get_posts', 'dealsindia_store_archive_sorting');
function dealsindia_store_archive_sorting($query) {
    if (!is_admin() && $query->is_main_query() && is_tax('store')) {
        $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'date-desc';
        
        switch ($orderby) {
            case 'date-asc':
                $query->set('orderby', 'date');
                $query->set('order', 'ASC');
                break;
            case 'price-asc':
                $query->set('meta_key', 'sale_price');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'ASC');
                break;
            case 'price-desc':
                $query->set('meta_key', 'sale_price');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'discount-desc':
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                break;
            default: // date-desc
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
        }
    }
}

// =====================================================
// CATEGORY ICON IMAGE UPLOAD
// =====================================================

// Add image upload field to category add form
add_action('deal_category_add_form_fields', 'dealsindia_add_category_icon_field');
function dealsindia_add_category_icon_field() {
    ?>
    <div class="form-field">
        <label for="category_icon_image">Category Icon Image</label>
        <div class="category-icon-upload-wrapper">
            <img id="category-icon-preview" src="" style="max-width: 80px; max-height: 80px; display: none; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
            <br>
            <input type="hidden" id="category_icon_image_id" name="category_icon_image_id" value="">
            <button type="button" class="button category-icon-upload-btn">Upload Icon Image</button>
            <button type="button" class="button category-icon-remove-btn" style="display: none;">Remove Image</button>
        </div>
        <p class="description">Upload a category icon (recommended: 64x64px PNG with transparency)</p>
    </div>
    
    <div class="form-field">
        <label for="category_icon">Category Icon Emoji (Fallback)</label>
        <input type="text" name="category_icon" id="category_icon" value="" placeholder="ðŸ·ï¸">
        <p class="description">Emoji to use if no image is uploaded</p>
    </div>
    <?php
}

// Add image upload field to category edit form
add_action('deal_category_edit_form_fields', 'dealsindia_edit_category_icon_field');
function dealsindia_edit_category_icon_field($term) {
    $icon_image_id = get_term_meta($term->term_id, 'category_icon_image_id', true);
    $icon_image_url = $icon_image_id ? wp_get_attachment_url($icon_image_id) : '';
    $icon_emoji = get_term_meta($term->term_id, 'category_icon', true);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="category_icon_image">Category Icon Image</label>
        </th>
        <td>
            <div class="category-icon-upload-wrapper">
                <img id="category-icon-preview" src="<?php echo esc_url($icon_image_url); ?>" style="max-width: 80px; max-height: 80px; display: <?php echo $icon_image_url ? 'block' : 'none'; ?>; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                <br>
                <input type="hidden" id="category_icon_image_id" name="category_icon_image_id" value="<?php echo esc_attr($icon_image_id); ?>">
                <button type="button" class="button category-icon-upload-btn">Upload Icon Image</button>
                <button type="button" class="button category-icon-remove-btn" style="display: <?php echo $icon_image_url ? 'inline-block' : 'none'; ?>;">Remove Image</button>
            </div>
            <p class="description">Upload a category icon (recommended: 64x64px PNG with transparency)</p>
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row">
            <label for="category_icon">Category Icon Emoji (Fallback)</label>
        </th>
        <td>
            <input type="text" name="category_icon" id="category_icon" value="<?php echo esc_attr($icon_emoji); ?>" placeholder="ðŸ·ï¸">
            <p class="description">Emoji to use if no image is uploaded</p>
        </td>
    </tr>
    <?php
}

// Save category icon fields
add_action('created_deal_category', 'dealsindia_save_category_icon_field');
add_action('edited_deal_category', 'dealsindia_save_category_icon_field');
function dealsindia_save_category_icon_field($term_id) {
    if (isset($_POST['category_icon_image_id'])) {
        update_term_meta($term_id, 'category_icon_image_id', sanitize_text_field($_POST['category_icon_image_id']));
    }
    
    if (isset($_POST['category_icon'])) {
        update_term_meta($term_id, 'category_icon', sanitize_text_field($_POST['category_icon']));
    }
}

// Enqueue media uploader scripts
add_action('admin_enqueue_scripts', 'dealsindia_category_icon_admin_scripts');
function dealsindia_category_icon_admin_scripts($hook) {
    if ($hook !== 'edit-tags.php' && $hook !== 'term.php') {
        return;
    }
    
    wp_enqueue_media();
    wp_enqueue_script('dealsindia-category-icon-upload', get_template_directory_uri() . '/assets/js/category-icon-upload.js', array('jquery'), '1.0.0', true);
}


?>