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
	 * Settings Option for server-side tracking
	 */
	public const SETTING_TRACKING_SIDE_SERVER = 'server';

	/**
	 * Settings Option for client-side tracking
	 */
	public const SETTING_TRACKING_SIDE_CLIENT = 'client';

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'add_settings' ] );
		add_filter(
			'plugin_action_links_wp-last-viewed/wp-last-viewed.php',
			[ $this, 'plugin_settings_link' ]
		);
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
				submit_button();
				?>
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
		$this->add_tracking_side_setting();
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
				'sanitize_callback' => [ $this, 'sanitize_amount_setting' ],
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
	 * Default tracking-side setting.
	 */
	public function add_tracking_side_setting(): void {
		register_setting(
			self::SETTINGS_PREFIX . 'general',
			Config::get_option_name( 'tracking_side' ),
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_tracking_side_setting' ],
				'default'           => self::SETTING_TRACKING_SIDE_SERVER,
			]
		);

		add_settings_field(
			self::SETTINGS_PREFIX . 'tracking_side',
			__( 'Tracking Side', 'wp-last-viewed' ),
			[
				$this,
				'tracking_side_setting_field',
			],
			self::GENERAL_PAGE_SLUG,
			self::SETTINGS_PREFIX . 'general'
		);
	}

	/**
	 * Sanitize input of amount
	 *
	 * @param string $input Raw field value of amount.
	 * @return int
	 */
	public function sanitize_amount_setting( string $input ): int {
		if ( false === is_numeric( $input ) ) {
			return 5;
		}
		$input = intval( $input );
		if ( $input > 20 || $input < 0 ) {
			return 5;
		}
		return $input;
	}

	/**
	 * Sanitize tracking side input
	 *
	 * @param string $input Raw field value of tracking side.
	 * @return string
	 */
	public function sanitize_tracking_side_setting( string $input ): string {
		$input_is_valid = in_array(
			$input,
			[
				self::SETTING_TRACKING_SIDE_SERVER,
				self::SETTING_TRACKING_SIDE_CLIENT,
			],
			true
		);
		if ( false === $input_is_valid ) {
			return self::SETTING_TRACKING_SIDE_SERVER;
		}
		return $input;
	}

	/**
	 * Form field of default amount setting.
	 */
	public function amount_setting_field(): void {
		$amount = Config::get_instance()->get( 'amount' );

		echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . 'amount' ) . '" class="small-text"
		name="' . esc_attr( Config::get_option_name( 'amount' ) ) . '"
		type="number" min="1" max="20" value="' . esc_attr( $amount ) . '" />';

		echo '<p class="description">';
		esc_html_e( 'Default amount of last viewed posts to display.', 'wp-last-viewed' );
		echo '</p>';
	}

	/**
	 * Form field of tracking side setting.
	 */
	public function tracking_side_setting_field(): void {
		$tracking_side = Config::get_instance()->get( 'tracking_side' );

		echo '<label><input type="radio" '
			. 'id="' . esc_attr( self::SETTINGS_PREFIX . 'tracking_side_' . self::SETTING_TRACKING_SIDE_SERVER ) . '"'
			. 'name="' . esc_attr( Config::get_option_name( 'tracking_side' ) )
			. '" value="' . esc_attr( self::SETTING_TRACKING_SIDE_SERVER ) . '" '
			. checked( $tracking_side, self::SETTING_TRACKING_SIDE_SERVER, false )
			. ' />' . esc_html__( 'Server-side', 'wp-last-viewed' ) . '</label><br>';

		echo '<label><input type="radio" '
			. 'id="' . esc_attr( self::SETTINGS_PREFIX . 'tracking_side_' . self::SETTING_TRACKING_SIDE_CLIENT ) . '"'
			. 'name="' . esc_attr( Config::get_option_name( 'tracking_side' ) )
			. '" value="' . esc_attr( self::SETTING_TRACKING_SIDE_CLIENT ) . '" '
			. checked( $tracking_side, self::SETTING_TRACKING_SIDE_CLIENT, false )
			. ' />' . esc_html__( 'Client-side', 'wp-last-viewed' ) . '</label>';

		echo '<p class="description">';
		esc_html_e( 'Default side where visited posts will be tracked.', 'wp-last-viewed' );
		echo '</p>';
	}

	/**
	 * Adds link to general settings page on plugin list page.
	 *
	 * @param array $links Links of this plugin to show on plugin list page.
	 *
	 * @return array
	 */
	public function plugin_settings_link( array $links ): array {
		// Get link to general settings page.
		$url = esc_url(
			add_query_arg(
				'page',
				self::GENERAL_PAGE_SLUG,
				get_admin_url() . 'admin.php'
			)
		);

		// Create the link.
		$links[] = '<a href="' . $url . '">' . esc_html__( 'Settings' ) . '</a>';

		return $links;
	}
}
