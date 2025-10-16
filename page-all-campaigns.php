<?php
if (!defined('ABSPATH')) exit; 
/**
 * Template Name: All Campaigns Archive
 * Description: Displays all campaigns (Active, Upcoming, Expired)
 */

get_header();

// Get all campaigns
$campaigns_args = array(
    'taxonomy'   => 'campaign',
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
);

$all_campaigns = get_terms($campaigns_args);

// Separate campaigns by status
$active_campaigns = array();
$upcoming_campaigns = array();
$expired_campaigns = array();

$today = current_time('Y-m-d');

foreach ($all_campaigns as $campaign) {
    $start_date = get_term_meta($campaign->term_id, 'campaign_start_date', true);
    $end_date = get_term_meta($campaign->term_id, 'campaign_end_date', true);
    
    if ($start_date && $today < $start_date) {
        $upcoming_campaigns[] = $campaign;
    } elseif ($end_date && $today > $end_date) {
        $expired_campaigns[] = $campaign;
    } else {
        $active_campaigns[] = $campaign;
    }
}
?>

<div class="archive-page">
    
    <!-- Page Header -->
    <section class="section-header">
        <div class="container">
            <h1><?php echo esc_html('🎪 All Campaigns & Sales', 'dealsindia'); ?></h1>
            <p><?php echo esc_html('Browse ongoing and upcoming festival sales & special offers', 'dealsindia'); ?></p>
        </div>
    </section>

    <!-- Active Campaigns -->
    <?php if (!empty($active_campaigns)) : ?>
    <section class="browse-section">
        <div class="container">
            <h2 class="browse-section-title"><?php echo esc_html('🔥 Active Campaigns', 'dealsindia'); ?></h2>
            <div class="browse-grid">
                <?php foreach ($active_campaigns as $campaign) :
                    $icon = get_term_meta($campaign->term_id, 'campaign_icon', true);
                    $banner_id = get_term_meta($campaign->term_id, 'campaign_banner_id', true);
                    $color = get_term_meta($campaign->term_id, 'campaign_color', true);
                    $tagline = get_term_meta($campaign->term_id, 'campaign_tagline', true);
                    $end_date = get_term_meta($campaign->term_id, 'campaign_end_date', true);
                    $deals_count = $campaign->count;
                    $campaign_url = get_term_link($campaign);
                ?>
                    <div class="browse-card">
                        <a href="<?php echo esc_url($campaign_url); ?>" class="browse-card-link">
                            <div class="browse-icon">
                                <?php if ($icon) : ?>
                                    <span class="browse-emoji"><?php echo esc_html($icon); ?></span>
                                <?php else : ?>
                                    <span class="browse-emoji">🎉</span>
                                <?php endif; ?>
                            </div>
                            <div class="browse-content">
                                <h3 class="browse-name"><?php echo esc_html($campaign->name); ?></h3>
                                <?php if ($tagline) : ?>
                                    <p style="font-size: 12px; color: #666; margin: 4px 0;"><?php echo esc_html($tagline); ?></p>
                                <?php endif; ?>
                                <p class="browse-count">
                                    <?php
                                    printf(
                                        _n('%s Deal', '%s Deals', $deals_count, 'dealsindia'),
                                        number_format_i18n($deals_count)
                                    );
                                    ?>
                                </p>
                                <?php if ($end_date) : ?>
                                    <p style="font-size: 11px; color: #ff6b6b; margin-top: 4px;">
                                        ⏰ Ends: <?php echo esc_html(date('M d, Y', strtotime($end_date))); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Upcoming Campaigns -->
    <?php if (!empty($upcoming_campaigns)) : ?>
    <section class="browse-section">
        <div class="container">
            <h2 class="browse-section-title"><?php echo esc_html('📅 Coming Soon', 'dealsindia'); ?></h2>
            <div class="browse-grid">
                <?php foreach ($upcoming_campaigns as $campaign) :
                    $icon = get_term_meta($campaign->term_id, 'campaign_icon', true);
                    $tagline = get_term_meta($campaign->term_id, 'campaign_tagline', true);
                    $start_date = get_term_meta($campaign->term_id, 'campaign_start_date', true);
                    $deals_count = $campaign->count;
                    $campaign_url = get_term_link($campaign);
                ?>
                    <div class="browse-card">
                        <a href="<?php echo esc_url($campaign_url); ?>" class="browse-card-link">
                            <div class="browse-icon">
                                <?php if ($icon) : ?>
                                    <span class="browse-emoji"><?php echo esc_html($icon); ?></span>
                                <?php else : ?>
                                    <span class="browse-emoji">🎉</span>
                                <?php endif; ?>
                            </div>
                            <div class="browse-content">
                                <h3 class="browse-name"><?php echo esc_html($campaign->name); ?></h3>
                                <?php if ($tagline) : ?>
                                    <p style="font-size: 12px; color: #666; margin: 4px 0;"><?php echo esc_html($tagline); ?></p>
                                <?php endif; ?>
                                <?php if ($deals_count > 0) : ?>
                                    <p class="browse-count"><?php echo esc_html(sprintf(__('%d Deals Coming', 'dealsindia'), $deals_count)); ?></p>
                                <?php endif; ?>
                                <?php if ($start_date) : ?>
                                    <p style="font-size: 11px; color: #fdcb6e; margin-top: 4px;">
                                        🗓️ Starts: <?php echo esc_html(date('M d, Y', strtotime($start_date))); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Expired Campaigns (Show last 3 only) -->
    <?php if (!empty($expired_campaigns)) : 
        $recent_expired = array_slice($expired_campaigns, 0, 3);
    ?>
    <section class="browse-section">
        <div class="container">
            <h2 class="browse-section-title"><?php echo esc_html('⏱️ Past Campaigns', 'dealsindia'); ?></h2>
            <div class="browse-grid">
                <?php foreach ($recent_expired as $campaign) :
                    $icon = get_term_meta($campaign->term_id, 'campaign_icon', true);
                    $deals_count = $campaign->count;
                    $campaign_url = get_term_link($campaign);
                ?>
                    <div class="browse-card">
                        <a href="<?php echo esc_url($campaign_url); ?>" class="browse-card-link">
                            <div class="browse-icon">
                                <?php if ($icon) : ?>
                                    <span class="browse-emoji"><?php echo esc_html($icon); ?></span>
                                <?php else : ?>
                                    <span class="browse-emoji">🎉</span>
                                <?php endif; ?>
                            </div>
                            <div class="browse-content">
                                <h3 class="browse-name"><?php echo esc_html($campaign->name); ?></h3>
                                <p style="font-size: 11px; color: #d63031; margin-top: 4px;">
                                    🔴 Campaign Ended
                                </p>
                                <?php if ($deals_count > 0) : ?>
                                    <p class="browse-count"><?php echo esc_html(sprintf(__('%d Deals Archived', 'dealsindia'), $deals_count)); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- No Campaigns Message -->
    <?php if (empty($all_campaigns)) : ?>
    <section class="browse-section">
        <div class="container">
            <div class="no-deals-message">
                <p><?php echo esc_html('No campaigns available at the moment. Check back soon!', 'dealsindia'); ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
</div>

<?php get_footer(); ?>
