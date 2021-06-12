<?php

/**
 * Basic class that do operations and get data from wp core
 * @since 1.0.0
 * @package WooFunnels
 * @author woofunnels
 */
class WooFunnels_Addons {

	public static $installed_addons = array();
	public static $update_available = array();

	public static function init() {

		add_filter( 'extra_plugin_headers', array( __CLASS__, 'extra_woocommerce_headers' ) );
	}

	/**
	 * Adding WooFunnels Header to tell WordPress to read one extra params while reading plugin's header info. <br/>
	 * Hooked over `extra_plugin_headers`
	 *
	 * @param array $headers already registered arrays
	 *
	 * @return type
	 * @since 1.0.0
	 *
	 */
	public static function extra_woocommerce_headers( $headers ) {
		array_push( $headers, 'WooFunnels' );
		array_push( $headers, 'WooFunnels' );

		return $headers;
	}

	/**
	 * Getting all installed plugin that has woofunnels header within
	 * @return array Addons
	 */
	public static function get_installed_plugins() {
		if ( ! empty( self::$installed_addon ) ) {
			return self::$installed_addon;
		}
		wp_cache_delete( 'plugins', 'plugins' );
		$plugins     = self::get_plugins( true );
		$plug_addons = array();
		foreach ( $plugins as $plugin_file => $plugin_data ) {

			if ( isset( $plugin_data['WooFunnels'] ) && $plugin_data['WooFunnels'] ) {
				$plug_addons[ $plugin_file ] = $plugin_data;
			}
		}
		self::$installed_addons = $plug_addons;

		return $plug_addons;
	}

	/**
	 * Play it safe and require WP's plugin.php before calling the get_plugins() function.
	 *
	 * @return array An array of installed plugins.
	 */
	public static function get_plugins( $clear_cache = false ) {
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		$plugins = get_plugins();

		if ( $clear_cache || ! self::plugins_have_woofunnels_plugin_header( $plugins ) ) {
			$plugins = get_plugins();
		}

		return $plugins;
	}

	/**
	 * Checking Plugin header and Trying to find out the one with the header `WooFunnels`
	 *
	 * @param Array $plugins array of available plugins
	 *
	 * @return mixed
	 */
	public static function plugins_have_woofunnels_plugin_header( $plugins ) {
		$plugin = reset( $plugins );

		return $plugin && isset( $plugin['WooFunnels'] );
	}


}

WooFunnels_Addons::init();
