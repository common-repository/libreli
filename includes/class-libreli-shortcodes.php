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

defined( 'ABSPATH' ) || exit;

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
class Libreli_Shortcodes {

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
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      array    Display Settings
	 */
	private static $display_options;

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

    /**
	 * Provides default values Settings
	 *
	 * @return array
	 */
	public static function default_tpul_settings_gen_options() {
		$defaults = array(
            'lbrty_dropdown_styles'	=>	true,
            'lbrty_text_color'		=>	'#FFFFFF',
            'lbrty_bg_color'		=>	'#2A7DE1',
            'lbrty_dropdown_label'  =>	'Get your copy:',
            'lbrty_select_label'	=>	'SELECT STORE',
            'lbrty_img_display'		=>	false,
		);
		return $defaults;
	}

    /**
	 * Adds shortcodes to list content type
	 *
	 * @since    0.0.1
	 */
	public static function libreli_init_shortcodes() {
        add_shortcode( 'libreli-book', __CLASS__. '::do__libreli_book_shortcode' );
    }

    public static function do__libreli_book_shortcode( $atts ) {


        // Attributes
        $atts = shortcode_atts(
            array(
                    'book' => '',
                    'label' => 'Get your copy:',
                    'default_value' => 'SELECT STORE',
                ),
                $atts
        );

        /**
         * Get General options (License Key etc.)
         */
        $lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
        $lbrty_subscription_active = $lbrty_settings_general_options['lbrty_subscription_active'];

        /**
         * If License is not active
         */
        if (empty($lbrty_subscription_active)){
            $output = "<div style='border:2px solid #1e8bc3; padding: 10px 5px; margin: 10px 0;'>";
                $output .= __("You need to activate Libreli with a license key.","libreli");
            $output .= "</div>";

            return $output;
        }

        /**
         * Get Display options or Provide Feafults
         */
        $get_display_options = get_option( 'lbrty_settings_display_options' );
		if(empty('get_display_options') || false ==  $get_display_options) {
			Self::$display_options = Self::default_tpul_settings_gen_options();
		}else{
			Self::$display_options = $get_display_options;
        }

        $book_id = $atts['book'];
        // $lbrty_dropdown_label = sanitize_text_field($atts['label']);
        // $lbrty_select_label = sanitize_text_field($atts['default_value']);

        $lbrty_dropdown_styles = Self::$display_options['lbrty_dropdown_styles'];
        $lbrty_dropdown_label = sanitize_text_field(Self::$display_options['lbrty_dropdown_label']);
        $lbrty_select_label = sanitize_text_field(Self::$display_options['lbrty_select_label']);
        $lbrty_bg_color = sanitize_text_field(Self::$display_options['lbrty_bg_color']);
        $lbrty_text_color = sanitize_text_field(Self::$display_options['lbrty_text_color']);
        $lbrty_text_color_no_hash = substr($lbrty_text_color, 1);

        // Validation
        if (is_nan($book_id)){
            return __("<b>!!</b> Book paramater must be a number ex: [libreli-book book=39]",'libreli');
        }

        $html_output = '';


        // WP_Query arguments
        $args = array(
            'p'                      => $book_id,
            'post_type'              => array( 'lbrty_book' ),
        );

        // The Query
        $libreli_query = new WP_Query( $args );

        if ( $libreli_query->have_posts() ) {
            while ($libreli_query->have_posts()) {

                $libreli_query->the_post();
                $post_title = get_the_title();
                $post_id = get_the_ID();
                $isbn = get_post_meta( $post_id, 'lbrty_isbn', true );
                $isbn13 = get_post_meta( $post_id, 'lbrty_isbn13', true );

                $urls = get_post_meta( $post_id, 'lbrty_stores_found', true );

                if(empty($urls)){
                    return "";
                }

                $thumbnail = get_the_post_thumbnail($post_id);

                /**
                 * Style
                 */

                if ($lbrty_dropdown_styles) {
                // if ($lbrty_dropdown_styles && $lbrty_subscription_active) {
                    // https://freefrontend.com/css-select-boxes/
                $html_output .= <<<EOD
    <style>
        .libreli-select-css {
            display: block;
            /* font-size: 16px; */
            font-family: inherit;
            font-weight: normal;
            color: $lbrty_text_color;
            line-height: 1.3;
            padding: .6em 1.4em .5em .8em;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            margin: 0;
            box-shadow: 0 1px 0 1px rgba(0,0,0,.04);
            border-radius: 0;
            -moz-appearance: none;
            -webkit-appearance: none;
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23$lbrty_text_color_no_hash%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
            /* linear-gradient(to bottom, #ffffff 0%,#e5e5e5 100%); */
            background-repeat: no-repeat, repeat;
            background-position: right .7em top 50%, 0 0;
            background-size: .65em auto, 100%;
            background-color: $lbrty_bg_color;
            border: 1px solid $lbrty_bg_color;
            border-color: $lbrty_bg_color;

        }

        .libreli-select-css::-ms-expand {
            display: none;
        }

        .libreli-select-css:hover {
            border-color: $lbrty_bg_color;
        }

        .libreli-select-css:focus {
            border-color: $lbrty_bg_color;
            box-shadow: 0 0 1px 1px rgba(59, 153, 252, .7);
            box-shadow: 0 0 0 3px -moz-mac-focusring;
            color: $lbrty_text_color;
            outline: none;

            outline: none !important;
            border:1px solid $lbrty_bg_color;
            box-shadow: 0 0 10px #719ECE;

        }
        .libreli-select-css option {
            font-family: inherit;
            font-weight:normal;
            background: white;
            color: #000;
            border-color: $lbrty_bg_color;
        }

    </style>
EOD;
                }

                /**
                 * Display dorpdown with or without image
                 */

                if (false){

                    $img_output = '';

                    if (has_post_thumbnail($post_id)) {
                        $thumbnail = get_the_post_thumbnail($post_id);
                        $img_output .= "<div class='libreli-book__img'>";
                            $img_output .= $thumbnail;
                        $img_output .= "</div>";
                    }

                    $html_output .= "<div class='libreli-book libreli-book--with-image'>";

                        $html_output .= $img_output;

                        $html_output .= "<div class='libreli-book__detail'>";

                            if (!empty($post_title)){

                                $html_output .= "<h2>";
                                    $html_output .= $post_title;
                                $html_output .= "</h2>";
                            }


                            $html_output .= "<form>";
                                $html_output .= "<label for='libreli-select' class='libreli-book__label'>";
                                    $html_output .= $lbrty_dropdown_label;
                                $html_output .= "</label>";

                                $html_output .= "<select id='libreli-select' class='libreli-select-css' onchange='if (this.value) window.location.href=this.value' >";
                                    $html_output .= "<option value=''>{$lbrty_select_label}</option>";
                                    foreach ($urls as $key => $link) {
                                        if (!empty($link['found'])){
                                            $html_output .= "<option value='". $link["link"] ."'>" . $link["name"] . "</option>";
                                        }
                                    }
                                $html_output .= "</select>";
                            $html_output .= "</form>";

                        $html_output .= "</div>";

                    $html_output .= "</div>";

                }else{

                    $html_output .= "<div class='libreli-book'>";
                        $html_output .= "<div class='libreli-book__detail'>";
                            $html_output .= "<form>";
                                $html_output .= "<label for='libreli-select' class='libreli-book__label'>";
                                    $html_output .= $lbrty_dropdown_label;
                                $html_output .= "</label>";

                                $html_output .= "<select id='libreli-select' class='libreli-select-css' onchange='if (this.value) window.location.href=this.value' >";
                                    $html_output .= "<option value=''>{$lbrty_select_label}</option>";
                                    foreach ($urls as $key => $link) {
                                        if (!empty($link['found'])){
                                            $html_output .= "<option value='" . $link["link"] . "'>" . $link["name"] . "</option>";
                                        }
                                    }
                                $html_output .= "</select>";
                            $html_output .= "</form>";
                        $html_output .= "</div>";
                    $html_output .= "</div>";

                }

            }
        }else{

            $output = "<div style='border:2px solid #1e8bc3; padding: 10px 5px; margin: 10px 0;'>";
                $output .= __("Book Does not exist.","libreli");
            $output .= "</div>";

            return $output;
        }

        return $html_output;


    }

    public static function get_shortcode_format_text($post_id){
        // return "[lbrty-book book={$post_id}]";
        return "[libreli-book book={$post_id}]";
    }



}
