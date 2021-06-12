<?php

class BWFAN_Model_Abandonedcarts extends BWFAN_Model {
	public static $primary_key = 'ID';

	public static function get_abandoned_data( $where = '', $offset = '', $per_page = '', $order_by = 'ID', $output = OBJECT ) {
		global $wpdb;

		$limit_string = '';
		if ( '' !== $offset ) {
			$limit_string = "LIMIT {$offset}";
		}
		if ( '' !== $per_page && '' !== $limit_string ) {

			$limit_string .= ',' . $per_page;
		}
		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bwfan_abandonedcarts {$where} ORDER BY {$order_by} DESC {$limit_string}", $output ); // WPCS: unprepared SQL OK

		return $results;
	}

	public static function delete_abandoned_cart_row( $data ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return;
		}

		global $wpdb;
		$where      = '';
		$count      = count( $data );
		$i          = 0;
		$table_name = $wpdb->prefix . 'bwfan_abandonedcarts';

		foreach ( $data as $key => $value ) {
			$i ++;

			if ( 'string' === gettype( $value ) ) {
				$where .= '`' . $key . '` = ' . "'" . $value . "'";
			} else {
				$where .= '`' . $key . '` = ' . $value;
			}

			if ( $i < $count ) {
				$where .= ' AND ';
			}
		}

		return $wpdb->query( 'DELETE FROM ' . $table_name . " WHERE $where" ); // WPCS: unprepared SQL OK
	}


}
