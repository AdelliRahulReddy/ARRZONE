<?php
/**
 * Enhanced Deal Filter Sidebar
 * Context-aware filtering with AJAX support
 * 
 * @package DealsIndia
 * @version 2.0 - Spider-Verse Enhanced Archive
 */

// Detect context for smart filtering
$context_type = '';
$context_slug = '';
$context_name = '';

if (is_tax('store')) {
    $context_type = 'store';
    $queried_object = get_queried_object();
    $context_slug = $queried_object->slug;
    $context_name = $queried_object->name;
} elseif (is_tax('deal_category')) {
    $context_type = 'deal_category';
    $queried_object = get_queried_object();
    $context_slug = $queried_object->slug;
    $context_name = $queried_object->name;
}

// Get filter counts (cached for performance)
$filter_counts = dealsindia_get_filter_counts($context_type, $context_slug);
?>

<aside class="deal-filters-sidebar enhanced" id="dealFiltersSidebar" data-context-type="<?php echo esc_attr($context_type); ?>" data-context-slug="<?php echo esc_attr($context_slug); ?>">
    
    <!-- Filter Header -->
    <div class="filter-header">
        <h2 class="filter-main-title">
            <svg class="filter-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="4" y1="6" x2="16" y2="6" stroke-width="2"/>
                <line x1="4" y1="12" x2="20" y2="12" stroke-width="2"/>
                <line x1="4" y1="18" x2="12" y2="18" stroke-width="2"/>
                <circle cx="18" cy="6" r="2"/>
                <circle cx="6" cy="12" r="2"/>
                <circle cx="14" cy="18" r="2"/>
            </svg>
            <?php esc_html_e('Filters', 'dealsindia'); ?>
        </h2>
        <button class="filter-close-btn" id="filterCloseBtn" aria-label="Close filters">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="6" x2="6" y2="18" stroke-width="2"/>
                <line x1="6" y1="6" x2="18" y2="18" stroke-width="2"/>
            </svg>
        </button>
    </div>

    <!-- Context Info (Store/Category Page) -->
    <?php if (!empty($context_name)): ?>
    <div class="filter-context-info">
        <span class="context-label"><?php echo $context_type === 'store' ? esc_html__('Store:', 'dealsindia') : esc_html__('Category:', 'dealsindia'); ?></span>
        <strong class="context-value"><?php echo esc_html($context_name); ?></strong>
    </div>
    <?php endif; ?>

    <!-- Active Filters Display -->
    <div class="active-filters-container" id="activeFiltersContainer" style="display: none;">
        <div class="active-filters-header">
            <span><?php esc_html_e('Active Filters:', 'dealsindia'); ?></span>
            <button class="clear-all-btn" id="clearAllFiltersBtn"><?php esc_html_e('Clear All', 'dealsindia'); ?></button>
        </div>
        <div class="active-filters-list" id="activeFiltersList"></div>
    </div>

    <!-- Filter Sections -->
    <div class="filter-sections-wrapper">

        <!-- Sort By Section (Always visible) -->
        <div class="filter-section filter-section-sort">
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
                        'latest'      => __('Latest Deals', 'dealsindia'),
                        'popular'     => __('Most Popular', 'dealsindia'),
                        'discount'    => __('Highest Discount', 'dealsindia'),
                        'expiring'    => __('Expiring Soon', 'dealsindia'),
                        'price_low'   => __('Price: Low to High', 'dealsindia'),
                        'price_high'  => __('Price: High to Low', 'dealsindia'),
                    );
                    
                    foreach ($sort_options as $value => $label):
                    ?>
                    <label class="filter-option-item">
                        <input type="radio" name="sort_by" value="<?php echo esc_attr($value); ?>" <?php checked($value, 'latest'); ?> class="filter-radio">
                        <span class="filter-option-label"><?php echo esc_html($label); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Deal Types Section (Hidden on deal_type archives) -->
        <?php if ($context_type !== 'deal_type' && !empty($filter_counts['deal_types'])): ?>
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
                    <?php foreach ($filter_counts['deal_types'] as $type): ?>
                    <label class="filter-option-item">
                        <input type="checkbox" name="deal_types[]" value="<?php echo esc_attr($type['slug']); ?>" class="filter-checkbox" data-filter-type="deal_type">
                        <span class="filter-option-label">
                            <?php echo esc_html($type['name']); ?>
                            <span class="filter-count">(<?php echo esc_html($type['count']); ?>)</span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Categories Section (Hidden on category archives) -->
        <?php if ($context_type !== 'deal_category' && !empty($filter_counts['categories'])): ?>
        <div class="filter-section filter-section-categories">
            <button class="filter-section-header" type="button" aria-expanded="true">
                <h3 class="filter-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Categories', 'dealsindia'); ?>
                </h3>
                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                </svg>
            </button>
            <div class="filter-section-content">
                <?php if (count($filter_counts['categories']) > 5): ?>
                <div class="filter-search-box">
                    <input type="text" class="filter-search-input" placeholder="<?php esc_attr_e('Search categories...', 'dealsindia'); ?>" data-search-target="categories">
                    <svg class="filter-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"/>
                    </svg>
                </div>
                <?php endif; ?>
                <div class="filter-options-list filter-options-searchable" data-filter-type="categories">
                    <?php 
                    $category_count = 0;
                    foreach ($filter_counts['categories'] as $category): 
                        $is_hidden = $category_count >= 5 ? ' filter-option-hidden' : '';
                    ?>
                    <label class="filter-option-item<?php echo $is_hidden; ?>" data-search-name="<?php echo esc_attr(strtolower($category['name'])); ?>">
                        <input type="checkbox" name="categories[]" value="<?php echo esc_attr($category['slug']); ?>" class="filter-checkbox" data-filter-type="category">
                        <span class="filter-option-label">
                            <?php if (!empty($category['icon'])): ?>
                            <img src="<?php echo esc_url($category['icon']); ?>" alt="" class="filter-option-icon">
                            <?php endif; ?>
                            <?php echo esc_html($category['name']); ?>
                            <span class="filter-count">(<?php echo esc_html($category['count']); ?>)</span>
                        </span>
                    </label>
                    <?php 
                    $category_count++;
                    endforeach; 
                    ?>
                </div>
                <?php if (count($filter_counts['categories']) > 5): ?>
                <button class="filter-show-more-btn" data-target="categories">
                    <span class="show-more-text"><?php esc_html_e('Show More', 'dealsindia'); ?></span>
                    <span class="show-less-text" style="display:none;"><?php esc_html_e('Show Less', 'dealsindia'); ?></span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                    </svg>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stores Section (Hidden on store archives) -->
        <?php if ($context_type !== 'store' && !empty($filter_counts['stores'])): ?>
        <div class="filter-section filter-section-stores">
            <button class="filter-section-header" type="button" aria-expanded="true">
                <h3 class="filter-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-width="2"/>
                        <polyline points="9 22 9 12 15 12 15 22" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Stores', 'dealsindia'); ?>
                </h3>
                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                </svg>
            </button>
            <div class="filter-section-content">
                <?php if (count($filter_counts['stores']) > 5): ?>
                <div class="filter-search-box">
                    <input type="text" class="filter-search-input" placeholder="<?php esc_attr_e('Search stores...', 'dealsindia'); ?>" data-search-target="stores">
                    <svg class="filter-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8" stroke-width="2"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"/>
                    </svg>
                </div>
                <?php endif; ?>
                <div class="filter-options-list filter-options-searchable" data-filter-type="stores">
                    <?php 
                    $store_count = 0;
                    foreach ($filter_counts['stores'] as $store): 
                        $is_hidden = $store_count >= 5 ? ' filter-option-hidden' : '';
                    ?>
                    <label class="filter-option-item<?php echo $is_hidden; ?>" data-search-name="<?php echo esc_attr(strtolower($store['name'])); ?>">
                        <input type="checkbox" name="stores[]" value="<?php echo esc_attr($store['slug']); ?>" class="filter-checkbox" data-filter-type="store">
                        <span class="filter-option-label">
                            <?php if (!empty($store['logo'])): ?>
                            <img src="<?php echo esc_url($store['logo']); ?>" alt="" class="filter-option-icon">
                            <?php endif; ?>
                            <?php echo esc_html($store['name']); ?>
                            <span class="filter-count">(<?php echo esc_html($store['count']); ?>)</span>
                            <?php if (!empty($store['cashback'])): ?>
                            <span class="filter-cashback-badge"><?php echo esc_html($store['cashback']); ?>% 🎁</span>
                            <?php endif; ?>
                        </span>
                    </label>
                    <?php 
                    $store_count++;
                    endforeach; 
                    ?>
                </div>
                <?php if (count($filter_counts['stores']) > 5): ?>
                <button class="filter-show-more-btn" data-target="stores">
                    <span class="show-more-text"><?php esc_html_e('Show More', 'dealsindia'); ?></span>
                    <span class="show-less-text" style="display:none;"><?php esc_html_e('Show Less', 'dealsindia'); ?></span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                    </svg>
                </button>
                <?php endif; ?>
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
            <div class="filter-section-content">
                <div class="price-range-inputs">
                    <input type="number" id="filterPriceMin" name="price_min" placeholder="<?php esc_attr_e('Min ₹', 'dealsindia'); ?>" min="0" class="filter-input-number">
                    <span class="price-separator">–</span>
                    <input type="number" id="filterPriceMax" name="price_max" placeholder="<?php esc_attr_e('Max ₹', 'dealsindia'); ?>" min="0" class="filter-input-number">
                </div>
            </div>
        </div>

        <!-- Discount Section -->
        <div class="filter-section filter-section-discount">
            <button class="filter-section-header" type="button" aria-expanded="false">
                <h3 class="filter-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="9" cy="9" r="2" stroke-width="2"/>
                        <circle cx="15" cy="15" r="2" stroke-width="2"/>
                        <line x1="3" y1="3" x2="21" y2="21" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Discount', 'dealsindia'); ?>
                </h3>
                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                </svg>
            </button>
            <div class="filter-section-content">
                <div class="filter-options-list">
                    <?php
                    $discount_options = array(
                        '10'  => __('10% or more', 'dealsindia'),
                        '20'  => __('20% or more', 'dealsindia'),
                        '30'  => __('30% or more', 'dealsindia'),
                        '50'  => __('50% or more', 'dealsindia'),
                        '70'  => __('70% or more', 'dealsindia'),
                    );
                    
                    foreach ($discount_options as $value => $label):
                    ?>
                    <label class="filter-option-item">
                        <input type="radio" name="discount_min" value="<?php echo esc_attr($value); ?>" class="filter-radio" data-filter-type="discount">
                        <span class="filter-option-label"><?php echo esc_html($label); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Expiry Section -->
        <div class="filter-section filter-section-expiry">
            <button class="filter-section-header" type="button" aria-expanded="false">
                <h3 class="filter-section-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <polyline points="12 6 12 12 16 14" stroke-width="2"/>
                    </svg>
                    <?php esc_html_e('Expiry', 'dealsindia'); ?>
                </h3>
                <svg class="filter-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9" stroke-width="2"/>
                </svg>
            </button>
            <div class="filter-section-content">
                <div class="filter-options-list">
                    <?php
                    $expiry_options = array(
                        'today' => __('Expiring Today', 'dealsindia'),
                        'week'  => __('This Week', 'dealsindia'),
                        'month' => __('This Month', 'dealsindia'),
                    );
                    
                    foreach ($expiry_options as $value => $label):
                    ?>
                    <label class="filter-option-item">
                        <input type="radio" name="expiry" value="<?php echo esc_attr($value); ?>" class="filter-radio" data-filter-type="expiry">
                        <span class="filter-option-label"><?php echo esc_html($label); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Filter Actions (Sticky Footer) -->
    <div class="filter-actions-footer">
        <button type="button" class="btn-clear-filters" id="btnClearAllFilters">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="3 6 5 6 21 6" stroke-width="2"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-width="2"/>
            </svg>
            <?php esc_html_e('Clear All', 'dealsindia'); ?>
        </button>
        <button type="button" class="btn-apply-filters" id="btnApplyFilters">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="20 6 9 17 4 12" stroke-width="2"/>
            </svg>
            <?php esc_html_e('Apply Filters', 'dealsindia'); ?>
        </button>
    </div>

    <!-- Loading Overlay -->
    <div class="filter-loading-overlay" id="filterLoadingOverlay" style="display: none;">
        <div class="filter-spinner"></div>
        <span class="filter-loading-text"><?php esc_html_e('Applying filters...', 'dealsindia'); ?></span>
    </div>

</aside>

<!-- Mobile Filter Toggle Button -->
<button class="mobile-filter-toggle-btn" id="mobileFilterToggleBtn" aria-label="<?php esc_attr_e('Open Filters', 'dealsindia'); ?>">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <line x1="4" y1="6" x2="16" y2="6" stroke-width="2"/>
        <line x1="4" y1="12" x2="20" y2="12" stroke-width="2"/>
        <line x1="4" y1="18" x2="12" y2="18" stroke-width="2"/>
        <circle cx="18" cy="6" r="2"/>
        <circle cx="6" cy="12" r="2"/>
        <circle cx="14" cy="18" r="2"/>
    </svg>
    <span><?php esc_html_e('Filters', 'dealsindia'); ?></span>
</button>
