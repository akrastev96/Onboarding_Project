<?php
/**
 * Server-side render callback for the students block.
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function students_block_render_callback( $attributes ) {
	$number_of_students = isset( $attributes['numberOfStudents'] ) ? absint( $attributes['numberOfStudents'] ) : 4;
	$filter_by_status   = isset( $attributes['filterByStatus'] ) ? $attributes['filterByStatus'] : 'all'; // 'all', 'active', 'inactive'
	$show_single        = isset( $attributes['showSingle'] ) ? (bool) $attributes['showSingle'] : false;
	$student_id         = isset( $attributes['studentId'] ) ? absint( $attributes['studentId'] ) : 0;

	// Build query args.
	$args = array(
		'post_type'      => 'student',
		'posts_per_page' => $show_single ? 1 : $number_of_students,
		'post_status'    => 'publish',
	);

	// Filter by single student ID.
	if ( $show_single && $student_id > 0 ) {
		$args['p'] = $student_id;
	}

	// Filter by active/inactive status.
	if ( 'all' !== $filter_by_status ) {
		$args['meta_query'] = array(
			array(
				'key'   => '_student_active',
				'value' => 'active' === $filter_by_status ? 1 : 0,
			),
		);
	}

	$students_query = new WP_Query( $args );

	if ( ! $students_query->have_posts() ) {
		return '<p class="students-block-no-results">' . esc_html__( 'No students found.', 'students-block' ) . '</p>';
	}

	ob_start();
	?>

	<div class="students-block-wrapper">
		<div class="students-block-grid">
			<?php
			while ( $students_query->have_posts() ) :
				$students_query->the_post();
				?>
				<article class="students-block-card">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="students-block-thumb">
							<a href="<?php echo esc_url( get_permalink() ); ?>">
								<?php the_post_thumbnail( 'large' ); ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="students-block-info">
						<?php
						$cats = get_the_category();
						if ( ! empty( $cats ) ) :
							?>
							<div class="students-block-categories">
								<?php
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
						<?php endif; ?>

						<h2 class="students-block-name">
							<a href="<?php echo esc_url( get_permalink() ); ?>">
								<?php the_title(); ?>
							</a>
						</h2>

						<?php if ( has_excerpt() ) : ?>
							<p class="students-block-excerpt"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
						<?php endif; ?>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>

	<?php
	return ob_get_clean();
}

