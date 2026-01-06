<?php
/**
 * Plugin Name:       Students Block
 * Plugin URI:        https://devrix.com
 * Description:       A Gutenberg block to display students with filtering options.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            DevriX
 * Author URI:        https://devrix.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       students-block
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin constants.
 */
define( 'STUDENTS_BLOCK_VERSION', '1.0.0' );
define( 'STUDENTS_BLOCK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'STUDENTS_BLOCK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Include render callback.
 */
require_once STUDENTS_BLOCK_PLUGIN_DIR . 'src/render.php';

/**
 * Register the block.
 */
function students_block_register_block() {
	$block_json_path = STUDENTS_BLOCK_PLUGIN_DIR . 'build/block.json';
	
	// If build directory exists, use it. Otherwise, register manually.
	if ( file_exists( $block_json_path ) ) {
		register_block_type(
			STUDENTS_BLOCK_PLUGIN_DIR . 'build',
			array(
				'render_callback' => 'students_block_render_callback',
			)
		);
	} else {
		// Fallback: register block manually from src.
		register_block_type(
			'students-block/students',
			array(
				'editor_script'   => 'students-block-editor',
				'editor_style'    => 'students-block-editor',
				'style'           => 'students-block-frontend',
				'render_callback' => 'students_block_render_callback',
				'attributes'      => array(
					'numberOfStudents' => array(
						'type'    => 'number',
						'default' => 4,
					),
					'filterByStatus'   => array(
						'type'    => 'string',
						'default' => 'all',
					),
					'showSingle'       => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'studentId'        => array(
						'type'    => 'number',
						'default' => 0,
					),
				),
			)
		);
	}
}
add_action( 'init', 'students_block_register_block' );

/**
 * Enqueue block editor assets.
 */
function students_block_enqueue_editor_assets() {
	$asset_file_path = STUDENTS_BLOCK_PLUGIN_DIR . 'build/index.asset.php';
	
	if ( file_exists( $asset_file_path ) ) {
		$asset_file = include $asset_file_path;
		$dependencies = $asset_file['dependencies'];
		$version      = $asset_file['version'];
	} else {
		// Fallback dependencies if build doesn't exist.
		$dependencies = array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
			'wp-editor',
			'wp-components',
			'wp-server-side-render',
			'wp-data',
		);
		$version = STUDENTS_BLOCK_VERSION;
	}

	wp_enqueue_script(
		'students-block-editor',
		file_exists( STUDENTS_BLOCK_PLUGIN_DIR . 'build/index.js' ) 
			? STUDENTS_BLOCK_PLUGIN_URL . 'build/index.js'
			: STUDENTS_BLOCK_PLUGIN_URL . 'src/index.js',
		$dependencies,
		$version,
		true
	);

	wp_enqueue_style(
		'students-block-editor',
		file_exists( STUDENTS_BLOCK_PLUGIN_DIR . 'build/index.css' )
			? STUDENTS_BLOCK_PLUGIN_URL . 'build/index.css'
			: STUDENTS_BLOCK_PLUGIN_URL . 'src/edit.scss',
		array(),
		$version
	);
}
add_action( 'enqueue_block_editor_assets', 'students_block_enqueue_editor_assets' );

/**
 * Enqueue frontend assets.
 */
function students_block_enqueue_frontend_assets() {
	if ( has_block( 'students-block/students' ) ) {
		wp_enqueue_style(
			'students-block-frontend',
			STUDENTS_BLOCK_PLUGIN_URL . 'build/style-index.css',
			array(),
			STUDENTS_BLOCK_VERSION
		);
		// Enqueue custom CSS file with theme styles.
		wp_enqueue_style(
			'students-block-custom',
			STUDENTS_BLOCK_PLUGIN_URL . 'students-block.css',
			array( 'students-block-frontend' ),
			STUDENTS_BLOCK_VERSION
		);
	}
}
add_action( 'wp_enqueue_scripts', 'students_block_enqueue_frontend_assets' );


