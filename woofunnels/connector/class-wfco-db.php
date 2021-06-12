<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WFCO_Db
 * @package Autonami
 * @author XlPlugins
 */
class WFCO_Db {
	private static $ins = null;

	/**
	 * WFCO_Db constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'load_db_classes' ], 8 );
	}

	/**
	 * Return the object of current class
	 *
	 * @return null|WFCO_Db
	 */
	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Include all the DB Table files
	 */
	public static function load_db_classes() {
		$connector_dir = __DIR__ . '/db';
		foreach ( glob( $connector_dir . '/class-*.php' ) as $_field_filename ) {
			$file_data = pathinfo( $_field_filename );
			if ( isset( $file_data['basename'] ) && 'index.php' === $file_data['basename'] ) {
				continue;
			}
			require_once( $_field_filename );
		}
	}
}

if ( class_exists( 'WFCO_Db' ) ) {
	WFCO_Db::get_instance();
}
