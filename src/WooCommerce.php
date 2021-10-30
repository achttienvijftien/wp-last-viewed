<?php
/**
 * This file contains the logic for WooCommerce.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

use AchttienVijftien\Plugin\LastViewed\Config;
use AchttienVijftien\Plugin\LastViewed\View;

/**
 * Outputs most viewed posts on WooCommerce thank you page
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */
class WooCommerce {

	/**
	 * Tracker constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_thankyou', [ $this, 'output_posts' ] );
	}

	/**
	 * Output posts found in the tracking cookie
	 */
	public function output_posts(): void {

		echo View::get_instance()->output_posts();

	}

}
