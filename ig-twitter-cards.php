<?php
/*
Plugin Name: iG:Twitter Cards
Plugin URI: http://blog.igeek.info/
Description: This plugin enables Twitter Cards on a site. Check Twitter Cards documentation for details - https://dev.twitter.com/docs/cards
Version: 1.0 alpha
Author: Amit Gupta
Author URI: http://blog.igeek.info/
License: GPL v2
*/

add_action( 'init', 'ig_twitter_cards_loader' );

function ig_twitter_cards_loader() {
	//load up plugin base class
	require_once( __DIR__ . '/class-ig-twitter-cards.php' );

	if( is_admin() ) {
		//load up & init plugin admin UI class
		require_once( __DIR__ . '/class-ig-twitter-cards-admin.php' );

		if( ! isset($GLOBALS['ig_twitter_cards_admin']) || ! is_a( $GLOBALS['ig_twitter_cards_admin'], 'iG_Twitter_Cards_Admin' ) ) {
			$GLOBALS['ig_twitter_cards_admin'] = iG_Twitter_Cards_Admin::get_instance();
		}
	} else {
		//load up & init plugin front-end class
		require_once( __DIR__ . '/class-ig-twitter-cards-frontend.php' );

		if( ! isset($GLOBALS['ig_twitter_cards_frontend']) || ! is_a( $GLOBALS['ig_twitter_cards_frontend'], 'iG_Twitter_Cards_Frontend' ) ) {
			$GLOBALS['ig_twitter_cards_frontend'] = iG_Twitter_Cards_Frontend::get_instance();
		}
	}
}



//EOF
