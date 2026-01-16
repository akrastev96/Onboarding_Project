<?php
$posts_per_page = get_field( 'posts_per_page' ) ?: 4;

$args = array(
	'post_type'      => 'student',
	'post_status'    => 'publish',
	'posts_per_page' => $posts_per_page,
);

if ( $categories ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'student_category',
			'field'    => 'term_id',
			'terms'    => wp_list_pluck( $categories, 'term_id' ),
		),
	);
}

$query = new WP_Query( $args );

if ( ! $query->have_posts() ) {
	return;
}
?>

<section class="students-archive">
	<div class="students-grid">
		<?php while ( $query->have_posts() ) : $query->the_post(); ?>
			<article class="student-card">

				<div class="student-thumb">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail( 'large' ); ?>
					</a>
				</div>

				<div class="student-info">
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

					<h3 class="student-name">
						<a href="<?php the_permalink(); ?>">
							<?php the_title(); ?>
						</a>
					</h3>

					<div class="student-excerpt">
						<?php echo wp_trim_words( get_the_excerpt(), 18 ); ?>
					</div>
				</div>

			</article>
		<?php endwhile; ?>
	</div>
</section>

<?php wp_reset_postdata(); ?>