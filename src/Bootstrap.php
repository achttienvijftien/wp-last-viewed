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
require plugin_dir_path( __FILE__ ) . 'Block.php';

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
		new Block();

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

	/**
	 * Returns the public URL of a plugin asset.
	 *
	 * @param string $asset_file path of asset file within asset folder.
	 */
	public function get_plugin_assets_url( $asset_file ): string {
		$asset_file = 'assets/' . $asset_file;

		return plugins_url( $asset_file, __DIR__ );
	}
}
