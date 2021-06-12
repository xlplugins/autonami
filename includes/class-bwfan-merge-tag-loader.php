<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class BWFAN_Merge_Tag_Loader {
	public static $included_merge_tags = array();
	/** @var array */
	protected static $data = array(
		'is_preview' => false,
	);
	/** @var array */
	private static $merge_tags_list;
	private static $instance = null;
	private static $localize_tags_with_source = [];
	private static $localize_tags = [];

	private static $_registered_entity = array(
		'active' => array(),
	);

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'load_merge_tags' ], 8 );
		add_action( 'plugins_loaded', [ $this, 'register_classes' ], 9 );
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_Merge_Tag_Loader|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Function to get all the data set
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 */
	public static function get_data( $key = '' ) {
		if ( empty( $key ) ) {
			return self::$data;
		}
		if ( isset( self::$data[ $key ] ) ) {
			return self::$data[ $key ];
		}

		return array();
	}

	/**
	 * Set merge tag data
	 *
	 * @param $data
	 */
	public static function set_data( $data ) {
		if ( ! is_array( $data ) ) {
			return;
		}
		foreach ( $data as $key => $value ) {
			self::$data[ $key ] = $value;
		}
	}

	/**
	 * Reset the data
	 */
	public static function reset_data() {
		self::$data = array(
			'is_preview' => false,
		);
	}

	/**
	 * Include all the Merge Tags's files
	 */
	public static function load_merge_tags() {
		$integration_dir = __DIR__ . '/merge_tags';
		foreach ( glob( $integration_dir . '/class-*.php' ) as $_field_filename ) {
			$file_data = pathinfo( $_field_filename );
			if ( isset( $file_data['basename'] ) && 'index.php' === $file_data['basename'] ) {
				continue;
			}
			require_once( $_field_filename );
		}
		do_action( 'bwfan_merge_tags_loaded' );

	}

	/**
	 * Register the integration when the integration file is included
	 *
	 * @param $shortName
	 * @param $class
	 * @param null $overrides
	 */
	public static function register( $shortName, $class, $overrides = null ) {
		if ( ! class_exists( $class ) || ! method_exists( $class, 'get_instance' ) ) {
			return;
		}

		/**
		 * @var $instance BWFAN_Merge_Tag
		 */
		$instance                                               = $class::get_instance();
		$slug                                                   = $instance->get_slug();
		self::$localize_tags_with_source[ $shortName ][ $slug ] = $instance->get_localize_data();
		self::$localize_tags[ $slug ]                           = self::$localize_tags_with_source[ $shortName ][ $slug ];
		self::$_registered_entity[ $shortName ][ $slug ]        = $instance;
	}

	/**
	 * Return all the actions with group and their integrations
	 *
	 * @return array
	 */
	public static function get_all_merge_tags() {
		return self::$included_merge_tags;
	}

	/**
	 * Registers every integration as a system integration
	 */
	public function register_classes() {
		$load_classes = self::get_registered_merge_tags();

		if ( is_array( $load_classes ) && count( $load_classes ) > 0 ) {
			self::$included_merge_tags = $load_classes;
		}
	}

	/**
	 * Return the registered integrations
	 *$instance->get_localize_data()
	 * @return mixed
	 */
	public static function get_registered_merge_tags() {
		return self::$_registered_entity;
	}

	/**
	 * Return associated merge tag localize data(Parent child based)
	 * @return array
	 */
	public function get_localize_tags_with_source() {

		return self::$localize_tags_with_source;
	}

	/**
	 * Return merge tag localize data
	 * @return array
	 */
	public function get_localize_tags() {
		return self::$localize_tags_with_source;
	}

}

if ( class_exists( 'BWFAN_Merge_Tag_Loader' ) ) {
	BWFAN_Core::register( 'merge_tags', 'BWFAN_Merge_Tag_Loader' );

}

