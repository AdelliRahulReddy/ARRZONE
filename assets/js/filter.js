/**
 * Deal Filters - Pure AJAX Implementation
 * Handles horizontal chips + sidebar filters
 * No page reload, instant results
 * 
 * @package ARRZONE
 * @version 4.0 - Fixed Variable Name + Added Mobile Toggle
 */

(function() {
    'use strict';
    
    // =====================================================
    // SAFETY CHECK - Ensure localized data exists
    // =====================================================
    if (typeof dealsindiaFilter === 'undefined') {
        console.error('ARRZONE Filter Error: dealsindiaFilter is not defined. Script loading issue.');
        return;
    }
    
    // =====================================================
    // CONFIGURATION
    // =====================================================
    const config = {
        ajaxUrl: dealsindiaFilter.ajaxUrl,
        nonce: dealsindiaFilter.nonce,
        containerSelector: '#dealsGridContainer',
        filtersSelector: '#dealFiltersSidebar',
        horizontalFiltersSelector: '#horizontalFilters',
        mobileToggleSelector: '#mobileFilterToggle',
        debounceDelay: 500
    };
    
    // =====================================================
    // ACTIVE FILTERS STATE
    // =====================================================
    let activeFilters = {
        stores: [],
        categories: [],
        dealTypes: [],
        sortBy: 'latest',
        priceMin: '',
        priceMax: '',
        discountMin: '0',
        status: '',
        context: {
            type: dealsindiaFilter.contextType || '',
            slug: dealsindiaFilter.contextSlug || ''
        }
    };
    
    // =====================================================
    // INITIALIZE ON DOM READY
    // =====================================================
    document.addEventListener('DOMContentLoaded', function() {
        initFilters();
        initFilterSections();
        initMobileToggle();
    });
    
    /**
     * Initialize all filter handlers
     */
    function initFilters() {
        // Get context from horizontal filters wrapper (fallback)
        const horizontalWrapper = document.querySelector(config.horizontalFiltersSelector);
        if (horizontalWrapper) {
            // Only update if not already set from PHP
            if (!activeFilters.context.type) {
                activeFilters.context.type = horizontalWrapper.dataset.contextType || '';
            }
            if (!activeFilters.context.slug) {
                activeFilters.context.slug = horizontalWrapper.dataset.contextSlug || '';
            }
        }
        
        // Horizontal filter chips (stores, categories)
        document.querySelectorAll('.filter-chip').forEach(chip => {
            chip.addEventListener('click', handleChipClick);
        });
        
        // Sidebar sort options
        document.querySelectorAll('.filter-radio[name="sortby"]').forEach(radio => {
            radio.addEventListener('change', handleSortChange);
        });
        
        // Sidebar deal type checkboxes
        document.querySelectorAll('.filter-section-deal-types .filter-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', handleDealTypeChange);
        });
        
        // Price range inputs
        const priceMin = document.getElementById('priceRangeMin');
        const priceMax = document.getElementById('priceRangeMax');
        if (priceMin && priceMax) {
            priceMin.addEventListener('input', debounce(handlePriceChange, config.debounceDelay));
            priceMax.addEventListener('input', debounce(handlePriceChange, config.debounceDelay));
        }
        
        // Discount select
        const discountSelect = document.getElementById('discountMin');
        if (discountSelect) {
            discountSelect.addEventListener('change', handleDiscountChange);
        }
        
        // Status select
        const statusSelect = document.getElementById('statusFilter');
        if (statusSelect) {
            statusSelect.addEventListener('change', handleStatusChange);
        }
        
        // Reset button
        const resetBtn = document.getElementById('filterResetBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', resetAllFilters);
        }
    }
    
    /**
     * Initialize filter section collapse/expand
     * Moved from inline script in filter-sidebar.php
     */
    function initFilterSections() {
        document.querySelectorAll('.filter-section-header').forEach(header => {
            header.addEventListener('click', function() {
                const section = this.parentElement;
                const content = section.querySelector('.filter-section-content');
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                this.setAttribute('aria-expanded', !isExpanded);
                
                if (content) {
                    content.style.display = isExpanded ? 'none' : 'block';
                }
                
                section.classList.toggle('active', !isExpanded);
            });
        });
    }
    
    /**
     * Initialize mobile filter toggle
     * NEW FUNCTIONALITY - Added for mobile UX
     */
    function initMobileToggle() {
        const mobileToggle = document.querySelector(config.mobileToggleSelector);
        const sidebar = document.querySelector(config.filtersSelector);
        
        if (!mobileToggle || !sidebar) return;
        
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
            document.body.classList.toggle('filter-sidebar-open');
            
            // Update button text
            const isOpen = sidebar.classList.contains('mobile-open');
            this.textContent = isOpen ? 'Close Filters' : 'Filters';
        });
        
        // Close sidebar when clicking outside (mobile only)
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnToggle = mobileToggle.contains(event.target);
                
                if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    document.body.classList.remove('filter-sidebar-open');
                }
            }
        });
    }
    
    // =====================================================
    // EVENT HANDLERS
    // =====================================================
    
    /**
     * Handle horizontal chip click (stores, categories)
     */
    function handleChipClick(event) {
        event.preventDefault();
        const chip = event.currentTarget;
        const filterType = chip.dataset.filterType;
        const filterValue = chip.dataset.filterValue;
        
        // Toggle active state
        chip.classList.toggle('active');
        
        // Update active filters
        if (filterType === 'store') {
            toggleArrayValue(activeFilters.stores, filterValue);
        } else if (filterType === 'category') {
            toggleArrayValue(activeFilters.categories, filterValue);
        }
        
        // Trigger filter update
        updateDeals();
    }
    
    /**
     * Handle sort radio change
     */
    function handleSortChange(event) {
        activeFilters.sortBy = event.target.value;
        updateDeals();
    }
    
    /**
     * Handle deal type checkbox change
     */
    function handleDealTypeChange(event) {
        const value = event.target.value;
        toggleArrayValue(activeFilters.dealTypes, value);
        updateDeals();
    }
    
    /**
     * Handle price range change
     */
    function handlePriceChange() {
        const priceMin = document.getElementById('priceRangeMin');
        const priceMax = document.getElementById('priceRangeMax');
        
        activeFilters.priceMin = priceMin ? priceMin.value : '';
        activeFilters.priceMax = priceMax ? priceMax.value : '';
        
        updateDeals();
    }
    
    /**
     * Handle discount select change
     */
    function handleDiscountChange(event) {
        activeFilters.discountMin = event.target.value;
        updateDeals();
    }
    
    /**
     * Handle status select change
     */
    function handleStatusChange(event) {
        activeFilters.status = event.target.value;
        updateDeals();
    }
    
    /**
     * Reset all filters
     */
    function resetAllFilters() {
        // Reset state (preserve context)
        activeFilters = {
            stores: [],
            categories: [],
            dealTypes: [],
            sortBy: 'latest',
            priceMin: '',
            priceMax: '',
            discountMin: '0',
            status: '',
            context: activeFilters.context
        };
        
        // Reset UI - chips
        document.querySelectorAll('.filter-chip.active').forEach(chip => {
            chip.classList.remove('active');
        });
        
        // Reset UI - radio buttons
        document.querySelectorAll('.filter-radio[name="sortby"]').forEach(radio => {
            radio.checked = radio.value === 'latest';
        });
        
        // Reset UI - checkboxes
        document.querySelectorAll('.filter-checkbox:checked').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Reset UI - inputs
        const priceMin = document.getElementById('priceRangeMin');
        const priceMax = document.getElementById('priceRangeMax');
        const discountSelect = document.getElementById('discountMin');
        const statusSelect = document.getElementById('statusFilter');
        
        if (priceMin) priceMin.value = '';
        if (priceMax) priceMax.value = '';
        if (discountSelect) discountSelect.value = '0';
        if (statusSelect) statusSelect.value = '';
        
        // Update deals
        updateDeals();
    }
    
    // =====================================================
    // AJAX FUNCTIONALITY
    // =====================================================
    
    /**
     * Send AJAX request to update deals
     */
    function updateDeals() {
        const container = document.querySelector(config.containerSelector);
        if (!container) {
            console.error('ARRZONE Filter Error: Deals container not found');
            return;
        }
        
        // Show loading state
        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';
        container.classList.add('loading');
        
        // Prepare data
        const data = new FormData();
        data.append('action', 'filter_deals');
        data.append('nonce', config.nonce);
        data.append('filters', JSON.stringify(activeFilters));
        
        // Send request
        fetch(config.ajaxUrl, {
            method: 'POST',
            body: data
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                container.innerHTML = result.data.html;
                updateDealCount(result.data.count);
            } else {
                console.error('Filter error:', result.data.message);
                showError(dealsindiaFilter.strings.ajaxError || 'Failed to load deals. Please try again.');
            }
        })
        .catch(error => {
            console.error('AJAX error:', error);
            showError('Network error. Please check your connection.');
        })
        .finally(() => {
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            container.classList.remove('loading');
        });
    }
    
    /**
     * Update deal count display
     */
    function updateDealCount(count) {
        const countElement = document.querySelector('.archive-count');
        if (countElement) {
            const dealText = count === 1 
                ? (dealsindiaFilter.strings.dealSingular || 'deal found')
                : (dealsindiaFilter.strings.dealPlural || 'deals found');
            countElement.textContent = `${count.toLocaleString()} ${dealText}`;
        }
    }
    
    /**
     * Show error message
     */
    function showError(message) {
        const container = document.querySelector(config.containerSelector);
        if (container) {
            container.innerHTML = `
                <div class="filter-error-message">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        <line x1="12" y1="8" x2="12" y2="12" stroke-width="2"/>
                        <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2"/>
                    </svg>
                    <p>${message}</p>
                    <button onclick="location.reload()" class="btn btn-primary">Reload Page</button>
                </div>
            `;
        }
    }
    
    // =====================================================
    // UTILITY FUNCTIONS
    // =====================================================
    
    /**
     * Toggle value in array
     */
    function toggleArrayValue(array, value) {
        const index = array.indexOf(value);
        if (index > -1) {
            array.splice(index, 1);
        } else {
            array.push(value);
        }
    }
    
    /**
     * Debounce function for performance
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // =====================================================
    // EXPOSE PUBLIC API
    // =====================================================
    window.triggerFilterUpdate = updateDeals;
    
})();
