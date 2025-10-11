<?php
/**
 * Admin Columns Customization
 * Enhance deal listing in admin
 * 
 * @package DealsIndia
 * @version 1.0
 */

if (!defined('ABSPATH')) exit;

/**
 * Add custom columns to Deals admin list
 */
function dealsindia_deals_custom_columns($columns) {
    $new_columns = array();
    
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        
        // Add columns after title
        if ($key === 'title') {
            $new_columns['deal_image'] = 'Image';
            $new_columns['deal_price'] = 'Price';
            $new_columns['deal_discount'] = 'Discount';
            $new_columns['deal_expiry'] = 'Expiry';
            $new_columns['deal_stats'] = 'Views / Clicks';
        }
    }
    
    // Remove date, add it back at end
    unset($new_columns['date']);
    $new_columns['date'] = 'Published';
    
    return $new_columns;
}
add_filter('manage_deals_posts_columns', 'dealsindia_deals_custom_columns');

/**
 * Populate custom columns
 */
function dealsindia_deals_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'deal_image':
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, array(60, 60));
            } else {
                echo '<span style="color:#999;">No image</span>';
            }
            break;
            
        case 'deal_price':
            $price = get_post_meta($post_id, 'deal_price', true);
            $original = get_post_meta($post_id, 'deal_original_price', true);
            
            if ($price) {
                echo '<strong>₹' . number_format($price) . '</strong>';
                if ($original && $original > $price) {
                    echo '<br><del style="color:#999;">₹' . number_format($original) . '</del>';
                }
            } else {
                echo '<span style="color:#999;">-</span>';
            }
            break;
            
        case 'deal_discount':
            $price = get_post_meta($post_id, 'deal_price', true);
            $original = get_post_meta($post_id, 'deal_original_price', true);
            $discount = get_post_meta($post_id, 'deal_discount_percentage', true);
            
            if ($discount) {
                echo '<span style="background:#10b981;color:#fff;padding:3px 8px;border-radius:3px;font-weight:600;">' 
                     . $discount . '% OFF</span>';
            } else {
                echo '<span style="color:#999;">-</span>';
            }
            break;
            
        case 'deal_expiry':
            $expiry = get_post_meta($post_id, 'deal_expiry_date', true);
            
            if ($expiry) {
                $is_expired = strtotime($expiry) < current_time('timestamp');
                $days_left = floor((strtotime($expiry) - current_time('timestamp')) / 86400);
                
                if ($is_expired) {
                    echo '<span style="color:#ef4444;font-weight:600;">EXPIRED</span><br>';
                    echo '<small>' . date('M d, Y', strtotime($expiry)) . '</small>';
                } elseif ($days_left <= 3) {
                    echo '<span style="color:#f59e0b;font-weight:600;">⚠️ ' . $days_left . ' days</span><br>';
                    echo '<small>' . date('M d, Y', strtotime($expiry)) . '</small>';
                } else {
                    echo '<span style="color:#10b981;">' . $days_left . ' days left</span><br>';
                    echo '<small>' . date('M d, Y', strtotime($expiry)) . '</small>';
                }
            } else {
                echo '<span style="color:#999;">No expiry</span>';
            }
            break;
            
        case 'deal_stats':
            $views = get_post_meta($post_id, 'deal_views_count', true);
            $clicks = get_post_meta($post_id, 'deal_clicks_count', true);
            
            $views = $views ? intval($views) : 0;
            $clicks = $clicks ? intval($clicks) : 0;
            
            echo '<strong>👁 ' . $views . '</strong> | ';
            echo '<strong>🖱 ' . $clicks . '</strong>';
            
            if ($views > 0) {
                $ctr = round(($clicks / $views) * 100, 1);
                echo '<br><small style="color:#6b7280;">CTR: ' . $ctr . '%</small>';
            }
            break;
    }
}
add_action('manage_deals_posts_custom_column', 'dealsindia_deals_custom_column_content', 10, 2);

/**
 * Make columns sortable
 */
function dealsindia_deals_sortable_columns($columns) {
    $columns['deal_price'] = 'deal_price';
    $columns['deal_discount'] = 'deal_discount_percentage';
    $columns['deal_expiry'] = 'deal_expiry_date';
    return $columns;
}
add_filter('manage_edit-deals_sortable_columns', 'dealsindia_deals_sortable_columns');

/**
 * Handle sorting
 */
function dealsindia_deals_column_orderby($query) {
    if (!is_admin()) {
        return;
    }
    
    $orderby = $query->get('orderby');
    
    if ('deal_price' === $orderby) {
        $query->set('meta_key', 'deal_price');
        $query->set('orderby', 'meta_value_num');
    }
    
    if ('deal_discount_percentage' === $orderby) {
        $query->set('meta_key', 'deal_discount_percentage');
        $query->set('orderby', 'meta_value_num');
    }
    
    if ('deal_expiry_date' === $orderby) {
        $query->set('meta_key', 'deal_expiry_date');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'dealsindia_deals_column_orderby');
