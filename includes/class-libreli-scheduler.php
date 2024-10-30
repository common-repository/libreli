<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.lehelmatyus.com/
 * @since      1.0.0
 *
 * @package    Libreli
 * @subpackage Libreli/admin
 */

/**
 * Book Lookup Request Scheduler
 *
 *
 * @package    Libreli
 * @subpackage Libreli/admin
 * @author     Lehel Matyus <contact@lehelmatyus.com>
 */


/**
 * No longer used
 * was originally to shcedule lookup based on when post was created
 * this is now changed to one lookup per day based on when the plugin was activated by license key
 */

// class Libreli_Book_Scheduler {

// 	/**
// 	 * The ID of this plugin.
// 	 *
// 	 * @since    1.0.0
// 	 * @access   private
// 	 * @var      string    $plugin_name    The ID of this plugin.
// 	 */
// 	private $plugin_name;

// 	/**
// 	 * The version of this plugin.
// 	 *
// 	 * @since    1.0.0
// 	 * @access   private
// 	 * @var      string    $version    The current version of this plugin.
// 	 */
// 	private $version;

// 	/**
// 	 * Initialize the class and set its properties.
// 	 *
// 	 * @since    1.0.0
// 	 * @param      string    $plugin_name       The name of this plugin.
// 	 * @param      string    $version    The version of this plugin.
// 	 */
// 	public function __construct( $plugin_name, $version ) {

// 		$this->plugin_name = $plugin_name;
// 		$this->version = $version;

//     }

//     // https://wordpress.stackexchange.com/questions/134664/what-is-correct-way-to-hook-when-update-post
//     // https://codex.wordpress.org/Post_Status_Transitions

//     public function create_book_run_schedule($post_id, $post){


//         // If this is a revision, don't do anything
//         if ( wp_is_post_revision( $post_id ) )

//         var_dump("revision");

//         // return;
        
//         var_dump(get_post_type($post_id));
//         var_dump($post_id);


//         if( get_post_type($post_id) == 'lbrty_book' ){
//             var_dump(" Book was created");
//         }

//         $post_url = get_permalink( $post_id );

//         // exit;

//     }
    
// }