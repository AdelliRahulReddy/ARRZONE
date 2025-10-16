/**
 * Admin Media Uploader - Enhanced for All Taxonomies
 * Handles media uploads for:
 * - Stores (logo, banner)
 * - Categories (icon image, banner)
 * - Deal Types (icon image, banner)
 * 
 * @package ARRZONE
 * @version 4.0 - Enhanced
 */

(function($) {
    'use strict';

    // Check if WordPress media library is loaded
    if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
        console.error('WordPress media library not loaded');
        return;
    }

    // =====================================================
    // SECTION 1: STORE TAXONOMY UPLOADERS
    // =====================================================

    /**
     * Initialize Store Logo Uploader
     */
    function initStoreLogoUpload() {
        let storeLogoFrame;
        
        const uploadButton = $('#store_logo_upload, .store-logo-upload-btn');
        const removeButton = $('.store-logo-remove-btn');
        const logoInput = $('#store_logo_id');
        const logoPreview = $('.store-logo-preview');

        if (uploadButton.length === 0) return;

        // Upload button click
        uploadButton.on('click', function(e) {
            e.preventDefault();

            // If media frame already exists, reopen it
            if (storeLogoFrame) {
                storeLogoFrame.open();
                return;
            }

            // Create new media frame
            storeLogoFrame = wp.media({
                title: 'Select Store Logo',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            // When image is selected
            storeLogoFrame.on('select', function() {
                const attachment = storeLogoFrame.state().get('selection').first().toJSON();
                
                // Set logo ID in hidden input
                logoInput.val(attachment.id);
                
                // Show preview
                logoPreview.html('<img src="' + attachment.url + '" style="max-width: 150px; height: auto; border: 2px solid #ddd; border-radius: 8px; padding: 5px;">');
                
                // Show remove button
                removeButton.show();
            });

            // Open media frame
            storeLogoFrame.open();
        });

        // Remove button click
        removeButton.on('click', function(e) {
            e.preventDefault();
            
            // Clear input and preview
            logoInput.val('');
            logoPreview.html('');
            
            // Hide remove button
            $(this).hide();
        });

        // Show remove button if logo exists on page load
        if (logoInput.val()) {
            removeButton.show();
        }
    }

    /**
     * Initialize Store Banner Uploader
     */
    function initStoreBannerUpload() {
        let storeBannerFrame;
        
        const uploadButton = $('#store_banner_upload, .store-banner-upload-btn');
        const removeButton = $('.store-banner-remove-btn');
        const bannerInput = $('#store_banner_id');
        const bannerPreview = $('.store-banner-preview');

        if (uploadButton.length === 0) return;

        // Upload button click
        uploadButton.on('click', function(e) {
            e.preventDefault();

            if (storeBannerFrame) {
                storeBannerFrame.open();
                return;
            }

            storeBannerFrame = wp.media({
                title: 'Select Store Banner',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            storeBannerFrame.on('select', function() {
                const attachment = storeBannerFrame.state().get('selection').first().toJSON();
                
                bannerInput.val(attachment.id);
                bannerPreview.html('<img src="' + attachment.url + '" style="max-width: 400px; height: auto; border: 2px solid #ddd; border-radius: 8px;">');
                removeButton.show();
            });

            storeBannerFrame.open();
        });

        removeButton.on('click', function(e) {
            e.preventDefault();
            bannerInput.val('');
            bannerPreview.html('');
            $(this).hide();
        });

        if (bannerInput.val()) {
            removeButton.show();
        }
    }

    // =====================================================
    // SECTION 2: CATEGORY TAXONOMY UPLOADERS
    // =====================================================

    /**
     * Initialize Category Icon Image Uploader
     */
    function initCategoryIconImageUpload() {
        let categoryIconFrame;
        
        const uploadButton = $('.category-icon-upload-btn');
        const removeButton = $('.category-icon-remove-btn');
        const imageInput = $('#category_icon_image_id');
        const imagePreview = $('.category-icon-preview');

        if (uploadButton.length === 0) return;

        uploadButton.on('click', function(e) {
            e.preventDefault();

            if (categoryIconFrame) {
                categoryIconFrame.open();
                return;
            }

            categoryIconFrame = wp.media({
                title: 'Select Category Icon Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            categoryIconFrame.on('select', function() {
                const attachment = categoryIconFrame.state().get('selection').first().toJSON();
                
                imageInput.val(attachment.id);
                imagePreview.html('<img src="' + attachment.url + '" style="width: 100px; height: 100px; object-fit: contain; border: 2px solid #ddd; border-radius: 8px; padding: 5px;">');
                removeButton.show();
            });

            categoryIconFrame.open();
        });

        removeButton.on('click', function(e) {
            e.preventDefault();
            imageInput.val('');
            imagePreview.html('');
            $(this).hide();
        });

        if (imageInput.val()) {
            removeButton.show();
        }
    }

    /**
     * Initialize Category Banner Uploader
     */
    function initCategoryBannerUpload() {
        let categoryBannerFrame;
        
        const uploadButton = $('.category-banner-upload-btn');
        const removeButton = $('.category-banner-remove-btn');
        const bannerInput = $('#category_banner_id');
        const bannerPreview = $('.category-banner-preview');

        if (uploadButton.length === 0) return;

        uploadButton.on('click', function(e) {
            e.preventDefault();

            if (categoryBannerFrame) {
                categoryBannerFrame.open();
                return;
            }

            categoryBannerFrame = wp.media({
                title: 'Select Category Banner',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            categoryBannerFrame.on('select', function() {
                const attachment = categoryBannerFrame.state().get('selection').first().toJSON();
                
                bannerInput.val(attachment.id);
                bannerPreview.html('<img src="' + attachment.url + '" style="max-width: 400px; height: auto; border: 2px solid #ddd; border-radius: 8px;">');
                removeButton.show();
            });

            categoryBannerFrame.open();
        });

        removeButton.on('click', function(e) {
            e.preventDefault();
            bannerInput.val('');
            bannerPreview.html('');
            $(this).hide();
        });

        if (bannerInput.val()) {
            removeButton.show();
        }
    }

    // =====================================================
    // SECTION 3: DEAL TYPE TAXONOMY UPLOADERS
    // =====================================================

    /**
     * Initialize Deal Type Icon Image Uploader
     */
    function initDealTypeIconImageUpload() {
        let dealtypeIconFrame;
        
        const uploadButton = $('.dealtype-icon-upload-btn');
        const removeButton = $('.dealtype-icon-remove-btn');
        const imageInput = $('#dealtype_icon_image_id');
        const imagePreview = $('.dealtype-icon-preview');

        if (uploadButton.length === 0) return;

        uploadButton.on('click', function(e) {
            e.preventDefault();

            if (dealtypeIconFrame) {
                dealtypeIconFrame.open();
                return;
            }

            dealtypeIconFrame = wp.media({
                title: 'Select Deal Type Icon Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            dealtypeIconFrame.on('select', function() {
                const attachment = dealtypeIconFrame.state().get('selection').first().toJSON();
                
                imageInput.val(attachment.id);
                imagePreview.html('<img src="' + attachment.url + '" style="width: 100px; height: 100px; object-fit: contain; border: 2px solid #ddd; border-radius: 8px; padding: 5px;">');
                removeButton.show();
            });

            dealtypeIconFrame.open();
        });

        removeButton.on('click', function(e) {
            e.preventDefault();
            imageInput.val('');
            imagePreview.html('');
            $(this).hide();
        });

        if (imageInput.val()) {
            removeButton.show();
        }
    }

    /**
     * Initialize Deal Type Banner Uploader
     */
    function initDealTypeBannerUpload() {
        let dealtypeBannerFrame;
        
        const uploadButton = $('.dealtype-banner-upload-btn');
        const removeButton = $('.dealtype-banner-remove-btn');
        const bannerInput = $('#dealtype_banner_id');
        const bannerPreview = $('.dealtype-banner-preview');

        if (uploadButton.length === 0) return;

        uploadButton.on('click', function(e) {
            e.preventDefault();

            if (dealtypeBannerFrame) {
                dealtypeBannerFrame.open();
                return;
            }

            dealtypeBannerFrame = wp.media({
                title: 'Select Deal Type Banner',
                button: {
                    text: 'Use this image'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });

            dealtypeBannerFrame.on('select', function() {
                const attachment = dealtypeBannerFrame.state().get('selection').first().toJSON();
                
                bannerInput.val(attachment.id);
                bannerPreview.html('<img src="' + attachment.url + '" style="max-width: 400px; height: auto; border: 2px solid #ddd; border-radius: 8px;">');
                removeButton.show();
            });

            dealtypeBannerFrame.open();
        });

        removeButton.on('click', function(e) {
            e.preventDefault();
            bannerInput.val('');
            bannerPreview.html('');
            $(this).hide();
        });

        if (bannerInput.val()) {
            removeButton.show();
        }
    }

    // =====================================================
    // INITIALIZATION
    // =====================================================

    /**
     * Initialize all uploaders when document is ready
     */
    $(document).ready(function() {
        // Store taxonomy
        initStoreLogoUpload();
        initStoreBannerUpload();
        
        // Category taxonomy
        initCategoryIconImageUpload();
        initCategoryBannerUpload();
        
        // Deal Type taxonomy
        initDealTypeIconImageUpload();
        initDealTypeBannerUpload();
    });

})(jQuery);
