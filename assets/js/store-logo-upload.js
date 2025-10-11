/**
 * Store Logo & Banner Upload JavaScript
 * 
 * @package DealsIndia
 */

jQuery(document).ready(function($){
    'use strict';
    
    // Logo Upload
    var logoFrame;
    $('.store-logo-upload-btn').on('click', function(e){
        e.preventDefault();
        
        if(logoFrame){
            logoFrame.open();
            return;
        }
        
        logoFrame = wp.media({
            title: 'Select Store Logo',
            button: {
                text: 'Use this logo'
            },
            multiple: false
        });
        
        logoFrame.on('select', function(){
            var attachment = logoFrame.state().get('selection').first().toJSON();
            $('#store_logo_id').val(attachment.id);
            $('#store-logo-preview').attr('src', attachment.url).show();
            $('.store-logo-remove-btn').show();
        });
        
        logoFrame.open();
    });
    
    $('.store-logo-remove-btn').on('click', function(e){
        e.preventDefault();
        $('#store_logo_id').val('');
        $('#store-logo-preview').hide();
        $(this).hide();
    });
    
    // Banner Upload
    var bannerFrame;
    $('.store-banner-upload-btn').on('click', function(e){
        e.preventDefault();
        
        if(bannerFrame){
            bannerFrame.open();
            return;
        }
        
        bannerFrame = wp.media({
            title: 'Select Store Banner',
            button: {
                text: 'Use this banner'
            },
            multiple: false
        });
        
        bannerFrame.on('select', function(){
            var attachment = bannerFrame.state().get('selection').first().toJSON();
            $('#store_banner_id').val(attachment.id);
            $('#store-banner-preview').attr('src', attachment.url).show();
            $('.store-banner-remove-btn').show();
        });
        
        bannerFrame.open();
    });
    
    $('.store-banner-remove-btn').on('click', function(e){
        e.preventDefault();
        $('#store_banner_id').val('');
        $('#store-banner-preview').hide();
        $(this).hide();
    });
});
