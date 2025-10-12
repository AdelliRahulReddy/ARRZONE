<?php
/**
 * Standard Page Template
 * Displays WordPress Pages (Contact, Privacy Policy, About, etc.)
 * 
 * @package DealsIndia
 * @version 1.0
 */

get_header();
?>

<div class="page-wrapper">
    <div class="container">
        
        <?php while (have_posts()) : the_post(); ?>
        
        <article class="page-content-wrapper">
            
            <header class="page-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </header>
            
            <div class="page-content">
                <?php the_content(); ?>
            </div>
            
        </article>
        
        <?php endwhile; ?>
        
    </div>
</div>

<?php get_footer(); ?>
