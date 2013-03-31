<?php

/**
 * class that handles admin UI for iG:Twitter Cards plugin
 *
 * @author Amit Gupta
 */

class iG_Twitter_Cards_Admin extends iG_Twitter_Cards {

	protected $_default_options_mb = array(
		'card_type' => 'summary',
		'author_twitter' => '',
		'player_url' => '',
		'player_width' => '',
		'player_height' => '',
		'player_image' => '',
	);

	/**
	 * Singleton implemented, protected constructor so its accessible by parent class
	 */
	protected function __construct() {
		//init core stuff
		$this->_init_core();

		//call function to add options menu item
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		//setup our style/script enqueuing for wp-admin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_stuff' ) );
		//setup callback for AJAX on admin page
		add_action( 'wp_ajax_ig-tc-save-opts', array( $this, 'save_plugin_options' ) );

		//set up display of Twitter field in user profile
		add_action( 'show_user_profile', array( $this, 'show_user_twitter_field' ) );
		add_action( 'edit_user_profile', array( $this, 'show_user_twitter_field' ) );
		//set up to save Twitter field in user profile
		add_action( 'personal_options_update', array( $this, 'save_user_twitter_field' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_user_twitter_field' ) );

		if( $this->_get_option('show_on_post_page') == 'yes' ) {
			//add metabox on post pages in admin
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
			//save metabox data on post pages in admin
			add_action( 'save_post', array( $this, 'save_meta_box' ) );
		}
	}

	public function add_meta_box() {
		if( empty( $this->_post_types ) || ! is_array( $this->_post_types ) ) {
			return;
		}

		foreach( $this->_post_types as $post_type ) {
			add_meta_box(
				parent::plugin_id . '-mb',
				'iG:Twitter Cards',
				array( $this, 'add_meta_box_ui' ),
				$post_type,
				'normal',
				'core'
			);
		}
	}

	public function add_meta_box_ui( $post ) {
		//add nonce field
		wp_nonce_field( parent::plugin_id . '-mb-nonce', 'ig_tc_nonce' );
		$mb_options = get_post_meta( $post->ID, 'ig_tc_mb', true );
		if( empty( $mb_options ) || ! is_array( $mb_options ) ) {
			$mb_options = array();

			//for the first time, use twitter name from author's profile
			$mb_options['author_twitter'] = get_the_author_meta( 'twitter', intval( $post->post_author ) );
		}
		$mb_options = wp_parse_args( $mb_options, $this->_default_options_mb );
?>
		<div id="<?php echo parent::plugin_id; ?>-mb-inner">
		<table width="95%" border="0">
			<tr>
				<td width="20%">
					<label for="ig_tc_author_twitter">Author's Twitter <strong>:</strong></label>
					<div style="display: inline-block; float: right;">@</div>
				</td>
				<td>
					<input name="ig_tc_author_twitter" id="ig_tc_author_twitter" class="regular-text" value="<?php echo $mb_options['author_twitter']; ?>" />
				</td>
			</tr>
			<tr>
				<td><label for="ig_tc_card_type">Card Type <strong>:</strong></label></td>
				<td>
					<select id="ig_tc_card_type" name="ig_tc_card_type">
						<option value="player" <?php selected( $mb_options['card_type'], 'player' ) ?>>Player</option>
						<option value="summary" <?php selected( $mb_options['card_type'], 'summary' ) ?>>Summary</option>
					</select>
					<span class="description">Select <strong>Player</strong> if you have video in your content</span>
				</td>
			</tr>
			<tr class="ig-tc-mb-player-ui">
				<td><label for="ig_tc_player_url">Player URL <strong>:</strong></label></td>
				<td>
					<input name="ig_tc_player_url" id="ig_tc_player_url" class="regular-text" value="<?php echo $mb_options['player_url']; ?>" />
				</td>
			</tr>
			<tr class="ig-tc-mb-player-ui">
				<td><label for="ig_tc_player_width">Player Width <strong>:</strong></label></td>
				<td>
					<input name="ig_tc_player_width" id="ig_tc_player_width" class="regular-text" value="<?php echo $mb_options['player_width']; ?>" />
					<span class="description">Enter player width in pixels</span>
				</td>
			</tr>
			<tr class="ig-tc-mb-player-ui">
				<td><label for="ig_tc_player_height">Player Height <strong>:</strong></label></td>
				<td>
					<input name="ig_tc_player_height" id="ig_tc_player_height" class="regular-text" value="<?php echo $mb_options['player_height']; ?>" />
					<span class="description">Enter player height in pixels</span>
				</td>
			</tr>
			<tr class="ig-tc-mb-player-ui">
				<td><label for="ig_tc_player_image">Player Image URL <strong>:</strong></label></td>
				<td>
					<input name="ig_tc_player_image" id="ig_tc_player_image" class="regular-text" value="<?php echo $mb_options['player_image']; ?>" />
					<span class="description">Enter URL of image displayed as placeholder for player</span>
				</td>
			</tr>
		</table>
		</div>
<?php
	}

	/**
	 * This function saves input from meta-box on post/page add/edit in wp-admin
	 */
	public function save_meta_box( $post_id ) {
		if( wp_is_post_revision( $post_id ) !== false ) {
			$post_id = wp_is_post_revision( $post_id );
		}

		if( ! isset( $_POST['post_type'] ) || empty( $_POST['post_type'] ) || ! in_array( $_POST['post_type'], $this->_post_types ) ) {
			return;
		}

		check_admin_referer( parent::plugin_id . '-mb-nonce', 'ig_tc_nonce' );

		$data['author_twitter'] = sanitize_title( strtolower( trim( str_replace( '-', '', $_POST['ig_tc_author_twitter'] ) ) ) );
		$data['card_type'] = ( $_POST['ig_tc_card_type'] !== 'player' ) ? 'summary' : 'player';

		$data['player_url'] = '';
		$data['player_width'] = '';
		$data['player_height'] = '';
		$data['player_image'] = '';

		if( $data['card_type'] == 'player' ) {
			$data['player_url'] = esc_url_raw( $_POST['ig_tc_player_url'] );
			$data['player_width'] = floatval( $_POST['ig_tc_player_width'] );
			$data['player_height'] = floatval( $_POST['ig_tc_player_height'] );
			$data['player_image'] = esc_url_raw( $_POST['ig_tc_player_image'] );
		}

		update_post_meta( $post_id, 'ig_tc_mb', $data );
	}

	/**
	 * This function adds Twitter field in user profiles
	 */
	public function show_user_twitter_field( $user ) {
		if( empty( $user ) || ! is_object( $user ) ) {
			return;
		}
?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="twitter_name">Twitter</label></th>
					<td>
						<input type="text" id="twitter_name" name="twitter_name" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" />
						<span class="description">Enter your Twitter username without @</span>
					</td>
				</tr>
			</tbody>
		</table>
<?php
	}

	/**
	 * This function saves Twitter field in user profiles
	 */
	public function save_user_twitter_field( $user_id ) {
		if( empty( $user_id ) || ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		if( ! isset( $_POST['twitter_name'] ) ) {
			return;
		}

		$_POST['twitter_name'] = sanitize_title( $_POST['twitter_name'] );
		update_usermeta( $user_id, 'twitter', $_POST['twitter_name'] );
	}

	/**
	 * This function adds plugin's admin page in the Settings menu
	 */
	public function add_menu() {
		add_options_page( parent::plugin_name . ' Options', parent::plugin_name, 'manage_options', parent::plugin_id . '-page', array($this, 'admin_page') );
	}

	/**
	 * This function constructs the UI for the plugin admin page
	 */
	public function admin_page() {
?>
		<div class="wrap">
			<h2><?php print( '<strong>' . parent::plugin_name . '</strong> Options' ); ?></h2>
			<p>&nbsp;</p>
			<p>You can change global options here.</p>
			<p>&nbsp;</p>
			<form id="ig-tc-admin-form" action="" method="post">
			<table id="ig-tc-admin-ui" width="85%" border="0">
				<tr>
					<td width="28%">
						<label for="site_twitter_name">Site Twitter Name</label>
						<div style="display: inline-block; float: right;">@</div>
					</td>
					<td width="35%">
						<input name="site_twitter_name" id="site_twitter_name" class="ig-tc-option regular-text" value="<?php echo $this->_get_option( 'site_twitter_name' ); ?>" />
					</td>
					<td><span class="description">Should be a valid Twitter username</span></td>
				</tr>
				<tr>
					<td><label for="fallback_image_url">Fallback Image URL</label></td>
					<td>
						<input name="fallback_image_url" id="fallback_image_url" class="ig-tc-option regular-text" value="<?php echo $this->_get_option( 'fallback_image_url' ); ?>" />
					</td>
					<td>&nbsp</td>
				</tr>
				<tr>
					<td><label for="show_on_post_page">Allow card customization on post?</label></td>
					<td>
						<select name="show_on_post_page" id="show_on_post_page" class="ig-tc-option">
							<option value="yes" <?php selected( $this->_get_option( 'show_on_post_page' ), 'yes' ) ?>>YES</option>
							<option value="no" <?php selected( $this->_get_option( 'show_on_post_page' ), 'no' ) ?>>NO</option>
						</select>
					</td>
					<td>&nbsp</td>
				</tr>
				<tr>
					<td><label for="home_title">Title for Home/Archive Page</label> <span class="ig-tc-required">*</span></td>
					<td>
						<input name="home_title" id="home_title" class="ig-tc-option regular-text" value="<?php echo $this->_get_option( 'home_title' ); ?>" />
					</td>
					<td><span class="description">No HTML in Title</span></td>
				</tr>
				<tr>
					<td><label for="home_desc">Description for Home/Archive Page</label> <span class="ig-tc-required">*</span></td>
					<td>
						<textarea name="home_desc" id="home_desc" class="ig-tc-option large-text" rows="5" cols="55"><?php echo $this->_get_option( 'home_desc' ); ?></textarea>
					</td>
					<td>&nbsp</td>
				</tr>
				<tr>
					<td colspan="3">&nbsp;</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<input type="submit" id="btn_ig_tc_opts" name="btn_ig_tc_opts" class="button button-primary" value="Save Options" />
					</td>
					<td>&nbsp</td>
				</tr>
			</table>
			</form>
		</div>
<?php
	}

	/**
	 * This function takes in the message & its type to be sent in response to AJAX call
	 * and returns the proper HTML string which can be sent back to browser as is.
	 */
	private function _create_ajax_message( $message, $type='success' ) {
		if( empty($message) ) {
			return;
		}

		$type = ( strtolower( trim($type) ) === 'success' ) ? 'success' : 'error';	//type can only be either one

		return '<span class="ig-tc-' . $type . '">' . $message . '</span>';
	}

	/**
	 * This function is used to send a JSON encoded response to the browser. It accepts
	 * a string or an array as parameter.
	 */
	private function _send_ajax_response( $response = array() ) {
		$response = ( ! is_array($response) ) ? array($response) : $response;

		header("Content-Type: application/json");
		echo json_encode( $response );		//we want json
		unset( $response );	//clean up
		die();	//wp_die() is not good if you're sending json content
	}

	/**
	 * This function is called by WP to handle our AJAX requests
	 */
	public function save_plugin_options() {
		if( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$response = array(
			'nonce' => wp_create_nonce( parent::plugin_id . '-nonce' ),	//lets refresh the nonce for our next ajax call
			'error' => 1	//lets assume its an error
		);

		//check & see if we have the values
		if( ! check_ajax_referer( parent::plugin_id . '-nonce', '_ig_tc_nonce', false ) || empty( $_POST['options'] ) ) {
			$response['msg'] = $this->_create_ajax_message( 'Invalid request sent, please refresh the page and try again', 'error' );
			$this->_send_ajax_response($response);
		}

		parse_str( $_POST['options'], $data );

		if( empty( $data ) || ! is_array( $data ) ) {
			//set error message
			$response['msg'] = $this->_create_ajax_message( 'Unable to save options', 'error' );
			$this->_send_ajax_response($response);	//send response to browser & exit
		}

		$data['site_twitter_name'] = sanitize_title( strtolower( trim( $data['site_twitter_name'] ) ) );
		$data['fallback_image_url'] = esc_url_raw( $data['fallback_image_url'] );
		$data['show_on_post_page'] = ( ! $this->_is_yesno( $data['show_on_post_page'] ) ) ? 'no' : strtolower( $data['show_on_post_page'] );
		$data['home_title'] = wp_kses_post( strip_tags( $data['home_title'] ) );
		$data['home_desc'] = wp_kses_post( $data['home_desc'] );

		$data = wp_parse_args( $data, $this->_default_options );	//weed out any extra vars

		if( empty( $data['home_title'] ) ) {
			//set error message
			$response['msg'] = $this->_create_ajax_message( '<span class="ig-tc-msg-field">Title for Home/Archive Page</span> cannot be empty', 'error' );
		} elseif( empty( $data['home_desc'] ) ) {
			//set error message
			$response['msg'] = $this->_create_ajax_message( '<span class="ig-tc-msg-field">Description for Home/Archive Page</span> cannot be empty', 'error' );
		} else {
			$response['error'] = 0;	//all ok, we will proceed

			//save data
			foreach( $data as $key => $value ) {
				$this->_set_option( $key, $value );
			}

			$response['msg'] = $this->_create_ajax_message( 'Options saved successfully', 'success' );
		}

		$response['options'] = $data;

		$this->_send_ajax_response($response);	//send response to browser & exit
	}

	/**
	 * function to enqueue stuff in wp-admin head
	 */
	public function enqueue_stuff( $hook ) {
		$allowed_pages = array(
			'settings_page_' . parent::plugin_id . '-page',
			'post-new.php',
			'post.php',
		);
		if( ! is_admin() || ! in_array( $hook, $allowed_pages ) ) {
			//page is not in wp-admin or not our settings page, so bail out
			return false;
		}

		//load stylesheet
		wp_enqueue_style( parent::plugin_id . '-admin', plugins_url( 'css/admin.css', __FILE__ ), false );
		//load jQuery::msg stylesheet
		wp_enqueue_style( parent::plugin_id . '-jquery-msg', plugins_url( 'css/jquery.msg.css', __FILE__ ), false );

		//load jQuery::center script
		wp_enqueue_script( parent::plugin_id . '-jquery-center', plugins_url( 'js/jquery.center.min.js', __FILE__ ), array( 'jquery' ) );
		//load jQuery::msg script
		wp_enqueue_script( parent::plugin_id . '-jquery-msg', plugins_url( 'js/jquery.msg.min.js', __FILE__ ), array( parent::plugin_id . '-jquery-center' ) );
		//load our script
		wp_enqueue_script( parent::plugin_id . '-admin', plugins_url( 'js/admin.js', __FILE__ ), array( parent::plugin_id . '-jquery-msg' ) );

		//some vars in JS that we'll need
		wp_localize_script( parent::plugin_id . '-admin', 'ig_tc', array(
			'plugins_url' => plugins_url( '', __FILE__ ),
			'nonce' => wp_create_nonce( parent::plugin_id . '-nonce' )
		) );
	}

//end of class
}

//EOF
