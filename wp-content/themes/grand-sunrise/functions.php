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
 * Register Dictionary menu page.
 */
function dictionary_register_menu_page() {
    add_menu_page(
        __( 'Dictionary', 'grand-sunrise' ),
        __( 'Dictionary', 'grand-sunrise' ),
        'read',
        'dictionary',
        'dictionary_render_page',
        'dashicons-book-alt',
        27 // Position right below Students (26)
    );
}
add_action( 'admin_menu', 'dictionary_register_menu_page' );

/**
 * Render Dictionary page.
 */
function dictionary_render_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Oxford Dictionary', 'grand-sunrise' ); ?></h1>
        <p><?php esc_html_e( 'Search for word definitions using Oxford Learner\'s Dictionary.', 'grand-sunrise' ); ?></p>
        
        <div id="dictionary-search-container">
            <form id="dictionary-search-form">
                <p>
                    <label for="dictionary-word-input">
                        <strong><?php esc_html_e( 'Search for a word:', 'grand-sunrise' ); ?></strong>
                    </label>
                </p>
                <p>
                    <input 
                        type="text" 
                        id="dictionary-word-input" 
                        name="word" 
                        class="regular-text" 
                        placeholder="<?php esc_attr_e( 'e.g., fidget spinner', 'grand-sunrise' ); ?>"
                        required
                    />
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Search', 'grand-sunrise' ); ?>
                    </button>
                </p>
            </form>
            
            <?php
            $last_transient = get_option( 'dictionary_last_transient' );
            $cached_result  = $last_transient ? get_transient( $last_transient ) : false;
            ?>
            <div id="dictionary-results" style="margin-top: 20px;">
                <?php
                if ( $cached_result && isset( $cached_result['html'] ) ) {
                    echo $cached_result['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
                ?>
            </div>
            <div id="dictionary-loading" style="display: none; margin-top: 20px;">
                <span class="spinner is-active" style="float: none;"></span>
                <?php esc_html_e( 'Searching...', 'grand-sunrise' ); ?>
            </div>
        </div>
        <hr />
        <form method="post" action="options.php">
            <?php
            settings_fields( 'dictionary_settings_group' );
            do_settings_sections( 'dictionary-settings' );
            submit_button( __( 'Save Cache Settings', 'grand-sunrise' ) );
            ?>
        </form>
    </div>
    <?php
}

/**
 * Register dictionary cache duration setting.
 */
function dictionary_register_settings() {

    register_setting(
        'dictionary_settings_group',
        'dictionary_cache_duration',
        array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => HOUR_IN_SECONDS,
        )
    );

    add_settings_section(
        'dictionary_settings_section',
        __( 'Dictionary Cache Settings', 'grand-sunrise' ),
        '__return_false',
        'dictionary-settings'
    );

    add_settings_field(
        'dictionary_cache_duration',
        __( 'Cache duration', 'grand-sunrise' ),
        'dictionary_render_cache_duration_field',
        'dictionary-settings',
        'dictionary_settings_section'
    );
}
add_action( 'admin_init', 'dictionary_register_settings' );

/**
 * Render cache duration select field.
 */
function dictionary_render_cache_duration_field() {
    $value = get_option( 'dictionary_cache_duration', HOUR_IN_SECONDS );
    ?>
    <select name="dictionary_cache_duration">
        <option value="<?php echo esc_attr( 10 ); ?>" <?php selected( $value, 10 ); ?>>
            <?php esc_html_e( '10 seconds (testing)', 'grand-sunrise' ); ?>
        </option>
        <option value="<?php echo esc_attr( HOUR_IN_SECONDS ); ?>" <?php selected( $value, HOUR_IN_SECONDS ); ?>>
            <?php esc_html_e( '1 hour', 'grand-sunrise' ); ?>
        </option>
    </select>
    <?php
}

/**
 * Enqueue dictionary admin scripts.
 */
function dictionary_enqueue_admin_scripts( $hook ) {
    // Only load on dictionary page.
    if ( 'toplevel_page_dictionary' !== $hook ) {
        return;
    }

    wp_enqueue_script(
        'dictionary-admin',
        get_stylesheet_directory_uri() . '/js/dictionary-admin.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );

    wp_localize_script(
        'dictionary-admin',
        'dictionaryAdmin',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'dictionary_search_nonce' ),
        )
    );
}
add_action( 'admin_enqueue_scripts', 'dictionary_enqueue_admin_scripts' );

/**
 * AJAX handler to fetch Oxford dictionary content.
 */
function dictionary_ajax_search() {
    // Verify nonce.
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dictionary_search_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'grand-sunrise' ) ) );
    }

    // Check permissions.
    if ( ! current_user_can( 'read' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'grand-sunrise' ) ) );
    }

    // Get and sanitize search term.
    $word = isset( $_POST['word'] ) ? sanitize_text_field( wp_unslash( $_POST['word'] ) ) : '';
    $transient_key = 'dictionary_result_' . md5( strtolower( $word ) );
    $cache_duration = get_option( 'dictionary_cache_duration', HOUR_IN_SECONDS );

    // Return cached result if it exists.
    $cached = get_transient( $transient_key );
    if ( false !== $cached ) {
        // Remember this as the last searched result.
        update_option( 'dictionary_last_transient', $transient_key );
        wp_send_json_success( $cached );
    }
    if ( empty( $word ) ) {
        wp_send_json_error( array( 'message' => __( 'Please enter a word to search.', 'grand-sunrise' ) ) );
    }

    // Build Oxford dictionary URL.
    $word_slug = strtolower( trim( $word ) );
    $word_slug = preg_replace( '/[^a-z0-9-]/', '-', $word_slug );
    $word_slug = preg_replace( '/-+/', '-', $word_slug );
    $word_slug = trim( $word_slug, '-' );
    
    $oxford_url = 'https://www.oxfordlearnersdictionaries.com/definition/english/' . urlencode( $word_slug );

    // Fetch content from Oxford.
    $response = wp_remote_get(
        $oxford_url,
        array(
            'timeout'     => 15,
            'user-agent'  => 'Mozilla/5.0 (compatible; WordPress Dictionary Plugin)',
            'sslverify'   => true,
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => __( 'Failed to fetch dictionary content. Please try again.', 'grand-sunrise' ) ) );
    }

    $body = wp_remote_retrieve_body( $response );
    $status_code = wp_remote_retrieve_response_code( $response );

    if ( 200 !== $status_code ) {
        wp_send_json_error( array( 'message' => sprintf( __( 'Word not found. Status code: %d', 'grand-sunrise' ), $status_code ) ) );
    }

    // Parse HTML to extract definition content.
    if ( ! class_exists( 'DOMDocument' ) ) {
        wp_send_json_error( array( 'message' => __( 'DOMDocument not available.', 'grand-sunrise' ) ) );
    }

    libxml_use_internal_errors( true );
    $dom = new DOMDocument();
    @$dom->loadHTML( '<?xml encoding="UTF-8">' . $body );
    libxml_clear_errors();

    $xpath = new DOMXPath( $dom );

    // Extract word heading.
    $word_heading = '';
    $heading_nodes = $xpath->query( '//h1[contains(@class, "headword")] | //span[contains(@class, "headword")] | //h1[@class="headword"]' );
    if ( $heading_nodes->length > 0 ) {
        $word_heading = trim( $heading_nodes->item( 0 )->textContent );
    }
    if ( empty( $word_heading ) ) {
        $word_heading = $word;
    }

    // Extract part of speech.
    $part_of_speech = '';
    $pos_nodes = $xpath->query( '//span[contains(@class, "pos")] | //span[@class="pos"] | //div[contains(@class, "pos")]' );
    if ( $pos_nodes->length > 0 ) {
        $part_of_speech = trim( $pos_nodes->item( 0 )->textContent );
    }

    // Extract phonetic transcription (IPA only, ignore navigation / UI text).
    $phonetic = '';
    $phon_nodes = $xpath->query( '//span[contains(concat(" ", normalize-space(@class), " "), " phon ")]' );

    if ( $phon_nodes->length > 0 ) {
        foreach ( $phon_nodes as $node ) {
            $text = trim( $node->textContent );

            // Real IPA usually contains slashes or IPA symbols.
            if ( preg_match( '/^\/.*\/$/u', $text ) || preg_match( '/[ˈˌəɪʊɔɛæ]/u', $text ) ) {
                $phonetic = esc_html( $text );
                break;
            }
        }
    }

    // Extract main definition content - exclude navigation areas.
    $definitions = array();
    
    // First, try to find the main entry content area and exclude navigation.
    $main_entry = $xpath->query( '//div[contains(@class, "entry")] | //div[@id="entryContent"] | //div[contains(@class, "webtop")]' );
    
    // Try to find definition sections within the main content, excluding nav areas.
    $def_nodes = $xpath->query( '//div[contains(@class, "def")][not(ancestor::nav)][not(ancestor::header)][not(ancestor::*[contains(@class, "nav")])] | //span[contains(@class, "def")][not(ancestor::nav)][not(ancestor::header)][not(ancestor::*[contains(@class, "nav")])]' );
    
    if ( $def_nodes->length > 0 ) {
        foreach ( $def_nodes as $node ) {
            $text = trim( $node->textContent );
            
            // Filter out navigation-related content.
            $is_navigation = false;
            $nav_keywords = array( 'toggle navigation', 'dictionaries home', 'grammar home', 'word lists home', 'resources home', 'text checker', 'academic collocations', 'german-english', 'american english', 'practical english usage', 'learn & practise' );
            $text_lower = strtolower( $text );
            foreach ( $nav_keywords as $keyword ) {
                if ( strpos( $text_lower, $keyword ) !== false ) {
                    $is_navigation = true;
                    break;
                }
            }
            
            // Only include if it's a real definition (long enough and not navigation).
            if ( ! $is_navigation && ! empty( $text ) && strlen( $text ) > 30 ) {
                // Additional check: definitions usually contain complete sentences.
                if ( preg_match( '/[.!?]/', $text ) || strlen( $text ) > 50 ) {
                    $definitions[] = esc_html( $text );
                }
            }
        }
    }
    
    // If no good definitions found, try more specific selectors.
    if ( empty( $definitions ) ) {
        $def_nodes = $xpath->query( '//span[@class="def"] | //div[@class="def"]' );
        if ( $def_nodes->length > 0 ) {
            foreach ( $def_nodes as $node ) {
                $text = trim( $node->textContent );
                // Filter navigation content.
                $text_lower = strtolower( $text );
                $is_nav = false;
                foreach ( array( 'navigation', 'dictionaries', 'grammar', 'word lists', 'resources' ) as $nav_term ) {
                    if ( strpos( $text_lower, $nav_term ) !== false && strlen( $text ) < 100 ) {
                        $is_nav = true;
                        break;
                    }
                }
                if ( ! $is_nav && ! empty( $text ) && strlen( $text ) > 30 ) {
                    $definitions[] = esc_html( $text );
                }
            }
        }
    }

    // If no definitions found, try to get the main content area.
    if ( empty( $definitions ) ) {
        $main_content = $xpath->query( '//main | //div[@id="main-content"] | //div[contains(@class, "entry")]' );
        if ( $main_content->length > 0 ) {
            $content_html = $dom->saveHTML( $main_content->item( 0 ) );
            // Strip scripts and styles for security.
            $content_html = preg_replace( '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content_html );
            $content_html = preg_replace( '/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $content_html );
            $definitions[] = wp_kses_post( $content_html );
        }
    }

    // Build HTML output.
    $html = '<div class="dictionary-result" style="max-width: 800px;">';
    
    // Word heading with part of speech and pronunciation.
    $html .= '<div style="margin-bottom: 15px;">';
    $html .= '<h2 style="font-size: 28px; font-weight: bold; color: #1a1a1a; margin: 0 0 5px 0;">' . esc_html( $word_heading ) . '</h2>';

    if ( ! empty( $part_of_speech ) ) {
        $html .= '<span style="font-style: italic; color: #666; font-size: 16px; margin-right: 10px;">' . esc_html( $part_of_speech ) . '</span>';
    }

    if ( ! empty( $phonetic ) ) {
        $html .= '<div style="margin-top: 6px; font-size: 16px; color: #333;">';
        $html .= '<span>' . esc_html( $phonetic ) . '</span>';
        $html .= '</div>';
    }

    $html .= '</div>';

    // Definitions.
    if ( ! empty( $definitions ) ) {
        $html .= '<div class="dictionary-definitions" style="margin-top: 20px;">';
        foreach ( $definitions as $index => $def ) {
            $html .= '<div class="dictionary-definition-item" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">';
            $html .= '<p style="margin: 0; line-height: 1.6;">' . $def . '</p>';
            $html .= '</div>';
        }
        $html .= '</div>';
    }

    // Link to full definition.
    $html .= '<p style="margin-top: 20px;">';
    $html .= '<a href="' . esc_url( $oxford_url ) . '" target="_blank" style="color: #0073aa;">';
    $html .= esc_html__( 'View full definition on Oxford Learner\'s Dictionary', 'grand-sunrise' );
    $html .= '</a>';
    $html .= '</p>';

    $html .= '</div>';

    // If no content found, show fallback.
    if ( empty( $definitions ) && empty( $phonetic ) ) {
        $html = '<div class="dictionary-result">';
        $html .= '<p>' . sprintf( __( 'Word found! <a href="%s" target="_blank">View definition on Oxford Learner\'s Dictionary</a>', 'grand-sunrise' ), esc_url( $oxford_url ) ) . '</p>';
        $html .= '</div>';
    }

    $result = array(
        'html'       => $html,
        'word'       => esc_html( $word ),
        'oxford_url' => esc_url( $oxford_url ),
    );

    // Cache result.
    set_transient( $transient_key, $result, $cache_duration );
    update_option( 'dictionary_last_transient', $transient_key );

    wp_send_json_success( $result );
}
add_action( 'wp_ajax_dictionary_search', 'dictionary_ajax_search' );

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

    // AJAX-powered settings submenu.
    add_submenu_page(
        'student-settings',
        __( 'Students AJAX Settings', 'grand-sunrise' ),
        __( 'AJAX Settings', 'grand-sunrise' ),
        'manage_options',
        'student-ajax-settings',
        'student_render_ajax_settings_page'
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

/**
 * Enqueue assets for AJAX settings page.
 */
function student_ajax_settings_assets( $hook ) {
    // Only load on our AJAX settings page.
    $is_ajax_settings = ( isset( $_GET['page'] ) && 'student-ajax-settings' === $_GET['page'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( ! $is_ajax_settings ) {
        return;
    }

    wp_enqueue_script(
        'student-ajax-settings',
        get_stylesheet_directory_uri() . '/js/student-ajax-settings.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );

    wp_localize_script(
        'student-ajax-settings',
        'studentAjaxSettings',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'student_ajax_settings_nonce' ),
        )
    );
}
add_action( 'admin_enqueue_scripts', 'student_ajax_settings_assets' );

/**
 * AJAX handler to update a single visibility field.
 */
function student_ajax_update_visibility() {
    check_ajax_referer( 'student_ajax_settings_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission denied.', 'grand-sunrise' ) ), 403 );
    }

    $field_key = isset( $_POST['field'] ) ? sanitize_key( wp_unslash( $_POST['field'] ) ) : '';
    $value     = isset( $_POST['value'] ) ? (int) $_POST['value'] : 0;

    $fields = student_meta_fields_definitions();
    if ( ! isset( $fields[ $field_key ] ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid field.', 'grand-sunrise' ) ), 400 );
    }

    $settings              = get_option( 'student_meta_visibility', array() );
    $settings[ $field_key ] = $value ? 1 : 0;
    update_option( 'student_meta_visibility', $settings );

    wp_send_json_success( array( 'field' => $field_key, 'value' => $settings[ $field_key ] ) );
}
add_action( 'wp_ajax_student_update_visibility', 'student_ajax_update_visibility' );

/**
 * Render AJAX settings page.
 */
function student_render_ajax_settings_page() {
    $fields   = student_meta_fields_definitions();
    $settings = get_option( 'student_meta_visibility', array() );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Students AJAX Settings', 'grand-sunrise' ); ?></h1>
        <p><?php esc_html_e( 'Toggle the visibility of each student field. Changes save automatically.', 'grand-sunrise' ); ?></p>
        <div id="student-ajax-settings">
            <?php foreach ( $fields as $key => $field ) :
                $checked = isset( $settings[ $key ] ) ? (bool) $settings[ $key ] : true;
                ?>
                <p>
                    <label>
                        <input
                            type="checkbox"
                            class="student-ajax-setting"
                            data-field="<?php echo esc_attr( $key ); ?>"
                            <?php checked( $checked ); ?>
                        />
                        <?php echo esc_html( $field['label'] ); ?>
                    </label>
                </p>
            <?php endforeach; ?>
        </div>
        <div class="student-ajax-status" style="margin-top: 10px;"></div>
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

/**
 * Add "Active" column to students list in admin.
 */
function student_add_active_column( $columns ) {
    $columns['student_active'] = __( 'Active', 'grand-sunrise' );
    return $columns;
}
add_filter( 'manage_student_posts_columns', 'student_add_active_column' );

/**
 * Populate the "Active" column with checkboxes.
 */
function student_render_active_column( $column, $post_id ) {
    if ( 'student_active' !== $column ) {
        return;
    }

    $active = (int) get_post_meta( $post_id, '_student_active', true );
    $checked = ( 1 === $active ) ? 'checked' : '';
    ?>
    <input 
        type="checkbox" 
        class="student-active-toggle" 
        data-post-id="<?php echo esc_attr( $post_id ); ?>" 
        <?php echo esc_attr( $checked ); ?> 
        aria-label="<?php esc_attr_e( 'Toggle student active status', 'grand-sunrise' ); ?>"
    />
    <?php
}
add_action( 'manage_student_posts_custom_column', 'student_render_active_column', 10, 2 );

/**
 * Enqueue admin scripts for AJAX functionality.
 */
function student_enqueue_admin_scripts( $hook ) {
    // Only load on edit.php for student post type.
    if ( 'edit.php' !== $hook ) {
        return;
    }

    $screen = get_current_screen();
    if ( ! $screen || 'student' !== $screen->post_type ) {
        return;
    }

    wp_enqueue_script(
        'student-admin-ajax',
        get_stylesheet_directory_uri() . '/js/student-admin-ajax.js',
        array( 'jquery' ),
        '1.0.0',
        true
    );

    wp_localize_script(
        'student-admin-ajax',
        'studentAdminAjax',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'student_toggle_active_nonce' ),
        )
    );
}
add_action( 'admin_enqueue_scripts', 'student_enqueue_admin_scripts' );

/**
 * AJAX handler to toggle student active status.
 */
function student_ajax_toggle_active() {
    // Verify nonce.
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'student_toggle_active_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'grand-sunrise' ) ) );
    }

    // Check permissions.
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'grand-sunrise' ) ) );
    }

    // Get and sanitize post ID.
    $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
    if ( ! $post_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid post ID.', 'grand-sunrise' ) ) );
    }

    // Verify it's a student post.
    if ( 'student' !== get_post_type( $post_id ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid post type.', 'grand-sunrise' ) ) );
    }

    // Get and sanitize active status.
    $active = isset( $_POST['active'] ) ? absint( $_POST['active'] ) : 0;
    $active = ( 1 === $active ) ? 1 : 0;

    // Update post meta.
    update_post_meta( $post_id, '_student_active', $active );

    wp_send_json_success(
        array(
            'message' => $active ? __( 'Student activated.', 'grand-sunrise' ) : __( 'Student deactivated.', 'grand-sunrise' ),
            'active'  => $active,
        )
    );
}
add_action( 'wp_ajax_student_toggle_active', 'student_ajax_toggle_active' );





/**
 * Shortcode: [students_list count="3" infinite-scroll="true"]
 */
function gs_students_list_shortcode( $atts ) {

    $atts = shortcode_atts(
        array(
            'count'           => 3,
            'id'              => 0,
            'infinite-scroll' => 'false',
        ),
        $atts,
        'students_list'
    );

    $count           = absint( $atts['count'] );
    $student_id      = absint( $atts['id'] );
    // Check if infinite scroll is enabled (string 'true' or boolean true)
    $infinite_scroll = ( 'true' === $atts['infinite-scroll'] || true === $atts['infinite-scroll'] );

    $args = array(
        'post_type'   => 'student',
        'post_status' => 'publish',
    );

    if ( $student_id > 0 ) {
        $args['p']              = $student_id;
        $args['posts_per_page'] = 1;
        $infinite_scroll        = false; 
    } else {
        if ( $count <= 0 ) {
            return '';
        }
        $args['posts_per_page'] = -1;
    }

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '<p>No students found.</p>';
    }

    $uid = uniqid( 'students_toggle_' );

    ob_start();

    // SCENARIO 1: Standard "Show More" Button (CSS Only)
    // Only output the CSS-toggle styles if Infinite Scroll is OFF
    if ( ! $infinite_scroll ) :
        ?>
        <style>
            #<?php echo esc_attr( $uid ); ?>:not(:checked) ~ .students-shortcode-list .student-card:nth-child(n + <?php echo esc_attr( $count + 1 ); ?>) {
                display: none;
            }
            #<?php echo esc_attr( $uid ); ?>:not(:checked) ~ .students-toggle-label .show-less-text {
                display: none;
            }
            #<?php echo esc_attr( $uid ); ?>:checked ~ .students-toggle-label .show-more-text {
                display: none;
            }
        </style>
        <input type="checkbox" id="<?php echo esc_attr( $uid ); ?>" hidden>
    <?php endif; ?>

    <div class="students-shortcode-list" id="<?php echo esc_attr( $uid ); ?>_list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;">
        <?php
        $current_index = 0;
        while ( $query->have_posts() ) :
            $query->the_post();
            $class = get_post_meta( get_the_ID(), '_student_class', true );
            
            // Logic for Infinite Scroll:
            // If Infinite Scroll is ON, and we are past the count, hide the item and add a specific class
            $style_attr = 'border:1px solid #eee;padding:15px;text-align:center;';
            $item_class = 'student-card';

            if ( $infinite_scroll && $current_index >= $count ) {
                $style_attr .= 'display:none;';
                $item_class .= ' gs-infinite-hidden'; // Marker class for JS
            }
            ?>
            <div class="<?php echo esc_attr( $item_class ); ?>" style="<?php echo esc_attr( $style_attr ); ?>">
                <div class="student-image" style="margin-bottom:10px;">
                    <?php
                    if ( has_post_thumbnail() ) {
                        the_post_thumbnail( 'medium', array( 'style' => 'max-width:100%;height:auto;' ) );
                    }
                    ?>
                </div>

                <h3 class="student-name" style="margin:10px 0 5px;">
                    <?php the_title(); ?>
                </h3>

                <?php if ( ! empty( $class ) ) : ?>
                    <div class="student-class" style="font-size:14px;color:#666;">
                        <?php echo esc_html( $class ); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php 
            $current_index++;
        endwhile; 
        ?>
    </div>

    <?php 
    // SCENARIO 1: Standard "Show More" Button UI
    if ( ! $infinite_scroll && 0 === $student_id && $query->post_count > $count ) : 
        ?>
        <div class="students-toggle-label" style="text-align:center;margin-top:40px;">
            <label for="<?php echo esc_attr( $uid ); ?>" class="students-toggle-label" style="cursor:pointer;">
                <span class="show-more-text">Show more</span>
                <span class="show-less-text">Show less</span>
            </label>
        </div>
    <?php endif; ?>

    <?php 
    // SCENARIO 2: Infinite Scroll Logic (JS)
    if ( $infinite_scroll && $query->post_count > $count ) : 
        ?>
        <div id="<?php echo esc_attr( $uid ); ?>_sentinel" style="height: 20px; width: 100%;"></div>

        <script>
        (function() {
            var sentinel = document.getElementById('<?php echo esc_js( $uid ); ?>_sentinel');
            var batchSize = <?php echo intval( $count ); ?>; // How many to reveal at once
            
            // Intersection Observer configuration
            var observer = new IntersectionObserver(function(entries) {
                if(entries[0].isIntersecting === true) {
                    
                    // Find all currently hidden items within this specific list
                    var list = document.getElementById('<?php echo esc_js( $uid ); ?>_list');
                    var hiddenItems = list.querySelectorAll('.gs-infinite-hidden');

                    if (hiddenItems.length > 0) {
                        // Reveal the next batch
                        for (var i = 0; i < batchSize; i++) {
                            if (hiddenItems[i]) {
                                hiddenItems[i].style.display = 'block'; // Or 'revert' depending on grid
                                hiddenItems[i].classList.remove('gs-infinite-hidden');
                            }
                        }
                    } else {
                        // No more items to show, disconnect observer
                        observer.disconnect();
                        sentinel.style.display = 'none';
                    }
                }
            }, { threshold: [0] });

            observer.observe(sentinel);
        })();
        </script>
    <?php endif; ?>

    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'students_list', 'gs_students_list_shortcode' );