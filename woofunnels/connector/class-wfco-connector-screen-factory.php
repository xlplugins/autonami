<?php

abstract class WFCO_Connector_Screen_Factory {

	private static $screens = [];


	public static function create( $slug, $data ) {

		$type                            = $data['type'];
		self::$screens[ $type ][ $slug ] = new WFCO_Connector_Screen( $slug, $data );
	}

	public static function get( $screen ) {
		return self::$screens[ $screen ];
	}

	public static function print_screens( $type = '' ) {
		$all_connector = self::getAll( $type );
		if ( empty( $all_connector ) ) {
			WFCO_Admin::get_available_connectors( $type );
			$all_connector = self::getAll( $type );
		}
		if ( ! is_array( $all_connector ) || 0 === count( $all_connector ) ) {
			return;
		}
		echo '<div class="wfco-col-group">';
		foreach ( $all_connector as $source_slug => $connector ) {
			$connector->print_card();
		}
		echo '</div>';
	}

	public static function getAll( $type = '' ) {
		if ( empty( $type ) ) {
			return self::$screens;
		}

		return isset( self::$screens[ $type ] ) ? self::$screens[ $type ] : [];
	}

}
