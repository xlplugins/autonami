<?php

/**
 * Perfmatters
 * https://perfmatters.io/
 */
class BWFAN_Compatibility_With_Perfmatters {

	public function __construct() {
		/**
		 * Checking Perfmatters existence
		 */
		if ( ! defined( 'PERFMATTERS_VERSION' ) ) {
			return;
		}

		add_filter( 'rest_jsonp_enabled', array( $this, 'bwfan_allow_rest_apis_with_perfmatters' ) );
	}

	/**
	 * @return bool
	 */
	public function bwfan_allow_rest_apis_with_perfmatters( $status ) {
		$rest_route = $GLOBALS['wp']->query_vars['rest_route'];

		if ( strpos( $rest_route, 'autonami' ) !== false || strpos( $rest_route, 'woofunnel' ) !== false ) {
			remove_filter( 'rest_authentication_errors', 'perfmatters_rest_authentication_errors', 20 );
		}

		return $status;
	}
}

new BWFAN_Compatibility_With_Perfmatters();
