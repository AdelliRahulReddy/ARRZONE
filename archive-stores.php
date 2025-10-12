<?php
/**
 * All Stores Archive
 * URL: /deals-store/
 * 
 * @package DealsIndia
 */

get_header();
?>

<div class="all-stores-page">
    <div class="container">
        
        <!-- Breadcrumb -->
        <?php dealsindia_breadcrumb(); ?>
        
        <!-- Page Header -->
        <div class="page-header" style="text-align: center; padding: 40px 0;">
            <h1 style="font-size: 48px; margin-bottom: 10px;">Browse by Store</h1>
            <p style="font-size: 18px; color: #718096;">Discover amazing deals from your favorite stores</p>
        </div>
        
        <!-- Stores Grid -->
        <div class="stores-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 40px;">
            <?php
            $stores = get_terms(array(
                'taxonomy' => 'store',
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ));
            
            if ($stores && !is_wp_error($stores)) :
                foreach ($stores as $store) :
                    $logo_id = get_term_meta($store->term_id, 'store_logo_id', true);
                    $logo_url = $logo_id ? wp_get_attachment_url($logo_id) : '';
                    $cashback = get_term_meta($store->term_id, 'store_cashback', true);
                    $store_link = get_term_link($store);
                    ?>
                    <a href="<?php echo esc_url($store_link); ?>" class="store-card" style="background: white; border: 2px solid #e2e8f0; border-radius: 12px; padding: 24px; text-align: center; text-decoration: none; transition: all 0.3s ease; display: block; position: relative;">
                        
                        <?php if ($cashback) : ?>
                            <div class="store-cashback-badge" style="position: absolute; top: 10px; right: 10px; background: #00897B; color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                <?php echo esc_html($cashback); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="store-logo" style="height: 80px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                            <?php if ($logo_url) : ?>
                                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($store->name); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            <?php else : ?>
                                <span style="font-size: 48px;">🏪</span>
                            <?php endif; ?>
                        </div>
                        
                        <h3 style="font-size: 18px; color: #2d3748; margin-bottom: 8px;"><?php echo esc_html($store->name); ?></h3>
                        <p style="font-size: 14px; color: #718096; margin: 0;">
                            <strong><?php echo $store->count; ?></strong> <?php echo $store->count == 1 ? 'Deal' : 'Deals'; ?>
                        </p>
                    </a>
                    <?php
                endforeach;
            else :
                ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                    <p style="font-size: 18px; color: #718096;">No stores found.</p>
                </div>
                <?php
            endif;
            ?>
        </div>
        
    </div>
</div>

<style>
.store-card:hover {
    border-color: #00897B !important;
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 137, 123, 0.15);
}
</style>

<?php get_footer(); ?>
