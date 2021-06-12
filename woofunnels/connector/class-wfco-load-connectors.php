<?php

class WFCO_Load_Connectors {
	/** @var class instance */
	private static $ins = null;

	/** @var array All connectors with object */
	private static $connectors = array();

	/** @var array All calls with object */
	private static $registered_calls = array();

	/** @var array All calls objects group by connectors */
	private static $registered_connectors_calls = array();

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'load_connectors' ], 8 );
	}

	/**
	 * Return class instance
	 *
	 * @return class|WFCO_Load_Connectors
	 */
	public static function get_instance() {
		if ( null == self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Include all the connectors files
	 */
	public static function load_connectors() {
		do_action( 'wfco_load_connectors' );
	}

	/**
	 * Register a connector with their object
	 * Assign to static property $connectors
	 * Load connector respective calls
	 *
	 * @param $class
	 */
	public static function register( $class ) {
		if ( ! class_exists( $class ) && ! method_exists( $class, 'get_instance' ) ) {
			return;
		}

		$temp_ins = $class::get_instance();
		if ( ! $temp_ins instanceof BWF_CO ) {
			return;
		}

		$slug = $temp_ins->get_slug();

		self::$connectors[ $slug ] = $temp_ins;
		add_action( 'wfco_connector_screen', [ $temp_ins, 'setting_view' ] );
		$temp_ins->load_calls();
	}

	/**
	 * Register a call with their object
	 * Assign to static property $registered_calls
	 * Assign to static property $registered_connectors_calls
	 *
	 * @param WFCO_Call $call_obj
	 */
	public static function register_calls( WFCO_Call $call_obj ) {
		if ( method_exists( $call_obj, 'get_instance' ) ) {
			$slug           = $call_obj->get_slug();
			$connector_slug = $call_obj->get_connector_slug();

			self::$registered_connectors_calls[ $connector_slug ][ $slug ] = self::$registered_calls[ $slug ] = $call_obj;
		}
	}

	/**
	 * Return all the connectors with their calls objects
	 *
	 * @return array
	 */
	public static function get_all_connectors() {
		return self::$registered_connectors_calls;
	}


	/**
	 * Returns Instance of single connector
	 *
	 * @param $connector
	 *
	 * @return BWF_CO
	 */
	public static function get_connector( $connector ) {
		return isset( self::$connectors[ $connector ] ) ? self::$connectors[ $connector ] : null;
	}


	/**
	 * Returns all the active connectors i.e. plugin active
	 *
	 * @return array
	 */
	public static function get_active_connectors() {
		return self::$connectors;
	}

	/**
	 * Return a call object if call slug is passed.
	 * Return all calls object if no single call slug passed.
	 *
	 * @param string $slug
	 *
	 * @return array|mixed
	 */
	public function get_calls( $slug = '' ) {
		if ( empty( $slug ) ) {
			return self::$registered_calls;
		}
		if ( isset( self::$registered_calls[ $slug ] ) ) {
			return self::$registered_calls[ $slug ];
		}
	}

	/**
	 * Return a call object based on the given slug.
	 *
	 * @param string $slug call slug
	 *
	 * @return WFCO_Call | null
	 */
	public function get_call( $slug ) {
		return ( ! empty( $slug ) && isset( self::$registered_calls[ $slug ] ) ) ? self::$registered_calls[ $slug ] : null;
	}
}

/**
 * Initiate the class as soon as it is included
 */
WFCO_Load_Connectors::get_instance();
