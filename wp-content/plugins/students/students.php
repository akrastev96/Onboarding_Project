<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://devrix.com
 * @since             1.0.0
 * @package           Students
 *
 * @wordpress-plugin
 * Plugin Name:       Students
 * Plugin URI:        https://devrix.com
 * Description:       Students plugin generated from DevriX boilerplate.
 * Version:           1.0.0
 * Author:            DevriX
 * Author URI:        https://devrix.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       students
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version. Start at version 1.0.0
 * For the versioning of the plugin is used SemVer - https://semver.org
 * Rename this for every new plugin and update it as you release new versions.
 */
define( 'STUDENTS_VERSION', '1.0.0' );

if ( ! defined( 'STUDENTS_DIR' ) ) {
	define( 'STUDENTS_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'STUDENTS_URL' ) ) {
	define( 'STUDENTS_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/classes/class-activator.php
 */
function dx_activate_students() {
	require_once STUDENTS_DIR . 'includes/classes/class-activator.php';
	Students\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/classes/class-deactivator.php
 */
function dx_deactivate_students() {
	require_once STUDENTS_DIR . 'includes/classes/class-deactivator.php';
	Students\Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'dx_activate_students' );
register_deactivation_hook( __FILE__, 'dx_deactivate_students' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once STUDENTS_DIR . 'includes/classes/class-students.php';

/**
 * The plugin functions file that is used to define general functions, shortcodes etc.
 */
require_once STUDENTS_DIR . 'includes/functions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function dx_run_students() {
	$plugin = new Students\Students();
	$plugin->run();
}

dx_run_students();
