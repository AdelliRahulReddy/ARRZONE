<?php
/**
 * Single Giveaway Template
 * 
 * @package DealsIndia
 */

get_header();

while (have_posts()) : the_post();
    $prize = get_post_meta(get_the_ID(), 'giveaway_prize', true);
    $end_date = get_post_meta(get_the_ID(), 'giveaway_end_date', true);
    $is_active = get_post_meta(get_the_ID(), 'giveaway_active', true);
?>

<article class="giveaway-single-page">
    <div class="container">
        
        <!-- Giveaway Header -->
        <div class="giveaway-single-header">
            <h1 class="giveaway-single-title"><?php the_title(); ?></h1>
            
            <?php if ($is_active === '1') : ?>
                <span class="giveaway-status-badge active">🎉 Active Giveaway</span>
            <?php else : ?>
                <span class="giveaway-status-badge ended">Ended</span>
            <?php endif; ?>
        </div>

        <!-- Giveaway Content Grid -->
        <div class="giveaway-single-grid">
            
            <!-- Left: Image -->
            <div class="giveaway-single-image">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('large'); ?>
                <?php else : ?>
                    <div class="giveaway-placeholder">
                        <span style="font-size: 80px;">🎁</span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Details -->
            <div class="giveaway-single-details">
                
                <?php if ($prize) : ?>
                    <div class="giveaway-prize-box">
                        <div class="prize-label">Grand Prize</div>
                        <div class="prize-title"><?php echo esc_html($prize); ?></div>
                    </div>
                <?php endif; ?>

                <div class="giveaway-single-content">
                    <?php the_content(); ?>
                </div>

                <?php if ($end_date) : ?>
                    <div class="giveaway-end-date">
                        <strong>Ends:</strong> <?php echo esc_html(date('F j, Y', strtotime($end_date))); ?>
                    </div>
                <?php endif; ?>

                <?php if ($is_active === '1') : ?>
                    <div class="giveaway-cta-box">
                        <a href="#entry-form" class="giveaway-enter-btn">Enter Giveaway Now!</a>
                        <p class="giveaway-terms">By entering, you agree to our <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">terms and conditions</a>.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Entry Form Section (if active) -->
        <?php if ($is_active === '1') : ?>
            <div id="entry-form" class="giveaway-entry-section">
                <h2>Enter the Giveaway</h2>
                <p>Fill out the form below to participate:</p>
                
                <form class="giveaway-entry-form" method="post">
                    <div class="form-group">
                        <label for="participant_name">Full Name *</label>
                        <input type="text" id="participant_name" name="participant_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="participant_email">Email Address *</label>
                        <input type="email" id="participant_email" name="participant_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="participant_phone">Phone Number</label>
                        <input type="tel" id="participant_phone" name="participant_phone">
                    </div>
                    
                    <button type="submit" class="submit-entry-btn">Submit Entry</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</article>

<style>
.giveaway-single-page {
    padding: 40px 0;
    background: #f8f9fa;
}

.giveaway-single-header {
    text-align: center;
    margin-bottom: 40px;
}

.giveaway-single-title {
    font-size: 42px;
    font-weight: 900;
    color: #2d3748;
    margin: 0 0 16px 0;
}

.giveaway-status-badge {
    display: inline-block;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 14px;
}

.giveaway-status-badge.active {
    background: #00897B;
    color: white;
}

.giveaway-status-badge.ended {
    background: #e0e0e0;
    color: #718096;
}

.giveaway-single-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 60px;
}

.giveaway-single-image img {
    width: 100%;
    height: auto;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.giveaway-placeholder {
    background: white;
    border-radius: 16px;
    padding: 80px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.giveaway-prize-box {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    border: 2px solid #00897B;
}

.prize-label {
    font-size: 12px;
    color: #00897B;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.prize-title {
    font-size: 28px;
    font-weight: 900;
    color: #2d3748;
}

.giveaway-single-content {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    line-height: 1.8;
}

.giveaway-end-date {
    background: #fff3cd;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    color: #856404;
}

.giveaway-cta-box {
    text-align: center;
}

.giveaway-enter-btn {
    display: inline-block;
    background: #00897B;
    color: white;
    padding: 16px 48px;
    border-radius: 10px;
    font-weight: 800;
    font-size: 18px;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 137, 123, 0.3);
}

.giveaway-enter-btn:hover {
    background: #00695C;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 137, 123, 0.4);
    color: white;
}

.giveaway-terms {
    margin-top: 12px;
    font-size: 13px;
    color: #718096;
}

.giveaway-entry-section {
    background: white;
    padding: 48px;
    border-radius: 16px;
    max-width: 600px;
    margin: 0 auto;
}

.giveaway-entry-section h2 {
    text-align: center;
    margin-bottom: 16px;
}

.giveaway-entry-form {
    margin-top: 32px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #2d3748;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 15px;
}

.form-group input:focus {
    outline: none;
    border-color: #00897B;
}

.submit-entry-btn {
    width: 100%;
    background: #00897B;
    color: white;
    padding: 16px;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.submit-entry-btn:hover {
    background: #00695C;
}

@media (max-width: 768px) {
    .giveaway-single-grid {
        grid-template-columns: 1fr;
    }
    
    .giveaway-single-title {
        font-size: 28px;
    }
}
</style>

<?php
endwhile;

get_footer();
