<?php
/**
 * Plugin Name:     1815 - WordPress Last Viewed
 * Plugin URI:      https://1815.nl
 * Description:     Show last viewed WordPress posts
 * Version:         0.1.0
 * Author:          1815 <it@1815.nl>
 * Author URI:      https://1815.nl
 * Text Domain:     wp-last-viewed
 * Domain Path:     /languages
 * License:         GPL-3.0+
 * License URI:     https://1815.nl
 *
 * @package         AchttienVijftien\Plugin\LastViewed
 */

require plugin_dir_path( __FILE__ ) . 'src/Bootstrap.php';

if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
	require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

\AchttienVijftien\Plugin\LastViewed\Bootstrap::get_instance()->init();
