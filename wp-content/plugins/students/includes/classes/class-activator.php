<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
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
 * Students Activator class.
 */
class Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Register the CPT before flushing so rules exist.
		if ( function_exists( '\sasho_register_student_cpt' ) ) {
			\sasho_register_student_cpt();
		}

		// Flush rewrite rules to register /student/ and pagination.
		flush_rewrite_rules();
	}
}
