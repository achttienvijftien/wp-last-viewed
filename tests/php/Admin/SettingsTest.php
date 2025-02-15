<?php

namespace AchttienVijftien\Plugin\LastViewed\Test\Admin;

use AchttienVijftien\Plugin\LastViewed\Admin\Settings;

class SettingsTest extends \WP_UnitTestCase
{
	private Settings $settings;

	public function set_up(): void {
		parent::set_up();

		$this->settings = new Settings();
	}

	public function test_sanitize_amount_setting_returns_valid_integer_within_bounds()
	{
		$this->assertEquals(5, $this->settings->sanitize_amount_setting('5'));
		$this->assertEquals(0, $this->settings->sanitize_amount_setting('0'));
		$this->assertEquals(20, $this->settings->sanitize_amount_setting('20'));
	}

	public function test_sanitize_amount_setting_returns_default_for_out_of_bounds()
	{
		$this->assertEquals(5, $this->settings->sanitize_amount_setting('21'));
		$this->assertEquals(5, $this->settings->sanitize_amount_setting('-1'));
	}

	public function test_sanitize_amount_setting_handles_non_numeric_input()
	{
		$this->assertEquals(5, $this->settings->sanitize_amount_setting('abc'));
		$this->assertEquals(5, $this->settings->sanitize_amount_setting(''));
	}

	public function test_sanitize_tracking_side_setting_returns_valid_side()
	{
		$this->assertEquals(
			Settings::SETTING_TRACKING_SIDE_SERVER,
			$this->settings->sanitize_tracking_side_setting(Settings::SETTING_TRACKING_SIDE_SERVER)
		);

		$this->assertEquals(
			Settings::SETTING_TRACKING_SIDE_CLIENT,
			$this->settings->sanitize_tracking_side_setting(Settings::SETTING_TRACKING_SIDE_CLIENT)
		);
	}

	public function test_sanitize_tracking_side_setting_returns_default_for_invalid_input()
	{
		$this->assertEquals(
			Settings::SETTING_TRACKING_SIDE_SERVER,
			$this->settings->sanitize_tracking_side_setting('invalid_input')
		);

		$this->assertEquals(
			Settings::SETTING_TRACKING_SIDE_SERVER,
			$this->settings->sanitize_tracking_side_setting('')
		);
	}

	public function test_sanitize_post_types_setting_returns_valid_post_types()
	{
		$valid_post_types = ['post', 'page'];
		$this->assertEqualSets(
			$valid_post_types,
			$this->settings->sanitize_post_types_setting($valid_post_types)
		);
	}

	public function test_sanitize_post_types_setting_returns_default_for_empty_input()
	{
		$this->assertEqualSets(
			['post'],
			$this->settings->sanitize_post_types_setting([])
		);
	}

	public function test_sanitize_post_types_setting_filters_invalid_post_types()
	{
		$valid_post_types = ['post', 'page'];
		$input = ['post', 'invalid_type', 'page'];
		$this->assertEqualSets(
			$valid_post_types,
			$this->settings->sanitize_post_types_setting($input)
		);
	}
}