<?php
if (!defined('ABSPATH')) exit; 
/**
 * Template Name: All Deal Types Archive
 * Description: Displays all deal types in a grid layout
 */

get_header();
?>

<div class="archive-page">
    
    <!-- Page Header -->
    <section class="section-header">
        <div class="container">
            <h1><?php echo esc_html('All Deal Types', 'dealsindia'); ?></h1>
            <p><?php echo esc_html('Explore different types of deals and find the best offers', 'dealsindia'); ?></p>
        </div>
    </section>

    <!-- Browse Section -->
    <section class="browse-section">
        <div class="container">
            
            <?php
            // Get all deal types
            $deal_types_args = array(
                'taxonomy'   => 'deal-type',
                'hide_empty' => false,
                'orderby'    => 'name',
                'order'      => 'ASC',
            );
            
            $all_deal_types = get_terms($deal_types_args);
            
            if (!empty($all_deal_types) && !is_wp_error($all_deal_types)):
            ?>
                <div class="all-deal-types-section">
                    <h2 class="browse-section-title"><?php echo esc_html('All Deal Types', 'dealsindia'); ?></h2>
                    <div class="browse-grid">
                        <?php foreach ($all_deal_types as $deal_type):
                            $dealtype_icon = get_term_meta($deal_type->term_id, 'dealtype_icon', true);
                            $dealtype_icon_image_id = get_term_meta($deal_type->term_id, 'dealtype_icon_image_id', true);
                            $dealtype_icon_image_url = $dealtype_icon_image_id ? wp_get_attachment_url($dealtype_icon_image_id) : '';
                            $dealtype_color = get_term_meta($deal_type->term_id, 'dealtype_color', true);
                            $deals_count = $deal_type->count;
                            $deal_type_url = get_term_link($deal_type);
                        ?>
                            <div class="browse-card">
                                <a href="<?php echo esc_url($deal_type_url); ?>" class="browse-card-link">
                                    <div class="browse-icon">
                                        <?php if ($dealtype_icon_image_url): ?>
                                            <img src="<?php echo esc_url($dealtype_icon_image_url); ?>" 
                                                 alt="<?php echo esc_attr($deal_type->name); ?>" 
                                                 class="dealtype-icon-img">
                                        <?php elseif ($dealtype_icon): ?>
                                            <span class="browse-emoji"><?php echo esc_html($dealtype_icon); ?></span>
                                        <?php else: ?>
                                            <span class="browse-emoji">🎫</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="browse-content">
                                        <h3 class="browse-name"><?php echo esc_html($deal_type->name); ?></h3>
                                        <p class="browse-count">
                                            <?php
                                            printf(
                                                _n('%s Deal', '%s Deals', $deals_count, 'dealsindia'),
                                                number_format_i18n($deals_count)
                                            );
                                            ?>
                                        </p>
                                        <?php if ($dealtype_color): ?>
                                            <div class="dealtype-color-indicator" style="background-color: <?php echo esc_attr($dealtype_color); ?>; width: 40px; height: 4px; border-radius: 2px; margin-top: 8px;"></div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="no-deal-types">
                    <p><?php echo esc_html('No deal types found.', 'dealsindia'); ?></p>
                </div>
            <?php endif; ?>
            
        </div>
    </section>
    
</div>

<?php get_footer(); ?>
