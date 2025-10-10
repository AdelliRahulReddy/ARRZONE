jQuery(document).ready(function($) {
    
    var fileFrame;
    
    // Upload button click
    $('.category-icon-upload-btn').on('click', function(e) {
        e.preventDefault();
        
        // If media frame exists, open it
        if (fileFrame) {
            fileFrame.open();
            return;
        }
        
        // Create media frame
        fileFrame = wp.media({
            title: 'Select Category Icon',
            button: {
                text: 'Use this icon'
            },
            multiple: false
        });
        
        // When image is selected
        fileFrame.on('select', function() {
            var attachment = fileFrame.state().get('selection').first().toJSON();
            
            $('#category_icon_image_id').val(attachment.id);
            $('#category-icon-preview').attr('src', attachment.url).show();
            $('.category-icon-remove-btn').show();
        });
        
        fileFrame.open();
    });
    
    // Remove button click
    $('.category-icon-remove-btn').on('click', function(e) {
        e.preventDefault();
        
        $('#category_icon_image_id').val('');
        $('#category-icon-preview').attr('src', '').hide();
        $(this).hide();
    });
    
});
