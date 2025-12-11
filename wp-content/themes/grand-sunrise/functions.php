<?php
/**
 * Functions for the Grand Sunrise child theme
 */

/**
 * Student meta field definitions (for meta boxes, display, and settings).
 */
function student_meta_fields_definitions() {
    return array(
        'country'   => array(
            'meta_key' => '_student_country',
            'label'    => __( 'Country', 'grand-sunrise' ),
        ),
        'city'      => array(
            'meta_key' => '_student_city',
            'label'    => __( 'City', 'grand-sunrise' ),
        ),
        'address'   => array(
            'meta_key' => '_student_address',
            'label'    => __( 'Address', 'grand-sunrise' ),
        ),
        'birthdate' => array(
            'meta_key' => '_student_birthdate',
            'label'    => __( 'Birth Date', 'grand-sunrise' ),
        ),
        'class'     => array(
            'meta_key' => '_student_class',
            'label'    => __( 'Class / Grade', 'grand-sunrise' ),
        ),
        'active'    => array(
            'meta_key' => '_student_active',
            'label'    => __( 'Active Student', 'grand-sunrise' ),
        ),
    );
}

/**
 * Helper to check if a student meta field is set to be visible.
 * Defaults to visible when setting is absent.
 */
function student_meta_is_visible( $field_key ) {
    $settings = get_option( 'student_meta_visibility', array() );
    // Default to visible if not set.
    if ( ! is_array( $settings ) || ! array_key_exists( $field_key, $settings ) ) {
        return true;
    }
    return (bool) $settings[ $field_key ];
}

/**
 * Register top-level Students settings page.
 */
function student_register_settings_page() {
    // Create top-level admin menu.
    add_menu_page(
        __( 'Students Settings', 'grand-sunrise' ), // Page title
        __( 'Students', 'grand-sunrise' ),          // Menu title
        'manage_options',                           // Capability
        'student-settings',                         // Menu slug
        'student_render_settings_page',             // Callback function
        'dashicons-id',                             // Icon
        26                                          // Position
    );
}
add_action( 'admin_menu', 'student_register_settings_page' );

/**
 * Register settings/fields (visibility toggles).
 */
function student_register_settings() {
    register_setting(
        'student_settings_group',
        'student_meta_visibility',
        'student_sanitize_visibility_settings'
    );

    add_settings_section(
        'student_settings_section',
        __( 'Student Meta Visibility', 'grand-sunrise' ),
        function() {
            echo '<p>' . esc_html__( 'Choose which student fields appear on the single student page.', 'grand-sunrise' ) . '</p>';
        },
        'student-settings'
    );

    $fields = student_meta_fields_definitions();
    foreach ( $fields as $key => $field ) {
        add_settings_field(
            'student_meta_visibility_' . $key,
            esc_html( $field['label'] ),
            'student_render_visibility_checkbox',
            'student-settings',
            'student_settings_section',
            array(
                'field_key' => $key,
                'label'     => $field['label'],
            )
        );
    }
}
add_action( 'admin_init', 'student_register_settings' );

/**
 * Sanitize visibility settings.
 */
function student_sanitize_visibility_settings( $input ) {
    $fields     = student_meta_fields_definitions();
    $sanitized  = array();
    foreach ( $fields as $key => $field ) {
        $sanitized[ $key ] = ( isset( $input[ $key ] ) && '1' === (string) $input[ $key ] ) ? 1 : 0;
    }
    return $sanitized;
}

/**
 * Render a single visibility checkbox.
 */
function student_render_visibility_checkbox( $args ) {
    $settings  = get_option( 'student_meta_visibility', array() );
    $field_key = $args['field_key'];
    $checked   = isset( $settings[ $field_key ] ) ? (bool) $settings[ $field_key ] : true; // default visible
    ?>
    <label>
        <input type="checkbox" name="student_meta_visibility[<?php echo esc_attr( $field_key ); ?>]" value="1" <?php checked( $checked ); ?> />
        <?php esc_html_e( 'Show on single student page', 'grand-sunrise' ); ?>
    </label>
    <?php
}

/**
 * Render settings page markup.
 */
function student_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Students Settings', 'grand-sunrise' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'student_settings_group' );
            do_settings_sections( 'student-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

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


/**
 * Add meta boxes to Student CPT
 */
function student_add_meta_boxes() {
    add_meta_box(
        'student_info_metabox',
        __( 'Student Information', 'textdomain' ),
        'student_info_metabox_callback',
        'student',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'student_add_meta_boxes' );

/**
 * Metabox output
 */
function student_info_metabox_callback( $post ) {

    wp_nonce_field( 'student_info_nonce_action', 'student_info_nonce' );

    $country  = get_post_meta( $post->ID, '_student_country', true );
    $city     = get_post_meta( $post->ID, '_student_city', true );
    $address  = get_post_meta( $post->ID, '_student_address', true );
    $birth    = get_post_meta( $post->ID, '_student_birthdate', true );
    $class    = get_post_meta( $post->ID, '_student_class', true );
    $active   = get_post_meta( $post->ID, '_student_active', true );
    ?>

    <p>
        <label><strong>Country:</strong></label><br>
        <input type="text" name="student_country" value="<?php echo esc_attr( $country ); ?>" class="widefat">
    </p>

    <p>
        <label><strong>City:</strong></label><br>
        <input type="text" name="student_city" value="<?php echo esc_attr( $city ); ?>" class="widefat">
    </p>

    <p>
        <label><strong>Address:</strong></label><br>
        <input type="text" name="student_address" value="<?php echo esc_attr( $address ); ?>" class="widefat">
    </p>

    <p>
        <label><strong>Birth Date:</strong></label><br>
        <input type="date" name="student_birthdate" value="<?php echo esc_attr( $birth ); ?>">
    </p>

    <p>
        <label><strong>Class / Grade:</strong></label><br>
        <input type="text" name="student_class" value="<?php echo esc_attr( $class ); ?>" class="widefat">
    </p>

    <p>
        <label>
            <input type="checkbox" name="student_active" value="1" <?php checked( $active, 1 ); ?>>
            Active Student
        </label>
    </p>

    <?php
}

/**
 * Save Student Meta
 */
function student_save_meta( $post_id ) {

    // Verify nonce
    if ( ! isset( $_POST['student_info_nonce'] ) || 
         ! wp_verify_nonce( $_POST['student_info_nonce'], 'student_info_nonce_action' ) ) {
        return;
    }

    // Prevent autosave overwrite
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check permissions
    if ( isset( $_POST['post_type'] ) && 'student' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // Save fields
    $fields = [
        'student_country'   => '_student_country',
        'student_city'      => '_student_city',
        'student_address'   => '_student_address',
        'student_birthdate' => '_student_birthdate',
        'student_class'     => '_student_class',
    ];

    foreach ( $fields as $input => $meta_key ) {
        if ( isset( $_POST[ $input ] ) ) {
            $value = sanitize_text_field( wp_unslash( $_POST[ $input ] ) );
            update_post_meta( $post_id, $meta_key, $value );
        }
    }

    // Checkbox — save 1 or 0
    $active = isset( $_POST['student_active'] ) ? 1 : 0;
    update_post_meta( $post_id, '_student_active', $active );
}
add_action( 'save_post', 'student_save_meta' );