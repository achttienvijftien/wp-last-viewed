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
		// bail early if not tracking.
		if ( ! $this->should_track() ) {
			return;
		}

		$posts = $this->read_posts_from_cookie();
		$posts = $this->apply_tracking_rules( $posts, get_the_ID() );
		$this->write_posts_to_cookie( $posts );
	}

	/**
	 *  Whether the current request should be tracked.
	 */
	private function should_track(): bool {
		return is_singular( [ 'post' ] );
	}

	/**
	 * Reads tracked post IDs from the tracking cookie.
	 */
	private function read_posts_from_cookie(): array {
		// check cookie.
		if ( empty( $_COOKIE[ self::TRACKING_COOKIE ] ) ) {
			return [];
		}

		// if cookie, get value.
		return explode( ',', $_COOKIE[ self::TRACKING_COOKIE ] );
	}

	/**
	 * Applies tracking rules:
	 * - moves current post to the front
	 * - ensures uniqueness
	 * - limits list size
	 */
	private function apply_tracking_rules( array $posts, int $post_id ): array {
		// check if post already in array.
		$existing_post_key = array_search( (string) $post_id, $posts, true );

		if ( $existing_post_key !== false ) {
		  // remove current position, so it is moved to the start.
			unset( $posts[ $existing_post_key ] );
		}

		// add to start of array.
		array_unshift( $posts, $post_id );

		$max = Config::get_instance()->get( 'amount' ) ?: 20;

		// array max size posts.
		return array_slice( $posts, 0, $max );
	}

	/**
	 * Writes tracked post IDs to the tracking cookie.
	 */
	private function write_posts_to_cookie( array $posts ): void {
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
