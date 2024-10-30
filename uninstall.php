<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://www.lehelmatyus.com
 * @since      0.0.1
 *
 * @package    Libreli
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-libreli-keyhandler.php';

/**
 * Deleting the plugin will make your books go goodbye
 */
$books= get_posts([
    'post_type'=>'lbrty_book',
    'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
    'numberposts'=>-1
    ]); // all posts

foreach($books as $book){
    wp_delete_post($book->ID,true);
}

/**
 * Attempt to deactivate key
 */
$lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
if(!empty($lbrty_settings_general_options)){
    $lbrty_license_key = $lbrty_settings_general_options['lbrty_license_key'];
}
// print_r( $lbrty_settings_general_options );

/**
 * Delete all options
 */
delete_option( 'lbrty_settings_general_options' );
delete_option( 'lbrty_settings_display_options' );
delete_option( 'lbrty_settings_advanced_options' );

if ($lbrty_license_key){
    $key_handler = new Libreli_Key_Handler("libreli", 1);
    // $_com_response = json_decode($key_handler->_comm__deactivate_key($lbrty_license_key));
    // print_r( $_com_response );
}

// wp_die( 'check the array' );
