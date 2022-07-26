<?php
/**
 * This file contains the logic for Tracker.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

use AchttienVijftien\Plugin\LastViewed\Config;

/**
 * Tracks page views on single posts.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */
class Tracker {
	/**
	 * Name of tracking cookie.
	 */
	public const TRACKING_COOKIE = '1815_last-viewed';

	/**
	 * Tracker constructor.
	 */
	public function __construct() {
		$selected_tracking_type = Config::get_instance()->get( 'tracking_type' );

		// Add track method or enqueue script / add meta tag based on tracking type.
		if ($selected_tracking_type === 'server_side') {
			add_action( 'wp', [ $this, 'track' ] );
		} else if ($selected_tracking_type === 'client_side') {
			add_action('wp_enqueue_scripts', [ $this, 'enqueue_tracking_script' ]);
			add_action('wp_head', [ $this, 'add_id_meta' ], 5);
		}
	}

	/**
	 * Page view track handler.
	 */
	public function track(): void {
		// get selected post types.
		$selected_post_type = Config::get_instance()->get( 'types' );

		// bail early if not on one of selected posttypes.
		if ( ! is_singular( $selected_post_type ) ) {
			return;
		}

		$posts = [];
		// check cookie.
		if ( ! empty( $_COOKIE[ self::TRACKING_COOKIE ] ) ) {
			// if cookie, get value.
			$posts = explode( ',', $_COOKIE[ self::TRACKING_COOKIE ] );

			// check if post already in array.
			$existing_post_key = array_search( (string) get_the_ID(), $posts, true );

			if ( is_int( $existing_post_key ) ) {
				// remove current position, so it is moved to the start.
				unset( $posts[ $existing_post_key ] );
			}

			// add to start of array.
			array_unshift( $posts, get_the_ID() );

			// array max size of 20 posts.
			$posts = array_slice( $posts, 0, Config::get_instance()->get( 'amount' ) ? Config::get_instance()->get( 'amount' ) : 20 );
		} else {
			// else set to post id.
			$posts[] = get_the_ID();
		}

		// (re)set tracking cookie.
		setcookie(
			self::TRACKING_COOKIE,
			implode( ',', $posts ),
			time() + MONTH_IN_SECONDS,
			COOKIEPATH,
			COOKIE_DOMAIN
		);
	}

	/**
	 * Enqueue script if current page is selected post-type.
	 */
	public function enqueue_tracking_script(): void {
		$selected_post_type = Config::get_instance()->get( 'types' );

		if ( is_singular( $selected_post_type ) ) {
			wp_enqueue_script('tracking-js', plugin_dir_url(last_viewed_root) .'assets/js/tracking.js', array(), null, true);
		}
	}

	/**
	 * Add custom meta tag to wordpress HEAD.
	 */
	public function add_id_meta() {
		global $post;
		
		echo '<meta name="post_id" content="'. $post->ID .'" />';	
	}
}
