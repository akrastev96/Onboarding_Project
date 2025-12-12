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

            <?php
            // Fetch all meta at once, then escape on output.
            $meta    = get_post_meta( get_the_ID() );
            $country = isset( $meta['_student_country'][0] ) ? esc_html( $meta['_student_country'][0] ) : '';
            $city    = isset( $meta['_student_city'][0] ) ? esc_html( $meta['_student_city'][0] ) : '';
            $address = isset( $meta['_student_address'][0] ) ? esc_html( $meta['_student_address'][0] ) : '';
            $birth   = isset( $meta['_student_birthdate'][0] ) ? esc_html( $meta['_student_birthdate'][0] ) : '';
            $class   = isset( $meta['_student_class'][0] ) ? esc_html( $meta['_student_class'][0] ) : '';
            $active  = isset( $meta['_student_active'][0] ) ? intval( $meta['_student_active'][0] ) : 0;

            // Visibility settings (default to visible).
            $show_country   = student_meta_is_visible( 'country' );
            $show_city      = student_meta_is_visible( 'city' );
            $show_address   = student_meta_is_visible( 'address' );
            $show_birth     = student_meta_is_visible( 'birthdate' );
            $show_class     = student_meta_is_visible( 'class' );
            $show_active    = student_meta_is_visible( 'active' );
            ?>

            <div class="student-meta">
                <?php if ( $show_country || $show_city ) : ?>
                    <?php
                    $location_parts = array();
                    if ( $show_country && $country ) {
                        $location_parts[] = $country;
                    }
                    if ( $show_city && $city ) {
                        $location_parts[] = $city;
                    }
                    ?>
                    <?php if ( ! empty( $location_parts ) ) : ?>
                        <p><strong>Lives In:</strong> <?php echo implode( ', ', $location_parts ); ?></p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ( $show_address && $address ) : ?>
                    <p><strong>Address:</strong> <?php echo $address; ?></p>
                <?php endif; ?>

                <?php if ( $show_birth && $birth ) : ?>
                    <p><strong>Birth Date:</strong> <?php echo $birth; ?></p>
                <?php endif; ?>

                <?php if ( $show_class && $class ) : ?>
                    <p><strong>Class / Grade:</strong> <?php echo $class; ?></p>
                <?php endif; ?>

                <?php if ( $show_active ) : ?>
                    <p><strong>Status:</strong> <?php echo $active ? 'Active' : 'Inactive'; ?></p>
                <?php endif; ?>
            </div>

		</div>

    </div>

</div>

<?php
    endwhile;
endif;

get_footer();