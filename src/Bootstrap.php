<?php
/**
 * This file contains the logic for Bootstrap.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

require plugin_dir_path( __FILE__ ) . 'Tracker.php';
require plugin_dir_path( __FILE__ ) . 'Admin.php';
require plugin_dir_path( __FILE__ ) . 'Config.php';
require plugin_dir_path( __FILE__ ) . 'View.php';
require plugin_dir_path( __FILE__ ) . 'Shortcode.php';
require plugin_dir_path( __FILE__ ) . 'WooCommerce.php';
require plugin_dir_path( __FILE__ ) . 'Admin/Settings.php';

use AchttienVijftien\Plugin;

/**
 * Bootstrap plugin.
 */
class Bootstrap {

	/**
	 * Instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Get (singleton) instance.
	 *
	 * @return $this
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize plugin.
	 */
	public function init(): void {
		new Tracker();
		new Shortcode();
		new WooCommerce();

		if ( is_admin() ) {
			$this->init_admin();
		}
	}

	/**
	 * Initialize admin.
	 */
	public function init_admin(): void {
		new Admin();
	}
}
