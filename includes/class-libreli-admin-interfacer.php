<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.lehelmatyus.com/
 * @since      0.0.1
 *
 * @package    Libreli
 * @subpackage Libreli/admin
 */


/**
 *
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Libreli
 * @subpackage Libreli/admin
 * @author     Lehel Matyus <contact@lehelmatyus.com>
 */

class Libreli_Admin_Interfacer {

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

    public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
    }


    public static function __get_freedom_pills($urls){

		$output = '';
		if (!empty($urls)){
			foreach ($urls as $key => $link) {
				if (!empty($link['found'])){
					$output .= "<span class='lbrty-money-pills'>{$link["name"]}</span>";
				}
			}
		}else{
			return '-';
		}
		return $output;
	}


	public static function __get_last_lookup_time($post_id){

		if (!empty($post_id)){
			$lbrty_stores_found = get_post_meta($post_id, 'lbrty_last_lookup_time', true);
			if (!empty($lbrty_stores_found)){
				return $lbrty_stores_found;
			}
		}
		return '-';

	}

    /**
     * Get Time
     */
    public static function __get_time(){
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $the_time = wp_date("{$date_format} {$time_format}", time());
        return $the_time;
	}

	public static function __get_time_w_sec(){
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $the_time = wp_date("{$date_format} h:i:s A", time());
        // $the_time = wp_date("h:i A", time());
        return $the_time;
    }


	/**
	 * Checks whats the max books number for this license
	 *
	 * @since 0.0.1
	 */

	public static function __get_max_books_allowed( $lbrty_subscription_type = false ) {

		if (false == $lbrty_subscription_type){
			$lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
			$lbrty_subscription_type = $lbrty_settings_general_options['lbrty_subscription_type'];
		}

		// if subscription type is not set default to 0
		$max_books = 0;

		if (!empty($lbrty_subscription_type)){

			switch ($lbrty_subscription_type) {
				case 'S':

					$max_books = 10;
					break;

				case 'F':
				default:

					$max_books = 1;
					break;
			}
		}

		return $max_books;

	}

		/**
	 * Checks whats the max books number for this license
	 *
	 * @since 0.0.1
	 */

	public static function __get_max_books_allowed_subtype( ) {

		$lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
		$lbrty_subscription_type = $lbrty_settings_general_options['lbrty_subscription_type'];

		// if subscription type is not set default to 0
		$max_books = 0;

		if (!empty($lbrty_subscription_type)){

			switch ($lbrty_subscription_type) {
				case 'S':

					$max_books = 10;
					break;

				case 'F':
				default:

					$max_books = 1;
					break;
			}
		}

		return $max_books;

	}

	/**
	 * Get License Type
	 */

	// @TODO


	/**
	 * Get current iterration for recursive call made on cron
	 */
	public function __get_current_teration() {
        $lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
        return $lbrty_settings_general_options['lbrty_books_iteration'];
    }

	/**
	 * Empty Quicklog
	 */
    public static function __empty_log(){

		$uploads  = wp_upload_dir( null, false );
		$logs_dir = $uploads['basedir'] . '/libreli-logs';

		if ( ! is_dir( $logs_dir ) ) {
			mkdir( $logs_dir, 0755, true );
		}

		$file = fopen( $logs_dir . '/' . 'log.log', 'r+' );

		ftruncate($file, 0);

		fclose( $file );

		return false;

	}

	/**
	 * Write to Quicklog
	 */
    public static function __write_log($message){

		// return if log is not turned on
		$lbrty_settings_advanced_options = get_option( 'lbrty_settings_advanced_options' );
		if (empty($lbrty_settings_advanced_options['lbrty_debug_log_on'])){
			return false;
		}

		$uploads  = wp_upload_dir( null, false );
		$logs_dir = $uploads['basedir'] . '/libreli-logs';

		if ( ! is_dir( $logs_dir ) ) {
			mkdir( $logs_dir, 0755, true );
		}

		$message = Libreli_Admin_Interfacer::__get_time_w_sec() . " :: " . $message . PHP_EOL;
		$file = fopen( $logs_dir . '/' . 'log.log', 'a' );
		$write = fputs( $file, $message);
		fclose( $file );

		return false;

	}

	/**
	 * Read full Log
	 */
    public static function __read_full_log(){

		// return if log is not turned on
		$lbrty_settings_advanced_options = get_option( 'lbrty_settings_advanced_options' );
		if (empty($lbrty_settings_advanced_options['lbrty_debug_log_on'])){
			return false;
		}

		$uploads  = wp_upload_dir( null, false );
		$logs_dir = $uploads['basedir'] . '/libreli-logs';

		$log = $logs_dir . '/' . 'log.log';
		$file = fopen( $logs_dir . '/' . 'log.log', 'r' );

		echo '<textarea name="message" rows="20" cols="220" readonly>';
			echo file_get_contents($log);
		echo '</textarea>';

		fclose( $file );

		return false;
	}

	/**
	 * Read Log size
	 */
    public static function __read_log_size(){

		// return if log is not turned on
		$lbrty_settings_advanced_options = get_option( 'lbrty_settings_advanced_options' );
		if (empty($lbrty_settings_advanced_options['lbrty_debug_log_on'])){
			return false;
		}

		$uploads  = wp_upload_dir( null, false );
		$logs_dir = $uploads['basedir'] . '/libreli-logs';

		$log = $logs_dir . '/' . 'log.log';
		$file = fopen( $logs_dir . '/' . 'log.log', 'r' );

		echo "<b>FILE SIZE: " . round(filesize($log)/1000,2) . "KB</b> </br>";
		// echo file_get_contents($log);

		fclose( $file );

		return false;
	}


	/**
	 * Read full Log as HTML
	 */
    public static function __read_full_log_html(){

		// return if log is not turned on
		$lbrty_settings_advanced_options = get_option( 'lbrty_settings_advanced_options' );
		if (empty($lbrty_settings_advanced_options['lbrty_debug_log_on'])){
			return false;
		}

		$uploads  = wp_upload_dir( null, false );
		$logs_dir = $uploads['basedir'] . '/libreli-logs';

		$log = $logs_dir . '/' . 'log.log';
		$file = fopen( $logs_dir . '/' . 'log.log', 'r' );

		// echo "FILE SIZE:" . fread($file,filesize($log)) . "</br>";
		echo "<b>FILE SIZE: " . round(filesize($log)/1000,2) . "KB</b> </br>";
		echo nl2br(file_get_contents($log));

		fclose( $file );

		return false;

    }


	/**
	* Check if max books reached
	*
	* @since 0.0.1
	*/
	public static function __has_reached_max_books( $total_book_count, $lbrty_subscription_type ) {

		$max_books = Self::__get_max_books_allowed($lbrty_subscription_type);

		if ($max_books == 0){
			return 0;
		}

		if($total_book_count >= $max_books + 1){
			// User will need to delete a book
			return 2;
		}
		if($total_book_count == $max_books){
			// User has maxed out but is able to use it
			return 1;
		}

		// User can still add more books
		return 0;
	}


	/**
	 * Get all current books
	 */
	public static function __get_all_published_books(){
		$args = array(
			'numberposts'		=> -1, // -1 is for all
			'post_type'		=> 'lbrty_book', // or 'post', 'page'
			'post_status'     => 'publish',
			'orderby' 		=> 'date', // or 'date', 'rand'
			'order' 		=> 'ASC', // or 'DESC'
		  );

		  // Get the posts
		  $myposts = get_posts($args);

		  return $myposts;
	}



}