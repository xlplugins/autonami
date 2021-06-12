<?php

/**
 * Force Login
 * https://wordpress.org/plugins/wp-force-login/
 */
class BWFAN_Compatibility_With_Force_Login {

	public function __construct() {
		/**
		 * check Force Login existence
		 */
		if ( ! function_exists( 'v_forcelogin_rest_access' ) ) {
			return;
		}
		add_filter( 'rest_jsonp_enabled', array( $this, 'bwfan_allow_rest_apis_with_force_login' ) );
	}

	/**
	 * @return bool
	 */
	public function bwfan_allow_rest_apis_with_force_login( $status ) {
		$rest_route = $GLOBALS['wp']->query_vars['rest_route'];

		if ( strpos( $rest_route, 'autonami' ) !== false || strpos( $rest_route, 'woofunnel' ) !== false ) {
			remove_filter( 'rest_authentication_errors', 'v_forcelogin_rest_access', 99 );
		}

		return $status;
	}
}

new BWFAN_Compatibility_With_Force_Login();
