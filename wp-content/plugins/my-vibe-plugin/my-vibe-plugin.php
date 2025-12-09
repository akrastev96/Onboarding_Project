<?php
/**
 * Plugin Name: My Vibe Plugin
 * Description: Playground plugin for vibe coding with AI.
 * Version: 1.0.0
 * Author: AK
 */

add_action( 'init', 'mvp_register_movie_post_type' );

/**
 * Register the "Movie" custom post type.
 */
function mvp_register_movie_post_type() {
	$labels = array(
		'name'               => __( 'Movies', 'my-vibe-plugin' ),
		'singular_name'      => __( 'Movie', 'my-vibe-plugin' ),
		'add_new'            => __( 'Add New', 'my-vibe-plugin' ),
		'add_new_item'       => __( 'Add New Movie', 'my-vibe-plugin' ),
		'edit_item'          => __( 'Edit Movie', 'my-vibe-plugin' ),
		'new_item'           => __( 'New Movie', 'my-vibe-plugin' ),
		'view_item'          => __( 'View Movie', 'my-vibe-plugin' ),
		'view_items'         => __( 'View Movies', 'my-vibe-plugin' ),
		'search_items'       => __( 'Search Movies', 'my-vibe-plugin' ),
		'not_found'          => __( 'No movies found.', 'my-vibe-plugin' ),
		'not_found_in_trash' => __( 'No movies found in Trash.', 'my-vibe-plugin' ),
		'all_items'          => __( 'All Movies', 'my-vibe-plugin' ),
		'archives'           => __( 'Movie Archives', 'my-vibe-plugin' ),
		'attributes'         => __( 'Movie Attributes', 'my-vibe-plugin' ),
		'menu_name'          => __( 'Movies', 'my-vibe-plugin' ),
        'menu_icon'          => 'dashicons-video-alt2',
        'has archive'        => true,
        'public'             => true,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
	);

	$args = array(
		'labels'       => $labels,
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'menu_icon'    => 'dashicons-video-alt2',
	);

	register_post_type( 'movie', $args );
}