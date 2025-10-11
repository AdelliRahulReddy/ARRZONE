/**
 * Deal Filtering & Search JavaScript
 * 
 * @package DealsIndia
 */

(function($) {
    'use strict';
    
    const DealFilters = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            $('#applyFilters').on('click', this.applyFilters);
            $('#resetFilters').on('click', this.resetFilters);
            
            // Apply filters on Enter key in price inputs
            $('#filterMinPrice, #filterMaxPrice').on('keypress', function(e) {
                if (e.which === 13) {
                    DealFilters.applyFilters();
                }
            });
        },
        
        applyFilters: function() {
            const category = $('#filterCategory').val();
            const store = $('#filterStore').val();
            const sort = $('#filterSort').val();
            const minPrice = $('#filterMinPrice').val() || 0;
            const maxPrice = $('#filterMaxPrice').val() || 999999;
            
            // Show loader
            $('#filterLoader').show();
            $('.deals-grid').addClass('loading');
            
            $.ajax({
                url: dealsindia_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'filter_deals',
                    nonce: dealsindia_ajax.nonce,
                    category: category,
                    store: store,
                    sort: sort,
                    min_price: minPrice,
                    max_price: maxPrice
                },
                success: function(response) {
                    if (response.success) {
                        $('.deals-grid').html(response.data.html);
                        $('.results-count').text(response.data.count + ' deals found');
                    }
                },
                error: function() {
                    alert('Failed to load deals. Please try again.');
                },
                complete: function() {
                    $('#filterLoader').hide();
                    $('.deals-grid').removeClass('loading');
                }
            });
        },
        
        resetFilters: function() {
            $('#filterCategory').val('');
            $('#filterStore').val('');
            $('#filterSort').val('latest');
            $('#filterMinPrice').val('');
            $('#filterMaxPrice').val('');
            
            DealFilters.applyFilters();
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        if ($('#dealFilters').length) {
            DealFilters.init();
        }
    });
    
})(jQuery);
