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
		add_action( 'wp', [ $this, 'track' ] );
		add_action( 'wp_loaded', [ $this, 'register_scripts' ] );
	}

	public function register_scripts() {

		wp_register_script(
			'1815wlv-tracker',
			LAST_VIEWED_PLUGIN_DIR_URL . 'assets/js/tracker.js',
			[],
			LAST_VIEWED_PLUGIN_VERSION,
			true
		);
	}

	/**
	 * Page view track handler.
	 */
	public function track(): void {
		// bail early if not on post single.
		if ( ! is_singular( [ 'post' ] ) ) {
			return;
		}

		$tracking_type = Config::get_instance()->get( 'tracking-type' );

		if ( 'server' == $tracking_type ) {
			$this->track_serverside();
		} else if ( 'client' == $tracking_type ) {
			$this->track_clientside();
		}
	}

	private function track_serverside(): void {

		$posts   = [];
		$post_id = get_the_ID();

		// check cookie.
		if ( ! empty( $_COOKIE[ self::TRACKING_COOKIE ] ) ) {
			// if cookie, get value.
			$posts = explode( ',', $_COOKIE[ self::TRACKING_COOKIE ] );

			// check if post already in array.
			$existing_post_key = array_search( (string) $post_id, $posts, true );

			if ( is_int( $existing_post_key ) ) {
				// remove current position, so it is moved to the start.
				unset( $posts[ $existing_post_key ] );
			}

			// add to start of array.
			array_unshift( $posts, $post_id );

			// array max size of 20 posts.
			$posts = array_slice( $posts, 0, Config::get_instance()->get( 'amount' ) ? Config::get_instance()->get( 'amount' ) : 20 );
		} else {
			// else set to post id.
			$posts[] = $post_id;
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

	private function track_clientside(): void {

		wp_enqueue_script( '1815wlv-tracker' );

		$tracker_data = [
			'post_id'         => get_the_ID(),
			'tracking_cookie' => self::TRACKING_COOKIE,
			'amount'          => Config::get_instance()->get( 'amount' ),
			'cookie_age'      => MONTH_IN_SECONDS
		];

		wp_localize_script(
			'1815wlv-tracker',
			'wlv_tracker_data',
			$tracker_data
		);
	}
}
