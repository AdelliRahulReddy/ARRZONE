<?php
/**
 * Taxonomy Meta Fields
 * Adds custom fields to taxonomies (Store cashback, category icons, etc.)
 */

if (!defined('ABSPATH')) exit;

/**
 * Add Store Cashback field to Store taxonomy
 */

// Add field on "Add New" form
add_action('store_add_form_fields', 'dealsindia_add_store_cashback_field');
function dealsindia_add_store_cashback_field() {
    ?>
    <div class="form-field">
        <label for="store_cashback"><?php _e('Cashback Percentage', 'dealsindia'); ?></label>
        <input type="text" name="store_cashback" id="store_cashback" placeholder="e.g., 5%" value="">
        <p class="description"><?php _e('Enter cashback percentage (e.g., 5% or Upto 10%)', 'dealsindia'); ?></p>
    </div>
    <?php
}

// Add field on "Edit" form
add_action('store_edit_form_fields', 'dealsindia_edit_store_cashback_field');
function dealsindia_edit_store_cashback_field($term) {
    $cashback = get_term_meta($term->term_id, 'store_cashback', true);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="store_cashback"><?php _e('Cashback Percentage', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" name="store_cashback" id="store_cashback" value="<?php echo esc_attr($cashback); ?>" placeholder="e.g., 5%">
            <p class="description"><?php _e('Enter cashback percentage (e.g., 5% or Upto 10%)', 'dealsindia'); ?></p>
        </td>
    </tr>
    <?php
}

// Save store cashback field
add_action('created_store', 'dealsindia_save_store_cashback_field');
add_action('edited_store', 'dealsindia_save_store_cashback_field');
function dealsindia_save_store_cashback_field($term_id) {
    if (isset($_POST['store_cashback'])) {
        update_term_meta($term_id, 'store_cashback', sanitize_text_field($_POST['store_cashback']));
    }
}


/**
 * Add Category Icon field to Deal Category taxonomy
 */

// Add field on "Add New" form
add_action('deal_category_add_form_fields', 'dealsindia_add_category_icon_field');
function dealsindia_add_category_icon_field() {
    ?>
    <div class="form-field">
        <label for="category_icon"><?php _e('Category Icon (Emoji)', 'dealsindia'); ?></label>
        <input type="text" name="category_icon" id="category_icon" placeholder="🏷️" value="">
        <p class="description"><?php _e('Enter an emoji icon for this category', 'dealsindia'); ?></p>
    </div>
    <?php
}

// Add field on "Edit" form
add_action('deal_category_edit_form_fields', 'dealsindia_edit_category_icon_field');
function dealsindia_edit_category_icon_field($term) {
    $icon = get_term_meta($term->term_id, 'category_icon', true);
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="category_icon"><?php _e('Category Icon (Emoji)', 'dealsindia'); ?></label>
        </th>
        <td>
            <input type="text" name="category_icon" id="category_icon" value="<?php echo esc_attr($icon); ?>" placeholder="🏷️">
            <p class="description"><?php _e('Enter an emoji icon for this category', 'dealsindia'); ?></p>
        </td>
    </tr>
    <?php
}

// Save category icon field
add_action('created_deal_category', 'dealsindia_save_category_icon_field');
add_action('edited_deal_category', 'dealsindia_save_category_icon_field');
function dealsindia_save_category_icon_field($term_id) {
    if (isset($_POST['category_icon'])) {
        update_term_meta($term_id, 'category_icon', sanitize_text_field($_POST['category_icon']));
    }
}


/**
 * Display term meta in admin columns
 */

// Add column to Store taxonomy
add_filter('manage_edit-store_columns', 'dealsindia_store_columns');
function dealsindia_store_columns($columns) {
    $columns['cashback'] = __('Cashback', 'dealsindia');
    return $columns;
}

add_filter('manage_store_custom_column', 'dealsindia_store_column_content', 10, 3);
function dealsindia_store_column_content($content, $column_name, $term_id) {
    if ($column_name === 'cashback') {
        $cashback = get_term_meta($term_id, 'store_cashback', true);
        $content = $cashback ? esc_html($cashback) : '—';
    }
    return $content;
}

// Add column to Deal Category taxonomy
add_filter('manage_edit-deal_category_columns', 'dealsindia_category_columns');
function dealsindia_category_columns($columns) {
    $columns['icon'] = __('Icon', 'dealsindia');
    return $columns;
}

add_filter('manage_deal_category_custom_column', 'dealsindia_category_column_content', 10, 3);
function dealsindia_category_column_content($content, $column_name, $term_id) {
    if ($column_name === 'icon') {
        $icon = get_term_meta($term_id, 'category_icon', true);
        $content = $icon ? esc_html($icon) : '—';
    }
    return $content;
}
