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