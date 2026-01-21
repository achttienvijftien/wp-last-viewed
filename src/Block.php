<?php
/**
 * Block
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

/**
 * Class Block
 */
class Block {

	/**
	 * Block constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers block.
	 *
	 * @return void
	 */
	public function register_block() {
		register_block_type_from_metadata(
			dirname( __DIR__ ) . '/build/block',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @return string
	 */
	public function render_callback( $attributes ) {

    $amount = isset( $attributes['amount'] ) && $attributes['amount'] !== ''
		? (int) $attributes['amount']
		: 5;

		return View::get_instance()->output_posts( $amount );
	}
}
