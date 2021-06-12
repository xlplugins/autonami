<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * This file is to initiate WooFunnel core and to run some common methods and decide which WooFunnels core should run
 */
if ( ! class_exists( 'WooFunnel_Loader' ) ) {
	class WooFunnel_Loader {

		public static $plugins = array();
		public static $loaded = false;
		public static $ultimate_path = '';
		public static $version = null;

		public static function include_core() {
			$get_configuration = self::get_the_latest();
			if ( false === self::$loaded && $get_configuration && is_array( $get_configuration ) && isset( $get_configuration['class'] ) ) {
				if ( is_callable( array( $get_configuration['class'], 'load_files' ) ) ) {
					self::$version       = $get_configuration['version'];
					self::$ultimate_path = $get_configuration['plugin_path'] . '/woofunnels/';
					self::$loaded        = true;
					call_user_func( array( $get_configuration['class'], 'load_files' ) );
				}
			}
		}

		public static function register( $configuration ) {
			array_push( self::$plugins, $configuration );
		}

		public static function get_the_latest() {
			$get_all = self::$plugins;
			uasort( $get_all, function ( $a, $b ) {
				if ( version_compare( $a['version'], $b['version'], '=' ) ) {
					return 0;
				} else {
					return ( version_compare( $a['version'], $b['version'], '<' ) ) ? - 1 : 1;
				}
			} );

			$get_most_recent_configuration = end( $get_all );

			return $get_most_recent_configuration;
		}

	}
}


class WooFunnel_BWFAN {
	public static $version = BWFAN_BWF_VERSION;

	public static function register() {
		$configuration = array(
			'basename'    => plugin_basename( BWFAN_PLUGIN_FILE ),
			'version'     => self::$version,
			'plugin_path' => dirname( BWFAN_PLUGIN_FILE ),
			'class'       => __CLASS__,
		);
		WooFunnel_Loader::register( $configuration );
	}

	public static function load_files() {
		$get_global_path = dirname( __FILE__ ) . '/woofunnels/';
		if ( false === @file_exists( $get_global_path . 'includes/class-woofunnels-api.php' ) ) { //phpcs:ignore PHP_CodeSniffer - Generic.PHP.NoSilencedErrors, Generic.PHP.NoSilencedErrors
			_doing_it_wrong( __FUNCTION__, esc_html__( 'WooFunnels Core should be present in folder \'woofunnels\' in order to run this properly. ' ), self::$version ); //phpcs:ignore WordPress.Security.EscapeOutput
			die( 0 );
		}

		/**
		 * Loading Core XL Files
		 */
		require_once dirname( BWFAN_PLUGIN_FILE ) . '/woofunnels/includes/class-woofunnels-dashboard-loader.php';

		if ( BWF_VERSION === self::$version ) {
			do_action( 'woofunnels_loaded', $get_global_path );
		} elseif ( ( defined( 'BWFAN_IS_DEV' ) && true === BWFAN_IS_DEV ) || ( defined( 'BWF_DEV' ) && true === BWF_DEV ) ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'WooFunnels Core should be at the same version as declared in your start.php' ), self::$version ); //phpcs:ignore WordPress.Security.EscapeOutput
			die( 0 );
		}
	}

}

WooFunnel_BWFAN::register();
