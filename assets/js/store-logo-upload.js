jQuery(document).ready(function($) {
    
    // Upload button click
    $(document).on('click', '.upload-store-logo-btn', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var preview = button.siblings('.store-logo-preview');
        var input = button.siblings('#store_logo_id');
        var removeBtn = button.siblings('.remove-store-logo-btn');
        
        var mediaUploader = wp.media({
            title: 'Choose Store Logo',
            button: {
                text: 'Use this logo'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            input.val(attachment.id);
            preview.html('<img src="' + attachment.url + '" style="max-width: 150px; max-height: 150px; display: block; border: 1px solid #ddd; padding: 5px; background: white;">');
            button.text('Change Logo');
            removeBtn.show();
        });
        
        mediaUploader.open();
    });
    
    // Remove button click
    $(document).on('click', '.remove-store-logo-btn', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var preview = button.siblings('.store-logo-preview');
        var input = button.siblings('#store_logo_id');
        var uploadBtn = button.siblings('.upload-store-logo-btn');
        
        input.val('');
        preview.html('');
        uploadBtn.text('Upload Logo');
        button.hide();
    });
    
});
