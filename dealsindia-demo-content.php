<?php
/**
 * DealsIndia Demo Content Importer
 * Run once to populate site with sample data
 * Access: yoursite.com/wp-admin/admin.php?page=dealsindia-demo-import
 */

// Add admin menu
add_action('admin_menu', 'dealsindia_demo_menu');
function dealsindia_demo_menu() {
    add_menu_page(
        'Demo Import',
        'Import Demo',
        'manage_options',
        'dealsindia-demo-import',
        'dealsindia_demo_import_page',
        'dashicons-download',
        100
    );
}

// Demo import page
function dealsindia_demo_import_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    ?>
    <div class="wrap">
        <h1>🎉 DealsIndia Demo Content Importer</h1>
        <p>This will create sample content to showcase your theme!</p>
        
        <div style="background: #fff; padding: 20px; border-radius: 8px; max-width: 600px; margin: 20px 0;">
            <h2>📦 What will be imported:</h2>
            <ul style="line-height: 2;">
                <li>✅ <strong>10 Stores</strong> (Amazon, Flipkart, Myntra, etc.)</li>
                <li>✅ <strong>10 Categories</strong> (Electronics, Fashion, Food, etc.)</li>
                <li>✅ <strong>50 Deals</strong> with prices, coupons, images</li>
                <li>✅ <strong>3 Hero Banners</strong> for homepage slider</li>
                <li>✅ <strong>3 Work Steps</strong> (How it works section)</li>
                <li>✅ <strong>2 Giveaways</strong> (Contest banners)</li>
            </ul>
            
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('dealsindia_demo_import', 'demo_nonce'); ?>
                <button type="submit" name="import_demo" class="button button-primary button-hero" style="font-size: 18px; padding: 12px 30px;">
                    🚀 Import Demo Content Now!
                </button>
            </form>
        </div>
    </div>
    <?php
    
    // Process import
    if (isset($_POST['import_demo']) && check_admin_referer('dealsindia_demo_import', 'demo_nonce')) {
        dealsindia_import_demo_content();
    }
}

// Main import function
function dealsindia_import_demo_content() {
    set_time_limit(300); // 5 minutes
    
    echo '<div class="notice notice-info"><p>⏳ Starting import... Please wait...</p></div>';
    flush();
    
    // Import in order
    dealsindia_import_stores();
    dealsindia_import_categories();
    dealsindia_import_deals();
    dealsindia_import_hero_banners();
    dealsindia_import_work_steps();
    dealsindia_import_giveaways();
    
    echo '<div class="notice notice-success"><p><strong>✅ Demo content imported successfully!</strong></p></div>';
    echo '<p><a href="' . home_url() . '" class="button button-primary" target="_blank">🎨 View Your Site</a></p>';
}

// Import Stores
function dealsindia_import_stores() {
    echo '<p>📦 Importing stores...</p>';
    flush();
    
    $stores = array(
        array('name' => 'Amazon', 'cashback' => '5%'),
        array('name' => 'Flipkart', 'cashback' => '7%'),
        array('name' => 'Myntra', 'cashback' => '10%'),
        array('name' => 'Ajio', 'cashback' => '8%'),
        array('name' => 'Swiggy', 'cashback' => '15%'),
        array('name' => 'Zomato', 'cashback' => '12%'),
        array('name' => 'BookMyShow', 'cashback' => '6%'),
        array('name' => 'MakeMyTrip', 'cashback' => '9%'),
        array('name' => 'Nykaa', 'cashback' => '11%'),
        array('name' => 'FirstCry', 'cashback' => '8%')
    );
    
    foreach ($stores as $store) {
        $term = wp_insert_term($store['name'], 'store');
        if (!is_wp_error($term)) {
            update_term_meta($term['term_id'], 'store_cashback', $store['cashback']);
        }
    }
    
    echo '<p>✅ 10 stores imported!</p>';
}

// Import Categories
function dealsindia_import_categories() {
    echo '<p>📂 Importing categories...</p>';
    flush();
    
    $categories = array(
        array('name' => 'Electronics', 'icon' => '📱'),
        array('name' => 'Fashion', 'icon' => '👕'),
        array('name' => 'Food & Dining', 'icon' => '🍔'),
        array('name' => 'Beauty & Health', 'icon' => '💄'),
        array('name' => 'Travel', 'icon' => '✈️'),
        array('name' => 'Home & Kitchen', 'icon' => '🏠'),
        array('name' => 'Entertainment', 'icon' => '🎬'),
        array('name' => 'Kids & Baby', 'icon' => '👶'),
        array('name' => 'Sports & Fitness', 'icon' => '⚽'),
        array('name' => 'Books & Education', 'icon' => '📚')
    );
    
    foreach ($categories as $cat) {
        $term = wp_insert_term($cat['name'], 'deal_category');
        if (!is_wp_error($term)) {
            update_term_meta($term['term_id'], 'category_icon', $cat['icon']);
        }
    }
    
    echo '<p>✅ 10 categories imported!</p>';
}

// Import Deals
function dealsindia_import_deals() {
    echo '<p>🎁 Importing 50 deals (this may take a minute)...</p>';
    flush();
    
    $deals_data = array(
        array('title' => 'Samsung Galaxy S24 Ultra', 'store' => 'Amazon', 'category' => 'Electronics', 'original' => 124999, 'sale' => 109999, 'coupon' => 'SAMS24', 'featured' => true, 'trending' => true),
        array('title' => 'iPhone 15 Pro Max', 'store' => 'Flipkart', 'category' => 'Electronics', 'original' => 159999, 'sale' => 149999, 'coupon' => 'APPLE15', 'featured' => true),
        array('title' => 'Sony WH-1000XM5 Headphones', 'store' => 'Amazon', 'category' => 'Electronics', 'original' => 29999, 'sale' => 24999, 'coupon' => 'SONYX5', 'trending' => true),
        array('title' => 'OnePlus 12 5G', 'store' => 'Amazon', 'category' => 'Electronics', 'original' => 64999, 'sale' => 59999, 'coupon' => 'OP12DEAL'),
        array('title' => 'Mi TV 55 inch 4K', 'store' => 'Flipkart', 'category' => 'Electronics', 'original' => 49999, 'sale' => 39999, 'coupon' => 'MITV55'),
        
        array('title' => 'Nike Air Max Shoes', 'store' => 'Myntra', 'category' => 'Fashion', 'original' => 8999, 'sale' => 5999, 'coupon' => 'NIKESHOE', 'trending' => true),
        array('title' => 'Levis Jeans Combo', 'store' => 'Ajio', 'category' => 'Fashion', 'original' => 4999, 'sale' => 2999, 'coupon' => 'LEVI30'),
        array('title' => 'Puma T-Shirt Pack of 3', 'store' => 'Myntra', 'category' => 'Fashion', 'original' => 2999, 'sale' => 1499, 'coupon' => 'PUMA3X'),
        array('title' => 'Zara Winter Jacket', 'store' => 'Ajio', 'category' => 'Fashion', 'original' => 12999, 'sale' => 8999, 'coupon' => 'ZARA50'),
        array('title' => 'Adidas Running Shoes', 'store' => 'Myntra', 'category' => 'Fashion', 'original' => 7999, 'sale' => 4999, 'coupon' => 'ADIRUN'),
        
        array('title' => 'Swiggy One Membership', 'store' => 'Swiggy', 'category' => 'Food & Dining', 'original' => 999, 'sale' => 499, 'coupon' => 'SWGONE', 'featured' => true),
        array('title' => 'Zomato Gold Annual', 'store' => 'Zomato', 'category' => 'Food & Dining', 'original' => 1999, 'sale' => 999, 'coupon' => 'ZGOLD50'),
        array('title' => 'Pizza Hut Meal Combo', 'store' => 'Swiggy', 'category' => 'Food & Dining', 'original' => 799, 'sale' => 499, 'coupon' => 'PH499'),
        array('title' => 'Dominos Buy 1 Get 1', 'store' => 'Zomato', 'category' => 'Food & Dining', 'original' => 599, 'sale' => 299, 'coupon' => 'DOMBOGO'),
        array('title' => 'McDonalds Burger Meal', 'store' => 'Swiggy', 'category' => 'Food & Dining', 'original' => 349, 'sale' => 199, 'coupon' => 'MC199'),
        
        array('title' => 'Lakme Makeup Kit', 'store' => 'Nykaa', 'category' => 'Beauty & Health', 'original' => 3999, 'sale' => 2499, 'coupon' => 'LAKME50', 'trending' => true),
        array('title' => 'Mamaearth Skincare Combo', 'store' => 'Nykaa', 'category' => 'Beauty & Health', 'original' => 1499, 'sale' => 999, 'coupon' => 'MAMA40'),
        array('title' => 'Loreal Hair Care', 'store' => 'Amazon', 'category' => 'Beauty & Health', 'original' => 2999, 'sale' => 1999, 'coupon' => 'LOREAL30'),
        array('title' => 'Nivea Body Lotion Pack', 'store' => 'Flipkart', 'category' => 'Beauty & Health', 'original' => 899, 'sale' => 599, 'coupon' => 'NIVEA3'),
        array('title' => 'Plum Face Cream', 'store' => 'Nykaa', 'category' => 'Beauty & Health', 'original' => 599, 'sale' => 399, 'coupon' => 'PLUM200'),
        
        array('title' => 'Goa Beach Resort Package', 'store' => 'MakeMyTrip', 'category' => 'Travel', 'original' => 12999, 'sale' => 9999, 'coupon' => 'GOA3000'),
        array('title' => 'Dubai Tour 5 Days', 'store' => 'MakeMyTrip', 'category' => 'Travel', 'original' => 45999, 'sale' => 39999, 'coupon' => 'DUBAI6K'),
        array('title' => 'Manali Honeymoon Package', 'store' => 'MakeMyTrip', 'category' => 'Travel', 'original' => 15999, 'sale' => 12999, 'coupon' => 'MANALI3'),
        array('title' => 'Kerala Backwaters Cruise', 'store' => 'MakeMyTrip', 'category' => 'Travel', 'original' => 8999, 'sale' => 6999, 'coupon' => 'KERALA2'),
        array('title' => 'Jaipur Heritage Tour', 'store' => 'MakeMyTrip', 'category' => 'Travel', 'original' => 5999, 'sale' => 4499, 'coupon' => 'JAIPUR15'),
        
        array('title' => 'Prestige Cooker 5L', 'store' => 'Amazon', 'category' => 'Home & Kitchen', 'original' => 2999, 'sale' => 1999, 'coupon' => 'PRESS1K'),
        array('title' => 'Philips Air Fryer', 'store' => 'Flipkart', 'category' => 'Home & Kitchen', 'original' => 9999, 'sale' => 7499, 'coupon' => 'PHILAF'),
        array('title' => 'Ikea Furniture Set', 'store' => 'Amazon', 'category' => 'Home & Kitchen', 'original' => 19999, 'sale' => 14999, 'coupon' => 'IKEA5K'),
        array('title' => 'Milton Water Bottle Pack', 'store' => 'Flipkart', 'category' => 'Home & Kitchen', 'original' => 999, 'sale' => 599, 'coupon' => 'MILTON4'),
        array('title' => 'Cello Storage Container Set', 'store' => 'Amazon', 'category' => 'Home & Kitchen', 'original' => 1499, 'sale' => 899, 'coupon' => 'CELLO6'),
        
        array('title' => 'BookMyShow Movie Voucher', 'store' => 'BookMyShow', 'category' => 'Entertainment', 'original' => 500, 'sale' => 399, 'coupon' => 'BMS100'),
        array('title' => 'Netflix 1 Year Plan', 'store' => 'Amazon', 'category' => 'Entertainment', 'original' => 7999, 'sale' => 5999, 'coupon' => 'NFLX2K'),
        array('title' => 'Amazon Prime Annual', 'store' => 'Amazon', 'category' => 'Entertainment', 'original' => 1499, 'sale' => 999, 'coupon' => 'PRIME50'),
        array('title' => 'Spotify Premium 6 Months', 'store' => 'Flipkart', 'category' => 'Entertainment', 'original' => 999, 'sale' => 699, 'coupon' => 'SPOT300'),
        array('title' => 'Disney+ Hotstar Annual', 'store' => 'Amazon', 'category' => 'Entertainment', 'original' => 1499, 'sale' => 999, 'coupon' => 'HOTSTAR'),
        
        array('title' => 'Fisher Price Toy Set', 'store' => 'FirstCry', 'category' => 'Kids & Baby', 'original' => 2999, 'sale' => 1999, 'coupon' => 'FP1K'),
        array('title' => 'Pampers Diaper Pack', 'store' => 'FirstCry', 'category' => 'Kids & Baby', 'original' => 1499, 'sale' => 999, 'coupon' => 'PAMPER5'),
        array('title' => 'Baby Care Combo', 'store' => 'Amazon', 'category' => 'Kids & Baby', 'original' => 3999, 'sale' => 2499, 'coupon' => 'BABY1500'),
        array('title' => 'Kids Clothing Bundle', 'store' => 'FirstCry', 'category' => 'Kids & Baby', 'original' => 2499, 'sale' => 1499, 'coupon' => 'KIDS1K'),
        array('title' => 'Educational Toys Pack', 'store' => 'Amazon', 'category' => 'Kids & Baby', 'original' => 1999, 'sale' => 1299, 'coupon' => 'EDUPLAY'),
        
        array('title' => 'Gym Equipment Combo', 'store' => 'Amazon', 'category' => 'Sports & Fitness', 'original' => 9999, 'sale' => 6999, 'coupon' => 'GYM3K'),
        array('title' => 'Yoga Mat Premium', 'store' => 'Flipkart', 'category' => 'Sports & Fitness', 'original' => 1999, 'sale' => 999, 'coupon' => 'YOGA1K'),
        array('title' => 'Cricket Kit Complete', 'store' => 'Amazon', 'category' => 'Sports & Fitness', 'original' => 5999, 'sale' => 3999, 'coupon' => 'CRIK2K'),
        array('title' => 'Cycling Gear Bundle', 'store' => 'Flipkart', 'category' => 'Sports & Fitness', 'original' => 4999, 'sale' => 2999, 'coupon' => 'CYCLE2'),
        array('title' => 'Badminton Racket Pair', 'store' => 'Amazon', 'category' => 'Sports & Fitness', 'original' => 2999, 'sale' => 1799, 'coupon' => 'BADMIN'),
        
        array('title' => 'NCERT Books Class 10', 'store' => 'Amazon', 'category' => 'Books & Education', 'original' => 1499, 'sale' => 999, 'coupon' => 'NCERT50'),
        array('title' => 'Kindle Unlimited 1 Year', 'store' => 'Amazon', 'category' => 'Books & Education', 'original' => 1999, 'sale' => 1499, 'coupon' => 'KINDLE5'),
        array('title' => 'Study Lamp LED', 'store' => 'Flipkart', 'category' => 'Books & Education', 'original' => 899, 'sale' => 549, 'coupon' => 'LAMP350'),
        array('title' => 'School Bag Premium', 'store' => 'Amazon', 'category' => 'Books & Education', 'original' => 1999, 'sale' => 1299, 'coupon' => 'BAG700'),
        array('title' => 'Online Course Bundle', 'store' => 'Flipkart', 'category' => 'Books & Education', 'original' => 9999, 'sale' => 4999, 'coupon' => 'LEARN5K')
    );
    
    $count = 0;
    foreach ($deals_data as $deal) {
        $post_id = wp_insert_post(array(
            'post_title' => $deal['title'],
            'post_content' => 'Get amazing ' . $deal['title'] . ' at unbeatable prices! Limited time offer. Use coupon code for extra discount.',
            'post_status' => 'publish',
            'post_type' => 'deals'
        ));
        
        if ($post_id) {
            // Add meta
            update_post_meta($post_id, 'original_price', $deal['original']);
            update_post_meta($post_id, 'sale_price', $deal['sale']);
            update_post_meta($post_id, 'coupon_code', $deal['coupon']);
            update_post_meta($post_id, 'expiry_date', date('Y-m-d', strtotime('+30 days')));
            
            if (isset($deal['featured']) && $deal['featured']) {
                update_post_meta($post_id, 'is_featured', '1');
            }
            
            if (isset($deal['trending']) && $deal['trending']) {
                update_post_meta($post_id, 'is_trending', '1');
            }
            
            // Assign store
            $store_term = get_term_by('name', $deal['store'], 'store');
            if ($store_term) {
                wp_set_object_terms($post_id, $store_term->term_id, 'store');
            }
            
            // Assign category
            $cat_term = get_term_by('name', $deal['category'], 'deal_category');
            if ($cat_term) {
                wp_set_object_terms($post_id, $cat_term->term_id, 'deal_category');
            }
            
            $count++;
            if ($count % 10 == 0) {
                echo '<p>⏳ Imported ' . $count . ' deals...</p>';
                flush();
            }
        }
    }
    
    echo '<p>✅ 50 deals imported!</p>';
}

// Import Hero Banners
function dealsindia_import_hero_banners() {
    echo '<p>🎨 Importing hero banners...</p>';
    flush();
    
    $banners = array(
        array('heading' => 'Upto 70% Off', 'subheading' => 'Electronics & Gadgets', 'store' => 'Amazon', 'color' => '#e74c3c'),
        array('heading' => 'Fashion Sale', 'subheading' => 'Trending Styles', 'store' => 'Myntra', 'color' => '#ff9800'),
        array('heading' => 'Food Fest', 'subheading' => 'Delicious Deals', 'store' => 'Swiggy', 'color' => '#009688')
    );
    
    foreach ($banners as $index => $banner) {
        $post_id = wp_insert_post(array(
            'post_title' => $banner['heading'] . ' - ' . $banner['store'],
            'post_status' => 'publish',
            'post_type' => 'hero_banner'
        ));
        
        if ($post_id) {
            update_post_meta($post_id, 'banner_heading', $banner['heading']);
            update_post_meta($post_id, 'banner_subheading', $banner['subheading']);
            update_post_meta($post_id, 'banner_store', $banner['store']);
            update_post_meta($post_id, 'banner_bg_color', $banner['color']);
            update_post_meta($post_id, 'banner_cashback', ($index + 3) . '%');
            update_post_meta($post_id, 'banner_order', $index + 1);
        }
    }
    
    echo '<p>✅ 3 hero banners imported!</p>';
}

// Import Work Steps
function dealsindia_import_work_steps() {
    echo '<p>📝 Importing work steps...</p>';
    flush();
    
    $steps = array(
        array('title' => 'Browse & Shop', 'content' => 'Find amazing deals and click to shop', 'icon' => '🛍️', 'order' => 1),
        array('title' => 'Earn Cashback', 'content' => 'Get cashback added to your wallet', 'icon' => '💰', 'order' => 2),
        array('title' => 'Redeem Rewards', 'content' => 'Withdraw to bank or as vouchers', 'icon' => '🎁', 'order' => 3)
    );
    
    foreach ($steps as $step) {
        $post_id = wp_insert_post(array(
            'post_title' => $step['title'],
            'post_content' => $step['content'],
            'post_status' => 'publish',
            'post_type' => 'work_step'
        ));
        
        if ($post_id) {
            update_post_meta($post_id, 'step_icon', $step['icon']);
            update_post_meta($post_id, 'step_order', $step['order']);
        }
    }
    
    echo '<p>✅ 3 work steps imported!</p>';
}

// Import Giveaways
function dealsindia_import_giveaways() {
    echo '<p>🎁 Importing giveaways...</p>';
    flush();
    
    $giveaways = array(
        array('title' => 'iPhone 15 Giveaway', 'prize' => 'iPhone 15 Pro', 'color' => '#667eea'),
        array('title' => 'Amazon Voucher Contest', 'prize' => '₹10,000 Voucher', 'color' => '#e74c3c')
    );
    
    foreach ($giveaways as $giveaway) {
        $post_id = wp_insert_post(array(
            'post_title' => $giveaway['title'],
            'post_content' => 'Win amazing prizes! Participate now.',
            'post_status' => 'publish',
            'post_type' => 'giveaway'
        ));
        
        if ($post_id) {
            update_post_meta($post_id, 'giveaway_prize', $giveaway['prize']);
            update_post_meta($post_id, 'giveaway_bg_color', $giveaway['color']);
            update_post_meta($post_id, 'giveaway_start_date', date('Y-m-d'));
            update_post_meta($post_id, 'giveaway_end_date', date('Y-m-d', strtotime('+60 days')));
            update_post_meta($post_id, 'giveaway_active', '1');
        }
    }
    
    echo '<p>✅ 2 giveaways imported!</p>';
}

// Include in functions.php
require_once get_template_directory() . '/dealsindia-demo-content.php';
