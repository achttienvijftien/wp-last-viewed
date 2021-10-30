<?php
/**
 * This file contains the logic for Bootstrap.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

use AchttienVijftien\Plugin\LastViewed\Admin\Settings;

/**
 * Admin only functionality.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */
class Admin {

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		new Settings();
	}
}
