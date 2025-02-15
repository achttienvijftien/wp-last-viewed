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
		register_block_type(
			dirname( __DIR__ ) . '/build/block',
			[
				'render_callback' => [ $this, 'render_callback' ],
				'attributes'      => [
					'amount' => [
						'type'    => 'number',
						'default' => 20,
					],
				],
			]
		);
	}

	/**
	 * Renders the block.
	 *
	 * @param array $attributes list of attributes from block.
	 * @return string
	 */
	public function render_callback( array $attributes ): string {
		// get default amount from config.
		$default_amount = Config::get_instance()->get( 'amount' );

		// fallback to default when no attributes set.
		$amount = isset( $attributes['amount'] ) ? intval( $attributes['amount'] ) : $default_amount;

		return View::get_instance()->output_posts( $amount );
	}
}
