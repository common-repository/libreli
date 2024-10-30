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

class Libreli_Post_Type_Metaboxes {

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

	// public function init() {
	// 	add_action( 'add_meta_boxes', array( $this, 'isbn_meta_boxes' ) );
	// 	add_action( 'save_post', array( $this, 'save_meta_boxes' ),  10, 2 );
	// }

	/**
	 * Register the metaboxes to be used for the team post type
	 *
	 * @since 0.1.0
	 */
	public function isbn_meta_boxes() {
		add_meta_box(
			'profile_fields',
			'Book fields',
			array( $this, 'render_meta_boxes' ),
			'lbrty_book',
			'normal',
			'high'
		);
	}

   /**
	* The HTML for the fields
	*
	* @since 0.1.0
	*/
	function render_meta_boxes( $post ) {

		$lbrty_settings_general_options = get_option( 'lbrty_settings_general_options' );

		$lbrty_license_key = $lbrty_settings_general_options['lbrty_license_key'];
		$lbrty_subscription_type = $lbrty_settings_general_options['lbrty_subscription_type'];

		$meta = get_post_custom( $post->ID );
		$is_published = ('publish' === get_post_status($post->ID)) ? true : false;
		$lbrty_isbn = ! isset( $meta['lbrty_isbn'][0] ) ? '' : $meta['lbrty_isbn'][0];
		$lbrty_isbn13 = ! isset( $meta['lbrty_isbn13'][0] ) ? '' : $meta['lbrty_isbn13'][0];

		$lbrty_first_lookup_done = ! isset( $meta['lbrty_first_lookup'][0] ) ? '' : $meta['lbrty_first_lookup'][0];
		$lbrty_last_lookup_time = ! isset( $meta['lbrty_last_lookup_time'][0] ) ? '' : $meta['lbrty_last_lookup_time'][0];
		// $lbrty_first_lookup_done = get_post_meta($post->ID, 'lbrty_first_lookup');
		$lbrty_stores_found = get_post_meta($post->ID, 'lbrty_stores_found');

		$total_book_count = wp_count_posts( 'lbrty_book' )->publish;

		/**
		 * Check if using the free license
		 * if free license check if we are editing the one that was added first
		 * if we are editing another (tryning to add another) Lock verything
		 *
		 */
		$free_user_edit_allowed = $this->is_free_user_editing_his_first_book();


		// $libreli_shortcode = ! isset( $meta['lbrty_shortcode'][0] ) ? '' : $meta['lbrty_shortcode'][0];

		$libreli_shortcode = "";
		if (!empty($post->post_title)){
			$shortcode = new Libreli_Shortcodes($this->plugin_name, $this->version);
			$libreli_shortcode = $shortcode->get_shortcode_format_text($post->ID);
		}

		$last_lookup_performed = ! isset( $meta['links'][0] ) ? '' : $meta['links'][0];
		$links = ! isset( $meta['links'][0] ) ? '' : $meta['links'][0];

		// $facebook = ! isset( $meta['profile_facebook'][0] ) ? '' : $meta['profile_facebook'][0];

		wp_nonce_field( basename( __FILE__ ), 'profile_fields' );

		/**
		 * Check if reached max books
		 */

		$max_books = Libreli_Admin_Interfacer::__get_max_books_allowed($lbrty_subscription_type);
		$disable = "";
		$message = "";
		$totals = "";

		/**
		 *  0 - if not active license
		 *  1 - if reached
		 *  2 - if over
		 */
		$reached_or_over = Libreli_Admin_Interfacer::__has_reached_max_books($total_book_count, $lbrty_subscription_type);

		if ($total_book_count < $max_books) {
			$message .= '<div class="lbrty_initial_lookup_notify lbrty_initial_lookup_notify--noborder">';
				$message .= $total_book_count . " " . __( 'books being looked up', 'libreli' ) . " / " . ($max_books - $total_book_count) . " " . __( 'available','libreli' );
			$message .= '</div>';
		}

		if ( $max_books == 0){

			// License type is not set, most likely license is not active
			$disable = "disabled='true'";
			$message .= '<div class="lbrty_initial_lookup_notify">';
				$message .= __( 'You need to have an active license to add books. ','libreli' );
				$message .= " <a href='/wp-admin/edit.php?post_type=lbrty_book&page=lbrty_general_settings'>" . __( 'Enter free or premium license key.','libreli' ) . "</a>";
			$message .= '</div>';

		}

		// if not a free user editing is first added post

		if ( $reached_or_over == 1){

			$disable = "disabled='true'";

			$totals .= '<div style="">';
				$totals .= "Current number of books: " . $total_book_count . " (max allowed: " . $max_books . ")";
			$totals .= '</div>';

			$message .= '<div class="lbrty_initial_lookup_notify lbrty_initial_lookup_notify--noborder">';
				$message .= $totals;
				$message .= __( 'You have reached the maximum number of books allowed for your account. If you wish to add more books','libreli' ) . " <a target='_blank' href='https://www.libreli.com'>" . __("Upgrade to Premium", "libreli")  . "</a>.";;
			$message .= '</div>';

		}

		if ( $reached_or_over == 2 ) {

			$disable = "disabled='true'";

			$totals .= '<div style="">';
				$totals .= $total_book_count . " " . __( 'published','libreli' ) . " / " . ($max_books - $total_book_count) . " " . __( 'book lookup available with current license (Need more? ','libreli' ) . "<a target='_blank' href='https://www.libreli.com'>" . __("Upgrade to Premium", "libreli")  . "</a>.)";
			$totals .= '</div>';

			$message .= '<div class="lbrty_initial_lookup_notify">';
				$message .= "<div><b>" . __( 'You need to upgrade your account or delete books in order to continue','libreli' ) . '.</b></div>';
				$message .= $totals;
				$message .= "<div><br />" . __( 'You have reached the maximum number of books allowed for your account. Upgrade to a Premium account to add more books.','libreli' ) . '</div>';
			$message .= '</div>';

		}

		/**
		 * Whatever the case above, allow free users to edit their first book
		 */
		if($free_user_edit_allowed){
			$disable = "";
		}

		?>

		<table class="form-table">

			<tr>
				<td class="lbrty_meta_box_td" colspan="2">
					<label for="lbrty_isbn"><?php _e( 'Books', 'libreli' ); ?>
					</label>
				</td>
				<td colspan="4">
						<?php echo $message; ?>
				</td>
			</tr>
			<tr>
				<td class="lbrty_meta_box_td" colspan="2">

				</td>
				<td colspan="4">
				<p class="description"><?php _e( 'Entering both ISBNs will yield better results.', 'libreli' ); ?></p>

				</td>
			</tr>
			<tr>
				<td class="lbrty_meta_box_td" colspan="2">
					<label for="lbrty_isbn"><?php _e( 'ISBN', 'libreli' ); ?>
					</label>
				</td>
				<td colspan="4">
					<input type="text" name="lbrty_isbn" class="regular-text" value="<?php echo $lbrty_isbn; ?>" <?php echo $disable; ?>>
				</td>
			</tr>

			<tr>
				<td class="lbrty_meta_box_td" colspan="2">
					<label for="disable13"><?php _e( 'ISBN13', 'libreli' ); ?>
					</label>
				</td>
				<td colspan="4">
					<input type="text" name="lbrty_isbn13" class="regular-text" value="<?php echo $lbrty_isbn13; ?>" <?php echo $disable; ?>>
				</td>
			</tr>

			<tr>
				<td class="lbrty_meta_box_td" colspan="2">
					<label for="lbrty_shortcode"><?php _e( 'Shortcode', 'libreli' ); ?>
					</label>
				</td>
				<td colspan="4">
					<input type="text" name="lbrty_shortcode" class="regular-text" value="<?php echo $libreli_shortcode; ?> " readonly <?php echo $disable; ?>>
					<p class="description"><?php _e( 'Publish this Libreli book to have the shortcode generated. Once generated, place this shortcode on any page to display this book', 'libreli' ); ?></p>
				</td>
			</tr>

			<?php if ($is_published) : ?>
				<tr>
					<td colspan="6">
						<hr />
					</td>
				</tr>


				<?php // @TODO : Remove True in If statement ?>
				<?php //if ( true || ($is_published && ($reached_or_over !== 2 ) && $lbrty_first_lookup_done == false  ) ) : ?>
				<?php if ( ($is_published && ($reached_or_over !== 2 ) && $lbrty_first_lookup_done == false  ) ) : ?>

					<tr class="lbrty_first_lookup_done_row">
						<td colspan="2">
							<label for="lbrty_run_initial_book_lookup"><?php _e( 'Perform initial lookup', 'libreli' ); ?>
							</label>
						</td>
						<td colspan="4">
							<span>
								<a id="lbrty_run_initial_book_lookup" href="#" class="button button-primary lbrty_button_with_icon lbrty_run_initial_book_lookup" style="vertical-align: middle;display: inline-block;" onclick="perform_initial_lookup(event)">
									<span aria-hidden="true" class="dashicons dashicons-controls-repeat"></span>
									<?php _e( 'Run initial Libreli book lookup', 'libreli' ); ?>
								</a>
								<p class="description"><?php _e( 'All subsequent lookup will be automatically scheduled.', 'libreli' ); ?></p>
							</span>
						</td>
					</tr>
					<tr>
						<td colspan="2">
						</td>
						<td colspan="4">
							<div class="lbrty_initial_lookup_progress lbrty_clearfix lbrty_hide lbrty_spinner_with_message">
								<span class="spinner is-active"></span>
								<span class="lbrty_initial_progress_msg spinner_msg">
								<?php _e( 'Standby. We\'re checking popular online sites to see where your book is listed, and it could take up to three minutes. Please leave this window open.', 'libreli' ); ?>
								</span>
							</div>

							<div class="lbrty_initial_lookup_msg lbrty_hide">
							</div>
						</td>
					</tr>
				<?php endif; ?>


				<?php if ($lbrty_first_lookup_done == true): ?>

					<tr>
						<td class="lbrty_meta_box_td" colspan="2">
							<label for="lbrty_last_lookup_performed"><?php _e( 'Last Lookup Performed', 'libreli' ); ?>
							</label>
						</td>
						<td colspan="4">
							<?php
								if (!empty($lbrty_last_lookup_time)){
									echo $lbrty_last_lookup_time;
								}else{
									echo "-";
								}
							?>
						</td>
					</tr>

					<?php
						// var_dump($lbrty_license_key);
						// var_dump($lbrty_first_lookup_done);
						// var_dump($lbrty_stores_found);
					?>

					<tr>
						<td class="lbrty_meta_box_td" colspan="2">
							<label for="lbrty_links"><?php _e( 'Links found', 'libreli' ); ?>
							</label>
						</td>
						<td colspan="4">
							<?php
								echo Libreli_Admin_Interfacer::__get_freedom_pills($lbrty_stores_found[0]);
							?>
						</td>
					</tr>

				<?php endif; ?>


				<!--
				<tr>
					<td class="lbrty_meta_box_td" colspan="2">
					</td>
					<td colspan="4">
						<a href="/wp-admin/edit.php?post_type=lbrty_book&page=lbrty_general_settings">Libreli Book Manager </a>
					</td>
				</tr> -->

			<?php endif; ?>


		</table>

	<?php }


	private function is_free_user_editing_his_first_book(){
		$post_id = $_GET['post'];

		if (Libreli_Admin_Interfacer::__get_max_books_allowed() !== 1){
			// user is not a free user
			return false;
		}

		if (empty($post_id)){
			return false;
		}

		$all_books = Libreli_Admin_Interfacer::__get_all_published_books();

		if ( !empty($all_books[0]) &&  ($all_books[0]->ID == $post_id) ){
			// we are editing the very earliest added post
			return true;
		}

		return false;

	}

	/**
	 * Save metaboxes
	 *
	 * @since 0.1.0
	 */
	function save_meta_boxes( $post_id ) {

		global $post;

		// Verify nonce
		if ( !isset( $_POST['profile_fields'] ) || !wp_verify_nonce( $_POST['profile_fields'], basename(__FILE__) ) ) {
			return $post_id;
		}

		// Check Autosave
		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']) ) {
			return $post_id;
		}

		// Don't save if only a revision
		if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
			return $post_id;
		}

		// Check permissions
		if ( !current_user_can( 'edit_post', $post->ID ) ) {
			return $post_id;
		}

        $meta['lbrty_isbn'] = ( isset( $_POST['lbrty_isbn'] ) ? sanitize_title( $_POST['lbrty_isbn'] ) : '' );

		$meta['lbrty_isbn13'] = ( isset( $_POST['lbrty_isbn13'] ) ? sanitize_title( $_POST['lbrty_isbn13'] ) : '' );

		// $meta['lbrty_isbn13'] = ( isset( $_POST['lbrty_isbn13'] ) ? esc_url( $_POST['lbrty_isbn13'] ) : '' );

		// $meta['profile_facebook'] = ( isset( $_POST['profile_facebook'] ) ? esc_url( $_POST['profile_facebook'] ) : '' );

		foreach ( $meta as $key => $value ) {
			update_post_meta( $post->ID, $key, $value );
		}
	}




	/**
	 * Filters for Book create page
	 */

	// Change the book title placeholder text on Book crete/edit page
	function change_book_title_placeholder($title , $post){

        if( $post->post_type == 'lbrty_book' ){
            $my_title = "Enter the book's title";
            return $my_title;
        }

        return $title;

    }





}