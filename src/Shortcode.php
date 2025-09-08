<?php
/**
 * This file contains the logic for the shortcode.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

use AchttienVijftien\Plugin\LastViewed\View;

/**
 * Make the shortcode.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */
class Shortcode {

	/**
	 * Shortcode constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'shortcode_init' ] );
	}

	/**
	 * Fire add shortcode
	 *
	 * @return void
	 */
	public function shortcode_init(): void {
		add_shortcode( 'wp-last-viewed', [ $this, 'output_posts' ] );
	}

	/**
	 * Output posts found in the tracking cookie
	 *
	 * @param array $attr Shortcode attributes.
	 * @return string
	 */
	public function output_posts( array $attr = [] ): string {

		$posts_per_page = isset( $attr['maximum'] ) ? (int) $attr['maximum'] : -1;

		return View::get_instance()->output_posts( $posts_per_page );
	}
}
