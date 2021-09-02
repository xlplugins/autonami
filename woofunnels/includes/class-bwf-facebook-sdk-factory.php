<?php

class BWF_Facebook_Sdk_Factory {


	private static $pixel_id = null;
	private static $access_token = null;
	private static $version = null;
	private static $setup_run = false;
	private static $test_event_code = false;
	private static $partner_code = false;

	/**
	 * @param $pixel_id
	 * @param $access_token
	 * @param string $version
	 *
	 * @return boolean
	 */
	public static function setup( $pixel_id, $access_token, $version = 'v11.0' ) {

		if ( empty( $pixel_id ) || empty( $access_token ) ) {
			return false;
		}
		self::$pixel_id     = $pixel_id;
		self::$access_token = $access_token;
		self::$version      = $version;
		self::$setup_run    = true;

		return true;
	}

	public static function set_test( $test_code ) {
		self::$test_event_code = $test_code;

	}

	public static function set_partner( $partner_code ) {
		self::$partner_code = $partner_code;

	}

	public static function create() {
		if ( false == self::$setup_run ) {
			return null;
		}

		$instance = new BWF_Facebook_Sdk( self::$pixel_id, self::$access_token, self::$version );
		if ( ! empty( self::$test_event_code ) ) {
			$instance->set_test_event_code( self::$test_event_code );
		}
		if ( ! empty( self::$partner_code ) ) {
			$instance->set_partner_agent( self::$partner_code );
		}

		return $instance;
	}
}
