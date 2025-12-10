<?php
/**
 * Archive Template for Students
 */

get_header(); ?>

<main id="site-content" class="students-archive">

    <!-- CATEGORY FILTERS -->
    <div class="student-category-nav">
        <?php
        wp_list_categories(array(
            'taxonomy'   => 'category',
            'title_li'   => '',
            'hide_empty' => true,
        ));
        ?>
    </div>


    <?php
    // Custom Query: 4 Students per page
    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

    $args = array(
        'post_type'      => 'student',
        'posts_per_page' => 4,
        'paged'          => $paged,
    );

    $students_query = new WP_Query( $args );
    ?>

    <?php if ( $students_query->have_posts() ) : ?>

        <div class="students-grid">

            <?php while ( $students_query->have_posts() ) : $students_query->the_post(); ?>

                <article class="student-card">

                    <!-- Image -->
                    <div class="student-thumb">
                        <a href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail('large'); ?>
                        </a>
                    </div>

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

        <!-- CUSTOM PAGINATION -->
        <div class="student-pagination">

            <div class="pagination-numbers">
                <?php
                echo paginate_links(array(
                    'total'   => $students_query->max_num_pages,
                    'current' => $paged,
                    'type'    => 'plain'
                ));
                ?>
            </div>

            
        </div>

    <?php endif; wp_reset_postdata(); ?>

</main>

<?php get_footer(); ?>