<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFAN_AB_Load_Events {
	private static $instance = null;

	public function __construct() {
		add_action( 'bwfan_wc_source_loaded', [ $this, 'load_events' ] );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_AB_Load_Events|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Loads all events of current trigger
	 */
	public function load_events( $source ) {
		$global_settings = BWFAN_Common::get_global_settings();
		if ( empty( $global_settings['bwfan_ab_enable'] ) ) {
			return;
		}

		$resource_dir = __DIR__ . '/events';
		if ( file_exists( $resource_dir ) ) {
			foreach ( glob( $resource_dir . '/class-*.php' ) as $_field_filename ) {

				$event_class = require_once( $_field_filename );
				if ( ! is_null( $event_class ) && method_exists( $event_class, 'get_instance' ) ) {
					$event_obj = $event_class::get_instance( $source->get_slug() );
					$event_obj->load_hooks();
					BWFAN_Load_Sources::register_events( $event_obj );
				}
			}

			do_action( 'bwfanac_events_loaded' );
		}
	}

}

if ( bwfan_is_woocommerce_active() ) {
	new BWFAN_AB_Load_Events();
}
