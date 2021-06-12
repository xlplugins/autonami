<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class BWFAN_API_Loader
 * @package Autonami
 * @author XlPlugins
 */
class BWFAN_API_Loader {
	private static $ins = null;

	/**
	 * @var array $registered_apis
	 */
	private static $registered_apis = array();

	/**
	 * Supported HTTP Method Constants
	 */
	const POST = 'POST';
	const GET = 'GET';
	const PUT = 'PUT';
	const PATCH = 'PATCH';
	const DELETE = 'DELETE';

	/**
	 * BWFAN_API_Loader constructor.
	 */
	public function __construct() {
		self::load_api_supporter_classes();
		self::load_api_classes();
		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
	}

	/**
	 * Return the object of current class
	 *
	 * @return null|BWFAN_API_Loader
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}


	public static function load_api_supporter_classes(){
		$api_dir = __DIR__ . '/api';
		foreach ( glob( $api_dir . '/class-*.php' ) as $_field_filename ) {
			$file_data = pathinfo( $_field_filename );
			if ( isset( $file_data['basename'] ) && 'index.php' === $file_data['basename'] ) {
				continue;
			}
			require_once( $_field_filename );
		}
	}



	/**
	 * Include all the Api files
	 */
	public static function load_api_classes() {
		$api_dir = __DIR__ . '/api';
		foreach ( glob( $api_dir . '/**/class-*.php' ) as $_field_filename ) {
			$file_data = pathinfo( $_field_filename );
			if ( isset( $file_data['basename'] ) && 'index.php' === $file_data['basename'] ) {
				continue;
			}
			require_once( $_field_filename );
		}
		do_action( 'bwfan_api_classes_loaded' );
	}

	public static function register( $api_class ) {
		if ( ! class_exists( $api_class ) || ! method_exists( $api_class, 'get_instance' ) ) {
			return;
		}

		if ( empty( $api_class ) ) {
			return;
		}

		$api_slug = strtolower( $api_class );
		if ( false === strpos( $api_slug, 'bwfan_api_' ) ) {
			return;
		}

		$api_slug = explode( 'bwfan_api_', $api_slug )[1];
		/** @var BWFAN_API_Base $api_obj */
		$api_obj = $api_class::get_instance();

		if ( empty( $api_obj->route ) ) {
			return;
		}

		self::$registered_apis[ $api_obj->route ][ $api_slug ] = $api_obj;
	}

	public static function register_routes() {
		foreach ( self::$registered_apis as $route => $registered_api ) {
			if ( empty( $registered_api ) ) {
				continue;
			}

			$api_group = array_map( function ( $api ) {
				/** @var BWFAN_API_Base $api */
				if ( empty( $api->method ) ) {
					return false;
				}

				$route_args = array(
					'methods'             => $api->method,
					'callback'            => array( $api, 'api_call' ),
					'permission_callback' => [__CLASS__, 'rest_permission_callback']
				);

				if ( is_array( $api->request_args ) && ! empty( $api->request_args ) ) {
					$route_args['args'] = $api->request_args;
				}

				return $route_args;
			}, $registered_api );

			$api_group = array_filter( $api_group );
			$api_group = array_values( $api_group );

			register_rest_route( BWFAN_API_NAMESPACE, $route, $api_group );
		}

		do_action( 'bwfan_api_routes_registered', self::$registered_apis );
	}

	public static function rest_permission_callback() {
		return current_user_can( 'manage_options' );
	}

}

if ( class_exists( 'BWFAN_API_Loader' ) ) {
	BWFAN_Core::register( 'bwfan_api', 'BWFAN_API_Loader' );
}
