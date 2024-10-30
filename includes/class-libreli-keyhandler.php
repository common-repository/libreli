<?php

class Libreli_Key_Handler {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
    protected $version;

	/**
	 * WP options
	 */

	private $lbrty_settings_general_options;
	private $lbrty_subscription_active;
	private $lbrty_license_key;


	/**
	 * To check if membership and subscription is active
	 * to ger Activatio n key
	 */
	private $membership_server = 'https://libreli.com';

	// protocol to connect servers
	private $protocol = "https://";
	// Must provide "default" value
	private $servers = array(
		"default" => "libreli.com",
		"dev"     => "devlibreli.wpengine.com",
		"test"    => "stagelibrelli.wpengine.com",
		"prod"    => "libreli.com",
	);

	private $endpoints = array(
		"roam" => "/wp-json/libreli/v1/lbrty/roam"
	);

    private $consumer_key = 'ck_6d0ae6a0f3ea2eae3f9ee79de89e7189ed3011ff';
	private $consumer_secret = 'cs_d8e536a1cc7d517bc9e452a3a22e589eb3f60b7c';


    public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
        $this->lbrty_subscription_active = $this->lbrty_settings_general_options['lbrty_subscription_active'];
        $this->lbrty_license_key = $this->lbrty_settings_general_options['lbrty_license_key'];
    }


	/**
	 * Flush Key related options
	 */

	public function flush_key_related_info(){

        $lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );

		$lbrty_settings_general_options['lbrty_license_key'] = '';
		$lbrty_settings_general_options['lbrty_subscription_active'] = false;
		$lbrty_settings_general_options['lbrty_subscription_type'] = '';

		update_option( 'lbrty_settings_general_options', $lbrty_settings_general_options);

	}

	/**
	 * Save key in DB
	 */

	public function activate_key($lbrty_license_key){

        $lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );

		$lbrty_settings_general_options['lbrty_license_key'] = $lbrty_license_key;
        $lbrty_settings_general_options['lbrty_subscription_active'] = true;
		$lbrty_settings_general_options['lbrty_subscription_type'] = $this->__get_lbrty_subscription_type($lbrty_license_key);

		update_option( 'lbrty_settings_general_options', $lbrty_settings_general_options);

    }

    public function __get_lbrty_subscription_type($lbrty_license_key){
        return substr($lbrty_license_key, 5, 1);
    }

	/**
	 * Communicate with server to Validate Key
	 */
	public function _comm__validate_key($key_to_activate) {

		if(empty($key_to_activate)) {
			return false;
		}

		$curl = curl_init();

			curl_setopt_array($curl, array (
			CURLOPT_URL => $this->membership_server . '/wp-json/lmfwc/v2/licenses/validate/' . $key_to_activate .'?consumer_key=' . $this->consumer_key . '&consumer_secret=' . $this->consumer_secret,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			// CURLOPT_SSL_VERIFYPEER => false   /// REMOVE THIS
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return json_encode(array('message' => 'cURL Error #:' . $err ));
		} else {
			return $response;
		}
	}


	/**
	 * Communicate with server to Activate Key
	 */
	public function _comm__activate_key($key_to_activate) {

		if(empty($key_to_activate)){
			return false;
		}

		$curl = curl_init();

        curl_setopt_array($curl, array(
			CURLOPT_URL => $this->membership_server . '/wp-json/lmfwc/v2/licenses/activate/' . $key_to_activate .'?consumer_key=' . $this->consumer_key . '&consumer_secret=' . $this->consumer_secret,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			// CURLOPT_SSL_VERIFYPEER => false   /// REMOVE THIS
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        	return json_encode(array('message' => 'cURL Error #:' . $err ));
        } else {
        	return $response;
		}
	}

	/**
	 * Communicate with server to Activate Key
	 */
	public function _comm__deactivate_key($key_to_activate) {

		if(empty($key_to_activate)){
			return false;
		}

		$curl = curl_init();

        curl_setopt_array($curl, array(
			CURLOPT_URL => $this->membership_server . '/wp-json/lmfwc/v2/licenses/deactivate/' . $key_to_activate .'?consumer_key=' . $this->consumer_key . '&consumer_secret=' . $this->consumer_secret,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			// CURLOPT_SSL_VERIFYPEER => false   /// REMOVE THIS
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        	return json_encode(array('message' => 'cURL Error #:' . $err ));
        } else {
        	return $response;
		}
	}


	/**
	 * Return Custom Messages Based onevent
	 */
	public function get_message($code){

		$message = '';

		switch ($code) {

			case 'activated':
				$message = 'Succesfully activated.';
				break;

			case 'deactivated':
				$message = 'Succesfully deactivated.';
				break;

			case 'activated2':
				$message = 'Succesfully activated.';
				break;

			case 'no_key_provided':
				$message = 'No key provided.';
				break;

			case 'key_expired':
				$message = 'Key is expired.';
				break;

			case 'key_not_good':
				$message = 'Key provided is not good.';
				break;

			case 'json_parse_error':
				$message = 'Invalid response from lehelmatyus.com. Please contact support.';
				break;

			case 'empty_response':
				$message = 'Empty response from lehelmatyus.com. Please contact support.';
				break;

			case 'unable_to_activate':
				$message = 'Unable to activate.';
				break;

			case 'unable_to_deactivate':
				$message = 'Unable to deactivate.';
				break;

			default:
				# code...
				break;
		}

		return $message;

	}

	public function get_endpoint($key){

		if (empty($key)){
			return "-1";
		}

		if (array_key_exists($key, $this->endpoints)){
			$the_server = $this->get_server();
			return $the_server . $this->endpoints[$key];
		}

		return "-2";

	}
	public function get_server(){

		$roam_server = $this->get_default_server();
		$advanced_options = get_option( 'lbrty_settings_advanced_options' );

		// Server to connect to was set
		if (!empty($advanced_options['lbrty_roam_server'])){
			if (!empty($this->servers[$advanced_options['lbrty_roam_server']])){
				$roam_server = $this->protocol . $this->servers[$advanced_options['lbrty_roam_server']];
			}
		}

		return $roam_server;
	}

	public function get_default_server(){
		return $this->protocol . $this->servers['default'];
	}

}
