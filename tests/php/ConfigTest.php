<?php

namespace AchttienVijftien\Plugin\LastViewed\Test;

use AchttienVijftien\Plugin\LastViewed\Config;

class ConfigTest extends \WP_UnitTestCase {

	private $config;

  public function set_up(): void {
        parent::set_up();

		$this->config = Config::get_instance();
	}

	public function tear_down(): void {
		parent::tear_down();

		// Clean up options to avoid singleton pollution
		delete_option( Config::get_option_name( 'amount' ) );
	}

	public function test_instance_of() {
		$this->assertInstanceOf( Config::class, $this->config );
	}

	public function test_set_option() {
		$this->config->set( '1815', 'rocks' );

		$this->assertEquals( 'rocks', $this->config->get( '1815' ) );
	}

	public function test_prefixed_option_name() {
		$this->assertEquals( '1815_last-viewed_test', Config::get_option_name( 'test' ) );
	}

	public function test_default_amount_is_returned_when_not_set() {
		// Ensure the option is not set in DB
		delete_option( Config::get_option_name( 'amount' ) );

		$this->assertEquals(
			5,
			$this->config->get( 'amount' ),
			'Default amount should be returned when no value is set'
		);
	}

	public function test_amount_can_be_overridden() {
		$this->config->set( 'amount', 10 );

		$this->assertEquals(
			10,
			$this->config->get( 'amount' ),
			'Configured amount should override default'
		);
	}

}
