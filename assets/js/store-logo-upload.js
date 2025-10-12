/**
 * Store Logo & Banner Upload JavaScript
 * WordPress Media Library Integration - Fixed Version
 * 
 * @package DealsIndia
 * @version 1.1
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // =====================================================
        // STORE LOGO UPLOAD
        // =====================================================
        
        var logoFrame;
        
        // Logo Upload Button Click
        $(document).on('click', '.store-logo-upload-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            
            // If media frame already exists, reopen it
            if (logoFrame) {
                logoFrame.open();
                return;
            }
            
            // Create new media frame
            logoFrame = wp.media({
                title: 'Select Store Logo',
                button: {
                    text: 'Use this logo'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            // When image is selected
            logoFrame.on('select', function() {
                var attachment = logoFrame.state().get('selection').first().toJSON();
                
                // Update hidden input
                $('#store_logo_id').val(attachment.id);
                
                // Update button text
                $button.text('Change Logo');
                
                // Find or create preview container
                var $preview = $button.siblings('.store-logo-preview');
                if ($preview.length === 0) {
                    $preview = $('<div class="store-logo-preview"></div>').insertAfter($button);
                }
                
                // Update preview image
                $preview.html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px; padding: 5px;">');
                
                // Show remove button
                var $removeBtn = $button.siblings('.store-logo-remove-btn');
                if ($removeBtn.length === 0) {
                    $removeBtn = $('<button type="button" class="button store-logo-remove-btn" style="margin-left: 10px;">Remove Logo</button>').insertAfter($button);
                }
                $removeBtn.show();
                
                console.log('Logo uploaded:', attachment.url);
            });
            
            // Open media frame
            logoFrame.open();
        });
        
        // Logo Remove Button Click
        $(document).on('click', '.store-logo-remove-btn', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove this logo?')) {
                var $button = $(this);
                var $uploadBtn = $button.siblings('.store-logo-upload-btn');
                
                $('#store_logo_id').val('');
                $button.siblings('.store-logo-preview').remove();
                $button.hide();
                $uploadBtn.text('Upload Logo');
                
                console.log('Logo removed');
            }
        });
        
        
        // =====================================================
        // STORE BANNER UPLOAD
        // =====================================================
        
        var bannerFrame;
        
        // Banner Upload Button Click
        $(document).on('click', '.store-banner-upload-btn', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            
            // If media frame already exists, reopen it
            if (bannerFrame) {
                bannerFrame.open();
                return;
            }
            
            // Create new media frame
            bannerFrame = wp.media({
                title: 'Select Store Banner',
                button: {
                    text: 'Use this banner'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            // When image is selected
            bannerFrame.on('select', function() {
                var attachment = bannerFrame.state().get('selection').first().toJSON();
                
                // Update hidden input
                $('#store_banner_id').val(attachment.id);
                
                // Update button text
                $button.text('Change Banner Image');
                
                // Find or create preview container
                var $preview = $button.siblings('.store-banner-preview');
                if ($preview.length === 0) {
                    $preview = $('<div class="store-banner-preview"></div>').insertAfter($button);
                }
                
                // Update preview image
                $preview.html('<img src="' + attachment.url + '" style="max-width: 400px; height: auto; margin-top: 10px; border: 2px solid #ddd; border-radius: 8px;">');
                
                // Show remove button
                var $removeBtn = $button.siblings('.store-banner-remove-btn');
                if ($removeBtn.length === 0) {
                    $removeBtn = $('<button type="button" class="button store-banner-remove-btn" style="margin-left: 10px;">Remove Banner</button>').insertAfter($button);
                }
                $removeBtn.show();
                
                console.log('Banner uploaded:', attachment.url);
            });
            
            // Open media frame
            bannerFrame.open();
        });
        
        // Banner Remove Button Click
        $(document).on('click', '.store-banner-remove-btn', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove this banner?')) {
                var $button = $(this);
                var $uploadBtn = $button.siblings('.store-banner-upload-btn');
                
                $('#store_banner_id').val('');
                $button.siblings('.store-banner-preview').remove();
                $button.hide();
                $uploadBtn.text('Upload Banner Image');
                
                console.log('Banner removed');
            }
        });
        
        // Debug: Log when script loads
        console.log('Store logo/banner upload script loaded successfully');
        console.log('Upload buttons found:', $('.store-logo-upload-btn').length + $('.store-banner-upload-btn').length);
    });
    
})(jQuery);
