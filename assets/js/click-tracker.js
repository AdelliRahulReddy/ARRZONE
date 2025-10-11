/**
 * Deal Click Tracking
 * 
 * @package DealsIndia
 */

(function($) {
    'use strict';
    
    const ClickTracker = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Track clicks on deal buttons
            $('body').on('click', '.cd-get-deal, .btn-deal-cd, a[data-track-deal]', function(e) {
                const dealId = $(this).data('deal-id') || $(this).closest('.cd-deal-card, .deal-card').data('deal-id');
                
                if (dealId) {
                    ClickTracker.trackClick(dealId);
                }
            });
        },
        
        trackClick: function(dealId) {
            $.ajax({
                url: dealsindia_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'track_deal_click',
                    nonce: dealsindia_ajax.nonce,
                    deal_id: dealId
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Click tracked for deal #' + dealId);
                        
                        // Update click counter if exists on page
                        const counter = $('.deal-clicks-' + dealId);
                        if (counter.length && response.data.clicks) {
                            counter.text(response.data.clicks);
                        }
                    }
                },
                error: function() {
                    console.log('Failed to track click');
                }
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        ClickTracker.init();
    });
    
})(jQuery);
