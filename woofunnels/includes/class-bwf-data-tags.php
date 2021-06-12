<?php

if ( ! class_exists( 'BWF_Data_Tags' ) ) {
	class BWF_Data_Tags {


		public function __construct() {

			$this->shortcodes = array(
				'get_cookie',
				'get_url_parameter',

			);
			foreach ( $this->shortcodes as $code ) {
				add_shortcode( 'wf_' . $code, array( $this, $code ) );
			}

		}

		private static $ins = null;

		/**
		 * @return BWF_Optin_Tags|null
		 */
		public static function get_instance() {

			if ( null === self::$ins ) {
				self::$ins = new self;
			}

			return self::$ins;
		}


		public function get_first_name( $attr ) {
			if ( isset( $this->get_optin()->optin_first_name ) && ! empty( $this->get_optin()->optin_first_name ) ) {
				return $this->get_optin()->optin_first_name;
			}

			return $this->get_default( $attr, 'first_name' );

		}

		public function get_cookie( $attr ) {
			$attr = shortcode_atts( array(
				'key' => '',
			), $attr );

			if ( empty( $attr['key'] ) ) {
				return '';
			}

			return isset( $_COOKIE[ $attr['key'] ] ) ? bwf_clean( $_COOKIE[ $attr['key'] ] ) : '';

		}

		public function get_url_parameter( $attr ) {

			$attr = shortcode_atts( array(
				'key' => '',
			), $attr );

			if ( empty( $attr['key'] ) ) {
				return '';
			}

			return isset( $_GET[ $attr['key'] ] ) ? bwf_clean( $_GET[ $attr['key'] ] ) : '';

		}


	}

	BWF_Data_Tags::get_instance();
}