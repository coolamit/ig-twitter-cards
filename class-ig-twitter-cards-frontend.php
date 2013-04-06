<?php

/**
 * class that handles all the front-end for iG:Twitter Cards plugin
 *
 * @author Amit Gupta
 */

class iG_Twitter_Cards_Frontend extends iG_Twitter_Cards {

	/**
	 * @var Array An array which contains twitter card tags
	 */
	private $_tags;

	/**
	 * Singleton implemented, protected constructor so its accessible by parent class
	 */
	protected function __construct() {
		//init core stuff
		$this->_init_core();

		//if Jetpack is available & open graph tags are enabled then use that
		if ( class_exists( 'Jetpack' ) && apply_filters( 'jetpack_enable_open_graph', false ) ) {
			add_filter( 'jetpack_open_graph_tags', array( $this, 'get_twitter_card_tags' ) );
		} else {
			add_action( 'wp_head', array( $this, 'get_twitter_card_tags' ) );
		}
	}

	/**
	 * This function returns twitter card tags in array if called by 'jetpack_open_graph_tags'
	 * else it prints meta tags
	 */
	public function get_twitter_card_tags( $tags = array() ) {
		if( ! $this->_do_the_tags() ) {
			return;
		}

		$this->_build_twitter_card_tags();	//build twitter card tags

		switch( current_filter() ) {
			case 'jetpack_open_graph_tags':
				$tags = array_merge( $tags, $this->_tags );
				return $tags;
				break;
			case 'wp_head':
			default:
				if( empty( $this->_tags ) ) {
					return;
				}
				$tag_output = '';
				foreach( $this->_tags as $tag_name => $tag_value ) {
					$tag_output .= sprintf( '<meta property="%s" content="%s" />', esc_attr( $tag_name ), esc_attr( $tag_value ) );
				}
				echo $tag_output;
				unset( $tag_output );
				break;
		}
	}

	/**
	 * This function checks whether Twitter tags are to be done or not.
	 * If its a post/page and is password protected then it doesn't do Twitter
	 * tags
	 */
	private function _do_the_tags() {
		if( $this->_is_singular() && post_password_required() ) {
			return false;
		}

		return true;
	}

	/**
	 * This function checks whether current URL is of a post/page and whether
	 * its one of the allowed post_types or not
	 */
	private function _is_singular() {
		if( is_singular() && in_array( get_post_type(), $this->_post_types ) ) {
			return true;
		}

		return false;
	}

	/**
	 * This function builds the twitter card tags
	 * and sets them up in class array $this->_tags
	 */
	private function _build_twitter_card_tags() {
		$this->_add_card_type();
		$this->_add_page_tags();
		$this->_add_site_twitter();
		$this->_add_author_twitter();

		$this->_tags = apply_filters( 'ig_twitter_cards_tags', $this->_tags );	//allow override on tags

		$this->_tags = array_map( 'esc_attr', array_filter( $this->_tags ) );	//weed out empty tags and apply esc_attr() on remaining tags
	}

	/**
	 * This function adds the tag for card type. If $post_meta is supplied
	 * then card type is used from that (if it exists) and it also calls
	 * _add_player_tags() if card type is 'player'
	 */
	private function _add_card_type( $post_meta = array() ) {
		$this->_tags['twitter:card'] = 'summary';

		if( empty( $post_meta ) || ! is_array( $post_meta ) ) {
			return;
		}

		if( ! empty( $post_meta['card_type'] ) ) {
			$this->_tags['twitter:card'] = $post_meta['card_type'];
		}

		if( $this->_tags['twitter:card'] == 'player' ) {
			$this->_add_player_tags( $post_meta );
		}
	}

	/**
	 * This function adds misc tags like title, description, URL etc
	 */
	private function _add_page_tags() {
		if( $this->_is_singular() ) {
			$this->_tags['twitter:url'] = get_permalink();
			$this->_tags['twitter:title'] = single_post_title( '', false );
			$this->_tags['twitter:description'] = get_the_excerpt();

			if( empty( $this->_tags['twitter:description'] ) ) {
				$current_post = get_post();
				$this->_tags['twitter:description'] = wp_trim_words( $current_post->post_excerpt );
				unset( $current_post );
			}

			if( has_post_thumbnail() ) {
				$this->_tags['twitter:image'] = esc_url( array_shift( wp_get_attachment_image_src( get_post_thumbnail_id(), 'medium' ) ) );
			} elseif( function_exists( 'get_the_image' ) ) {
				$image = get_the_image( array(
					'size' => 'medium',
					'link_to_post' => false,
					'image_scan' => true,
					'format' => 'array',
					'echo' => false
				) );
				if( ! empty( $image ) && isset( $image['src'] ) && ! empty( $image['src'] ) ) {
					$this->_tags['twitter:image'] = esc_url( $image['src'] );
				}

				unset( $image );
			} else {
				$this->_tags['twitter:image'] = esc_url( $this->_get_option( 'fallback_image_url' ) );
				if( empty( $this->_tags['twitter:image'] ) ) {
					unset( $this->_tags['twitter:image'] );
				}
			}
		} else {
			$this->_tags['twitter:url'] = get_home_url();

			$this->_tags['twitter:title'] = $this->_get_option( 'home_title' );

			$this->_tags['twitter:description'] = $this->_get_option( 'home_desc' );

			$this->_tags['twitter:image'] = esc_url( $this->_get_option( 'fallback_image_url' ) );
			if( empty( $this->_tags['twitter:image'] ) ) {
				unset( $this->_tags['twitter:image'] );
			}
		}
	}

	/**
	 * This function adds site's twitter name to tags
	 */
	private function _add_site_twitter() {
		$site_twitter = $this->_get_option( 'site_twitter_name' );
		if( empty( $site_twitter ) ) {
			return;
		}

		$this->_tags['twitter:site'] = '@' . $site_twitter;

		unset( $site_twitter );
	}

	/**
	 * Add post author's Twitter, if it exists, to tags. Twitter name specified
	 * in post meta is given preference and if that does not exist then Twitter
	 * name set in author's profile is used
	 */
	private function _add_author_twitter() {
		if( ! $this->_is_singular() ) {
			return;
		}

		global $post;
		if( empty( $post ) || ! is_object( $post ) ) {
			return;
		}

		$author_twitter = '';

		$post_meta = get_post_meta( $post->ID, '_ig_tc_mb', true );

		if( ! empty( $post_meta ) && is_array( $post_meta ) ) {
			$author_twitter = ( isset( $post_meta['author_twitter'] ) && ! empty( $post_meta['author_twitter'] ) ) ? $post_meta['author_twitter'] : '';

			$this->_add_card_type( $post_meta );
		}

		if( empty( $author_twitter ) ) {
			return;
		}

		$this->_tags['twitter:creator'] = '@' . sanitize_title( $author_twitter );

		unset( $author_twitter );
	}

	/**
	 * This function adds player tags if they exist
	 */
	private function _add_player_tags( $post_meta ) {
		$this->_tags['twitter:card'] = 'summary';

		if( ! isset( $post_meta['player_url'] ) || ! isset( $post_meta['player_width'] ) || ! isset( $post_meta['player_height'] ) ) {
			return;
		}

		if( empty( $post_meta['player_url'] ) || empty( $post_meta['player_width'] ) || empty( $post_meta['player_height'] ) ) {
			return;
		}

		$this->_tags['twitter:card'] = 'player';

		$this->_tags['twitter:player'] = $post_meta['player_url'];
		$this->_tags['twitter:player:width'] = $post_meta['player_width'];
		$this->_tags['twitter:player:height'] = $post_meta['player_height'];

		if( isset( $post_meta['player_image'] ) && ! empty( $post_meta['player_image'] ) ) {
			$this->_tags['twitter:image'] = $post_meta['player_image'];
		}
	}

//end of class
}

//EOF
