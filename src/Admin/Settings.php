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

		add_action('wp_ajax_set_cookie', [ $this, 'set_cookie' ]);
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
		$this->add_types_setting();
		$this->add_tracking_setting();
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
		type="number" min="1" max="20" value="' . esc_attr( $amount ) . '" />';

		echo '<p class="description">';
		esc_html_e( 'Default amount of last viewed posts to display.', 'wp-last-viewed' );
		echo '</p>';
	}

	/**
	 * Post types setting.
	 */
	public function add_types_setting(): void {
		register_setting(
			self::SETTINGS_PREFIX . 'general',
			Config::get_option_name( 'types' ),
			[
				'type'    => 'array',
				'default' => [ 'post' ],
			]
		);

		add_settings_field(
			self::SETTINGS_PREFIX . 'types',
			__( 'Post types', 'wp-last-viewed' ),
			[
				$this,
				'types_setting_field',
			],
			self::GENERAL_PAGE_SLUG,
			self::SETTINGS_PREFIX . 'general'
		);
	}

	/**
	 * Form field of default amount setting.
	 */
	public function types_setting_field(): void {
		$types = Config::get_instance()->get( 'types' );

		$types_args = [
			'public' => true,
		];

		$registered_types = get_post_types( $types_args, 'names', 'and' );

		foreach ( $registered_types as $type_name ) {
			$selected = ( in_array( $type_name, $types ) ) ? 'checked' : '';

			echo '<label style="display: block; margin-bottom: 2px;">';
			echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . 'types' ) . '" class="select"
			name="' . esc_attr( Config::get_option_name( 'types' ) ) . '[]"
			type="checkbox" ' . esc_attr( $selected ) . ' value="' . esc_attr( $type_name ) . '" /> ';
			echo esc_attr( $type_name ) . '</label>';
		}

		echo '<p class="description">';
		esc_html_e( 'Post types to include in tracking.', 'wp-last-viewed' );
		echo '</p>';
	}

	/**
	 * Post types setting.
	 */
	public function add_tracking_setting(): void {
		register_setting(
			self::SETTINGS_PREFIX . 'general',
			Config::get_option_name( 'tracking_type' ),
			[
				'type'    => 'string',
				'default' => 'server_side',
			]
		);

		add_settings_field(
			self::SETTINGS_PREFIX . 'tracking_type',
			__( 'Tracking type', 'wp-last-viewed' ),
			[
				$this,
				'tracking_setting_field',
			],
			self::GENERAL_PAGE_SLUG,
			self::SETTINGS_PREFIX . 'general'
		);
	}

	/**
	 * Form field of default amount setting.
	 */
	public function tracking_setting_field(): void {
		$tracking_type = Config::get_instance()->get( 'tracking_type' );
		$server_side_active = ($tracking_type === 'server_side') ? 'checked' : '';
		$client_side_active = ($tracking_type === 'client_side') ? 'checked' : '';

		echo '<label style="display: block; margin-bottom: 2px;">';
		echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . 'track' ) . '" class="select" name="' . esc_attr( Config::get_option_name( 'tracking_type' ) ) .'"
		type="radio" '.  esc_attr( $server_side_active ) .' value="server_side" /> ';
		echo 'Server side</label>';

		echo '<label style="display: block; margin-bottom: 2px;">';
		echo '<input id="' . esc_attr( self::SETTINGS_PREFIX . 'track' ) . '" class="select" name="' . esc_attr( Config::get_option_name( 'tracking_type' ) ) .'"
		type="radio" '.  esc_attr( $client_side_active ) .' value="client_side" /> ';
		echo 'Client side</label>';
		
		echo '<p class="description">';
		esc_html_e( 'Type of tracking.', 'wp-last-viewed' );
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

	/**
	 * Ajax method to set cookie
	 * 
	 */

	public function set_cookie(): void {
		$post_id = $_POST['post_id'];

		$ajax_tracker = new Tracker();
	}
}
