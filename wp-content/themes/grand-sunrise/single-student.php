<?php
/**
 * The template for displaying a single Student
 *
 * @package YourTheme
 */

get_header();

if ( have_posts() ) :
    while ( have_posts() ) :
        the_post();
?>

<div class="single-student-wrapper">

    <!-- LEFT: IMAGE -->
    <div class="single-student-image">
        <?php the_post_thumbnail( 'large' ); ?>
    </div>

    <!-- RIGHT: TEXT -->
    <div class="single-student-content">

        <div class="student-info">

            <!-- Categories -->
            <div class="student-categories">
                <?php
                $cats = get_the_category();
                $links = array();
                foreach ( $cats as $cat ) {
                    $links[] = sprintf(
                        '<a href="%s">%s</a>',
                        esc_url( add_query_arg( 'post_type', 'student', get_category_link( $cat ) ) ),
                        esc_html( $cat->name )
                    );
                }
                echo implode( ', ', $links );
                ?>
            </div>

            <h1 class="single-student-name"><?php the_title(); ?></h1>

            <div class="single-student-description">
                <?php the_content(); ?>
            </div>

        </div>

    </div>

</div>

<?php
    endwhile;
endif;

get_footer();