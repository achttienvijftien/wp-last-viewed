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
	}

	/**
	 * Page view track handler.
	 */
	public function track(): void {
		// get selected post types
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
}
