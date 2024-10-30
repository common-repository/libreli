<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.lehelmatyus.com
 * @since             0.0.1
 * @package           Libreli
 *
 * @wordpress-plugin
 * Plugin Name:       Libreli
 * Plugin URI:        http://www.libreli.com/
 * Description:       Libreli automatically generates and keeps an always up-to-date list of the most popular retail sites selling your books.
 * Version:           0.0.8
 * Author:            Libreli
 * Author URI:        http://www.libreli.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       libreli
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LIBRELI_VERSION', '0.0.8' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-libreli-activator.php
 */
function activate_libreli() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-libreli-activator.php';
	Libreli_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-libreli-deactivator.php
 */
function deactivate_libreli() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-libreli-deactivator.php';
	Libreli_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_libreli' );
register_deactivation_hook( __FILE__, 'deactivate_libreli' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-libreli.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_libreli() {

	$plugin = new Libreli();
	$plugin->run();

}
run_libreli();
