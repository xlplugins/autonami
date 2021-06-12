<?php

class WFCO_Model_Connectors extends WFCO_Model {
	static $primary_key = 'ID';

	public static function count_rows( $dependency = null ) {
		global $wpdb;
		$table_name = self::_table();
		$sql        = 'SELECT COUNT(*) FROM ' . $table_name;

        if (  'all' !== filter_input(INPUT_GET,'status',FILTER_SANITIZE_STRING) ) {
            $status = filter_input(INPUT_GET,'status',FILTER_SANITIZE_STRING);
			$status = ( 'active' === $status ) ? 1 : 2;
			$sql    = $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE status = %d", $status ); //phpcs:ignore WordPress.DB.PreparedSQL
		}

		return $wpdb->get_var( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL
	}

	private static function _table() {
		global $wpdb;
		$table_name = strtolower( get_called_class() );
		$table_name = str_replace( 'wfco_model_', 'wfco_', $table_name );

		return $wpdb->prefix . $table_name;
	}
}
