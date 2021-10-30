<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package AchttienVijftien\Plugin\Republish\Test
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Get tests dir.
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: getenv( 'WP_PHPUNIT__DIR' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';