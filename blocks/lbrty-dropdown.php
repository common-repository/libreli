<?php
/**
 * Functions to register client-side assets (scripts and stylesheets) for the
 * Gutenberg block.
 *
 * @package libreli
 */

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 *
 * @see https://wordpress.org/gutenberg/handbook/designers-developers/developers/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */
function lbrty_dropdown_block_init() {
	// Skip block registration if Gutenberg is not enabled/merged.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$dir = dirname( __FILE__ );

	$index_js = 'lbrty-dropdown/index.js';
	wp_register_script(
		'lbrty-dropdown-block-editor',
		plugins_url( $index_js, __FILE__ ),
		array(
			'wp-blocks',
			'wp-i18n',
			'wp-element',
		),
		filemtime( "$dir/$index_js" )
	);

	$editor_css = 'lbrty-dropdown/editor.css';
	wp_register_style(
		'lbrty-dropdown-block-editor',
		plugins_url( $editor_css, __FILE__ ),
		array(),
		filemtime( "$dir/$editor_css" )
	);

	$style_css = 'lbrty-dropdown/style.css';
	wp_register_style(
		'lbrty-dropdown-block',
		plugins_url( $style_css, __FILE__ ),
		array(),
		filemtime( "$dir/$style_css" )
	);

	register_block_type( 'libreli/lbrty-dropdown', array(
		'editor_script' => 'lbrty-dropdown-block-editor',
		'editor_style'  => 'lbrty-dropdown-block-editor',
		'style'         => 'lbrty-dropdown-block',
	) );
}
add_action( 'init', 'lbrty_dropdown_block_init' );
