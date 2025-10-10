<?php
/**
 * Store Logo & Banner Upload
 * Add logo and banner image upload to store taxonomy
 */

if (!defined('ABSPATH')) exit;

/**
 * Add store logo field to Add New form
 */
add_action('store_add_form_fields', 'dealsindia_add_store_logo_field');
function dealsindia_add_store_logo_field() {
    ?>
    <div class="form-field">
        <label><?php _e('Store Logo', 'dealsindia'); ?></label>
        <div class="store-logo-upload-wrapper">
            <img id="store-logo-preview" src="" style="max-width: 100px; max-height: 100px; display: none; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;">
            <br>
            <input type="hidden" id="store_logo_id" name="store_logo_id" value="">
            <button type="button" class="button store-logo-upload-btn"><?php _e('Upload Logo', 'dealsindia'); ?></button>
            <button type="button" class="button store-logo-remove-btn" style="display: none;"><?php _e('Remove', 'dealsindia'); ?></button>
        </div>
        <p class="description"><?php _e('Upload store logo (recommended: 200x200px PNG)', 'dealsindia'); ?></p>
    </div>
    
    <div class="form-field">
        <label><?php _e('Store Banner Image', 'dealsindia'); ?></label>
        <div class="store-banner-upload-wrapper">
            <img id="store-banner-preview" src="" style="max-width: 300px; max-height: 150px; display: none; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;">
            <br>
            <input type="hidden" id="store_banner_id" name="store_banner_id" value="">
            <button type="button" class="button store-banner-upload-btn"><?php _e('Upload Banner', 'dealsindia'); ?></button>
            <button type="button" class="button store-banner-remove-btn" style="display: none;"><?php _e('Remove', 'dealsindia'); ?></button>
        </div>
        <p class="description"><?php _e('Upload store hero banner (recommended: 1200x400px)', 'dealsindia'); ?></p>
    </div>
    
    <script>
    jQuery(document).ready(function($){
        // Logo Upload
        var logoFrame;
        $('.store-logo-upload-btn').on('click', function(e){
            e.preventDefault();
            if(logoFrame){logoFrame.open();return;}
            logoFrame = wp.media({title:'Select Store Logo',button:{text:'Use this logo'},multiple:false});
            logoFrame.on('select',function(){
                var attachment = logoFrame.state().get('selection').first().toJSON();
                $('#store_logo_id').val(attachment.id);
                $('#store-logo-preview').attr('src',attachment.url).show();
                $('.store-logo-remove-btn').show();
            });
            logoFrame.open();
        });
        $('.store-logo-remove-btn').on('click',function(e){
            e.preventDefault();
            $('#store_logo_id').val('');
            $('#store-logo-preview').hide();
            $(this).hide();
        });
        
        // Banner Upload
        var bannerFrame;
        $('.store-banner-upload-btn').on('click', function(e){
            e.preventDefault();
            if(bannerFrame){bannerFrame.open();return;}
            bannerFrame = wp.media({title:'Select Store Banner',button:{text:'Use this banner'},multiple:false});
            bannerFrame.on('select',function(){
                var attachment = bannerFrame.state().get('selection').first().toJSON();
                $('#store_banner_id').val(attachment.id);
                $('#store-banner-preview').attr('src',attachment.url).show();
                $('.store-banner-remove-btn').show();
            });
            bannerFrame.open();
        });
        $('.store-banner-remove-btn').on('click',function(e){
            e.preventDefault();
            $('#store_banner_id').val('');
            $('#store-banner-preview').hide();
            $(this).hide();
        });
    });
    </script>
    <?php
}

/**
 * Add store logo and banner fields to Edit form
 */
add_action('store_edit_form_fields', 'dealsindia_edit_store_logo_field');
function dealsindia_edit_store_logo_field($term) {
    $logo_id = get_term_meta($term->term_id, 'store_logo_id', true);
    $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
    
    $banner_id = get_term_meta($term->term_id, 'store_banner_id', true);
    $banner_url = $banner_id ? wp_get_attachment_url($banner_id) : '';
    ?>
    <tr class="form-field">
        <th scope="row"><label><?php _e('Store Logo', 'dealsindia'); ?></label></th>
        <td>
            <img id="store-logo-preview" src="<?php echo esc_url($logo_url); ?>" style="max-width: 100px; max-height: 100px; display: <?php echo $logo_url ? 'block' : 'none'; ?>; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;">
            <br>
            <input type="hidden" id="store_logo_id" name="store_logo_id" value="<?php echo esc_attr($logo_id); ?>">
            <button type="button" class="button store-logo-upload-btn"><?php _e('Upload Logo', 'dealsindia'); ?></button>
            <button type="button" class="button store-logo-remove-btn" style="display: <?php echo $logo_url ? 'inline-block' : 'none'; ?>;"><?php _e('Remove', 'dealsindia'); ?></button>
            <p class="description"><?php _e('Upload store logo (200x200px PNG)', 'dealsindia'); ?></p>
        </td>
    </tr>
    
    <tr class="form-field">
        <th scope="row"><label><?php _e('Store Banner Image', 'dealsindia'); ?></label></th>
        <td>
            <img id="store-banner-preview" src="<?php echo esc_url($banner_url); ?>" style="max-width: 400px; max-height: 200px; display: <?php echo $banner_url ? 'block' : 'none'; ?>; margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;">
            <br>
            <input type="hidden" id="store_banner_id" name="store_banner_id" value="<?php echo esc_attr($banner_id); ?>">
            <button type="button" class="button store-banner-upload-btn"><?php _e('Upload Banner', 'dealsindia'); ?></button>
            <button type="button" class="button store-banner-remove-btn" style="display: <?php echo $banner_url ? 'inline-block' : 'none'; ?>;"><?php _e('Remove', 'dealsindia'); ?></button>
            <p class="description"><?php _e('Upload store hero banner (1200x400px recommended)', 'dealsindia'); ?></p>
        </td>
    </tr>
    
    <script>
    jQuery(document).ready(function($){
        // Logo Upload
        var logoFrame;
        $('.store-logo-upload-btn').on('click', function(e){
            e.preventDefault();
            if(logoFrame){logoFrame.open();return;}
            logoFrame = wp.media({title:'Select Store Logo',button:{text:'Use this logo'},multiple:false});
            logoFrame.on('select',function(){
                var attachment = logoFrame.state().get('selection').first().toJSON();
                $('#store_logo_id').val(attachment.id);
                $('#store-logo-preview').attr('src',attachment.url).show();
                $('.store-logo-remove-btn').show();
            });
            logoFrame.open();
        });
        $('.store-logo-remove-btn').on('click',function(e){
            e.preventDefault();
            $('#store_logo_id').val('');
            $('#store-logo-preview').hide();
            $(this).hide();
        });
        
        // Banner Upload
        var bannerFrame;
        $('.store-banner-upload-btn').on('click', function(e){
            e.preventDefault();
            if(bannerFrame){bannerFrame.open();return;}
            bannerFrame = wp.media({title:'Select Store Banner',button:{text:'Use this banner'},multiple:false});
            bannerFrame.on('select',function(){
                var attachment = bannerFrame.state().get('selection').first().toJSON();
                $('#store_banner_id').val(attachment.id);
                $('#store-banner-preview').attr('src',attachment.url).show();
                $('.store-banner-remove-btn').show();
            });
            bannerFrame.open();
        });
        $('.store-banner-remove-btn').on('click',function(e){
            e.preventDefault();
            $('#store_banner_id').val('');
            $('#store-banner-preview').hide();
            $(this).hide();
        });
    });
    </script>
    <?php
}

/**
 * Save store logo and banner
 */
add_action('created_store', 'dealsindia_save_store_logo');
add_action('edited_store', 'dealsindia_save_store_logo');
function dealsindia_save_store_logo($term_id) {
    if (isset($_POST['store_logo_id'])) {
        update_term_meta($term_id, 'store_logo_id', absint($_POST['store_logo_id']));
    }
    
    if (isset($_POST['store_banner_id'])) {
        update_term_meta($term_id, 'store_banner_id', absint($_POST['store_banner_id']));
    }
}
