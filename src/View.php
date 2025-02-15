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

		// get post types from config.
		$post_types = Config::get_instance()->get( 'post_types' );

		// bail early if no amount is set.
		if ( empty( $amount ) ) {
			return '';
		}

		// get tracking-side from config.
		$tracking_side = Config::get_instance()->get( 'tracking_side' );

		if ( Settings::SETTING_TRACKING_SIDE_CLIENT === $tracking_side ) {
			// when client side tracking is enabled, render the cache-friendly version.
			return $this->render_client_side_tracking( $amount, $post_types );
		} else {
			// get list of tracked post ids.
			$post_ids = $this->get_tracked_posts( true );

			// fetch the posts.
			$posts = $this->fetch_posts( $post_ids, $post_types, $amount );

			// render the posts.
			return $this->render_server_side_tracking( $posts );
		}
	}

	/**
	 * Get list of unique tracked post ids.
	 *
	 * @param bool $ignore_current Whether the current post should be ignored in the result.
	 */
	private function get_tracked_posts( bool $ignore_current = false ): array {
		// get data from tracking cookie.
		$tracking_cookie = $_COOKIE[ Tracker::TRACKING_COOKIE ];

		// bail early if no or empty cookie.
		if ( empty( $tracking_cookie ) ) {
			return [];
		}

		// get post ids.
		$post_ids = explode( ',', $tracking_cookie );

		// ignore current post if requested.
		if ( $ignore_current && is_singular() ) {
			$post_identifier = get_the_ID() . '/' . get_post_type();
			$post_index      = array_search( $post_identifier, $post_ids, true );
			unset( $post_ids [ $post_index ] );
		}

		// make list unique.
		$post_ids = array_unique( $post_ids );

		// decode posts to more usable arrays.
		$post_ids = array_map(
			function ( $raw_post ) {
				$post = explode( '/', $raw_post, 2 );
				return [
					'id'   => intval( $post[0] ),
					'type' => $post[1],
				];
			},
			$post_ids
		);

		return $post_ids;
	}

	/**
	 * Fetch given amount of posts from post_ids.
	 *
	 * @param array $post_ids List of post ids to fetch.
	 * @param array $post_types List of post types to fetch.
	 * @param int   $amount Amount of posts to fetch.
	 */
	private function fetch_posts( array $post_ids, array $post_types, int $amount ): array {
		// bail early if no post ids found.
		if ( empty( $post_ids ) ) {
			return [];
		}

		// get the ids.
		$post_ids = array_column( $post_ids, 'id' );

		// get posts.
		$posts = get_posts(
			[
				'post_type'      => $post_types,
				'posts_per_page' => $amount,
				'post__in'       => $post_ids,
				'orderby'        => 'post__in',
				'post_status'    => [ 'inherit', 'publish' ],
			]
		);

		return $posts;
	}

	/**
	 * Render HTML list of tracked posts, tracked by client. Can be used with proxy-caching.
	 *
	 * @param int   $amount Amount of posts that should be rendered in this list.
	 * @param array $post_types Post types that should be rendered in this list.
	 */
	public function render_client_side_tracking( int $amount, array $post_types ): string {
		ob_start();

		?>
		<h3><?php echo esc_html__( 'Last viewed', 'wp-last-viewed' ); ?></h3>
		<ul class="wp-last-viewed-list"
			data-amount="<?php echo esc_attr( $amount ); ?>"
			data-post-types="<?php echo esc_attr( implode( ',', $post_types ) ); ?>">
			<li class="wp-last-viewed-list__item placeholder">
				<?php echo esc_html__( 'Loading last viewed posts...', 'wp-last-viewed' ); ?>
			</li>
		</ul>
		<?php
		$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}

	/**
	 * Render HTML list of tracked posts, tracked by server. Can be used with proxy-caching.
	 *
	 * @param array $posts List of posts to use for rendering.
	 */
	public function render_server_side_tracking( array $posts ): string {
		ob_start();

		?>
		<h3><?php echo esc_html__( 'Last viewed', 'wp-last-viewed' ); ?></h3>
		<ul class="wp-last-viewed-list">
			<?php if ( empty( $posts ) ) : ?>
			<li class="wp-last-viewed-list__item placeholder">
				<?php echo esc_html__( 'No posts found.', 'wp-last-viewed' ); ?>
			</li>
			<?php endif; ?>
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
