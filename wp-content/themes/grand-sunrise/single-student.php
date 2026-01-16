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
                $terms = get_the_terms( get_the_ID(), 'student_category' );

                if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                    $links = array();

                    foreach ( $terms as $term ) {
                        $links[] = sprintf(
                            '<a href="%s">%s</a>',
                            esc_url( get_term_link( $term ) ),
                            esc_html( $term->name )
                        );
                    }

                    echo implode( ', ', $links );
                }
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
$linked_students = get_field( 'linked_students' );

if ( ! empty( $linked_students ) ) :
?>
	<section class="linked-students" style="margin-top:40px;">
		<h2><?php esc_html_e( 'Linked Students', 'grand-sunrise' ); ?></h2>

		<div class="linked-students-list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;margin-top:20px;">
			<?php foreach ( $linked_students as $row ) :
				$student = $row['student'];

				// Safety check
				if ( ! $student instanceof WP_Post ) {
					continue;
				}

				// Prevent self-linking display
				if ( get_the_ID() === $student->ID ) {
					continue;
				}
				?>
				<article class="linked-student-card" style="border:1px solid #eee;padding:15px;text-align:center;">
					
					<?php if ( has_post_thumbnail( $student ) ) : ?>
						<div class="linked-student-image" style="margin-bottom:10px;">
							<?php echo get_the_post_thumbnail( $student, 'medium' ); ?>
						</div>
					<?php endif; ?>

					<h3 style="margin:10px 0 5px;">
						<a href="<?php echo esc_url( get_permalink( $student ) ); ?>">
							<?php echo esc_html( get_the_title( $student ) ); ?>
						</a>
					</h3>

				</article>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php
    endwhile;
endif;

get_footer();