<?php
/**
 * Template for displaying all single posts in the Grand Sunrise child theme.
 */

get_header(); ?>

<main id="site-content" role="main">

    <?php while ( have_posts() ) : the_post(); ?>

        <article <?php post_class('grand-sunrise-single'); ?>>

            <h1 class="entry-title"><?php the_title(); ?></h1>

            <div class="entry-content">
                <?php the_content(); ?>
            </div>

            <div class="dixy-image-wrapper">
                <img 
                    src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/dixy.png' ); ?>" 
                    alt="DiXy mascot"
                />
            </div>

        </article>

    <?php endwhile; ?>

</main>

<?php get_footer(); ?>