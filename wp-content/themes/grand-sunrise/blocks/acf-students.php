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
			'taxonomy' => 'category',
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
					<?php the_post_thumbnail( 'large' ); ?>
				</div>

				<div class="student-info">
					<div class="student-categories">
						<?php echo esc_html( implode( ', ', wp_list_pluck( get_the_category(), 'name' ) ) ); ?>
					</div>

					<h3 class="student-name"><?php the_title(); ?></h3>

					<div class="student-excerpt">
						<?php echo wp_trim_words( get_the_excerpt(), 18 ); ?>
					</div>
				</div>

			</article>
		<?php endwhile; ?>
	</div>
</section>

<?php wp_reset_postdata(); ?>