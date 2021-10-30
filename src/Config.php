<?php
/**
 * This file contains the logic for Bootstrap.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */

namespace AchttienVijftien\Plugin\LastViewed;

/**
 * Plugin config using WordPress options API.
 *
 * @package AchttienVijftien\Plugin\LastViewed
 */
class Config {

	/**
	 * Prefix of option key to avoid collision.
	 */
	private const OPTION_PREFIX = '1815_last-viewed_';

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
	 * Set config value.
	 *
	 * @param string $key Key of option.
	 * @param mixed  $value Value of option.
	 *
	 * @return bool
	 */
	public function set( string $key, $value ): bool {
		// add to options registry.
		if ( ! update_option( self::get_option_name( $key ), $value, false ) ) {
			return false;
		}

		// add to options for current load time usage.
		$this->options[ $key ] = $value;

		return true;
	}

	/**
	 * Get config value.
	 *
	 * @param string $key Key of option.
	 *
	 * @return mixed
	 */
	public function get( string $key ) {
		if ( ! isset( $this->options[ $key ] ) ) {
			$this->options[ $key ] = get_option( self::get_option_name( $key ) );
		}

		return $this->options[ $key ];
	}

	/**
	 * Returns prefixed option name.
	 *
	 * @param string $key Key of option.
	 *
	 * @return string
	 */
	public static function get_option_name( string $key ): string {
		return self::OPTION_PREFIX . $key;
	}
}
