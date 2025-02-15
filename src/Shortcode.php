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
	 * Fire add shortcode.
	 *
	 * @return void
	 */
	public function shortcode_init(): void {
		add_shortcode( 'wp-last-viewed', [ $this, 'output_posts' ] );
	}

	/**
	 * Output posts found in the tracking cookie.
	 *
	 * @param array $attributes list of attributes from shortcode.
	 */
	public function output_posts( array $attributes ): string {
		// get default amount from config.
		$default_amount = Config::get_instance()->get( 'amount' );

		// fallback to default attributes.
		$attributes = shortcode_atts(
			[
				'amount' => $default_amount,
			],
			$attributes,
			'wp-last-viewed'
		);

		// get shortcode attributes.
		$amount = $attributes['amount'];

		return View::get_instance()->output_posts( $amount );
	}
}
