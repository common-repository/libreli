<?php

class Libreli_Admin_Notice {

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

    public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    function activate_notice() {

		$lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
		$lbrty_subscription_active = $lbrty_settings_general_options['lbrty_subscription_active'];

		if (!empty($lbrty_subscription_active)){
			
		}else{
			?>
				<div class="error notice">
					<p><b><?php _e( 'Libreli', 'libreli' ); ?></b></p>
					<p><?php _e( 'Complete the Libreli plugin setup by enabling the auto book lookup feature. ', 'libreli' ); ?><a href="/wp-admin/edit.php?post_type=lbrty_book&page=lbrty_general_settings"><?php _e( 'Enter free or premium license key.', 'libreli' ); ?></a></p>
				</div>
			<?php
		}
		
    }
    

}


?>