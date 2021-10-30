<?php
/**
 * This file contains the logic for Settings.
 *
 * @package AchttienVijftien\Plugin\LastViewed\Admin
 */

namespace AchttienVijftien\Plugin\LastViewed\Admin;

use AchttienVijftien\Plugin\LastViewed\Config;

/**
 * Admin settings page.
 *
 * @package AchttienVijftien\Plugin\LastViewed\Admin
 */
class Settings {

	/**
	 * Prefix used to namespace settings.
	 */
	private const SETTINGS_PREFIX = '1815_last-viewed_setting_';

	/**
	 * Page slug for general page.
	 */
	private const GENERAL_PAGE_SLUG = 'wp-last-viewed-general';

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'add_settings' ] );
	}

	/**
	 * Adds plugin menu item(s).
	 */
	public function add_menu_page(): void {
		// Add general plugin settings page to options menu.
		add_options_page(
			__( 'WordPress Last Viewed', 'wp-last-viewed' ),
			__( 'Last viewed', 'wp-last-viewed' ),
			'manage_options',
			self::GENERAL_PAGE_SLUG,
			[ $this, 'show_general_page' ]
		);
	}

	/**
	 * Renders HTML of general page.
	 */
	public function show_general_page(): void {
		?>
		<div class="wrap">
			<h1>
				<?php esc_html_e( 'WordPress Last Viewed', 'wp-last-viewed' ); ?>
			</h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::SETTINGS_PREFIX . 'general' );
				do_settings_sections( self::GENERAL_PAGE_SLUG );
				?>
				<input name="submit" class="button button-primary" type="submit"
					   value="<?php esc_attr_e( 'Save' ); ?>"/>
			</form>
		</div>
		<?php
	}

	/**
	 * Adds settings.
	 */
	public function add_settings(): void {
		add_settings_section(
			self::SETTINGS_PREFIX . 'general',
			__( 'General Settings', 'wp-last-viewed' ),
			[
				$this,
				'general_section_text',
			],
			self::GENERAL_PAGE_SLUG
		);

		$this->add_amount_setting();
	}

	/**
	 * Description of general section.
	 */
	public function general_section_text(): void {
		echo '<p>';
		esc_html_e(
			'Default settings used by shortcodes, widgets and Gutenberg blocks.',
			'wp-last-viewed'
		);
		echo '</p>';
	}

	/**
	 * Default amount setting.
	 */
	public function add_amount_setting(): void {
		register_setting(
			self::SETTINGS_PREFIX . 'general',
			Config::get_option_name( 'amount' ),
			[
				'type'              => 'integer',
				'sanitize_callback' => 'is_int',
				'default'           => 5,
			]
		);

		add_settings_field(
			self::SETTINGS_PREFIX . 'amount',
			__( 'Default amount', 'wp-last-viewed' ),
			[
				$this,
				'amount_setting_field',
			],
			self::GENERAL_PAGE_SLUG,
			self::SETTINGS_PREFIX . 'general'
		);
	}

	/**
	 * Form field of default amount setting.
	 */
	public function amount_setting_field(): void {
		$amount = Config::get_instance()->get( 'amount' );

		echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . 'amount' ) . '" class="small-text"
		name="' . esc_attr( Config::get_option_name( 'amount' ) ) . '"
		type="number" value="' . esc_attr( $amount ) . '" />';

		echo '<p class="description">';
		esc_html_e( 'Default amount of last viewed posts to display.', 'wp-last-viewed' );
		echo '</p>';
	}
}
