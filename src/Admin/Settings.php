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
		$this->add_client_side_tracking_field();
		$this->add_track_post_types();
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
				'sanitize_callback' => 'intval',
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
	 * Default client_side_tracking setting.
	 */
	public function add_client_side_tracking_field(): void {
		$field = 'client_side_tracking';
		register_setting(
			self::SETTINGS_PREFIX . 'general',
			Config::get_option_name( $field ),
			[
				'type'              => 'integer',
				'sanitize_callback' => 'intval',
				'default'           => 0,
			]
		);

		add_settings_field(
			self::SETTINGS_PREFIX . $field,
			__( 'Client side tracking', 'wp-last-viewed' ),
			[
				$this,
				'client_side_tracking_setting_field',
			],
			self::GENERAL_PAGE_SLUG,
			self::SETTINGS_PREFIX . 'general'
		);
	}

	/**
	 * Add track_post_types setting
	 */
	public function add_track_post_types(): void {
		register_setting(
			self::SETTINGS_PREFIX . 'general',
			Config::get_option_name( 'track_post_types' ),
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_post_types' ],
				'default'           => [],
			]
		);
		add_settings_field(
			self::SETTINGS_PREFIX . 'track_post_types',
			__( 'Track post types', 'wp-last-viewed' ),
			[
				$this,
				'track_post_types_setting_field',
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
		type="number" min="1" max="20" value="' . esc_attr( $amount ) . '" />';

		echo '<p class="description">';
		esc_html_e( 'Default amount of last viewed posts to display.', 'wp-last-viewed' );
		echo '</p>';
	}

	/**
	 * Form field setting to switch from server side, to client side tracking
	 */
	public function client_side_tracking_setting_field(): void {
		$field                = 'client_side_tracking';
		$client_side_tracking = self::get_client_side_tracking_setting();

		echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . $field ) . '"
		name="' . esc_attr( Config::get_option_name( $field ) ) . '"
		type="checkbox" value="1" ' . ( $client_side_tracking ? 'checked="checked"' : '' ) . ' />';

		echo '<label for="' . esc_attr( self::SETTINGS_PREFIX . $field ) . '" class="description">';
		esc_html_e( 'Use client side tracking instead of server side tracking.', 'wp-last-viewed' );
		echo '</label>';
		echo '<p class="description">';
		esc_html_e( 'Tracking of cached pages.', 'wp-last-viewed' );
		echo '</p>';
	}


	/**
	 * Sanitize post types from input
	 *
	 * @param array $input Form multi-select input.
	 * @return array
	 */
	public function sanitize_post_types( array $input ): array {
		$allowed = get_post_types();
		return array_intersect( $allowed, $input );
	}

	/**
	 * Form field for selecting multiple post types
	 */
	public function track_post_types_setting_field(): void {
		$field = 'track_post_types';

		$selected_types = self::get_track_post_types_settings();
		$name           = Config::get_option_name( $field );

		$post_types = get_post_types( [ 'public' => true ] );

		echo '<select multiple name="' . esc_attr( $name ) . '[]" style="min-width:200px;">';

		foreach ( $post_types as $id => $post_type ) {
			$selected = in_array( $id, $selected_types, true ) ? 'selected="selected"' : '';
			echo '<option value="' . esc_attr( $id ) . '" ' . esc_attr( $selected ) . '">';
			echo esc_attr( $post_type );
			echo '</option>';
		}

		echo '</select>';
	}


	/**
	 * Get Client Side Tracking setting
	 */
	public static function get_client_side_tracking_setting(): bool {
		$field = 'client_side_tracking';
		return Config::get_instance()->get( $field ) ? true : false;
	}

	/**
	 * Get track_post_types setting
	 *
	 * @return array
	 */
	public static function get_track_post_types_settings(): array {
		$field   = 'track_post_types';
		$setting = Config::get_instance()->get( $field );
		if ( ! $setting ) {
			return [ 'post' ];
		}
		return $setting;
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
