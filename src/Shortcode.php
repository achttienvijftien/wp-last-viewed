<?php
/**
 * This file contains the logic for the shortcode.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

use AchttienVijftien\Plugin\LastViewed\View;

/**
 * Tracks page views on single posts.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */
class Shortcode {

	/**
	 * Name of tracking cookie.
	 */
	public const TRACKING_COOKIE = '1815_last-viewed';

	/**
	 * Shortcode constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'shortcode_init' ] );
	}

	/**
	 * Fire add shortcode
	 * @return void
	 */
	public function shortcode_init(): void {
		add_shortcode( 'wp-last-viewed', [ $this, 'output_posts' ] );
	}

	/**
	 * Output posts found in the tracking cookie
	 */
	public function output_posts(): string {

		return View::get_instance()->output_posts();

	}
}
