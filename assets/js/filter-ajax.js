/**
 * Enhanced Archive AJAX Filtering System
 * Handles dynamic filtering without page reload
 * 
 * @package DealsIndia
 * @version 1.0 - Spider-Verse Enhanced Archive
 */

(function($) {
    'use strict';

    // Check if localized data exists
    if (typeof dealsindiaFilterData === 'undefined') {
        console.error('DealsIndia Filter: Localized data not found');
        return;
    }

    const FilterSystem = {
        
        // Configuration
        config: {
            ajaxUrl: dealsindiaFilterData.ajax_url,
            nonce: dealsindiaFilterData.nonce,
            contextType: dealsindiaFilterData.context_type,
            contextSlug: dealsindiaFilterData.context_slug,
            perPage: dealsindiaFilterData.per_page || 20,
            translations: dealsindiaFilterData.translations,
        },

        // State management
        state: {
            currentPage: 1,
            isLoading: false,
            totalPages: 0,
            totalDeals: 0,
            activeFilters: {},
        },

        // DOM elements cache
        elements: {
            sidebar: null,
            dealsGrid: null,
            resultsCount: null,
            loadMoreBtn: null,
            clearAllBtn: null,
            applyBtn: null,
            mobileToggleBtn: null,
            filterCloseBtn: null,
            loadingOverlay: null,
            activeFiltersContainer: null,
            activeFiltersList: null,
        },

        /**
         * Initialize the filter system
         */
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.initCollapsibleSections();
            this.initSearchBoxes();
            this.checkURLParams();
            console.log('DealsIndia Filter System initialized');
        },

        /**
         * Cache DOM elements for better performance
         */
        cacheElements: function() {
            this.elements.sidebar = $('#dealFiltersSidebar');
            this.elements.dealsGrid = $('#dealsGridContainer');
            this.elements.resultsCount = $('#dealsResultsCount');
            this.elements.loadMoreBtn = $('#loadMoreDealsBtn');
            this.elements.clearAllBtn = $('#btnClearAllFilters');
            this.elements.applyBtn = $('#btnApplyFilters');
            this.elements.mobileToggleBtn = $('#mobileFilterToggleBtn');
            this.elements.filterCloseBtn = $('#filterCloseBtn');
            this.elements.loadingOverlay = $('#filterLoadingOverlay');
            this.elements.activeFiltersContainer = $('#activeFiltersContainer');
            this.elements.activeFiltersList = $('#activeFiltersList');
        },

        /**
         * Bind all event listeners
         */
        bindEvents: function() {
            const self = this;

            // Apply filters button
            this.elements.applyBtn.on('click', function(e) {
                e.preventDefault();
                self.applyFilters();
            });

            // Clear all filters button
            this.elements.clearAllBtn.on('click', function(e) {
                e.preventDefault();
                self.clearAllFilters();
            });

            // Mobile filter toggle
            this.elements.mobileToggleBtn.on('click', function(e) {
                e.preventDefault();
                self.toggleMobileFilters();
            });

            // Filter close button (mobile)
            this.elements.filterCloseBtn.on('click', function(e) {
                e.preventDefault();
                self.closeMobileFilters();
            });

            // Load more button
            this.elements.loadMoreBtn.on('click', function(e) {
                e.preventDefault();
                self.loadMoreDeals();
            });

            // Auto-apply on radio button change
            $('.filter-radio', this.elements.sidebar).on('change', function() {
                self.applyFilters();
            });

            // Show more/less buttons
            $('.filter-show-more-btn').on('click', function(e) {
                e.preventDefault();
                self.toggleShowMore($(this));
            });

            // Section header toggle
            $('.filter-section-header').on('click', function(e) {
                e.preventDefault();
                self.toggleSection($(this));
            });

            // Remove individual active filter
            $(document).on('click', '.active-filter-remove', function(e) {
                e.preventDefault();
                const filterType = $(this).data('filter-type');
                const filterValue = $(this).data('filter-value');
                self.removeActiveFilter(filterType, filterValue);
            });

            // Clear all active filters
            $('#clearAllFiltersBtn').on('click', function(e) {
                e.preventDefault();
                self.clearAllFilters();
            });

            // Keyboard accessibility - Enter key on apply button
            this.elements.sidebar.on('keypress', function(e) {
                if (e.which === 13 && !$(e.target).is('input[type="text"]')) {
                    e.preventDefault();
                    self.applyFilters();
                }
            });
        },

        /**
         * Initialize collapsible filter sections
         */
        initCollapsibleSections: function() {
            $('.filter-section-header').each(function() {
                const $header = $(this);
                const isExpanded = $header.attr('aria-expanded') === 'true';
                const $content = $header.siblings('.filter-section-content');
                
                if (!isExpanded) {
                    $content.hide();
                }
            });
        },

        /**
         * Initialize search boxes for categories/stores
         */
        initSearchBoxes: function() {
            const self = this;

            $('.filter-search-input').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();
                const targetType = $(this).data('search-target');
                const $optionsList = $(this).siblings('.filter-options-searchable');

                $optionsList.find('.filter-option-item').each(function() {
                    const itemName = $(this).data('search-name');
                    
                    if (itemName.includes(searchTerm)) {
                        $(this).show().removeClass('filter-option-hidden');
                    } else {
                        $(this).hide();
                    }
                });

                // Hide "Show More" button during search
                if (searchTerm.length > 0) {
                    $optionsList.siblings('.filter-show-more-btn').hide();
                } else {
                    $optionsList.siblings('.filter-show-more-btn').show();
                    self.resetShowMore(targetType);
                }
            });
        },

        /**
         * Toggle filter section expand/collapse
         */
        toggleSection: function($header) {
            const isExpanded = $header.attr('aria-expanded') === 'true';
            const $content = $header.siblings('.filter-section-content');
            
            if (isExpanded) {
                $header.attr('aria-expanded', 'false');
                $content.slideUp(300);
            } else {
                $header.attr('aria-expanded', 'true');
                $content.slideDown(300);
            }
        },

        /**
         * Toggle show more/less for long lists
         */
        toggleShowMore: function($btn) {
            const targetType = $btn.data('target');
            const $section = $btn.siblings('.filter-options-searchable');
            const $hiddenItems = $section.find('.filter-option-hidden');
            const $showMoreText = $btn.find('.show-more-text');
            const $showLessText = $btn.find('.show-less-text');

            if ($hiddenItems.first().is(':visible')) {
                // Collapse
                $hiddenItems.hide();
                $showMoreText.show();
                $showLessText.hide();
                $btn.removeClass('expanded');
            } else {
                // Expand
                $hiddenItems.show();
                $showMoreText.hide();
                $showLessText.show();
                $btn.addClass('expanded');
            }
        },

        /**
         * Reset show more/less after search clear
         */
        resetShowMore: function(targetType) {
            const $section = $('[data-filter-type="' + targetType + '"]');
            const $hiddenItems = $section.find('.filter-option-hidden');
            $hiddenItems.hide();
        },

        /**
         * Collect all active filter values
         */
        collectFilters: function() {
            const filters = {
                deal_types: [],
                categories: [],
                stores: [],
                price_min: 0,
                price_max: 999999,
                discount_min: 0,
                expiry: '',
                sort_by: 'latest',
                paged: this.state.currentPage,
                per_page: this.config.perPage,
                context_type: this.config.contextType,
                context_slug: this.config.contextSlug,
            };

            // Collect deal types (checkboxes)
            $('input[name="deal_types[]"]:checked', this.elements.sidebar).each(function() {
                filters.deal_types.push($(this).val());
            });

            // Collect categories (checkboxes)
            $('input[name="categories[]"]:checked', this.elements.sidebar).each(function() {
                filters.categories.push($(this).val());
            });

            // Collect stores (checkboxes)
            $('input[name="stores[]"]:checked', this.elements.sidebar).each(function() {
                filters.stores.push($(this).val());
            });

            // Price range
            const priceMin = $('#filterPriceMin').val();
            const priceMax = $('#filterPriceMax').val();
            if (priceMin) filters.price_min = parseInt(priceMin);
            if (priceMax) filters.price_max = parseInt(priceMax);

            // Discount
            const discount = $('input[name="discount_min"]:checked').val();
            if (discount) filters.discount_min = parseInt(discount);

            // Expiry
            const expiry = $('input[name="expiry"]:checked').val();
            if (expiry) filters.expiry = expiry;

            // Sort by
            const sortBy = $('input[name="sort_by"]:checked').val();
            if (sortBy) filters.sort_by = sortBy;

            return filters;
        },

        /**
         * Apply filters and fetch deals via AJAX
         */
        applyFilters: function(isLoadMore = false) {
            if (this.state.isLoading) return;

            this.state.isLoading = true;
            this.showLoading();

            if (!isLoadMore) {
                this.state.currentPage = 1;
            }

            const filters = this.collectFilters();
            this.state.activeFilters = filters;

            const self = this;

            $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dealsindia_filter_deals',
                    nonce: this.config.nonce,
                    ...filters
                },
                success: function(response) {
                    if (response.success) {
                        self.handleFilterSuccess(response, isLoadMore);
                    } else {
                        self.handleFilterError();
                    }
                },
                error: function() {
                    self.handleFilterError();
                },
                complete: function() {
                    self.state.isLoading = false;
                    self.hideLoading();
                    self.closeMobileFilters();
                }
            });

            // Update URL params
            this.updateURLParams(filters);
        },

        /**
         * Handle successful filter response
         */
        handleFilterSuccess: function(response, isLoadMore) {
            const $dealsHTML = $(response.html);

            if (isLoadMore) {
                // Append to existing deals
                this.elements.dealsGrid.append($dealsHTML).hide().fadeIn(400);
            } else {
                // Replace all deals
                this.elements.dealsGrid.fadeOut(200, () => {
                    this.elements.dealsGrid.html($dealsHTML).fadeIn(400);
                    $('html, body').animate({
                        scrollTop: this.elements.dealsGrid.offset().top - 100
                    }, 500);
                });
            }

            // Update state
            this.state.totalDeals = response.count;
            this.state.totalPages = response.pages;
            this.state.currentPage = response.current;

            // Update results count
            this.updateResultsCount();

            // Update load more button
            this.updateLoadMoreButton();

            // Update active filters display
            this.updateActiveFiltersDisplay();
        },

        /**
         * Handle filter error
         */
        handleFilterError: function() {
            alert(this.config.translations.error);
        },

        /**
         * Load more deals (pagination)
         */
        loadMoreDeals: function() {
            if (this.state.currentPage >= this.state.totalPages) return;
            
            this.state.currentPage++;
            this.applyFilters(true);
        },

        /**
         * Update results count display
         */
        updateResultsCount: function() {
            const currentShowing = this.elements.dealsGrid.find('.deal-card').length;
            const countText = this.config.translations.showing + ' ' + 
                              currentShowing + ' ' + 
                              this.config.translations.of + ' ' + 
                              this.state.totalDeals + ' ' + 
                              this.config.translations.deals;
            
            this.elements.resultsCount.html(countText);
        },

        /**
         * Update load more button state
         */
        updateLoadMoreButton: function() {
            if (this.state.currentPage >= this.state.totalPages) {
                this.elements.loadMoreBtn.hide();
            } else {
                this.elements.loadMoreBtn.show();
                const remaining = this.state.totalDeals - this.elements.dealsGrid.find('.deal-card').length;
                this.elements.loadMoreBtn.html(
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="6 9 12 15 18 9" stroke-width="2"/></svg> ' +
                    this.config.translations.load_more + ' (' + remaining + ')'
                );
            }
        },

        /**
         * Update active filters display
         */
        updateActiveFiltersDisplay: function() {
            const filters = this.state.activeFilters;
            let hasActiveFilters = false;
            let filtersHTML = '';

            // Deal types
            if (filters.deal_types && filters.deal_types.length > 0) {
                filters.deal_types.forEach(slug => {
                    const $checkbox = $('input[name="deal_types[]"][value="' + slug + '"]');
                    const label = $checkbox.siblings('.filter-option-label').text().trim();
                    filtersHTML += this.createActiveFilterBadge('deal_type', slug, label);
                    hasActiveFilters = true;
                });
            }

            // Categories
            if (filters.categories && filters.categories.length > 0) {
                filters.categories.forEach(slug => {
                    const $checkbox = $('input[name="categories[]"][value="' + slug + '"]');
                    const label = $checkbox.siblings('.filter-option-label').text().trim();
                    filtersHTML += this.createActiveFilterBadge('category', slug, label);
                    hasActiveFilters = true;
                });
            }

            // Stores
            if (filters.stores && filters.stores.length > 0) {
                filters.stores.forEach(slug => {
                    const $checkbox = $('input[name="stores[]"][value="' + slug + '"]');
                    const label = $checkbox.siblings('.filter-option-label').text().trim();
                    filtersHTML += this.createActiveFilterBadge('store', slug, label);
                    hasActiveFilters = true;
                });
            }

            // Price range
            if (filters.price_min > 0 || filters.price_max < 999999) {
                const priceLabel = '₹' + filters.price_min + ' - ₹' + filters.price_max;
                filtersHTML += this.createActiveFilterBadge('price', 'range', priceLabel);
                hasActiveFilters = true;
            }

            // Discount
            if (filters.discount_min > 0) {
                const discountLabel = filters.discount_min + '%+';
                filtersHTML += this.createActiveFilterBadge('discount', filters.discount_min, discountLabel);
                hasActiveFilters = true;
            }

            // Expiry
            if (filters.expiry) {
                const $radio = $('input[name="expiry"][value="' + filters.expiry + '"]');
                const label = $radio.siblings('.filter-option-label').text().trim();
                filtersHTML += this.createActiveFilterBadge('expiry', filters.expiry, label);
                hasActiveFilters = true;
            }

            // Update display
            if (hasActiveFilters) {
                this.elements.activeFiltersList.html(filtersHTML);
                this.elements.activeFiltersContainer.slideDown(300);
            } else {
                this.elements.activeFiltersContainer.slideUp(300);
            }
        },

        /**
         * Create active filter badge HTML
         */
        createActiveFilterBadge: function(type, value, label) {
            return '<span class="active-filter-badge">' +
                   '<span class="active-filter-label">' + label + '</span>' +
                   '<button class="active-filter-remove" data-filter-type="' + type + '" data-filter-value="' + value + '">' +
                   '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor">' +
                   '<line x1="18" y1="6" x2="6" y2="18" stroke-width="2"/>' +
                   '<line x1="6" y1="6" x2="18" y2="18" stroke-width="2"/>' +
                   '</svg>' +
                   '</button>' +
                   '</span>';
        },

        /**
         * Remove individual active filter
         */
        removeActiveFilter: function(type, value) {
            switch(type) {
                case 'deal_type':
                    $('input[name="deal_types[]"][value="' + value + '"]').prop('checked', false);
                    break;
                case 'category':
                    $('input[name="categories[]"][value="' + value + '"]').prop('checked', false);
                    break;
                case 'store':
                    $('input[name="stores[]"][value="' + value + '"]').prop('checked', false);
                    break;
                case 'price':
                    $('#filterPriceMin').val('');
                    $('#filterPriceMax').val('');
                    break;
                case 'discount':
                    $('input[name="discount_min"]').prop('checked', false);
                    break;
                case 'expiry':
                    $('input[name="expiry"]').prop('checked', false);
                    break;
            }

            this.applyFilters();
        },

        /**
         * Clear all filters
         */
        clearAllFilters: function() {
            // Uncheck all checkboxes
            $('input[type="checkbox"]', this.elements.sidebar).prop('checked', false);
            
            // Uncheck all radio buttons except default sort
            $('input[type="radio"]', this.elements.sidebar).prop('checked', false);
            $('input[name="sort_by"][value="latest"]').prop('checked', true);
            
            // Clear price inputs
            $('#filterPriceMin, #filterPriceMax').val('');
            
            // Clear search boxes
            $('.filter-search-input').val('');
            
            // Reset show more/less
            $('.filter-option-hidden').hide();
            $('.filter-show-more-btn').removeClass('expanded')
                .find('.show-more-text').show().end()
                .find('.show-less-text').hide();

            // Apply filters (will show all deals)
            this.applyFilters();
        },

        /**
         * Update URL params for sharing/bookmarking
         */
        updateURLParams: function(filters) {
            const params = new URLSearchParams();

            if (filters.deal_types.length > 0) params.set('deal_types', filters.deal_types.join(','));
            if (filters.categories.length > 0) params.set('categories', filters.categories.join(','));
            if (filters.stores.length > 0) params.set('stores', filters.stores.join(','));
            if (filters.price_min > 0) params.set('price_min', filters.price_min);
            if (filters.price_max < 999999) params.set('price_max', filters.price_max);
            if (filters.discount_min > 0) params.set('discount_min', filters.discount_min);
            if (filters.expiry) params.set('expiry', filters.expiry);
            if (filters.sort_by !== 'latest') params.set('sort_by', filters.sort_by);

            const newURL = params.toString() ? '?' + params.toString() : window.location.pathname;
            window.history.replaceState({}, '', newURL);
        },

        /**
         * Check URL params on page load and apply filters
         */
        checkURLParams: function() {
            const params = new URLSearchParams(window.location.search);
            let hasParams = false;

            // Deal types
            if (params.has('deal_types')) {
                params.get('deal_types').split(',').forEach(slug => {
                    $('input[name="deal_types[]"][value="' + slug + '"]').prop('checked', true);
                    hasParams = true;
                });
            }

            // Categories
            if (params.has('categories')) {
                params.get('categories').split(',').forEach(slug => {
                    $('input[name="categories[]"][value="' + slug + '"]').prop('checked', true);
                    hasParams = true;
                });
            }

            // Stores
            if (params.has('stores')) {
                params.get('stores').split(',').forEach(slug => {
                    $('input[name="stores[]"][value="' + slug + '"]').prop('checked', true);
                    hasParams = true;
                });
            }

            // Price range
            if (params.has('price_min')) {
                $('#filterPriceMin').val(params.get('price_min'));
                hasParams = true;
            }
            if (params.has('price_max')) {
                $('#filterPriceMax').val(params.get('price_max'));
                hasParams = true;
            }

            // Discount
            if (params.has('discount_min')) {
                $('input[name="discount_min"][value="' + params.get('discount_min') + '"]').prop('checked', true);
                hasParams = true;
            }

            // Expiry
            if (params.has('expiry')) {
                $('input[name="expiry"][value="' + params.get('expiry') + '"]').prop('checked', true);
                hasParams = true;
            }

            // Sort by
            if (params.has('sort_by')) {
                $('input[name="sort_by"][value="' + params.get('sort_by') + '"]').prop('checked', true);
                hasParams = true;
            }

            // Auto-apply if params exist
            if (hasParams) {
                this.applyFilters();
            }
        },

        /**
         * Toggle mobile filters drawer
         */
        toggleMobileFilters: function() {
            this.elements.sidebar.toggleClass('mobile-active');
            $('body').toggleClass('filter-sidebar-open');
        },

        /**
         * Close mobile filters drawer
         */
        closeMobileFilters: function() {
            this.elements.sidebar.removeClass('mobile-active');
            $('body').removeClass('filter-sidebar-open');
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            this.elements.loadingOverlay.fadeIn(200);
            this.elements.applyBtn.prop('disabled', true);
        },

        /**
         * Hide loading state
         */
        hideLoading: function() {
            this.elements.loadingOverlay.fadeOut(200);
            this.elements.applyBtn.prop('disabled', false);
        },

    };

    // Initialize on document ready
    $(document).ready(function() {
        FilterSystem.init();
    });

})(jQuery);
