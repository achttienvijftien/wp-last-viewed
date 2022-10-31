<?php

namespace AchttienVijftien\Plugin\LastViewed\Test;

use AchttienVijftien\Plugin\LastViewed\Config;

class ConfigTest extends \WP_UnitTestCase {

	private $config;

    public function set_up(): void {
        parent::set_up();

		$this->config = Config::get_instance();
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
}
