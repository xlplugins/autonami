<?php

class BWFAN_Compatibility_With_WP_Rest_Authenticate {

	public function __construct() {
		/**
		 * Checking WP Rest Api Authenticate existence
		 */
		if ( ! function_exists( 'mo_api_auth_activate_miniorange_api_authentication' ) ) {
			return;
		}

		add_filter( 'dra_allow_rest_api', [ $this, 'bwfan_allow_rest_apis' ] );
	}

	/**
	 * @return bool
	 */
	public function bwfan_allow_rest_apis() {
		$rest_route = $GLOBALS['wp']->query_vars['rest_route'];
		if ( false !== strpos( $rest_route, 'autonami' ) || false !== strpos( $rest_route, 'woofunnel' ) ) {
			return true;
		}

		return false;
	}
}

new BWFAN_Compatibility_With_WP_Rest_Authenticate();
