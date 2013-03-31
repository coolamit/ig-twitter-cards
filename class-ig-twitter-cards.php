<?php

/**
 * Base class for iG Twitter Cards plugin
 *
 * @author Amit Gupta
 */

abstract class iG_Twitter_Cards {

	/**
	 * @var const Class constant containing unique plugin ID
	 */
	const plugin_id = "ig-twitter-cards";

	/**
	 * @var const Class constant containing plugin name for display
	 */
	const plugin_name = "iG:Twitter Cards";

	/**
	 * @var Array An array which contains plugin options
	 */
	private $_options;

	/**
	 * @var Array An array which contains default plugin options
	 */
	protected $_default_options = array(
		'show_on_post_page' => 'no',		//whether to show customization metabox on post add/edit or not
		'site_twitter_name' => '',			//default twitter name for the site
		'fallback_image_url' => '',			//default image to show if shared page has no image set
		'home_title' => '',					//title to show if home/archive page is shared
		'home_desc' => '',					//desc to show if home/archive page is shared
	);

	/**
	 * @var Array An array which contains the post types on which twiter cards are allowed. This can be overridden using the filter 'ig_twitter_cards_post_types'
	 */
	protected $_post_types = array(
		'post', 'page'
	);

	/**
	 * Static array that contains instances of all child classes
	 */
	private static $_instance = array();

	/**
	 * Static function that creates & returns instances of all child classes. This
	 * function cannot be overridden/overloaded
	 */
	final public static function get_instance() {
		$class_name = get_called_class();

		if( ! isset( self::$_instance[$class_name] ) || ! is_a( self::$_instance[$class_name], $class_name ) ) {
			self::$_instance[$class_name] = new $class_name();
		}

		return self::$_instance[$class_name];
	}

	/**
	 * Life without Object cloning
	 */
	final protected function  __clone() {}

	/**
	 * This function checks whether the passed value is YES/NO or not. If it is then
	 * it returns TRUE else FALSE. The parameter accepts only string.
	 */
	final protected function _is_yesno( $value ) {
		if( ! empty( $value ) && is_string( $value ) && in_array( $value, array( 'yes', 'no' ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * This function is run by every child class to initialize some core stuff
	 * of this plugin
	 */
	protected function _init_core() {
		$this->_init_options();		//init options

		//let allowed post types to be overridden
		$this->_post_types = apply_filters( 'ig_twitter_cards_post_types', $this->_post_types );
	}

	/**
	 * This function initializes the options storing them in the $_options
	 * class var. If the options are not in DB then it creates them using default ones.
	 */
	protected function _init_options() {
		$save_options = false;		//assume its not first run

		//fetch options array from wp_options & then do a safe merge with default options
		$this->_options = get_option( self::plugin_id . '-options', false );
		if( empty( $this->_options ) || ! is_array( $this->_options ) ) {
			//allow for override of default options first time
			//next time it'll be via admin UI
			$this->_options = apply_filters( 'ig_twitter_cards_init_opts', $this->_default_options );
			$save_options = true;
		}

		//If options in DB have extra/less keys than the default then equalize them
		//and store in DB. We need consistency.
		if( array_keys( $this->_options ) !== array_keys( $this->_default_options ) ) {
			$this->_options = wp_parse_args( $this->_options, $this->_default_options );
			$save_options = true;	//have it saved in DB
		}

		if( $save_options === true ) {
			//plugin initialization or there was key mismatch, save options
			$this->_commit();
		}

		unset( $save_options );
	}

	/**
	 * This function is a wrapper to fetch an option from $_options class var,
	 * since direct access to the $_options var is not allowed
	 */
	protected function _get_option( $option_name ) {
		if( ! empty($option_name) && is_string($option_name) && isset( $this->_options[$option_name] ) ) {
			return $this->_options[$option_name];
		}

		return false;
	}

	/**
	 * This function is for setting a value in the $_options class var as direct
	 * access to it isn't allowed. It takes care of sanitizing the value before
	 * putting it in $_options & saves only if the option name exists already.
	 */
	protected function _set_option( $option_name, $option_value ) {
		if( empty($option_name) || ! is_string($option_name) || ! isset( $this->_options[$option_name] ) ) {
			return false;
		}

		$this->_options[$option_name] = wp_kses_post( $option_value );
		$this->_commit();		//lets save in DB as well
	}

	/**
	 * This function is for saving the $_options class var in the DB, can be called anytime
	 * or in the class destructor
	 */
	private function _commit() {
		if( empty( $this->_options ) || ! is_array( $this->_options ) ) {
			return false;
		}

		update_option( self::plugin_id . '-options', $this->_options );

		return true;
	}


//end of class
}

//EOF
