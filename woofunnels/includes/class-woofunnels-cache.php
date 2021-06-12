<?php

/**
 * @author woofunnels
 * @package WooFunnels
 */
if ( ! class_exists( 'WooFunnels_Cache' ) ) {
	class WooFunnels_Cache {

		protected static $instance;
		protected $woofunnels_core_cache = array();

		/**
		 * WooFunnels_Cache constructor.
		 */
		public function __construct() {

		}

		/**
		 * Creates an instance of the class
		 * @return WooFunnels_Cache
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Set the cache contents by key and group within page scope
		 *
		 * @param $key
		 * @param $data
		 * @param string $group
		 */
		public function set_cache( $key, $data, $group = '0' ) {
			$this->woofunnels_core_cache[ $group ][ $key ] = $data;
		}

		/**
		 * Get the cache contents by the cache key or group.
		 *
		 * @param $key
		 * @param string $group
		 *
		 * @return bool|mixed
		 */
		public function get_cache( $key, $group = '0' ) {
			if ( isset( $this->woofunnels_core_cache[ $group ] ) && isset( $this->woofunnels_core_cache[ $group ][ $key ] ) && ! empty( $this->woofunnels_core_cache[ $group ][ $key ] ) ) {
				return $this->woofunnels_core_cache[ $group ][ $key ];
			}

			return false;
		}

		/**
		 * Reset the cache by group or complete reset by force param
		 *
		 * @param string $group
		 * @param bool $force
		 */
		function reset_cache( $group = '0', $force = false ) {
			if ( true === $force ) {
				$this->woofunnels_core_cache = array();
			} elseif ( isset( $this->woofunnels_core_cache[ $group ] ) ) {
				$this->woofunnels_core_cache[ $group ] = array();
			}
		}

	}
}
