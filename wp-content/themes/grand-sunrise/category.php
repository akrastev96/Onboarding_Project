<?php
/**
 * Category Archive using the Students grid template.
 */

get_header(); ?>

<main id="site-content" class="students-archive">

    <!-- CATEGORY FILTERS -->
    <div class="student-category-nav">
        <?php
        wp_list_categories( array(
            'taxonomy'   => 'category',
            'title_li'   => '',
            'hide_empty' => true,
        ) );
        ?>
    </div>

<?php
// Force student posts for this category archive.
$paged     = max( 1, get_query_var( 'paged' ) );
$term      = get_queried_object();
$term_id   = $term ? $term->term_id : 0;
$students_q = new WP_Query( array(
    'post_type'      => 'student',
    'posts_per_page' => 4,
    'paged'          => $paged,
    'cat'            => $term_id,
) );
?>

<?php if ( $students_q->have_posts() ) : ?>

        <div class="students-grid">

            <?php while ( $students_q->have_posts() ) : $students_q->the_post(); ?>

                <article class="student-card">

                    <!-- Image -->
                    <div class="student-thumb">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'large' ); ?>
                        </a>
                    </div>

                    <div class="student-info">

                        <!-- Categories -->
                        <div class="student-categories">
                            <?php the_category( ', ' ); ?>
                        </div>

                        <!-- Name -->
                        <h2 class="student-name">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>

                        <!-- Excerpt -->
                        <p class="student-excerpt"><?php echo get_the_excerpt(); ?></p>

                    </div>

                </article>

            <?php endwhile; ?>

        </div>

        <!-- PAGINATION -->
        <div class="student-pagination">
            <div class="pagination-numbers">
                <?php
                echo paginate_links( array(
                    'total'   => $students_q->max_num_pages,
                    'current' => $paged,
                    'type'    => 'plain',
                ) );
                ?>
            </div>
        </div>

<?php else : ?>
        <p><?php esc_html_e( 'No posts found.', 'grand-sunrise' ); ?></p>
<?php endif; wp_reset_postdata(); ?>

</main>

<?php get_footer(); ?>

