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
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Libreli
 * @subpackage Libreli/admin
 * @author     Lehel Matyus <contact@lehelmatyus.com>
 */
class Libreli_PostType {

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


    // Register Custom Post Type
    function add_post_types() {

        $labels = array(
            'name'                  => _x( 'Libreli Books', 'Post Type General Name', 'libreli' ),
            'singular_name'         => _x( 'Libreli Book', 'Post Type Singular Name', 'libreli' ),
            'menu_name'             => __( 'Libreli Books', 'libreli' ),
            'name_admin_bar'        => __( 'Libreli Books', 'libreli' ),
            'archives'              => __( 'Item Archives', 'libreli' ),
            'attributes'            => __( 'Item Attributes', 'libreli' ),
            'parent_item_colon'     => __( 'Parent Book:', 'libreli' ),
            'all_items'             => __( 'All Books', 'libreli' ),
            'add_new_item'          => __( 'Add New Book', 'libreli' ),
            'add_new'               => __( 'Add New Book', 'libreli' ),
            'new_item'              => __( 'New Book', 'libreli' ),
            'edit_item'             => __( 'Edit Book', 'libreli' ),
            'update_item'           => __( 'Update Book', 'libreli' ),
            'view_item'             => __( 'View Book', 'libreli' ),
            'view_items'            => __( 'View Books', 'libreli' ),
            'search_items'          => __( 'Search Book', 'libreli' ),
            'not_found'             => __( 'Not found', 'libreli' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'libreli' ),
            'featured_image'        => __( 'Featured Image', 'libreli' ),
            'set_featured_image'    => __( 'Set featured image', 'libreli' ),
            'remove_featured_image' => __( 'Remove featured image', 'libreli' ),
            'use_featured_image'    => __( 'Use as featured image', 'libreli' ),
            'insert_into_item'      => __( 'Insert into Book', 'libreli' ),
            'uploaded_to_this_item' => __( 'Uploaded to this Book', 'libreli' ),
            'items_list'            => __( 'Items list', 'libreli' ),
            'items_list_navigation' => __( 'Items list navigation', 'libreli' ),
            'filter_items_list'     => __( 'Filter Books list', 'libreli' ),
        );
        $args = array(
            'label'                 => __( 'Libreli Book', 'libreli' ),
            'description'           => __( 'Libreli Book', 'libreli' ),
            'labels'                => $labels,
            // 'supports'              => array( 'title', 'thumbnail' ),
            'supports'              => array( 'title' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-book',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'rewrite'               => false,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
        );
        register_post_type( 'lbrty_book', $args );

    }

}
