<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.lehelmatyus.com
 * @since      0.0.1
 *
 * @package    Libreli
 * @subpackage Libreli/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Libreli
 * @subpackage Libreli/admin
 * @author     Lehel Mátyus <contact@lehelmatyus.com>
 */
class Libreli_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Libreli_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Libreli_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/libreli-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Libreli_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Libreli_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/libreli-admin.js', array( 'jquery', 'wp-color-picker' ), $this->version, false );

		global $post;
		if (!empty($post)){
			$post_id = (!empty($post->ID)) ? $post->ID : 0;
		}else{
			$post_id = 0;
		}

		// Send it to our own backend
		wp_localize_script($this->plugin_name, 'LbrtyApiSettings', array(
			'root' => esc_url_raw(rest_url()),
			'lbrty_nonce' => wp_create_nonce('wp_rest'),
			'post_id' => $post_id,
		));

	}

}
