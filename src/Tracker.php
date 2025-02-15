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
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Page view track handler.
	 */
	public function track(): void {
		$post_types = Config::get_instance()->get( 'post_types' );

		// bail early if not on post single.
		if ( ! is_singular( $post_types ) ) {
			return;
		}

		// get the current tracking side setting.
		$tracking_side = Config::get_instance()->get( 'tracking_side' );

		// no server-side tracking required for client-side.
		if ( Settings::SETTING_TRACKING_SIDE_CLIENT === $tracking_side ) {
			return;
		}

		$post_identifier = get_the_ID() . '/' . get_post_type();

		$posts = [];
		// check cookie.
		if ( ! empty( $_COOKIE[ self::TRACKING_COOKIE ] ) ) {
			// if cookie, get value.
			$posts = explode( ',', $_COOKIE[ self::TRACKING_COOKIE ] );

			// check if post already in array.
			$existing_post_key = array_search( $post_identifier, $posts, true );

			if ( is_int( $existing_post_key ) ) {
				// remove current position, so it is moved to the start.
				unset( $posts[ $existing_post_key ] );
			}

			// add to start of array.
			array_unshift( $posts, $post_identifier );
		} else {
			// else set to post id.
			$posts[] = $post_identifier;
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
	 * Enqueue scripts handler.
	 */
	public function enqueue_scripts(): void {
		// get the current tracking side setting.
		$tracking_side = Config::get_instance()->get( 'tracking_side' );

		// no script needed for server-side tracking.
		if ( Settings::SETTING_TRACKING_SIDE_SERVER === $tracking_side ) {
			return;
		}

		// get the default amount.
		$amount = Config::get_instance()->get( 'amount' );

		// get tracked post types.
		$post_types = Config::get_instance()->get( 'post_types' );

		// get path of the js file.
		$js_file = Bootstrap::get_instance()->get_plugin_assets_url( 'js/wp-last-viewed.js' );

		// enqueue the js file.
		wp_enqueue_script( 'wp-last-viewed-tracking', $js_file, [], '1.0', true );

		// add localization and config.
		wp_localize_script(
			'wp-last-viewed-tracking',
			'wpLastViewedConfig',
			[
				'defaultAmount'           => $amount,
				'cookieName'              => self::TRACKING_COOKIE,
				'cookiePath'              => COOKIEPATH,
				'cookieExpirationSeconds' => MONTH_IN_SECONDS,
				'postTypes'               => $post_types,
				'i18n'                    => [
					'noPostsFound' => esc_html__( 'No posts found.', 'wp-last-viewed' ),
				],
			]
		);
	}
}
