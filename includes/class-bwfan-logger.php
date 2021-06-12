<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BWFAN_Logger
 * @package Autonami
 * @author XlPlugins
 */
class BWFAN_Logger {

	private static $ins = null;
	public $wc_logger = null;

	public function __construct() {

	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function log( $message, $file_name ) {
		$global_settings  = BWFAN_Common::get_global_settings();
		$should_logs_made = ( isset( $global_settings['bwfan_make_logs'] ) && 1 === intval( $global_settings['bwfan_make_logs'] ) ) ? true : false;

		/** Restricting logs creation for bulk execution */
		$should_logs_made = apply_filters( 'bwfan_before_making_logs', $should_logs_made );
		if ( false === $should_logs_made || ! class_exists( 'BWF_Logger' ) ) {
			return;
		}

		$file_name  = sanitize_title( $file_name );
		$logger_obj = BWF_Logger::get_instance();

		add_filter( 'bwf_logs_allowed', array( $this, 'overriding_bwf_logging' ), 99999 );
		$logger_obj->log( $message, $file_name, 'autonami' );
		remove_filter( 'bwf_logs_allowed', array( $this, 'overriding_bwf_logging' ), 99999 );
	}

	public function overriding_bwf_logging( $flag ) {
		return true;
	}

}

if ( class_exists( 'BWFAN_Logger' ) ) {
	BWFAN_Core::register( 'logger', 'BWFAN_Logger' );
}
