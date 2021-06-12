<?php
/*if ( ! class_exists( 'WFCO_Model' ) ) {
	require_once __DIR__ . '/class-wfco-model.php';
}*/

class WFCO_Model_Report_views extends WFCO_Model {
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

	/**
	 * @param string $date Date(Y-m-d)
	 * @param string $object_id post_id or unique_id
	 * @param int $type 1=abandoned,2=upstroke,3=aero,4=bump
	 */

	public static function update_data( $date = '', $object_id = '', $type = 1 ) {
		global $wpdb;
		$where  = [];
		$insert = [];
		if ( $date !== '' ) {
			$where['date']  = "`date`='$date'";
			$insert['date'] = $date;
		} else {
			$date           = date( 'Y-m-d' );
			$where['date']  = "`date`='$date'";
			$insert['date'] = $date;
		}
		if ( $object_id !== '' ) {
			$where['object_id']  = "`object_id`='$object_id'";
			$insert['object_id'] = $object_id;
		}
		$where['type']  = "`type`='$type'";
		$insert['type'] = $type;

		$where_string = implode( ' and ', $where );
		$table        = self::_table();
		$get_sql      = "SELECT * FROM $table WHERE {$where_string};";
		$result       = $wpdb->get_results( $get_sql, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		if ( ! empty( $result ) ) {
			$primary_id = $result[0]['id'];
			$sql        = "UPDATE $table set no_of_sessions=no_of_sessions+1 where id ='{$primary_id}';";
			$wpdb->query( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL
		} else {
			$wpdb->insert( $table, $insert );
		}
	}

	public static function get_data( $date = '', $object_id = '', $type = 1, $interval = false ) {
		$where = [];

		if ( $date !== '' ) {
			if ( true === $interval ) {
				$where['date'] = $date;

			} else {
				$where['date'] = "`date`='$date'";
			}
		} else {
			$date          = date( 'Y-m-d' );
			$where['date'] = "`date`='$date'";
		}

		if ( $object_id !== '' ) {
			$where['object_id'] = "`object_id`='$object_id'";
		}

		$where['type'] = "`type`='$type'";
		$where_string  = implode( ' and ', $where );
		global $wpdb;
		$table = self::_table();
		$sql   = "select * from `{$table}` WHERE {$where_string};";
		$data  = $wpdb->get_results( $sql, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		return $data;
	}


}
