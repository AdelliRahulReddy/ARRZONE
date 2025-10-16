<?php
if (!defined('ABSPATH')) exit;

/**
 * Enhanced Deal Filter Sidebar - Split Layout
 * Conditionally shows horizontal filters OR sidebar filters based on query vars
 * 
 * ALL JAVASCRIPT REMOVED - Handled by assets/js/filter.js
 * 
 * @package ARRZONE
 * @version 6.0 - Cleaned Up (No Inline Scripts)
 */

// =====================================================
// DISPLAY LOGIC
// =====================================================
$show_horizontal_only = get_query_var('show_horizontal_only', false);
$show_sidebar_only = get_query_var('show_sidebar_only', false);

// =====================================================
// CONTEXT DETECTION
// =====================================================
$context_type = '';
$context_slug = '';
$context_name = '';

if (is_tax('campaign')) {
    $context_type = 'campaign';
    $queried_object = get_queried_object();
    $context_slug = $queried_object->slug;
    $context_name = $queried_object->name;
} elseif (is_tax('store')) {
    $context_type = 'store';
    $queried_object = get_queried_object();
    $context_slug = $queried_object->slug;
    $context_name = $queried_object->name;
} elseif (is_tax('deal-category')) {
    $context_type = 'deal-category';
    $queried_object = get_queried_object();
    $context_slug = $queried_object->slug;
    $context_name = $queried_object->name;
} elseif (is_tax('deal-type')) {
    $context_type = 'deal-type';
    $queried_object = get_queried_object();
    $context_slug = $queried_object->slug;
    $context_name = $queried_object->name;
}

// =====================================================
// GET TAXONOMIES
// =====================================================
$stores = get_terms(array(
    'taxonomy'   => 'store',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 20,
    'exclude'    => ($context_type === 'store' && get_queried_object()) ? array(get_queried_object()->term_id) : array(),
));

$categories = get_terms(array(
    'taxonomy'   => 'deal-category',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 15,
    'exclude'    => ($context_type === 'deal-category' && get_queried_object()) ? array(get_queried_object()->term_id) : array(),
));

$deal_types = get_terms(array(
    'taxonomy'   => 'deal-type',
    'hide_empty' => true,
    'orderby'    => 'count',
    'order'      => 'DESC',
    'number'     => 10,
    'exclude'    => ($context_type === 'deal-type' && get_queried_object()) ? array(get_queried_object()->term_id) : array(),
));

// =====================================================
// HORIZONTAL FILTERS (Full Width)
// =====================================================
if (!$show_sidebar_only && (($context_type !== 'store' && !empty($stores) && !is_wp_error($stores)) || ($context_type !== 'deal-category' && !empty($categories) && !is_wp_error($categories)))) : ?>

<div class="horizontal-filters-wrapper" id="horizontalFilters" 
     data-context-type="<?php echo esc_attr($context_type); ?>" 
     data-context-slug="<?php echo esc_attr($context_slug); ?>">
    
    <div class="container">
        
        <!-- Stores Horizontal Chips -->
        <?php if ($context_type !== 'store' && !empty($stores) && !is_wp_error($stores)) : ?>
            <div class="horizontal-filter-section">
                <div class="horizontal-filter-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-width="2"/>
                        <polyline points="9 22 9 12 15 12 15 22" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Stores', 'dealsindia'); ?>
                </div>
                <div class="horizontal-filter-chips">
                    <?php foreach ($stores as $store) : 
                        $logo_id = get_term_meta($store->term_id, 'store_logo_id', true);
                        $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
                    ?>
                        <button class="filter-chip" data-filter-type="store" data-filter-value="<?php echo esc_attr($store->slug); ?>">
                            <?php if ($logo_url) : ?>
                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($store->name); ?>" class="chip-logo">
                            <?php endif; ?>
                            <span class="chip-label"><?php echo esc_html($store->name); ?></span>
                            <span class="chip-count"><?php echo number_format($store->count); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Categories Horizontal Chips -->
        <?php if ($context_type !== 'deal-category' && !empty($categories) && !is_wp_error($categories)) : ?>
            <div class="horizontal-filter-section">
                <div class="horizontal-filter-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" stroke-width="2"/>
                        <line x1="7" y1="7" x2="7.01" y2="7" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Categories', 'dealsindia'); ?>
                </div>
                <div class="horizontal-filter-chips">
                    <?php foreach ($categories as $category) : 
                        $icon = get_term_meta($category->term_id, 'category_icon', true);
                    ?>
                        <button class="filter-chip" data-filter-type="category" data-filter-value="<?php echo esc_attr($category->slug); ?>">
                            <?php if ($icon) : ?>
                                <span class="chip-icon"><?php echo esc_html($icon); ?></span>
                            <?php endif; ?>
                            <span class="chip-label"><?php echo esc_html($category->name); ?></span>
                            <span class="chip-count"><?php echo number_format($category->count); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
</div>

<?php endif; ?>

<?php
// =====================================================
// SIDEBAR FILTERS (Left Column)
// =====================================================
if (!$show_horizontal_only) : ?>

<div class="deal-filters-sidebar" id="dealFiltersSidebar">
    
    <!-- Filter Header -->
    <div class="filter-header">
        <h2 class="filter-main-title">
            <svg class="filter-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" stroke-width="2"/>
            </svg>
            <?php esc_html_e('Filters', 'dealsindia'); ?>
        </h2>
        <button class="filter-reset-btn" id="filterResetBtn">
            <?php esc_html_e('Reset', 'dealsindia'); ?>
        </button>
    </div>
    
    <!-- Context Info -->
    <?php if (!empty($context_name)) : ?>
        <div class="filter-context-info">
            <span class="context-label">
                <?php 
                if ($context_type === 'campaign') {
                    echo '🎪 ';
                    esc_html_e('Campaign', 'dealsindia');
                } elseif ($context_type === 'store') {
                    esc_html_e('Store', 'dealsindia');
                } elseif ($context_type === 'deal-category') {
                    esc_html_e('Category', 'dealsindia');
                } else {
                    esc_html_e('Filter', 'dealsindia');
                }
                ?>
            </span>
            <strong class="context-value"><?php echo esc_html($context_name); ?></strong>
        </div>
    <?php endif; ?>
    
    <!-- Filter Sections -->
    <div class="filter-sections-wrapper">
        
        <!-- Sort By Section -->
        <div class="filter-section filter-section-sort active">
            <button class="filter-section-header" type="button" aria-expanded="true">
                <h3 class="filter-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="12" y1="5" x2="12" y2="19" stroke-width="2"/>
                        <polyline points="5 12 12 5 19 12" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Sort By', 'dealsindia'); ?>
                </h3>
                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                </svg>
            </button>
            <div class="filter-section-content">
                <div class="filter-options-list">
                    <?php
                    $sort_options = array(
                        'latest'     => __('Latest Deals', 'dealsindia'),
                        'popular'    => __('Most Popular', 'dealsindia'),
                        'discount'   => __('Highest Discount', 'dealsindia'),
                        'price_low'  => __('Price: Low to High', 'dealsindia'),
                        'price_high' => __('Price: High to Low', 'dealsindia'),
                    );
                    
                    foreach ($sort_options as $value => $label) :
                    ?>
                        <label class="filter-option-item">
                            <input type="radio" name="sortby" value="<?php echo esc_attr($value); ?>" <?php checked($value, 'latest'); ?> class="filter-radio">
                            <span class="filter-option-label"><?php echo esc_html($label); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Deal Types Section -->
        <?php if ($context_type !== 'deal-type' && !empty($deal_types) && !is_wp_error($deal_types)) : ?>
            <div class="filter-section filter-section-deal-types">
                <button class="filter-section-header" type="button" aria-expanded="true">
                    <h3 class="filter-section-title">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/>
                            <rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/>
                            <rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/>
                            <rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/>
                        </svg>
                        <?php esc_html_e('Deal Types', 'dealsindia'); ?>
                    </h3>
                    <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                    </svg>
                </button>
                <div class="filter-section-content">
                    <div class="filter-options-list">
                        <?php foreach ($deal_types as $deal_type) : 
                            $icon = get_term_meta($deal_type->term_id, 'dealtype_icon', true);
                        ?>
                            <label class="filter-option-item">
                                <input type="checkbox" value="<?php echo esc_attr($deal_type->slug); ?>" class="filter-checkbox">
                                <span class="filter-option-label">
                                    <?php if ($icon) : ?>
                                        <span class="filter-emoji"><?php echo esc_html($icon); ?></span>
                                    <?php endif; ?>
                                    <?php echo esc_html($deal_type->name); ?>
                                </span>
                                <span class="filter-count"><?php echo number_format($deal_type->count); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Price Range Section -->
        <div class="filter-section filter-section-price">
            <button class="filter-section-header" type="button" aria-expanded="false">
                <h3 class="filter-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="12" y1="1" x2="12" y2="23" stroke-width="2"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Price Range', 'dealsindia'); ?>
                </h3>
                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                </svg>
            </button>
            <div class="filter-section-content" style="display: none;">
                <div class="filter-price-inputs">
                    <div class="price-input-group">
                        <label for="priceRangeMin"><?php esc_html_e('Min', 'dealsindia'); ?></label>
                        <input type="number" id="priceRangeMin" placeholder="₹0" min="0" class="filter-input">
                    </div>
                    <span class="price-separator">-</span>
                    <div class="price-input-group">
                        <label for="priceRangeMax"><?php esc_html_e('Max', 'dealsindia'); ?></label>
                        <input type="number" id="priceRangeMax" placeholder="₹99999" min="0" class="filter-input">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Discount Section -->
        <div class="filter-section filter-section-discount">
            <button class="filter-section-header" type="button" aria-expanded="false">
                <h3 class="filter-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <path d="M8 14l2 2 4-4" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Discount', 'dealsindia'); ?>
                </h3>
                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                </svg>
            </button>
            <div class="filter-section-content" style="display: none;">
                <div class="filter-discount-select">
                    <select id="discountMin" class="filter-select">
                        <option value="0"><?php esc_html_e('Any discount', 'dealsindia'); ?></option>
                        <option value="10"><?php esc_html_e('10%+', 'dealsindia'); ?></option>
                        <option value="20"><?php esc_html_e('20%+', 'dealsindia'); ?></option>
                        <option value="30"><?php esc_html_e('30%+', 'dealsindia'); ?></option>
                        <option value="50"><?php esc_html_e('50%+', 'dealsindia'); ?></option>
                        <option value="70"><?php esc_html_e('70%+', 'dealsindia'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Deal Status Section -->
        <div class="filter-section filter-section-status">
            <button class="filter-section-header" type="button" aria-expanded="false">
                <h3 class="filter-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Status', 'dealsindia'); ?>
                </h3>
                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                </svg>
            </button>
            <div class="filter-section-content" style="display: none;">
                <div class="filter-status-select">
                    <select id="statusFilter" class="filter-select">
                        <option value=""><?php esc_html_e('All deals', 'dealsindia'); ?></option>
                        <option value="active"><?php esc_html_e('Active', 'dealsindia'); ?></option>
                        <option value="ending_soon"><?php esc_html_e('Ending soon', 'dealsindia'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        
    </div>
    
</div>

<?php endif; ?>
