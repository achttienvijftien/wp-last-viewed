<?php
/**
 * This file contains the logic for the views.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

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
	private array $options = [];

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
	 */
	public function output_posts(): string {

		// bail early if no or empty cookie
		if ( empty( $_COOKIE[ Tracker::TRACKING_COOKIE ] ) ) {
			return '';
		}

		// get post ids
		$post_ids = explode( ',', $_COOKIE[ Tracker::TRACKING_COOKIE ] );

		// get posts
		$posts = get_posts( [
			'post_type'      => 'post',
			'posts_per_page' => -1,
			'post__in'       => $post_ids,
			'orderby'        => 'post__in'
		] );

		if ( empty( $posts ) ) {
			return '';
		}

		ob_start();

		?>
		<ul class="wp-last-viewed-list">
			<?php foreach ( $posts as $post ) { ?>
				<li class="wp-last-viewed-list__item"><a href="<?php echo get_the_permalink( $post ); ?>"><?php echo get_the_title( $post ); ?></a></li>
			<?php } ?>
		</ul>
		<?php
		$html = ob_get_contents();

		ob_end_clean();

		return $html;

	}
}
