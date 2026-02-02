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

  public function test_default_tracked_post_types() {
    $default = $this->config->get('tracked_post_types');
    $this->assertIsArray($default, 'Default tracked post types should be an array');
    $this->assertContains('post', $default, 'Default should include "post"');
  }

  public function test_set_and_get_tracked_post_types() {
    $this->config->set('tracked_post_types', ['post', 'page', 'legoset']);
    $retrieved = $this->config->get('tracked_post_types');

    $this->assertIsArray($retrieved);
    $this->assertEquals(['post', 'page', 'legoset'], $retrieved);
  }

	public function test_empty_tracked_post_types_disables_tracking(): void {
		update_option(
			Config::get_option_name( 'tracked_post_types' ),
			[]
		);

		Config::get_instance()->reset();

		$this->assertSame(
			[],
			Config::get_instance()->get( 'tracked_post_types' )
		);
	}


}
