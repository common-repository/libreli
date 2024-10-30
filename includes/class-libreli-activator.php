<?php

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-cron.php';

/**
 * Fired during plugin activation
 *
 * @link       http://www.lehelmatyus.com
 * @since      0.0.1
 *
 * @package    Libreli
 * @subpackage Libreli/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Libreli
 * @subpackage Libreli/includes
 * @author     Lehel Mátyus <contact@lehelmatyus.com>
 */
class Libreli_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function activate() {
		$libreli_cron = new Libreli_Cron();
		$libreli_cron->activate_cron();
	}

}
