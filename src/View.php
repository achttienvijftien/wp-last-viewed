<?php
/**
 * This file contains the logic for the views.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

use AchttienVijftien\Plugin\LastViewed\Admin\Settings;

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
		// get default amount from config.
		$amount = Config::get_instance()->get( 'amount' );

		// bail early if no amount is set.
		if ( empty( $amount ) ) {
			return '';
		}

		$tracking_side = Config::get_instance()->get( 'tracking_side' );

		if ( Settings::SETTING_TRACKING_SIDE_CLIENT === $tracking_side ) {
			ob_start();

			?>
			<h3><?php echo esc_html__( 'Last viewed', 'wp-last-viewed' ); ?></h3>
			<ul class="wp-last-viewed-list" data-amount="<?php echo esc_attr( $amount ); ?>">
				<?php echo esc_html__( 'Loading last viewed posts...', 'wp-last-viewed' ); ?>
			</ul>
			<?php
			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		} else {
			// get list of tracked post ids.
			$post_ids = $this->get_tracked_post_ids();

			// bail early if no post ids found.
			if ( empty( $post_ids ) ) {
				return '';
			}

			// fetch the posts.
			$posts = $this->fetch_posts( $post_ids, $amount );

			ob_start();

			?>
			<h3><?php echo esc_html__( 'Last viewed', 'wp-last-viewed' ); ?></h3>
			<ul class="wp-last-viewed-list">
				<?php foreach ( $posts as $post ) { ?>
					<li class="wp-last-viewed-list__item">
						<a href="<?php echo esc_url( get_the_permalink( $post ) ); ?>">
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

	/**
	 * Get list of unique tracked post ids;
	 */
	private function get_tracked_post_ids(): array {
		// get data from tracking cookie.
		$tracking_cookie = $_COOKIE[ Tracker::TRACKING_COOKIE ];

		// bail early if no or empty cookie.
		if ( empty( $tracking_cookie ) ) {
			return [];
		}

		// get post ids.
		$post_ids = explode( ',', $tracking_cookie );

		// make list unique.
		$post_ids = array_unique( $post_ids );

		return $post_ids;
	}

	/**
	 * Fetch given amount of posts from post_ids.
	 *
	 * @param array $post_ids List of post ids to fetch.
	 * @param int   $amount amount of posts to fetch.
	 */
	private function fetch_posts( $post_ids, $amount ): array {
		// get posts.
		$posts = get_posts(
			[
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'post__in'       => $post_ids,
				'orderby'        => 'post__in',
			]
		);

		// limit posts to default amount. This is done after fetching to ignore invalid ids.
		$posts = array_slice( $posts, 0, $amount );

		return $posts;
	}
}
