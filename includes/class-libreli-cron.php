<?php

/**
 * To Manage Scheduled runs
 * https://developer.wordpress.org/reference/functions/wp_schedule_event/
 *
 * @link       http://www.lehelmatyus.com
 * @since      0.0.1
 *
 * @package    Libreli
 * @subpackage Libreli/includes
 */

class Libreli_Cron {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */


	public function activate_cron() {
        if (!wp_next_scheduled('lbrty_book_check')){
            // wp_schedule_event(time(), 'hourly', 'lbrty_book_check' );
            wp_schedule_event(time(), 'daily', 'lbrty_book_check' );
        }
    }

    public function deactivate_cron() {
        wp_clear_scheduled_hook( 'lbrty_book_check' );
    }

    /**
     * Action hook
     */
	public function cron_lbrty_book_check() {

        // Log time of cron
        Libreli_Admin_Interfacer::__write_log("Cron hit - cron_lbrty_book_check()");


        // Have the links API make the call
        $get_links_api = new Libreli_Get_Links_API();
        $get_links_api->do_lbrty_get_links_from_api();


    }

    public function get_next_scheduled_date() {

        $next_date = wp_next_scheduled('lbrty_book_check');

        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        $date = wp_date("{$date_format} {$time_format}", $next_date);
        return $date;
    }

}
