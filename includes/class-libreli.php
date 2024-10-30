<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.lehelmatyus.com
 * @since      0.0.1
 *
 * @package    Libreli
 * @subpackage Libreli/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    Libreli
 * @subpackage Libreli/includes
 * @author     Lehel Mátyus <contact@lehelmatyus.com>
 */
class Libreli {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      Libreli_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {
		if ( defined( 'LIBRELI_VERSION' ) ) {
			$this->version = LIBRELI_VERSION;
		} else {
			$this->version = '0.0.8';
		}
		$this->plugin_name = 'libreli';

		$this->load_dependencies();
		$this->set_locale();
		$this->add_post_types();
		$this->add_post_type_metaboxes();
		$this->add_libreli_shortcodes();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Libreli_Loader. Orchestrates the hooks of the plugin.
	 * - Libreli_i18n. Defines internationalization functionality.
	 * - Libreli_Admin. Defines all hooks for the admin area.
	 * - Libreli_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {


		/**
		 * Backgroun processing
		 */

		if ( ! class_exists( 'WP_Async_Request', false ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libraries/wp-async-request.php';
		}
		
		if ( ! class_exists( 'WP_Background_Process', false ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libraries/wp-background-process.php';
		}

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-loader.php';

		/**
		 * The class responsible for adding custom post types
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-post-type.php';

		/**
		 * The class responsible for metaboxes for the custom post type
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-admin-interfacer.php';

		/**
		 * The class responsible for metaboxes for the custom post type
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-post-metabox.php';

		/**
		 * Register blocks
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'blocks/lbrty-dropdown.php';

		/**
		 * The class responsible to get Links from API
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-get-links-api.php';
		/**
		 * The class responsible to run the first lookup
		 */
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-scheduler.php';

		/**
		 * The class responsible to run schedule the lookups
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-cron.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-i18n.php';

		/**
		 * The class responsible for Adding the shortcodes.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-shortcodes.php';

		/**
		 * The class responsible for Creating the RestAPI
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-restapi.php';

		/**
		 * The class responsible for Creating the RestAPI
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-libreli-admin-notice.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-libreli-admin.php';

		/**
		 * The class responsible for adding the manae page.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-libreli-manage-page.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-libreli-public.php';

		$this->loader = new Libreli_Loader();

	}

	/**
	 * Add post types
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function add_post_types() {

		$plugin_post_types = new Libreli_PostType( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_post_types, 'add_post_types' );

	}

	/**
	 * Add post type Metaboxes
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function add_post_type_metaboxes() {

		$plugin_post_type_metaboxes = new Libreli_Post_Type_Metaboxes( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'add_meta_boxes', $plugin_post_type_metaboxes, 'isbn_meta_boxes' );
		$this->loader->add_action( 'save_post', $plugin_post_type_metaboxes, 'save_meta_boxes' );

		// Change the book title placeholder text on Book crete/edit page
		$this->loader->add_filter( 'enter_title_here', $plugin_post_type_metaboxes, 'change_book_title_placeholder', 20, 2 );


	}


	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Libreli_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Libreli_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Create Libreli Shortcodes
	 *
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function add_libreli_shortcodes() {

		$this->loader->add_action( 'init', 'Libreli_Shortcodes', 'libreli_init_shortcodes' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Libreli_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		/**
		 * Add Manage Page
		 */
		$manage_page = new Libreli_Manage_Page( $this->get_plugin_name(), $this->get_version() );
		// Add menu
		$this->loader->add_action( 'admin_menu', $manage_page, 'setup_manage_page_menu' );
		// Add Genera Options / Public key and license Key
		$this->loader->add_action( 'admin_init', $manage_page, 'initialize_general_options' );
		$this->loader->add_action( 'admin_init', $manage_page, 'initialize_display_options' );		
		$this->loader->add_action( 'admin_init', $manage_page, 'initialize_advanced_options' );		

		/**
		 * Post Creation
		 */

		// add_action( 'wp_insert_post', 'my_project_updated_send_email', 10, 3 );
		// $book_scheduler = new Libreli_Book_Scheduler( $this->get_plugin_name(), $this->get_version() );

		// Schedule book lookup after save Post
		// $this->loader->add_action( 'publish_lbrty_book', $book_scheduler, 'create_book_run_schedule', 10, 2 );
		

		/**
		 * Cron Creation
		 */
		
		$libreli_cron = new Libreli_Cron();
		$this->loader->add_action( 'lbrty_book_check', $libreli_cron, 'cron_lbrty_book_check', 10, 2 );


		/**
		 * Admin Notice to activate the Plugin with license
		 */
		$admin_notice = new Libreli_Admin_Notice( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_notices', $admin_notice, 'activate_notice' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Libreli_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		/**
		 * Rest APi stuff
		 */

		$plugin_restapi = new Libreli_Rest_API( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'rest_api_init', $plugin_restapi, 'register_routes' );

		/**
		 * Get links from API
		 */

		// $get_links = new Libreli_Get_Links_API( $this->get_plugin_name(), $this->get_version() );

		// $this->loader->add_action( 'wp_ajax_nopriv_get_links_from_api', $plugin_restapi, 'get_links_from_api' );
		// $this->loader->add_action( 'wp_ajax_get_links_from_api', $plugin_restapi, 'get_links_from_api' );

		// add_action( 'wp_ajax_nopriv_get_breweries_from_api', 'get_breweries_from_api' );
		// add_action( 'wp_ajax_get_breweries_from_api', 'get_breweries_from_api' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Libreli_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
