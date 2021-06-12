<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BWFAN_Load_Sources
 * @package Autonami
 * @author XlPlugins
 */
class BWFAN_Load_Sources {
	/**
	 * Saves all the main trigger's object
	 * @var array
	 */
	private static $sources_obj = [];
	/**
	 * Saves all the action's object
	 * @var array
	 */
	private static $sources_events_obj = [];
	private static $sources_events_arr = [];
	private static $sources_events_arr1 = [];
	private static $sources_localize_data = [];
	private static $sources_events_localize = [];
	private static $events_localize = [];
	private static $ins = null;
	private static $registered_events = [];
	public static $all_events = []; // This property is used for displaying all events options in global settings to stop event


	/**
	 * BWFAN_Load_Sources constructor.
	 */
	public function __construct() {
	}

	/**
	 * Return the object of current class
	 *
	 * @return null|BWFAN_Load_Sources
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}


	/**
	 * Register the source when the source file is included
	 *
	 * @param $class BWFAN_Source;
	 */
	public static function register( $source_class ) {
		if ( method_exists( $source_class, 'get_instance' ) ) {

			/**
			 * @var $source BWFAN_Source
			 */
			$source = $source_class::get_instance();
			$slug   = $source->get_slug();
			$source->load_events();
			self::$sources_obj[ $slug ] = $source;

			self::$sources_localize_data[ $slug ] = $source->get_localize_data();
			do_action( 'bwfan_' . $slug . '_source_loaded', $source );

		}
	}

	/**
	 * Register every event when event file is included
	 *
	 * @param $event BWFAN_Event
	 */
	public static function register_events( BWFAN_Event $event ) {
		if ( ! method_exists( $event, 'get_instance' ) ) {
			return;
		}

		$temp_source = $event->get_source();
		$event_slug  = $event->get_slug();
		$optgroup    = $event->get_optgroup_label();
		$priority    = $event->get_priority();

		self::$sources_events_obj[ $temp_source ][ $event_slug ]      = $event;
		self::$registered_events[ $event_slug ]                       = $event;
		self::$sources_events_localize[ $temp_source ][ $event_slug ] = $event->get_localize_data();
		self::$events_localize[ $event_slug ]                         = self::$sources_events_localize[ $temp_source ][ $event_slug ];

		if ( ! isset( self::$sources_events_arr[ $temp_source ] ) ) {
			self::$sources_events_arr[ $temp_source ] = [
				'events' => [],
			];
		}
		self::$sources_events_arr[ $temp_source ]['events'][ $optgroup ][ $event_slug ] = [
			'name'      => $event->get_name(),
			'available' => 'yes',
		];

		self::$sources_events_arr1[ $temp_source ]['events'][ strval( $priority ) ][] = [
			'opt_group' => $optgroup,
			'slug'      => $event_slug,
			'name'      => $event->get_name(),
			'available' => 'yes',
		];
	}

	/**
	 * Return all the Sources With object
	 *
	 * @return array
	 */
	public static function get_sources_obj() {
		ksort( self::$sources_obj );

		return apply_filters( 'bwfan_get_sources', self::$sources_obj );
	}

	/**
	 * Return all the events with group and their sources
	 *
	 * @return array
	 */
	public static function get_all_sources_obj() {
		$data = apply_filters( 'bwfan_get_all_sources', self::$sources_events_obj );

		return $data;
	}

	/**
	 * Hierarchy of source and events
	 * @return mixed|void
	 *
	 */
	public static function get_sources_events_arr() {
		$test_arr    = [];
		$final_array = [];
		if ( is_array( self::$sources_events_arr1 ) && count( self::$sources_events_arr1 ) > 0 ) {
			foreach ( self::$sources_events_arr1 as $source_slug => $events_list ) {
				if ( is_array( $events_list['events'] ) && count( $events_list['events'] ) > 0 ) {
					$keys = array_keys( $events_list['events'] );
					sort( $keys );
					foreach ( $keys as $key ) {
						$test_arr[ $source_slug ]['events'][ $key ] = $events_list['events'][ $key ];
					}
				}
			}

			foreach ( $test_arr as $source_slug => $events_list ) {
				if ( ! isset( $final_array[ $source_slug ] ) ) {
					$final_array[ $source_slug ] = [];
				}
				if ( ! isset( $final_array[ $source_slug ]['events'] ) ) {
					$final_array[ $source_slug ]['events'] = [];
				}
				foreach ( $events_list['events'] as $event ) {
					if ( ! is_array( $event ) ) {
						continue;
					}
					foreach ( $event as $event_data ) {
						if ( ! isset( $final_array[ $source_slug ]['events'][ $event_data['opt_group'] ] ) ) {
							$final_array[ $source_slug ]['events'][ $event_data['opt_group'] ] = [];
						}
						$final_array[ $source_slug ]['events'][ $event_data['opt_group'] ][ $event_data['slug'] ] = [
							'name'      => $event_data['name'],
							'available' => $event_data['available'],
						];
					}
				}
			}
		}

		return $final_array;
	}

	/**
	 * Return the source instance
	 *
	 * @return BWFAN_Source
	 */
	public function get_source( $slug = 'wp' ) {
		return isset( self::$sources_obj[ $slug ] ) ? self::$sources_obj[ $slug ] : null;
	}

	/**
	 * Returns the registered actions
	 *
	 * @param  $event
	 *
	 * @return array
	 */
	public function get_events() {
		return self::$registered_events;
	}

	/**
	 * Returns the registered actions
	 *
	 * @param  $event
	 *
	 * @return BWFAN_Event
	 */
	public function get_event( $event = '' ) {
		return isset( self::$registered_events[ $event ] ) ? self::$registered_events[ $event ] : null;
	}

	/**
	 * GEt Event  localize data with under the source
	 * @return array
	 */
	public function get_sources_events_localize_data() {
		return apply_filters( 'bwfan_source_action_localize_data', self::$sources_events_localize );
	}

	/**
	 * Get source localize data
	 * @return array
	 */
	public function get_source_localize_data() {
		uasort( self::$sources_localize_data, function ( $item1, $item2 ) {
			return $item1['priority'] >= $item2['priority'];
		} );

		return apply_filters( 'bwfan_source_localize_data', self::$sources_localize_data );
	}

}

if ( class_exists( 'BWFAN_Core' ) ) {
	BWFAN_Core::register( 'sources', 'BWFAN_Load_Sources' );
}
