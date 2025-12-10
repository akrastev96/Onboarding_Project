<?php
/**
 * Functions for the Grand Sunrise child theme
 */

function grand_sunrise_enqueue_styles() {
    wp_enqueue_style(
        'grand-sunrise-style',
        get_stylesheet_uri()
    );
}
add_action( 'wp_enqueue_scripts', 'grand_sunrise_enqueue_styles' );

/**
 * Prepend “This is my filter” to post content on singular posts.
 */

 function gs_prepend_filter_text( $content ) {
    if ( is_singular( 'post' ) ) {
        $text = apply_filters(
            'gs_prepend_filter_text',
            esc_html__( 'This is my filter', 'grand-sunrise' )
        );
        $content = $text . ' ' . $content;
    }
    return $content;
}
add_filter( 'the_content', 'gs_prepend_filter_text', 10 );

/**
 * Override the prepended filter text using the custom filter.
 */
function gs_extend_prepend_filter_text( $text ) {
    return esc_html__( 'This is my extendable filter', 'grand-sunrise' );
}
add_filter( 'gs_prepend_filter_text', 'gs_extend_prepend_filter_text', 10 );

/**
 * Append <div>Two</div> at the bottom of post content.
 */
function gs_append_two( $content ) {
    if ( is_singular( 'post' ) ) {
        $content .= '<div>Two</div>';
    }
    return $content;
}
add_filter( 'the_content', 'gs_append_two', 20 );

/**
 * Insert <div>One</div> BEFORE the “Two” div.
 * Must be added AFTER the Two filter.
 */
function gs_insert_one_before_two( $content ) {
    if ( is_singular( 'post' ) ) {
        $content = str_replace(
            '<div>Two</div>',
            '<div>One</div><div>Two</div>',
            $content
        );
    }
    return $content;
}
add_filter( 'the_content', 'gs_insert_one_before_two', 25 );

/**
 * Insert <div>Three</div> AFTER the “Two” div.
 * Must be added AFTER the Two filter.
 */
function gs_insert_three_after_two( $content ) {
    if ( is_singular( 'post' ) ) {
        $content = str_replace(
            '<div>Two</div>',
            '<div>Two</div><div>Three</div>',
            $content
        );
    }
    return $content;
}
add_filter( 'the_content', 'gs_insert_three_after_two', 30 );


/**
 * Add "Profile settings" as the last item in the Navigation block
 * only for logged-in users, and only if it does not already exist.
 */
function gs_add_profile_settings_nav_item( $block_content, $block ) {

    // Only target the Navigation block and only for logged-in users.
    if ( 'core/navigation' === $block['blockName'] && is_user_logged_in() ) {

        // If a "Profile settings" link is already present, do nothing.
        if ( false !== strpos( $block_content, 'Profile settings' ) ) {
            return $block_content;
        }

        $profile_url = esc_url( admin_url( 'profile.php' ) );
        $new_item    = '<li class="wp-block-navigation-item"><a href="' . $profile_url . '">Profile settings</a></li>';
        
        $last_ul_pos = strrpos( $block_content, '</ul>' );
        if ( false !== $last_ul_pos ) {
            $block_content = substr_replace( $block_content, $new_item . '</ul>', $last_ul_pos, strlen( '</ul>' ) );
        }

    }

    return $block_content;
}
add_filter( 'render_block', 'gs_add_profile_settings_nav_item', 10, 2 );

/**
 * Send email to admin when a user updates their profile.
 */
function gs_notify_admin_on_profile_update( $user_id, $old_user_data ) {
    $admin_email = get_option( 'admin_email' );
    $user        = get_userdata( $user_id );
    
    if ( ! $admin_email || ! $user ) {
        return;
    }
    
    $subject = sprintf( 'Profile Updated: %s', $user->display_name );
    $message = sprintf(
        "A user has updated their profile.\n\nUser: %s (%s)\nUser ID: %d\nUpdated: %s",
        $user->display_name,
        $user->user_email,
        $user_id,
        current_time( 'mysql' )
    );
    
    wp_mail( $admin_email, $subject, $message );
}
add_action( 'profile_update', 'gs_notify_admin_on_profile_update', 10, 2 );

/**
 * Example callback for the custom template action.
 * Runs after the content in the custom page template
 * checks which page template is being used and appends a note after the content.
 */
function gs_append_after_content_on_custom_template( $content ) {
    if ( ! is_singular() ) {
        return $content;
    }

    $template_slug = get_page_template_slug( get_queried_object_id() );
    $matches_template = in_array(
        $template_slug,
        array(
            'my-custom-template',
            'my-custom-template.html',
            'templates/my-custom-template.html',
        ),
        true
    );

    if ( $matches_template ) {
        $content .= '<p>This runs after the content via a custom action hook.</p>';
    }

    return $content;
}
add_filter( 'the_content', 'gs_append_after_content_on_custom_template', 999 );

/**
 * Ensure student archives paginate correctly by adjusting the main query.
 * If the main query has no posts on page 2+, WordPress returns 404 even
 * if a custom query renders results. This aligns the main query with the
 * intended page size.
 */
function gs_student_archive_pagination_fix( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }

    // Student archive pagination.
    if ( $query->is_post_type_archive( 'student' ) ) {
        $query->set( 'posts_per_page', 4 );
    }

    // Category archives: only adjust when explicitly requesting student posts.
    if ( $query->is_category() ) {
        $post_type = $query->get( 'post_type' );
        $has_student = ( 'student' === $post_type ) || ( is_array( $post_type ) && in_array( 'student', $post_type, true ) );

        if ( $has_student ) {
            $query->set( 'post_type', array( 'student' ) );
            $query->set( 'posts_per_page', 4 );
        }
    }
}
add_action( 'pre_get_posts', 'gs_student_archive_pagination_fix' );

/**
 * Append post_type=student to category links when in student context
 * (student archive or student category view) so category browsing stays
 * within student posts only.
 */
function gs_student_category_link( $termlink, $term, $taxonomy ) {
    if ( 'category' !== $taxonomy ) {
        return $termlink;
    }

    // Detect student context.
    $post_type_qv = get_query_var( 'post_type' );
    $in_student_context =
        is_post_type_archive( 'student' ) ||
        ( is_category() && ( 'student' === $post_type_qv || ( is_array( $post_type_qv ) && in_array( 'student', $post_type_qv, true ) ) ) );

    if ( $in_student_context ) {
        
    }

    return $termlink;
}
add_filter( 'term_link', 'gs_student_category_link', 10, 3 );