<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package AchttienVijftien\Plugin\LastViewed\Test
 */
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Get tests dir.
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: getenv( 'WP_PHPUNIT__DIR' );

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/wp-last-viewed.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
