<?php
if (!defined('ABSPATH')) exit; 
/**
 * Template Name: All Stores Archive
 * Description: Displays all stores in a grid layout
 */

get_header();
?>

<div class="archive-page">
    
    <!-- Page Header -->
    <section class="section-header">
        <div class="container">
            <h1><?php echo esc_html('All Stores', 'arrzone'); ?></h1>
            <p><?php echo esc_html('Browse through our collection of stores and grab the best deals', 'arrzone'); ?></p>
        </div>
    </section>

    <!-- Browse Section -->
    <section class="browse-section">
        <div class="container">
            
            <?php
            // Get all stores
            $stores_args = array(
                'taxonomy'   => 'store',
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            );
            
            $all_stores = get_terms($stores_args);
            
            if (!empty($all_stores) && !is_wp_error($all_stores)):
                
                // Separate featured and regular stores
                $featured_stores = array();
                $regular_stores = array();
                
                foreach ($all_stores as $store) {
                    $is_featured = get_term_meta($store->term_id, 'store_is_featured', true);
                    if ($is_featured) {
                        $featured_stores[] = $store;
                    } else {
                        $regular_stores[] = $store;
                    }
                }
                
                // Display Featured Stores
                if (!empty($featured_stores)): ?>
                    <div class="featured-stores-section">
                        <h2 class="browse-section-title"><?php echo esc_html('Featured Stores', 'arrzone'); ?></h2>
                        <div class="browse-grid">
                            <?php foreach ($featured_stores as $store):
                                $store_logo_id = get_term_meta($store->term_id, 'store_logo_id', true);
                                $store_cashback = get_term_meta($store->term_id, 'store_cashback', true);
                                $deals_count = $store->count;
                                $store_url = get_term_link($store);
                            ?>
                                <div class="browse-card featured">
                                    <a href="<?php echo esc_url($store_url); ?>" class="browse-card-link">
                                        <div class="browse-image">
                                            <?php if ($store_logo_id):
                                                $logo_url = wp_get_attachment_image_url($store_logo_id, 'medium');
                                            ?>
                                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($store->name); ?>" class="store-logo">
                                            <?php else:
                                                $initials = strtoupper(substr($store->name, 0, 2));
                                            ?>
                                                <div class="browse-store-fallback"><?php echo esc_html($initials); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="browse-content">
                                            <h3 class="browse-name"><?php echo esc_html($store->name); ?></h3>
                                            <p class="browse-count">
                                                <?php
                                                printf(
                                                    _n('%s Deal', '%s Deals', $deals_count, 'arrzone'),
                                                    number_format_i18n($deals_count)
                                                );
                                                ?>
                                            </p>
                                            <?php if ($store_cashback): ?>
                                                <p class="browse-cashback">
                                                    <?php echo esc_html('Up to', 'arrzone'); ?> 
                                                    <?php echo esc_html($store_cashback); ?> 
                                                    <?php echo esc_html('Cashback', 'arrzone'); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
                
                // Display All Stores
                if (!empty($regular_stores)): ?>
                    <div class="all-stores-section">
                        <h2 class="browse-section-title"><?php echo esc_html('All Stores', 'arrzone'); ?></h2>
                        <div class="browse-grid">
                            <?php foreach ($regular_stores as $store):
                                $store_logo_id = get_term_meta($store->term_id, 'store_logo_id', true);
                                $store_cashback = get_term_meta($store->term_id, 'store_cashback', true);
                                $deals_count = $store->count;
                                $store_url = get_term_link($store);
                            ?>
                                <div class="browse-card">
                                    <a href="<?php echo esc_url($store_url); ?>" class="browse-card-link">
                                        <div class="browse-image">
                                            <?php if ($store_logo_id):
                                                $logo_url = wp_get_attachment_image_url($store_logo_id, 'medium');
                                            ?>
                                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($store->name); ?>" class="store-logo">
                                            <?php else:
                                                $initials = strtoupper(substr($store->name, 0, 2));
                                            ?>
                                                <div class="browse-store-fallback"><?php echo esc_html($initials); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="browse-content">
                                            <h3 class="browse-name"><?php echo esc_html($store->name); ?></h3>
                                            <p class="browse-count">
                                                <?php
                                                printf(
                                                    _n('%s Deal', '%s Deals', $deals_count, 'arrzone'),
                                                    number_format_i18n($deals_count)
                                                );
                                                ?>
                                            </p>
                                            <?php if ($store_cashback): ?>
                                                <p class="browse-cashback">
                                                    <?php echo esc_html('Up to', 'arrzone'); ?> 
                                                    <?php echo esc_html($store_cashback); ?> 
                                                    <?php echo esc_html('Cashback', 'arrzone'); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif;
                
            else: ?>
                <div class="no-stores">
                    <p><?php echo esc_html('No stores found.', 'arrzone'); ?></p>
                </div>
            <?php endif; ?>
            
        </div>
    </section>
    
</div>

<?php get_footer(); ?>
