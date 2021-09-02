<?php

/**
 * Handle UTM Grabber By Haktan Suren
 * https://wordpress.org/plugins/handl-utm-grabber/
 */
class BWFAN_Compatibility_With_Handle_UTM_Grabber {

	public function __construct() {
		/**
		 * Checking Perfmatters existence
		 */
		if ( ! defined( 'HANDL_UTM_V3_LINK' ) ) {
			return;
		}

		add_filter( 'bwfan_ab_default_checkout_nice_names', array( $this, 'bwfan_set_handle_utm_field' ), 9, 1 );
		add_action( 'bwfan_ab_handle_checkout_data_externally', array( $this, 'bwfan_set_handle_utm_cookie' ), 9, 1 );
	}

	/**set handle_utm_grabber key in checkout field nice name
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	public function bwfan_set_handle_utm_field( $fields ) {
		$fields['handle_utm_grabber'] = __( 'Handle UTM Grabber' );

		return $fields;
	}

	/**
	 * @param $checkout_data
	 */
	public function bwfan_set_handle_utm_cookie( $checkout_data ) {
		if ( ! isset( $checkout_data['fields']['handle_utm_grabber'] ) || empty( $checkout_data['fields']['handle_utm_grabber'] ) ) {
			return;
		}
		$handle_utm_grabber = $checkout_data['fields']['handle_utm_grabber'];
		$cookie_field       = '';
		$field_array        = array( 'utm_source', 'utm_campaign', 'utm_term', 'utm_medium', 'utm_content' );
		foreach ( $handle_utm_grabber as $utm_key => $value ) {
			if ( ! in_array( $utm_key, $field_array, true ) ) {
				continue;
			}

			$cookie_field = $handle_utm_grabber[ $utm_key ];
			$domain       = isset( $_SERVER["SERVER_NAME"] ) ? $_SERVER["SERVER_NAME"] : '';
			if ( strtolower( substr( $domain, 0, 4 ) ) == 'www.' ) {
				$domain = substr( $domain, 4 );
			}
			if ( substr( $domain, 0, 1 ) != '.' && $domain != "localhost" && $domain != "handl-sandbox" ) {
				$domain = '.' . $domain;
			}

			setcookie( $utm_key, $cookie_field, time() + 60 * 60 * 24 * 30, '/', $domain );
		}
	}
}

new BWFAN_Compatibility_With_Handle_UTM_Grabber();
