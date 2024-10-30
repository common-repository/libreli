<?php

defined( 'ABSPATH' ) || exit;

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-libreli-keyhandler.php';

/**
 * Extend the main WP_REST_Posts_Controller to a private endpoint controller.
 */

class Libreli_Rest_API extends WP_REST_Posts_Controller {

    /**
     * The namespace.
     *
     * @var string
     */
    protected $namespace = 'libreli/v1';

    /**
     * Rest base for the current object.
     *
     * @var string
     */
    protected $rest_base = 'key';
    protected $rest_base_lookup = 'lookup';

    protected $lbrty_settings_general_options;
    private $license_is_active;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version) {

		$this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
        $this->lbrty_subscription_active = $this->lbrty_settings_general_options['lbrty_subscription_active'];

	}

    /**
     * Register the routes for the objects of the controller.
     *
     * Nearly the same as WP_REST_Posts_Controller::register_routes(), but all of these
     * endpoints are hidden from the index.
     */
    public function register_routes() {

        /* Validate Key
         * wp-json/libreli/v1/key/activatekey
         */
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/activatekey' , array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'activatekey' ),
                'permission_callback' => array( $this, 'activatekey_permission_check' ),
                'show_in_index'       => false,
            ),
        ) );


        /* Deactivate Key
         * wp-json/libreli/v1/key/deactivatekey
         */
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/deactivatekey' , array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'deactivatekey' ),
                'permission_callback' => array( $this, 'deactivatekey_permission_check' ),
                'show_in_index'       => false,
            ),
        ) );

        /* Initial Lookup
         * wp-json/libreli/v1/key/initial-lookup
         */
        register_rest_route( $this->namespace, '/' . $this->rest_base_lookup . '/initial-lookup' , array(
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'initial_lookup' ),
                'permission_callback' => array( $this, 'initial_lookup_check' ),
                'show_in_index'       => false,
            ),
        ) );

    }


    public function activatekey_permission_check ($request) {
        return true;
    }

    public function activatekey ($request) {
        $error = new WP_Error();
        $response = array();

        /**
         * Check if user is not logged in
         */
        // $user_id = get_current_user_id();
        // // $user  = wp_get_current_user();
		// // $user_id   = (int) $user->ID;

        // // $user_id = $user->ID;
        // $response['message'] = $user_id;
        // return new WP_REST_Response($response, 200);

        // if ($user_id == 0){
        //     $error->add( "no_such_user", __( 'No such user 0' ), array( 'status' => 401 ) );
        //     return $error;
        // }

        /**
         * Check Admin Referrer, make sure this is called by and Admin
         */
        check_admin_referer();

        /**
         * Check if nonce is bad
         */
        if ( rest_cookie_check_errors($request) ) {
            // Nonce is correct!
            $response['data'] = array('nonce'=>'correct');
        } else {
            // Don't send the data, it's a trap!
            $error->add( "no_such_user", __( 'No such user' ), array( 'status' => 401 ) );
            return $error;
        }

        /**
         * Get Parameters
         */
        $parameters = $request->get_json_params();

        /**
         * Init Handler
         */

        $key_handler = new Libreli_Key_Handler($this->plugin_name, $this->version);

        /**
         * Get License Key from request
         */
        if (empty($parameters["license_key"])){
            $error->add( "no_key_provided", __( $key_handler->get_message('no_key_provided') ), array( 'status' => 404 ) );
            $key_handler->flush_key_related_info();
            return $error;
        }

        /**
         * Activate License
         */

        // Decode response from activator.
        $_com_response = json_decode($key_handler->_comm__activate_key($parameters["license_key"]));


        if (!(json_last_error() === JSON_ERROR_NONE)) {
            $error->add( "json_parse_error", __( $key_handler->get_message('json_parse_error') . " " . json_last_error_msg() ), array( 'status' => 404 ) );
            $key_handler->flush_key_related_info();
            return $error;
        }
        if (empty($_com_response)) {
            $error->add( "empty_response", __( $key_handler->get_message('empty_response') ), array( 'status' => 404 ) );
            $key_handler->flush_key_related_info();
            return $error;
        }

        $response['_com_response'] = $_com_response;

        /**
         * If License Key manager says there is no such key
         */
        if (!empty($_com_response->data->status) && $_com_response->data->status == 404){
            $error->add( "key_not_good", __( $key_handler->get_message('key_not_good') . ' ' . $_com_response->message ), array( 'status' => 404 ) );
            $key_handler->flush_key_related_info();
            return $error;
        }

        /**
         * Test For Success
         */
        if (
            ! empty($_com_response->success) &&
            ($_com_response->success == true) &&
            ! empty($_com_response->data->timesActivated) &&
            ($_com_response->data->timesActivated <= $_com_response->data->timesActivatedMax)
        ) {

            // Save Stuff in WP DB and return success message
            $key_activated_succesfully = $key_handler->activate_key($parameters["license_key"]);

            // Successfully activated by License Manager
            $response['code'] = "activated";
            $response['message'] = __($key_handler->get_message('activated'), "libreli");
            $response['key_activated_succesfully'] = $key_activated_succesfully;

            return new WP_REST_Response($response, 200);

        }


        /**
         * Was not able to Activate
         */
        $error->add( "unable_to_activate", __( $key_handler->get_message('unable_to_activate') . " " . $_com_response->message ), array( 'status' => 404 ) );
        $key_handler->flush_key_related_info();
        return $error;

    }

    public function deactivatekey_permission_check ($request) {
        return true;
    }


    public function deactivatekey ($request) {
        $error = new WP_Error();
        $response = array();

        /**
         * Check Admin Referrer, make sure this is called by and Admin
         */
        check_admin_referer();

        /**
         * Check if nonce is bad
         */
        if ( rest_cookie_check_errors($request) ) {
            // Nonce is correct!
            $response['data'] = array('nonce'=>'correct');
        } else {
            // Don't send the data, it's a trap!
            $error->add( "no_such_user", __( 'No such user' ), array( 'status' => 401 ) );
            return $error;
        }

        /**
         * Get Parameters
         */
        $parameters = $request->get_json_params();

        /**
         * Init Handler
         */

        $key_handler = new Libreli_Key_Handler($this->plugin_name, $this->version);

        /**
         * Get License Key from request
         */
        if (empty($parameters["license_key"])){
            $error->add( "no_key_provided", __( $key_handler->get_message('no_key_provided') ), array( 'status' => 404 ) );
            $key_handler->flush_key_related_info();
            return $error;
        }


        /**
         * Deactivate License
         */

        // Decode response from activator.
        $_com_response = json_decode($key_handler->_comm__deactivate_key($parameters["license_key"]));

        // return $key_handler->_comm__activate_key($parameters["license_key"]);
        // return $_com_response;

        if (!(json_last_error() === JSON_ERROR_NONE)) {
            $error->add( "json_parse_error", __( $key_handler->get_message('json_parse_error')  . " " . json_last_error_msg()  ), array( 'status' => 404 ) );
            $key_handler->flush_key_related_info();
            return $error;
        }
        if (empty($_com_response)) {
            $error->add( "empty_response", __( $key_handler->get_message('empty_response') ), array( 'status' => 404 ) );
            $key_handler->flush_key_related_info();
            return $error;
        }

        $response['_com_response'] = $_com_response;

        /**
         * If License Key manager says there is no such key
         */
        if (!empty($_com_response->data->status) && $_com_response->data->status == 404){
            $error->add( "key_not_good", __( $key_handler->get_message('key_not_good') . ' ' . $_com_response->message ), array( 'status' => 404 ) );
            $key_handler->flush_key_related_info();
            return $error;
        }

         /**
         * Test For Success
         */
        if (
            ! empty($_com_response->success) &&
            ($_com_response->success == true)
        ){
            $key_handler->flush_key_related_info();
            $response['code'] = "deactivated";
            $response['message'] = __($key_handler->get_message('deactivated'), "libreli") . "";
            return new WP_REST_Response($response, 200);
        }

        /**
         * Was not able to DeActivate
         */
        $error->add( "unable_to_deactivate", __( $key_handler->get_message('unable_to_deactivate') . " " . $_com_response->message ), array( 'status' => 404 ) );
        $key_handler->flush_key_related_info();
        return $error;

    }


    public function initial_lookup_check ($request) {
        return true;
    }

    public function initial_lookup ($request) {

        set_time_limit(300);

        Libreli_Admin_Interfacer::__write_log("Rest API - initial_lookup (initiated)");

        $error = new WP_Error();
        $response = array();

        $response['message'] = "";

        /**
         * Check Admin Referrer, make sure this is called by and Admin
         */

        check_admin_referer();

        /**
         * Check if nonce is bad
         */
        if ( rest_cookie_check_errors($request) ) {
            // Nonce is correct!
            $response['data'] = array('nonce'=>'correct');
        } else {
            // Don't send the data, it's a trap!
            $error->add( "no_such_user", __( 'No such user' ), array( 'status' => 401 ) );
            return $error;
        }

        /**
         * Get Parameters
         */
        $parameters = $request->get_json_params();

        $isbn = $parameters["isbn"];
        $isbn13 = $parameters["isbn13"];
        $post_id = $parameters["post_id"];

        if ( empty($isbn) && empty($isbn13) ) {
            $error->add( "empty_isbns", __( "No ISBN Provided." ), array( 'status' => 404 ) );
            return $error;
        }

        if ( empty($post_id) ) {
            $error->add( "empty_post_id", __( "No post_id provided." ), array( 'status' => 404 ) );
            return $error;
        }

        /**
         * Build Post Request to Server
         */

        $key_handler = new Libreli_Key_Handler($this->plugin_name, $this->version);
        $url = $key_handler->get_endpoint("roam");


        $lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
        $lbrty_license_key = $lbrty_settings_general_options['lbrty_license_key'];
        if ( empty($lbrty_license_key) ) {
            $error->add( "missing_license_key", __( "Missing License Key." ), array( 'status' => 404 ) );
            return $error;
        }

        $lbrty_settings_advanced_options = get_option( 'lbrty_settings_advanced_options' );
        $lbrty_amzn_aff = $lbrty_settings_advanced_options['lbrty_amzn_aff'];
        if ( empty($lbrty_amzn_aff) ) {
            $lbrty_amzn_aff = '';
        }


        // Send it to Libreli Server
        $data_array = array(
            "license_key" => $lbrty_license_key,
            "book_slot" => "0",
            "isbn" => $isbn,
            "isbn13" => $isbn13,
            "amzn_aff_render" => wp_strip_all_tags($lbrty_amzn_aff),
            "sender" => get_home_url() . "(type: initial)"
        );

        $data_array = wp_json_encode( $data_array );

        Libreli_Admin_Interfacer::__write_log("Rest API - initial_lookup: Data sent: " . serialize($data_array) );

        $results = wp_remote_post(
            $url,
            array(
                'body'        => $data_array,
                'headers'     => [
                    'Content-Type' => 'application/json',
                ],
                'timeout'     => 65,
                'redirection' => 5,
                'blocking'    => true,
                'httpversion' => '1.0',
                'sslverify'   => false,
                'data_format' => 'body',
            )
        );

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

                $error->add( "rejected_by_server", __( "Rejected by server. Check if you have active License Key" ) . " | " . $server_message . " | " . $server_custom_message, array( 'status' => 404 ) );
                return $error;

            }
        }

        /**
         * Get the body of the response
         * and filter out the stores that were returned
         */
        $results = wp_remote_retrieve_body($results);
        $results = json_decode($results, true);

        if ( empty($results['data']['results_array'])) {
            $error->add( "empty_data", __( "Empty Data." ), array( 'status' => 404 ) );
            return $error;
        }

        $filtered_results_array = $results['data']['results_array'];
        $links_found = array();

        // Remove Keys where book was not found
        foreach($filtered_results_array as $key => $value){
            if ( ! $value['found'] ){
                unset($filtered_results_array[$key]);
            }else{
                $links_found[] = $value['name'];
            }
        }

        /**
         * Get the current time
         */

        $the_time = Libreli_Admin_Interfacer::__get_time();

        /**
         * Update the book
         */

        update_post_meta($post_id, 'lbrty_stores_found', $filtered_results_array);
        update_post_meta($post_id, 'lbrty_first_lookup', true);
        update_post_meta($post_id, 'lbrty_last_lookup_time', $the_time);

        // delete_post_meta($post_id, 'lbrty_stores_found',);
        // delete_post_meta($post_id, 'lbrty_first_lookup',);


        // $response['message'] = "Book Found on " . count($filtered_results_array) . " stores_found: " . implode(', ', $links_found);

        $response['message'] = __("<b>Success:</b> ","libreli") . __("This book was found on ","libreli") . count($filtered_results_array) . " " . __("stores: ","libreli") . implode(', ', $links_found);
        $response['message'] .= "<br />";
        $response['message'] .= __("You can now add the shortcode to any page to have the book and links to all online stores displayed on your website.","libreli");

        $response['full_results'] = $results['data']['results_array'];
        $response['results'] = $filtered_results_array;
        $response['stores_found'] = $links_found;

        $flatent_links_found = implode(", ", $links_found);
        Libreli_Admin_Interfacer::__write_log("Rest API - initial_lookup: Success (post_id: {$post_id}, isbn:{$isbn}, isbn13:{$isbn13}, links: {$flatent_links_found} )");

        return new WP_REST_Response($response, 200);

    }

    function __is_found_true($var){
    }

}