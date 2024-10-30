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

class Libreli_Get_Links_API {

    /**
     * Action hook
     */
	public function do_lbrty_get_links_from_api() {

        $key_handler = new Libreli_Key_Handler($this->plugin_name, $this->version);
        $url = $key_handler->get_endpoint("roam");
        Libreli_Admin_Interfacer::__write_log("Get Links API - do_lbrty_get_links_from_api (initiated R Loop) ---- " . $url);

        /**
         * Init
         */

        $lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
        $lbrty_subscription_active = $lbrty_settings_general_options['lbrty_subscription_active'];
        $lbrty_subscription_type = $lbrty_settings_general_options['lbrty_subscription_type'];

        /**
         * Check if license is active
         */

        if (!empty($lbrty_subscription_active)){

            // Get list of books to roam
            $max_books = Libreli_Admin_Interfacer::__get_max_books_allowed($lbrty_subscription_type);

            $query_for_books = get_posts(array(
                'post_type' => 'lbrty_book',
                'post_status'     => 'publish',
                'posts_per_page' => $max_books,
                'orderby' => 'date',
                'order'   => 'ASC',
            ) );

            // Set up the list for "lbrty_get_links_from_api"
            $count = count($query_for_books);

            if ($count){

                $books_to_roam = [];
                foreach($query_for_books as $key => $book){
                    if (!empty($book->ID)){

                        $isbn = get_post_meta( $book->ID, 'lbrty_isbn', true );
                        $isbn13 = get_post_meta( $book->ID, 'lbrty_isbn13', true );

                        if ((!empty($isbn)) || (!empty($isbn13))){
                            $books_to_roam[] = array(
                                'id' => $book->ID,
                                'isbn' => $isbn,
                                'isbn13' => $isbn13
                            );
                        }
                    }
                }

                /**
                 * Update time ran
                 * and list of prepared books
                 */

                // Wite time last ran
                $lbrty_settings_general_options['lbrty_last_ran'] = Libreli_Admin_Interfacer::__get_time();
                $lbrty_settings_general_options['lbrty_books'] = $books_to_roam;
                $lbrty_settings_general_options['lbrty_books_iteration'] = 0;
                update_option( 'lbrty_settings_general_options', $lbrty_settings_general_options);

                $this->lbrty_get_links_from_api();

            }

        }else{
            /**
             * Do nothing
             */
        }

    }

    /**
     * Get the book links from Libreli
     * scheduled run
     */
    public function lbrty_get_links_from_api() {

        set_time_limit(300);

        $log_trunk = "Get Links API - lbrty_get_links_from_api - ";

        $links = [];

        $lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
        $current_iteration = $lbrty_settings_general_options['lbrty_books_iteration'];
        $max_allowed_by_license = Libreli_Admin_Interfacer::__get_max_books_allowed_subtype();
        $books_to_roam = $lbrty_settings_general_options['lbrty_books'];
        $lbrty_license_key = $lbrty_settings_general_options['lbrty_license_key'];
        $lbrty_settings_advanced_options = get_option( 'lbrty_settings_advanced_options' );
        $lbrty_amzn_aff = $lbrty_settings_advanced_options['lbrty_amzn_aff'];


        if ( empty($lbrty_license_key) ) {
            $error = "Missing License Key.";
            Libreli_Admin_Interfacer::__write_log($log_trunk . $error);

            /**
             * Bad license, just quit
             */
            return false;
        }

        /**
         * Grab Aff Key if exists
         */
        if ( empty($lbrty_amzn_aff) ) {
            $lbrty_amzn_aff = '';
        }

        /**
         * If not bok to roam then exit loop
         */
        if (empty($books_to_roam)){
            Libreli_Admin_Interfacer::__write_log($log_trunk . "no books found");
            return false;
        }

        /**
         * Decide on how many iterations
         * either max allowed by license or total number of books
         *
         */
        $total_books = count($books_to_roam);
        $max_allowed_iterations = ($total_books < $max_allowed_by_license) ? $total_books : $max_allowed_by_license;

        Libreli_Admin_Interfacer::__write_log($log_trunk . "initiated ({$current_iteration}/{$max_allowed_iterations}) using slot: {$current_iteration}");


        /**
         * Counter starts at 0:
         * counter 0,1,2
         * max = 3
         */
        if( $current_iteration + 1 > $max_allowed_iterations){
            Libreli_Admin_Interfacer::__write_log($log_trunk . "Reached iteration end");
            return false;
        }

        $the_book_to_roam = $books_to_roam[$current_iteration];

        $post_id = $the_book_to_roam["id"];
        $isbn = $the_book_to_roam["isbn"];
        $isbn13 = $the_book_to_roam["isbn13"];
        $slot = $current_iteration;

        /**
         * Get book for current iteration
         */

        if ( empty($post_id) ) {
            $error = "No post_id provided.";
            Libreli_Admin_Interfacer::__write_log($log_trunk . $error);

            // skip this book but contineu with next one
            $this->__increment_counter();
            $this->lbrty_get_links_from_api();
            return false;
        }

        if ( empty($isbn) && empty($isbn13) ) {
            $error = "No ISBN Provided.";
            Libreli_Admin_Interfacer::__write_log($log_trunk . $error);

            // skip this book but contineu with next one
            $this->__increment_counter();
            $this->lbrty_get_links_from_api();
            return false;
        }

        /**
         * Build Post Request to Server
         */

        $key_handler = new Libreli_Key_Handler($this->plugin_name, $this->version);
        $url = $key_handler->get_endpoint("roam");


        /**
         * Make request
         */

        $data_array = array(
            "license_key" => $lbrty_license_key,
            "book_slot" => $slot,
            "isbn" => $isbn,
            "isbn13" => $isbn13,
            "amzn_aff_render" => $lbrty_amzn_aff,
            "sender" => get_home_url() . " (type: scheduled)"
        );

        $data_array = wp_json_encode( $data_array );

        $results = wp_remote_post(
            $url,
            array(
                'method' => 'POST',
                'body'        => $data_array,
                'headers'     => [
                    'Content-Type' => 'application/json',
                ],
                'timeout'     => 60,
                // 'redirection' => 5,
                'blocking'    => true,
                // 'httpversion' => '1.0',
                'sslverify'   => false,
                // 'data_format' => 'body',
            )
        );


        if ( is_wp_error( $results ) ) {
            $error_message = $results->get_error_message();
            Libreli_Admin_Interfacer::__write_log($log_trunk . "(something went wrong)" .  $error_message);
        }

        /**
         * Check if response from Server is not an error.
         */
        if (( empty($results['response']['code'] )) || ( 200 != $results['response']['code'] )){
            if ( empty($results['data']['results_array'])) {

                /**
                 * Generic Server Message
                 */
                $server_message = '';
                if (!empty($results['response']['message'])){
                    $server_message = ' | Server Message: ' . $results['response']['message'] . ".";
                }

                /**
                 * Custom Message from Libreli Server
                 */
                $server_custom_message = '';
                $results_body = wp_remote_retrieve_body($results);

                if(!empty($results_body)){
                    $results_body = json_decode($results_body, true);
                    if (!empty($results_body['message'])){
                        $server_message = ' Server Message: ' . $results_body['message'] . ".";
                    }
                }

                $error = "Rejected by server. Check if you have active License Key" . " | " . $server_message . " | " . $server_custom_message;
                Libreli_Admin_Interfacer::__write_log($log_trunk . $error);

                /**
                 * Skip this one
                 * and don't continue
                 * This site is making bad calls to the server
                 */
                return false;
            }
        }

        /**
         * Get the body of the response
         * and filter out the stores that were returned
         */
        $results = wp_remote_retrieve_body($results);
        $results = json_decode($results, true);

        if ( empty($results['data']['results_array'])) {
            $error= "Empty Data in results array.";
            Libreli_Admin_Interfacer::__write_log($log_trunk . $error);
            return false;
        }

        $filtered_results_array = $results['data']['results_array'];
        $stores_found = array();

        // Remove Keys where book was not found
        foreach($filtered_results_array as $key => $value){
            if ( ! $value['found'] ){
                unset($filtered_results_array[$key]);
            }else{
                $stores_found[] = $value['name'];
            }
        }

        /**
         * Get the current time
         */

        $the_time =Libreli_Admin_Interfacer::__get_time();

        /**
         * Success!
         * if you got this far
         */

        /**
         * Update the book
         */

        update_post_meta($post_id, 'lbrty_stores_found', $filtered_results_array);
        update_post_meta($post_id, 'lbrty_first_lookup', true);
        update_post_meta($post_id, 'lbrty_last_lookup_time', $the_time);

        /**
         * Log Success
         */

        $flatent_links_found = implode(", ", array_keys($filtered_results_array));
        Libreli_Admin_Interfacer::__write_log("Get Links API - get_links_from_api: Success (post_id: {$post_id}, isbn:{$isbn}, isbn13:{$isbn13}, links: {$flatent_links_found} )");

        /**
         * Increment counter and call myself
         */

        $this->__increment_counter();
        $this->lbrty_get_links_from_api();
        return false;

    }


    /**
     * Used inside the recursive function before it calls itself again so next time it takes the next book in the line
     */
    function __increment_counter(){
        $lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
		$lbrty_settings_general_options['lbrty_books_iteration'] = $lbrty_settings_general_options['lbrty_books_iteration'] + 1;
        update_option( 'lbrty_settings_general_options', $lbrty_settings_general_options);
    }

}
