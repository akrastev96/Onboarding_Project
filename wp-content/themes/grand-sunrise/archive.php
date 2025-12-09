<?php
get_header(); ?>

<main id="site-content">
    <h1><?php the_archive_title(); ?></h1>

    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            get_template_part( 'content', get_post_type() );
        endwhile;
    else :
        echo '<p>No posts found.</p>';
    endif;
    ?>
</main>

<?php get_footer();