<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://devrix.com
 * @since      1.0.0
 *
 * @package    Students
 * @subpackage Students/includes/classes
 * @author     DevriX <contact@devrix.com>
 */

namespace Students;

/**
 * I18n class.
 */
class I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'students',
			false,
			STUDENTS_DIR . '/languages/'
		);

	}
}
