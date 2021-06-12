<?php

/**
 * Password Protected
 * https://wordpress.org/plugins/password-protected/
 */
class BWFAN_Compatibility_With_Password_Protected {

	public function __construct() {
		/**
		 * Checking Password Protected existence
		 */
		if ( ! class_exists( 'Password_Protected' ) ) {
			return;
		}

		add_filter( 'password_protected_is_active', array( $this, 'bwfan_allow_rest_api_password_protected' ) );
	}

	/**
	 * @return bool
	 */
	public function bwfan_allow_rest_api_password_protected( $status ) {
		$rest_route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? $GLOBALS['wp']->query_vars['rest_route'] : '';

		if ( empty( $rest_route ) ) {
			return $status;
		}

		if ( strpos( $rest_route, 'autonami' ) !== false || strpos( $rest_route, 'woofunnel' ) !== false ) {
			return false;
		}

		return $status;
	}
}

new BWFAN_Compatibility_With_Password_Protected();
