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
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Libreli
 * @subpackage Libreli/admin
 * @author     Lehel Matyus <contact@lehelmatyus.com>
 */

class Libreli_Manage_Page {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


	/**
	 * General Options Holder
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $lbrty_settings_general_options    The settings for the modal.
	 */
	private $lbrty_settings_general_options;
	private $lbrty_settings_display_options;

	private $lbrty_subscription_active;
	private $lbrty_subscription_type;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );
		$this->lbrty_settings_display_options = get_option( 'lbrty_settings_display_options' );
		$this->lbrty_settings_advanced_options = get_option( 'lbrty_settings_advanced_options' );

		/**
		 * Active or no
		 */
		$this->lbrty_subscription_active = $this->lbrty_settings_general_options['lbrty_subscription_active'];
		// $this->lbrty_subscription_active = true;
		$this->lbrty_subscription_type = $this->lbrty_settings_general_options['lbrty_subscription_type'];
		// $this->lbrty_subscription_type = "F";


    }

    /**
	 * This function introduces the theme options into the 'Settings' menu and into a top-level
	 * 'Perfecto Portfolio' menu.
	 */
	public function setup_manage_page_menu() {
        add_submenu_page(
            'edit.php?post_type=lbrty_book',
			__( 'Libreli Book Manager', 'libreli' ), 			// The title to be displayed in the browser window for this page.
			__( 'Libreli Book Manager', 'libreli' ),			// The text to be displayed for this menu item
            'manage_options',					        		// Which type of users can see this menu item
            'lbrty_general_settings',			        		// The unique ID - that is, the slug - for this menu item
			array( $this, 'render_lookup_settings')	    		// The name of the function to call when rendering this menu's page
		);
    }

	/**---------------------------------------------------------------------
     * Default Options
	 ---------------------------------------------------------------------*/

	 public function default_general_options() {
		$defaults = array(
            'lbrty_public_key'				   =>	'',
            'lbrty_license_key'  			   =>	'',
			'lbrty_subscription_active'  	   =>	false,
			'lbrty_subscription_type'  	       =>	'',

            'lbrty_book_slots'  			   =>	'',
            'lbrty_book_maximum_slots'		   =>	'',
            'lbrty_last_ran'		  	       =>	'',
            'lbrty_books'			  	       =>	'',
            // 'lbrty_books'			  	       =>	array(
			// 	0 => array(
			// 		'isbn' => '',
			// 		'isbn13' => ''
			// 	),
			// 	1 => array(
			// 		'isbn' => '',
			// 		'isbn13' => ''
			// 	),
			// )

		);
		return $defaults;
	}

	public function default_display_options() {
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

	public function default_advanced_options() {
		$defaults = array(
            'lbrty_amzn_aff'		=>	'',
            'lbrty_debug_log_on'	=>	0,
		);
		return $defaults;
	}

	/**--------------------------------------------------------------------------------
     * Settings fields for General Options
	 ---------------------------------------------------------------------*/

	 /**
	 * Initializes the theme's activated options
	 *
	 * This function is registered with the 'admin_init' hook.
	 */

	public function initialize_general_options(  ) {

		// delete_option('lbrty_settings_general_options');
		// var_dump(get_option( 'lbrty_settings_general_options' ));

        if( false == get_option( 'lbrty_settings_general_options' ) ) {
			$default_array = $this->default_general_options();
			update_option( 'lbrty_settings_general_options', $default_array );
        }

        /**
         * Add Section
         */
        add_settings_section(
            'lbrty_general_section',
            __( 'License', 'libreli' ),
            array( $this, 'general_options_callback'),
            'lbrty_settings_general_options'
        );

        /**
         * Add option to Section
         */

        // add_settings_field(
        //     'lbrty_public_key',
        //     __( 'Public Key', 'libreli' ),
        //     array( $this, 'lbrty_public_key_renders'),
        //     'lbrty_settings_general_options',
        //     'lbrty_general_section'
        // );

        add_settings_field(
            'lbrty_license_key',
            __( 'License Key', 'libreli' ),
            array( $this, 'lbrty_license_key_renders'),
            'lbrty_settings_general_options',
            'lbrty_general_section'
        );


        /**
         * Register Section
         */
        register_setting(
			'lbrty_settings_general_options',
			'lbrty_settings_general_options',
			array( $this, 'validate_general_options')
        );

	}

	/**
     * The Callback to assist with extra text
     */
    public function general_options_callback() {
		// echo '<p>' . esc_html__( 'Libreli Book Manager', 'libreli' ) . '</p>';
    }

	/**
     * Validator Callback to assist in validation
     */
    public function validate_general_options( $input ) {

		return $input;

		// Create our array for storing the validated options
		$output = array();

		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {

			if(is_array($value)){

				foreach( $value as $i => $v ) {
					if( isset( $v[$i] ) ) {
						// Strip all HTML and PHP tags and properly handle quoted strings
						$v[$i] = strip_tags( stripslashes( $v[ $i ] ) );
					} // end if
				}

				$output[$key] = $value;


			}else{

				// Check to see if the current option has a value. If so, process it.
				if( isset( $input[$key] ) ) {
					// Strip all HTML and PHP tags and properly handle quoted strings
					$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
				} // end if

			}
		} // end foreach

		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'validate_general_options', $output, $input );
    }


	/**---------------------------------------------------------------------------------
	 * Display options
	 * --------------------------------------------------------------------
	 */

    public function initialize_display_options( ){

		// delete_option('lbrty_settings_display_options');
		// var_dump(get_option( 'lbrty_settings_display_options' ));

		if( false == get_option( 'lbrty_settings_display_options' ) ) {
			$default_array = $this->default_display_options();
			update_option( 'lbrty_settings_display_options', $default_array );
		}

		/**
         * Add Section
         */
        add_settings_section(
            'lbrty_display_section',
            __( 'Display Settings', 'libreli' ),
            array( $this, 'display_options_callback'),
            'lbrty_settings_display_options'
		);

		add_settings_field(
            'lbrty_dropdown_label',
            __( 'Dropdown Label', 'libreli' ),
            array( $this, 'lbrty_dropdown_label_render'),
            'lbrty_settings_display_options',
            'lbrty_display_section'
        );

		add_settings_field(
            'lbrty_select_label',
            __( 'Dropdown Default Text', 'libreli' ),
            array( $this, 'lbrty_select_label_render'),
            'lbrty_settings_display_options',
            'lbrty_display_section'
		);

		add_settings_field(
            'lbrty_dropdown_styles',
            __( 'Custom Colors', 'libreli' ),
            array( $this, 'lbrty_dropdown_styles_render'),
            'lbrty_settings_display_options',
            'lbrty_display_section'
		);

		add_settings_field(
            'lbrty_bg_color',
            __( 'Dropdown Color', 'libreli' ),
            array( $this, 'lbrty_bg_color_render'),
            'lbrty_settings_display_options',
            'lbrty_display_section'
		);

		add_settings_field(
            'lbrty_text_color',
            __( 'Text Color', 'libreli' ),
            array( $this, 'lbrty_text_color_render'),
            'lbrty_settings_display_options',
            'lbrty_display_section'
		);

		/**
         * Register Section
         */
        register_setting(
			'lbrty_settings_display_options',
			'lbrty_settings_display_options',
			array( $this, 'validate_display_options')
        );

	}

	/**
     * The Callback to assist with extra text
     */
    public function display_options_callback() {
		// echo '<p>' . esc_html__( 'Libreli Book Manager', 'libreli' ) . '</p>';
    }

	/**
     * Validator Callback to assist in validation
     */
    public function validate_display_options( $input ) {

		// Create our array for storing the validated options
		$output = array();

		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {


			if(is_array($value)){

			}else{

				// Check to see if the current option has a value. If so, process it.
				if( isset( $input[$key] ) ) {
					// Strip all HTML and PHP tags and properly handle quoted strings
					$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
				} // end if

			}


		} // end foreach

		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'validate_general_options', $output, $input );
	}



	/**---------------------------------------------------------------------------------
	 * Advanced options
	 * --------------------------------------------------------------------
	 */

    public function initialize_advanced_options( ){

		// delete_option('lbrty_settings_advanced_options');
		// var_dump(get_option( 'lbrty_settings_advanced_options' ));

		if( false == get_option( 'lbrty_settings_advanced_options' ) ) {
			$default_array = $this->default_advanced_options();
			update_option( 'lbrty_settings_advanced_options', $default_array );
		}

		/**
         * Add Section
         */
        add_settings_section(
            'lbrty_advanced_section',
            __( 'Advanced Settings', 'libreli' ),
            array( $this, 'advanced_options_callback'),
            'lbrty_settings_advanced_options'
		);

		add_settings_field(
            'lbrty_amzn_aff',
            __( 'Amazon affiliate ID', 'libreli' ),
            array( $this, 'lbrty_amzn_aff'),
            'lbrty_settings_advanced_options',
            'lbrty_advanced_section'
        );

		add_settings_field(
            'lbrty_roam_server',
            __( 'Lookup Server', 'libreli' ),
            array( $this, 'lbrty_roam_server_callback'),
            'lbrty_settings_advanced_options',
            'lbrty_advanced_section'
        );

		add_settings_field(
            'lbrty_debug_log_on',
            __( 'Debug Log', 'libreli' ),
            array( $this, 'lbrty_debug_log_on_render'),
            'lbrty_settings_advanced_options',
            'lbrty_advanced_section'
		);


		/**
         * Register Section
         */
        register_setting(
			'lbrty_settings_advanced_options',
			'lbrty_settings_advanced_options',
			array( $this, 'validate_advanced_options')
        );

	}

	/**
     * The Callback to assist with extra text
     */
    public function advanced_options_callback() {
		// echo '<p>' . esc_html__( 'Libreli Book Manager', 'libreli' ) . '</p>';
    }

	/**
     * Validator Callback to assist in validation
     */
    public function validate_advanced_options( $input ) {

		// Create our array for storing the validated options
		$output = array();

		// Loop through each of the incoming options
		foreach( $input as $key => $value ) {


			if(is_array($value)){

			}else{

				// Check to see if the current option has a value. If so, process it.
				if( isset( $input[$key] ) ) {
					// Strip all HTML and PHP tags and properly handle quoted strings
					$output[$key] = strip_tags( stripslashes( $input[ $key ] ) );
				} // end if

			}


		} // end foreach

		// Return the array processing any additional functions filtered by this action
		return apply_filters( 'validate_general_options', $output, $input );
    }

    /**---------------------------------------------------------------------
     * Render the actual page
     ---------------------------------------------------------------------*/

    /**
	 * Renders a simple page to display for the theme menu defined above.
	 */
	public function render_lookup_settings( $active_tab = '' ) {

        ?>
        <div class="wrap">

			<h2><?php esc_html_e( 'Libreli Book Manager', 'libreli' ); ?></h2>

			<?php settings_errors(); ?>

			<?php if( isset( $_GET[ 'tab' ] ) ) {
				$active_tab = $_GET[ 'tab' ];
			} else if( $active_tab == 'lbrty_display_options' ) {
                $active_tab = 'lbrty_display_options';
             } else {
				$active_tab = 'lbrty_general_options';
			}

			?>

			<h2 class="nav-tab-wrapper">
				<a href="?post_type=lbrty_book&page=lbrty_general_settings&tab=lbrty_general_options" class="nav-tab <?php echo $active_tab == 'lbrty_general_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Book Manager', 'libreli' ); ?></a>
				<a href="?post_type=lbrty_book&page=lbrty_general_settings&tab=lbrty_display_options" class="nav-tab <?php echo $active_tab == 'lbrty_display_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Display Settings', 'libreli' ); ?></a>
				<a href="?post_type=lbrty_book&page=lbrty_general_settings&tab=lbrty_advanced_options" class="nav-tab <?php echo $active_tab == 'lbrty_advanced_options' ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Advanced', 'libreli' ); ?></a>
			</h2>

			<?php
			if( $active_tab == 'lbrty_display_options' ) {

				$this->__display_options_display();

			}elseif( $active_tab == 'lbrty_advanced_options' ) {

				$this->__display_options_advanced();

			}else{

				// Default Tab with enter license Key Card
				$this->__license_card_display();

				$this->__add_new_book();

				// If License is Active
				if (!empty($this->lbrty_subscription_active)){
					$this->__book_query();

					$this->__get_max_num_books_message();
					$this->__next_cron_run();
				}

			}

			?>

		</div>
		<?php

	}

	/** --------------------------------------------------------------------
	 * General options - License
	 * ---------------------------------------------------------------------
	 */

	public function __license_card_display(){

		switch ($this->lbrty_subscription_type) {
			case 'F':
				$lbrty_subscription_type_label = "Free";
				break;

			case 'S':
				$lbrty_subscription_type_label = "Premium";
				break;

			default:
				$lbrty_subscription_type_label = "Free";
				break;
		}


		$active_class = '';
		if (!empty($this->lbrty_subscription_active)){
			$active_class .= " lbrty-license-box--activated";
		}

		if (empty($this->lbrty_subscription_type) && $this->lbrty_subscription_type == 'F') {
			$active_class .= " lbrty-license-box--free";
		}

		$padding_class = '';
		if ($this->lbrty_subscription_type == 'S'){
			$padding_class = " lbrty_pb0";
		}

		?>

			<form class="lbrty-license-box<?php echo $active_class; ?><?php echo $padding_class; ?>" method="post" action="options.php">

					<h2>Account Status:

						<?php
							echo '<span class="activated_label-just-active" style="color: #008000; font-style: italic;">Active</span>';
							echo '<span class="activated_label-activated" style="color: #008000; font-style: italic;">Active - '. $lbrty_subscription_type_label . '</span>';
							echo '<span class="activated_label-inactive" style="color: #800000; font-style: italic;">Inactive</span>';
						?>

						<div>

							<!-- <a class="button lbrty-activate-button" href="#" target="_blank">Activate</a> -->

							<!-- <?php // if ($this->lbrty_subscription_type == "F" ){ ?>
								<a class="button" href="https://devlibreli.wpengine.com/#plans" target="_blank">Upgrade to Premium</a>
							<?php // } ?> -->

							<?php if (!empty($this->lbrty_subscription_active)){ ?>
								<a class="lbrty-view-key-button lbrty-view-key-button--open" style="font-size:14px;" href="#" >View License</a>
								<a class="lbrty-view-key-button lbrty-view-key-button--close" style="font-size:14px;" href="#" >Close</a>
							<?php }?>

						</div>

					</h2>

					<div class="lbrty-card-activate lbrty-card-sub-item">
							<?php
								settings_fields( 'lbrty_settings_general_options' );
								do_settings_sections( 'lbrty_settings_general_options' );
								// submit_button();
							?>
					</div>

						<div class="lbrty-card-sub-sub-item lbrty-card-create-account">
							<span><?php _e("Don't have an account? Create one in minutes and enable automatic lookups for free.",'libreli')?></span>
							<div>
								<a class="" href="https://libreli.com" target="_blank">Create Account</a>
							</div>
						</div>

						<?php if ( !empty($this->lbrty_subscription_type) && $this->lbrty_subscription_type == 'F'): ?>
							<div class="lbrty-card-sub-sub-item ">
								<span><?php _e("Need auto lookup for more than one book?",'libreli')?></span>
									<a class="" href="https://libreli.com" target="_blank">Upgrade to Premium</a>
							</div>
						<?php endif; ?>

						<!-- <div class="lbrty-card-sub-sub-item ">
							<a class="" href="https://libreli.com" target="_blank">Deactivate Account</a>
						</div> -->

			</form>

		<?php

	}


	/** --------------------------------------------------------------------
	 * Display options - License
	 * ---------------------------------------------------------------------
	 */

	public function __display_options_display(){

		echo '<form method="post" action="options.php" class="lbrty-display-options-form">';
			settings_fields( 'lbrty_settings_display_options' );
			do_settings_sections( 'lbrty_settings_display_options' );
			submit_button();
		echo '</form>';

	}


	public function __display_options_advanced(){
		// echo "<h2>" . __("Advanced options","libreli") . "</h2>";

		echo '<form method="post" action="options.php" class="lbrty-advanced-options-form">';
			settings_fields( 'lbrty_settings_advanced_options' );
			do_settings_sections( 'lbrty_settings_advanced_options' );
			submit_button();
		echo '</form>';

		if (!empty($this->lbrty_settings_advanced_options['lbrty_debug_log_on'])){
			echo "<hr>";
			echo "<h3>" . __("Debug Log","libreli") . "</h3>";
			// Libreli_Admin_Interfacer::__empty_log();
			Libreli_Admin_Interfacer::__read_log_size();
			Libreli_Admin_Interfacer::__read_full_log();
		}

	}



	public function __get_max_num_books_message(){

		$total_book_count = wp_count_posts( 'lbrty_book' )->publish;
		$max_books = Libreli_Admin_Interfacer::__get_max_books_allowed($this->lbrty_subscription_type);

		echo "<div class='lbrty_get_max_num_books'>";

			if ($total_book_count > $max_books ){
				echo '<div style="color:red; padding-bottom: 10px;">';
					echo "Current number of books: " . $total_book_count . " (max allowed: " . $max_books . ")";
					echo "<div><b>" . __( 'You need to upgrade your account or delete books in order to continue','libreli' ) . '.</b> <a href="/wp-admin/edit.php?post_type=lbrty_book">View all my books</a></div>';
				echo '</div>';
			}

			echo "<div>" . __( 'Total book count', 'libreli' ) . ": ". $total_book_count . "</div>";
			echo "<div>" . __( 'Maximum number of books allowed with your current license', 'libreli' ) . ": ". $max_books . "</div>";
		echo "</div>";

	}

	public function __next_cron_run(){

		// var_dump(get_option( 'lbrty_settings_general_options' ));

		echo "<div class='lbrty_next_scheduled'>";

			if ($this->lbrty_subscription_active){

				$libreli_cron = new Libreli_Cron();

				echo "<div>";
					echo __( 'Next scheduled lookup', 'libreli' );
					echo " <span class='lbrty_next_scheduled__date'>";
					echo $libreli_cron->get_next_scheduled_date();
					echo "</span>";
				echo "</div>";

			}else{
				echo __( 'You must activate Libreli for the auto book lookup feature to work.', 'libreli' );
			}

		echo "</div>";

	}


	/**
	 * Add New book button
	 */

	public function __add_new_book(){

		if ($this->lbrty_subscription_active){
			$is_disabled = "";
			$link = "/wp-admin/post-new.php?post_type=lbrty_book";
		}else{
			$is_disabled = "disabled";
			$link = "#";
		}

		if ($this->lbrty_subscription_active){
			echo '<div class="lbrty_add_new_book_container">';
				echo '<a href="' . $link . '" class="button button-primary lbrty_add_new_book lbrty_button_with_icon" ' . $is_disabled . '><span aria-hidden="true" class="dashicons dashicons-plus"></span> ' . __( 'Add New Book', 'libreli' ) . '</a>';
			echo '</div>';
		}
	}

	/**
	 * Book Query
	 */

	public function __book_query(){

		$max_books = Libreli_Admin_Interfacer::__get_max_books_allowed($this->lbrty_subscription_type);

		// $query_for_books = query_posts(array(
		// 	'post_type' => 'lbrty_book',
		// 	'showposts' => $max_books,
		// 	'orderby' => 'date',
        //     'order'   => 'ASC',
		// ) );

		$loop = new WP_Query(array(
			'post_type' => 'lbrty_book',
			'posts_per_page' => $max_books,
			'orderby' => 'date',
            'order'   => 'ASC',
		) );





		echo "<h2>";
			echo __( "Books currelty connected to Libreli auto book lookup.", "libreli" );
		echo "</h2>";

		// Libreli_Admin_Interfacer::__read_full_log();
		// $link_api = new Libreli_Get_Links_API();
		// $link_api->do_lbrty_get_links_from_api();

		if ($loop->have_posts()) {

		?>

				<table class="wp-list-table widefat fixed striped table-view-list posts">

				<thead>
					<tr>

						<td id="cb" class="manage-column column-cb check-column">
						<!-- <th scope="col" id="title" class="manage-column column-title column-primary"> -->
							<span style="padding-left: 8px;">#</span>
						</td>

							<!-- Automatic Lookup -->
						<!-- <th scope="col" id="title" class="manage-column column-title column-primary">
							Libreli
						</th> -->

						<th scope="col" id="title" class="manage-column column-title column-primary">
							<span>Book Title</span>
						</th>

						<th scope="col" id="title" class="manage-column column-title column-primary">
							<span>ISBN</span>
						</th>

						<th scope="col" id="title" class="manage-column column-title column-primary">
							Last lookup
						</th>

						<th scope="col" id="title" class="manage-column column-title column-primary">
							<span>Shortcode</span>
						</th>
					</tr>

				</thead>

				<tbody>

				<?php

				$output = '';
				$counter = 0;

				while ($loop->have_posts()) : $loop->the_post();

					$counter++;
					$title = get_the_title();
					$post_id = get_the_ID();
					$chekmark = ($post_id == 217 ) ? "<span class='dashicons dashicons-yes-alt'></span>": "";
					$isbn = get_post_meta( $post_id, 'lbrty_isbn', true );
					$isbn13 = get_post_meta( $post_id, 'lbrty_isbn13', true );
					$isbn_display = "ISBN: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $isbn . "<br>ISBN-13:&nbsp;&nbsp; " . $isbn13;

					$shortcode = new Libreli_Shortcodes($this->plugin_name, $this->version);
					$shortcode_text = $shortcode->get_shortcode_format_text($post_id);

					$urls = get_post_meta( $post_id, 'lbrty_stores_found', false );
					$lbrty_stores_found = get_post_meta($post_id, 'lbrty_stores_found');


					$output .= "<tr data-isbn='" . $isbn ."' data-isbn='" . $isbn13 ."' >";

						$output .= "<td>";
								$output .= $counter;
						$output .= "</td>";

						// $output .= "<td>";
						// 		$output .= $this->__get_automatic_lookup_status($post_id);
						// $output .= "</td>";


						$output .= "<td> <a href='" . get_edit_post_link() . "'>{$title} <small>" . __( '(edit)', 'libreli' ) . "</small></a> </td>";
						$output .= "<td> {$isbn_display} </td>";
						$output .= "<td>";

							// $output .= $chekmark;

							$output .= '<div class="lbrty-money-got-time">';
								$output .= Libreli_Admin_Interfacer::__get_last_lookup_time($post_id);
							$output .= '</div>';

							$output .= Libreli_Admin_Interfacer::__get_freedom_pills($lbrty_stores_found[0]);

						$output .= "</td>";
						$output .= "<td> <input type='text' id='country' name='shortcode' style='min-width:200px;' value='" . $shortcode_text . "' readonly> </td>";
					$output .= "</tr>";



				endwhile;

				echo $output;
				?>

				</tbody>
				</table>
		<?php

		}else{

			?>
			<table class="wp-list-table widefat fixed striped table-view-list posts">

				<thead>
					<tr>

						<!-- <td id="cb" class="manage-column column-cb check-column">
								<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
						</td> -->

						<th scope="col" id="title" class="manage-column column-title column-primary">
							<!-- Automatic Lookup -->
							Libreli
						</th>

						<th scope="col" id="title" class="manage-column column-title column-primary">
							<span>Book Title</span>
						</th>

						<th scope="col" id="title" class="manage-column column-title column-primary">
							<span>ISBN</span>
						</th>

						<th scope="col" id="title" class="manage-column column-title column-primary">
							Last lookup found
						</th>

						<th scope="col" id="title" class="manage-column column-title column-primary">
							<span>Shortcode</span>
						</th>
					</tr>

				</thead>

				<tbody>
					<tr>
						<td colspan="5">
							<?php echo __("You don't have any books yet.")?>
						</td>
					</tr>
				</tbody>

			</tabley>
		<?php

		}

	}

	public function __get_automatic_lookup_status($post_id){

		$lbrty_book_slots = $this->lbrty_settings_general_options['lbrty_book_slots'];
		$lbrty_subscription_type = $this->lbrty_settings_general_options['lbrty_subscription_type'];
		// $lbrty_subscription_type = "F";

		$lbrty_book_slots = array(
			0 => array(
				"id" => 217

			),
			1 => array(
				"id" => 2
			),
		);

		if (!empty($lbrty_book_slots) && !empty($lbrty_book_slots[0])){

			/**
			 * For Free License only the first one is enabled for Auto Lookup
			 */
			if ($lbrty_subscription_type =="F"){
				if ( !empty($lbrty_book_slots[0]["id"]) && $lbrty_book_slots[0]["id"] == $post_id  ){
					return "<span style='color: #008000; font-style: italic;'>Automatic Lookup</span>";
				}
				return "";
			}

			/**
			 * For Standard license up to 10 books can be Auto Looked up
			 */

			if ($lbrty_subscription_type =="S"){
				foreach ($lbrty_book_slots as $key => $book) {
					if ($book['id'] ==  $post_id){
						return "<span style='color: #008000; font-style: italic;'>Automatic Lookup</span>";
					}
				}
			}


		}

		return "";

	}


	/** --------------------------------------------------------------------
	 * Render input fields
	 * ---------------------------------------------------------------------
	 */

	function lbrty_public_key_renders(  ) {
        $options = $this->lbrty_settings_general_options;
        ?>
        <input type='text' class="regular-text" name='lbrty_settings_general_options[lbrty_public_key]' value='<?php echo $options['lbrty_public_key']; ?>'>
        <p class="description"> <?php echo __( 'Your public key recieved in the registration email.', 'libreli' ); ?> </p>
        <?php
    }

	function lbrty_license_key_renders(  ) {
		$options = $this->lbrty_settings_general_options;

		// Readonly Input if already active
		$readonly = '';
		if ($this->lbrty_subscription_active){
			$readonly = "readonly";
		}

        ?>
        <div>
			<input type='text' class="regular-text" name='lbrty_settings_general_options[lbrty_license_key]' value='<?php echo $options['lbrty_license_key']; ?>' <?php echo $readonly;?>>

			<?php if ($this->lbrty_subscription_active) : ?>
				<a id="lbrty__deactivate-key" class="lbrty_script_button button" href="#" onclick="deactivateKey(event)">Deactivate Key</a>
				<div class="lbrty_deactivate_button_msg_container">
					<span class="lbrty_deactivate_button_loader lbrty_hide"><img class="load_spinner" src="/wp-includes/images/spinner.gif" /></span>
					<span class="lbrty_deactivate_button_msg lbrty_hide"></span>
				</div>
			<?php else : ?>

				<a id="lbrty__activate-key" class="lbrty_script_button button" href="#" onclick="activateKey(event)">Activate Key</a>
				<div class="lbrty_deactivate_button_msg_container">
					<span class="lbrty_activate_button_loader lbrty_hide"><img class="load_spinner" src="/wp-includes/images/spinner.gif" /></span>
					<span class="lbrty_activate_button_msg lbrty_hide"></span>
				</div>

			<?php endif; ?>
		<div>

        <p class="description"> <?php echo __( 'Enter license key recieved in your registration email.', 'libreli' ); ?> </p>
        <?php
    }

	public function lbrty_dropdown_label_render() {
		$options = $this->lbrty_settings_display_options;

		$val = ( isset( $options['lbrty_dropdown_label'] ) ) ? $options['lbrty_dropdown_label'] : '';
		echo '<input type="text" name="lbrty_settings_display_options[lbrty_dropdown_label]" value="' . $val . '" class="regular-text" >';
		echo '<p class="description">' . __( 'Label displayed above the dropdown.', 'libreli' ) . '</p>';

	}
	public function lbrty_select_label_render() {
		$options = $this->lbrty_settings_display_options;

		$val = ( isset( $options['lbrty_select_label'] ) ) ? $options['lbrty_select_label'] : '';
		echo '<input type="text" name="lbrty_settings_display_options[lbrty_select_label]" value="' . $val . '" class="regular-text" >';
		echo '<p class="description">' . __( 'Displayed in the dropdown before user selects anything.', 'libreli' ) . '</p>';

	}

	public function lbrty_dropdown_styles_render() {

		$options = $this->lbrty_settings_display_options;

		if( !isset( $options['lbrty_dropdown_styles'] ) ) $options['lbrty_dropdown_styles'] = 0;

		echo '<input type="checkbox" id="lbrty_dropdown_styles" name="lbrty_settings_display_options[lbrty_dropdown_styles]" value="1"' . checked( 1, $options['lbrty_dropdown_styles'], false ) . '/>';
		echo '<label for="lbrty_dropdown_styles">Enable custom colors below for dropdown.</label>';
		echo '<p class="description">' . __( 'Uncheck this if you would like to display a plain HTML dropdown.', 'libreli' ) . '</p>';

		if (!$this->lbrty_subscription_active || $this->lbrty_subscription_type == 'F'){
			echo  '<p class="lbrty-description-premium-only">' . __( 'This feature is only available in with a premium license key.') . "</p>";
			echo "<div class='lbrty-cover'>";
			echo "</div>";
		}

	}

	public function lbrty_bg_color_render() {
		$options = $this->lbrty_settings_display_options;

		$val = ( isset( $options['lbrty_bg_color'] ) ) ? $options['lbrty_bg_color'] : '';
		echo '<input type="text" name="lbrty_settings_display_options[lbrty_bg_color]" value="' . $val . '" class="lbrty-color-picker" >';
		echo '<p class="description">' . __( 'Color for the dropdown.', 'libreli' ) . '</p>';

		if (!$this->lbrty_subscription_active || $this->lbrty_subscription_type == 'F'){
			echo  '<p class="lbrty-description-premium-only">' . __( 'This feature is only available in with a premium license key.') . "</p>";
			echo "<div class='lbrty-cover'>";
			echo "</div>";
		}

	}

	public function lbrty_text_color_render() {
		$options = $this->lbrty_settings_display_options;


		$val = ( isset( $options['lbrty_text_color'] ) ) ? $options['lbrty_text_color'] : '';
		echo '<input type="text" name="lbrty_settings_display_options[lbrty_text_color]" value="' . $val . '" class="lbrty-color-picker" >';
		echo '<p class="description">' . __( 'Color for text inside the dropdown.', 'libreli' ) . '</p>';

		if (!$this->lbrty_subscription_active || $this->lbrty_subscription_type == 'F'){
			echo  '<p class="lbrty-description-premium-only">' . __( 'This feature is only available in with a premium license key.') . "</p>";
			echo "<div class='lbrty-cover'>";
			echo "</div>";
		}

	}

	public function lbrty_roam_server_callback() {
		$options = $this->lbrty_settings_advanced_options;
		$val = ( isset( $options['lbrty_roam_server'] ) ) ? $options['lbrty_roam_server'] : 'default';

        echo "<select name='lbrty_settings_advanced_options[lbrty_roam_server]'>";
            echo "<option value='default'" . selected( $options['lbrty_roam_server'], 'default' ) .">" .  __('- Libreli Default - ','libreli') . "</option>";
            echo "<option value='dev'" . selected( $options['lbrty_roam_server'], 'dev' ) .">" .  __('Libreli D Server','libreli') . "</option>";
            echo "<option value='test'" . selected( $options['lbrty_roam_server'], 'test' ) .">" .  __('Libreli T Server','libreli') . "</option>";
            echo "<option value='prod'" . selected( $options['lbrty_roam_server'], 'prod' ) .">" .  __('Libreli P Server','libreli') . "</option>";
		echo "</select>";

		echo " <p class='description'>";
		echo __( 'This option should be left "Default". It is only to be changed if asked by support to help debug any potential issues.', 'libreli' );
		echo "</p>";


	}

	public function lbrty_amzn_aff() {
		$options = $this->lbrty_settings_advanced_options;

		$val = ( isset( $options['lbrty_amzn_aff'] ) ) ? $options['lbrty_amzn_aff'] : '';
		echo '<input type="text" name="lbrty_settings_advanced_options[lbrty_amzn_aff]" value="' . $val . '" class="regular-text" >';
		echo '<p class="description">' . __( 'Add your Amazon affiliate ID if you have one. It looks something like: librelisample-20', 'libreli' ) . '</p>';
		echo '<p class="description">' . __( 'Once updated the Libreli book links to Amazon will be updated to use your affiliate ID within 24 hours.', 'libreli' ) . '</p>';

		if (!$this->lbrty_subscription_active || $this->lbrty_subscription_type == 'F'){
			echo  '<p class="lbrty-description-premium-only">' . __( 'This feature is only available in with a premium license key.') . "</p>";
			echo "<div class='lbrty-cover'>";
			echo "</div>";
		}

	}

	public function lbrty_debug_log_on_render() {
		$options = $this->lbrty_settings_advanced_options;

        if( !isset( $options['lbrty_debug_log_on'] ) ) $options['lbrty_debug_log_on'] = 0;

        $html = '<input type="checkbox" id="lbrty_debug_log_on" name="lbrty_settings_advanced_options[lbrty_debug_log_on]" value="1"' . checked( 1, $options['lbrty_debug_log_on'], false ) . '/>';
		$html .= '<label for="lbrty_debug_log_on">' . __("Enable Debug Log", 'libreli' ) . '</label>';
		$html .=  '<p class="description">' . __( 'Only enable this if you were asked to do so by the Libreli support team.', 'libreli' ) . '</p>';


		echo $html;

	}

}