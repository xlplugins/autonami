<?php

/**
 * @author woofunnels
 * @package WooFunnels
 */
if ( ! class_exists( 'WooFunnels_Transient' ) ) {
	class WooFunnels_Transient {

		protected static $instance;

		/**
		 * WooFunnels_Transient constructor.
		 */
		public function __construct() {

		}

		/**
		 * Creates an instance of the class
		 * @return WooFunnels_Transient
		 */
		public static function get_instance() {

			if ( null === self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Set the transient contents by key and group within page scope
		 *
		 * @param $key
		 * @param $value
		 * @param int $expiration | default 1 hour
		 * @param string $plugin_short_name
		 */
		public function set_transient( $key, $value, $expiration = 3600, $plugin_short_name = 'bwf' ) {

			$transient_key   = '_woofunnels_transient_' . $plugin_short_name . '_' . $key;
			$transient_value = array(
				'time'  => time() + (int) $expiration,
				'value' => $value,
			);

			$file_writing = $this->is_file_saving_enabled();
			if ( class_exists( 'WooFunnels_File_Api' ) && true === $file_writing ) {
				$file_api = new WooFunnels_File_Api( $plugin_short_name . '-transient' );
				$file_api->touch( $transient_key );
				if ( $file_api->is_writable( $transient_key ) && $file_api->is_readable( $transient_key ) ) {
					$transient_value = maybe_serialize( $transient_value );
					$file_api->put_contents( $transient_key, $transient_value );
				} else {

					// woofunnels file api folder not writable
					update_option( $transient_key, $transient_value, false );
				}
			} else {

				// woofunnels file api method not available
				update_option( $transient_key, $transient_value, false );
			}
		}

		/**
		 * Get the transient contents by the transient key or group.
		 *
		 * @param $key
		 * @param string $plugin_short_name
		 *
		 * @return bool|mixed
		 */
		public function get_transient( $key, $plugin_short_name = 'bwf' ) {
			if ( true === apply_filters( 'bwf_disable_woofunnels_transient', false, $plugin_short_name ) ) {
				return false;
			}

			$transient_key = '_woofunnels_transient_' . $plugin_short_name . '_' . $key;
			$file_writing  = $this->is_file_saving_enabled();
			if ( class_exists( 'WooFunnels_File_Api' ) && true === $file_writing ) {
				$file_api = new WooFunnels_File_Api( $plugin_short_name . '-transient' );
				if ( $file_api->is_writable( $transient_key ) && $file_api->is_readable( $transient_key ) ) {
					$data  = $file_api->get_contents( $transient_key );
					$data  = maybe_unserialize( $data );
					$value = $this->get_value( $transient_key, $data );
					if ( false === $value ) {
						$file_api->delete( $transient_key );
					}

					return $value;
				}
			}

			// woofunnels file api method not available
			$data = get_option( $transient_key, false );
			if ( false === $data ) {
				return false;
			}

			return $this->get_value( $transient_key, $data, true );
		}

		public function get_value( $transient_key, $data, $db_call = false ) {
			$current_time = time();
			if ( is_array( $data ) && isset( $data['time'] ) ) {
				if ( $current_time > (int) $data['time'] ) {
					if ( true === $db_call ) {
						delete_option( $transient_key );
					}

					return false;
				} else {
					return $data['value'];
				}
			}

			return false;
		}

		/**
		 * Delete the transient by key
		 *
		 * @param $key
		 * @param string $plugin_short_name
		 */
		public function delete_transient( $key, $plugin_short_name = 'bwf' ) {
			$transient_key = '_woofunnels_transient_' . $plugin_short_name . '_' . $key;
			$file_writing  = $this->is_file_saving_enabled();
			if ( class_exists( 'WooFunnels_File_Api' ) && true === $file_writing ) {
				$file_api = new WooFunnels_File_Api( $plugin_short_name . '-transient' );

				if ( $file_api->exists( $transient_key ) ) {
					$file_api->delete_file( $transient_key );
				}
			}

			// removing db transient
			delete_option( $transient_key );
		}

		/**
		 * Delete all the transients
		 *
		 * @param string $plugin_short_name
		 */
		public function delete_all_transients( $plugin_short_name = '' ) {
			global $wpdb;

			/** removing db transient */
			$query = "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE '%_woofunnels_transient_{$plugin_short_name}%'";
			$wpdb->query( $query ); //phpcs:ignore WordPress.DB.PreparedSQL

			/** removing files if file api exist */
			$file_writing = $this->is_file_saving_enabled();
			if ( class_exists( 'WooFunnels_File_Api' ) && true === $file_writing ) {
				$file_api = new WooFunnels_File_Api( $plugin_short_name . '-transient' );
				$file_api->delete_all( $plugin_short_name . '-transient', true );
			}
		}

		/**
		 * Delete all woofunnels plugins transients
		 */
		public function delete_force_transients() {
			global $wpdb;

			/** removing db transient */
			$query = "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE '%_woofunnels_transient_%'";
			$wpdb->query( $query ); //phpcs:ignore WordPress.DB.PreparedSQL

			/** removing files if file api exist */
			$file_writing = $this->is_file_saving_enabled();
			if ( class_exists( 'WooFunnels_File_Api' ) && true === $file_writing ) {
				$file_api = new WooFunnels_File_Api( 'bwf-transient' );

				$upload      = wp_upload_dir();
				$folder_path = $upload['basedir'] . '/woofunnels';
				$file_api->delete_folder( $folder_path, true );
			}
		}

		/**
		 * Can modify the file writing via filter hook
		 *
		 * @return bool
		 */
		protected function is_file_saving_enabled() {
			return apply_filters( '_bwf_transient_file_saving', true );
		}
	}
}
