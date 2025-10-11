<?php
/**
 * Deal Filter Sidebar
 * 
 * @package DealsIndia
 */

// Get all categories
$categories = get_terms(array(
    'taxonomy' => 'deal_category',
    'hide_empty' => true
));

// Get all stores
$stores = get_terms(array(
    'taxonomy' => 'store',
    'hide_empty' => true
));
?>

<aside class="deal-filters-sidebar" id="dealFilters">
    
    <div class="filter-section">
        <h3 class="filter-title">Sort By</h3>
        <select id="filterSort" class="filter-select">
            <option value="latest">Latest Deals</option>
            <option value="price_low">Price: Low to High</option>
            <option value="price_high">Price: High to Low</option>
            <option value="discount">Highest Discount</option>
            <option value="expiring">Expiring Soon</option>
        </select>
    </div>
    
    <?php if (!empty($categories)) : ?>
    <div class="filter-section">
        <h3 class="filter-title">Category</h3>
        <select id="filterCategory" class="filter-select">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat) : ?>
                <option value="<?php echo esc_attr($cat->slug); ?>">
                    <?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($stores)) : ?>
    <div class="filter-section">
        <h3 class="filter-title">Store</h3>
        <select id="filterStore" class="filter-select">
            <option value="">All Stores</option>
            <?php foreach ($stores as $store) : ?>
                <option value="<?php echo esc_attr($store->slug); ?>">
                    <?php echo esc_html($store->name); ?> (<?php echo $store->count; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    
    <div class="filter-section">
        <h3 class="filter-title">Price Range</h3>
        <div class="price-inputs">
            <input type="number" id="filterMinPrice" placeholder="Min ₹" min="0" class="filter-input" />
            <span class="price-separator">-</span>
            <input type="number" id="filterMaxPrice" placeholder="Max ₹" min="0" class="filter-input" />
        </div>
    </div>
    
    <div class="filter-actions">
        <button id="applyFilters" class="btn-apply-filters">Apply Filters</button>
        <button id="resetFilters" class="btn-reset-filters">Reset</button>
    </div>
    
    <div id="filterLoader" class="filter-loader" style="display: none;">
        <span>Loading deals...</span>
    </div>
    
</aside>
