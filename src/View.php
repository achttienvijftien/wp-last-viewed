<?php
/**
 * This file contains the logic for the views.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

use AchttienVijftien\Plugin\LastViewed\Admin\Settings;
use AchttienVijftien\Plugin\LastViewed\Config;
use AchttienVijftien\Plugin\LastViewed\Tracker;

/**
 * Outputs the html
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */
class View {

	/**
	 * Config instance.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Hot loaded bag of options.
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Gets (singleton) instance.
	 *
	 * @return static
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Output posts found in the tracking cookie
	 *
	 * @param int $posts_per_page Number of posts per page, default -1 to have unlimited.
	 * @return string
	 */
	public function output_posts( int $posts_per_page = -1 ): string {

		// bail early if no or empty cookie.
		if ( empty( $_COOKIE[ Tracker::TRACKING_COOKIE ] ) ) {
			return '';
		}

		if ( 0 === $posts_per_page ) {
			return '';
		}

		// get post ids.
		$post_ids = explode( ',', $_COOKIE[ Tracker::TRACKING_COOKIE ] );

		// limit to number of post_ids, if asked.
		if ( 1 !== $posts_per_page ) {
			$post_ids = array_slice( $post_ids, 0, $posts_per_page );
		}

		// get posts based on track post types settings.
		$posts = get_posts(
			[
				'post_type'      => Settings::get_track_post_types_settings(),
				'posts_per_page' => $posts_per_page,
				'post__in'       => $post_ids,
				'orderby'        => 'post__in',
			]
		);

		if ( empty( $posts ) ) {
			return '';
		}

		ob_start();

		?>
		<h3><?php esc_html_e( 'Last viewed', 'wp-last-viewed' ); ?></h3>
		<ul class="wp-last-viewed-list">
			<?php foreach ( $posts as $post ) { ?>
				<li class="wp-last-viewed-list__item">
					<a href="<?php echo esc_html( get_the_permalink( $post ) ); ?>">
						<?php echo esc_html( get_the_title( $post ) ); ?>
					</a>
				</li>
			<?php } ?>
		</ul>
		<?php
		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}
}
