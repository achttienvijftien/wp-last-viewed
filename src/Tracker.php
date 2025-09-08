<?php
/**
 * This file contains the logic for Tracker.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

use AchttienVijftien\Plugin\LastViewed\Admin\Settings;
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
		add_action( 'init', [ $this, 'setup_track_client_side' ] );
	}

	/**
	 * Add Client Side Tracking to posts.
	 *
	 * @param string $content the_content from WordPress.
	 * @return string
	 */
	public function add_client_side_tracking_to_posts( string $content ): string {
		if ( ! is_singular() ) {
			return $content;
		}
		if ( ! Settings::get_client_side_tracking_setting() ) {
			return $content;
		}

		global $post;

		// Adding Custom HTML, POST with body content $post->ID.
		$custom_html = "
		<script>
			document.addEventListener(\"DOMContentLoaded\", function() {
				fetch('/?rest_route=/wp-last-viewed/v1/track/',{
					method: 'POST',
					body: {$post->ID}
				});
			});
		</script>
		";
		$content     = $content . $custom_html;
		return $content;
	}

	/**
	 * Register WordPress REST API
	 * Setup Client side page view tracking.
	 */
	public function setup_track_client_side(): void {
		rest_api_init();
		if ( ! Settings::get_client_side_tracking_setting() ) {
			return;
		}
		add_filter( 'the_content', [ $this, 'add_client_side_tracking_to_posts' ] );
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'wp-last-viewed/v1',
					'/track/',
					[
						'methods'             => 'POST',
						'callback'            => [ $this, 'track_client_side' ],
						'permission_callback' => '__return_true',
					]
				);
			}
		);
	}

	/**
	 * Client side page view track handler.
	 *
	 * @param object $request Request from WordPress.
	 * @return array
	 */
	public function track_client_side( object $request ): array {

		$this->set_tracking_cookie( (int) $request->get_body() );

		return [
			'succes'  => true,
			'post_id' => $request->get_body(),
		];
	}

	/**
	 * Page view track handler.
	 */
	public function track(): void {
		// bail early if not on post single.
		if ( ! is_singular( [ 'post' ] ) ) {
			return;
		}

		// Return if client side tracking is enable, prevent double page tracking.
		if ( Settings::get_client_side_tracking_setting() ) {
			return;
		}

		$this->set_tracking_cookie( get_the_ID() );
	}

	/**
	 * Set Tracking Cookie from post id
	 *
	 * @param int $post_id The given post id.
	 */
	public function set_tracking_cookie( int $post_id ): void {

		$post = get_post( $post_id );
		if ( ! $post ) {
			return;
		}

		// Check if we have specific post_type settings, and post_type is in these settings.
		$allowed_post_types = Settings::get_track_post_types_settings();
		if ( $allowed_post_types && ! in_array( $post->post_type, $allowed_post_types, true ) ) {
			return;
		}

		$posts = [];
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

			$amount = Config::get_instance()->get( 'amount' ) ?
						Config::get_instance()->get( 'amount' ) : 20;
			// array max size of 20 posts.
			$posts = array_slice( $posts, 0, $amount );
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
}
