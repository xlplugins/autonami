<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BWFAN_Load_Connectors
 * @package Autonami
 * @author XlPlugins
 */
class BWFAN_Load_Connectors {
	/**
	 * Saves all the main trigger's object
	 * @var array
	 */
	public static $sources = array();
	/**
	 * Saves all the action's object
	 * @var array
	 */
	public static $sources_resources = array();
	public static $sources_events = array();
	private static $ins = null;
	private static $_registered_entity = array(
		'active' => array(),
	);
	private static $_registered_resource_entity = array();

	public $show_in_ui = true;

	/**
	 * BWFAN_Load_Connectors constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'load_connectors' ], 8 );
		add_action( 'plugins_loaded', [ $this, 'register_classes' ], 9 );
	}

	/**
	 * Return the object of current class
	 *
	 * @return null|BWFAN_Load_Connectors
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Include all the Sources's files
	 */
	public static function load_connectors() {
		do_action( 'bwfan_before_automations_loaded' );
		$integration_dir = __DIR__ . '/native-connectors';
		foreach ( glob( $integration_dir . '/*/class-*.php' ) as $_field_filename ) {
			require_once( $_field_filename );
		}

		if ( bwfan_is_autonami_pro_active() ) {

			do_action( 'bwfan_automations_loaded' );
		}
	}

	/**
	 * Register the source when the source file is included
	 *
	 * @param $shortName
	 * @param $class
	 * @param null $overrides
	 */
	public static function register( $shortName, $class, $overrides = null ) {
		//Ignore classes that have been marked as inactive
		if ( isset( self::$_registered_entity['inactive'] ) && in_array( $class, self::$_registered_entity['inactive'], true ) ) {
			return;
		}

		//Mark classes as active. Override existing active classes if they are supposed to be overridden
		$index = array_search( $overrides, self::$_registered_entity['active'], true );
		if ( false !== $index ) {
			self::$_registered_entity['active'][ $index ] = $class;
		} else {
			self::$_registered_entity['active'][ sanitize_title( $class ) ] = $class;
		}

		//Mark overridden classes as inactive.
		if ( ! empty( $overrides ) ) {
			self::$_registered_entity['inactive'][] = $overrides;
		}
	}

	/**
	 * Registers every source as a system source
	 */
	public function register_classes() {
		$load_classes = self::get_registered_sources();
		if ( is_array( $load_classes ) && count( $load_classes ) > 0 ) {
			foreach ( $load_classes as $access_key => $class ) {
				self::$sources[ $access_key ] = $class::get_instance();
			}
		}
	}

	/**
	 * Return the registered integrations
	 *
	 * @return mixed
	 */
	public static function get_registered_sources() {
		return self::$_registered_entity['active'];
	}
}

if ( class_exists( 'BWFAN_Load_Connectors' ) ) {
	BWFAN_Core::register( 'native_connectors', 'BWFAN_Load_Connectors' );
}
