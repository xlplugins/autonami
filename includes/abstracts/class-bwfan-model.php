<?php

abstract class BWFAN_Model {
	static $primary_key = 'id';
	static $count = 20;

	static function set_id() {

	}

	static function get( $value ) {
		global $wpdb;

		return $wpdb->get_row( self::_fetch_sql( $value ), ARRAY_A ); // WPCS: unprepared SQL OK
	}

	private static function _fetch_sql( $value ) {
		global $wpdb;
		$sql = sprintf( 'SELECT * FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );

		return $wpdb->prepare( $sql, $value ); // WPCS: unprepared SQL OK
	}

	protected static function _table() {
		global $wpdb;
		$tablename = strtolower( get_called_class() );
		$tablename = str_replace( 'bwfan_model_', 'bwfan_', $tablename );

		return $wpdb->prefix . $tablename;
	}

	static function insert( $data ) {
		global $wpdb;
		$wpdb->insert( self::_table(), $data );
	}

	static function update( $data, $where ) {
		global $wpdb;

		return $wpdb->update( self::_table(), $data, $where );
	}

	static function delete( $value ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );

		return $wpdb->query( $wpdb->prepare( $sql, $value ) ); // WPCS: unprepared SQL OK
	}

	static function insert_id() {
		global $wpdb;

		return $wpdb->insert_id;
	}

	static function now() {
		return self::time_to_date( time() );
	}

	static function time_to_date( $time ) {
		return gmdate( 'Y-m-d H:i:s', $time );
	}

	static function date_to_time( $date ) {
		return strtotime( $date . ' GMT' );
	}

	static function num_rows() {
		global $wpdb;

		return $wpdb->num_rows;
	}

	static function count_rows( $dependency = null ) {
		global $wpdb;

		$sql = 'SELECT COUNT(*) FROM ' . self::_table();
		if ( ! is_null( $dependency ) ) {
			$sql = 'SELECT COUNT(*) FROM ' . self::_table() . ' INNER JOIN ' . $dependency['dependency_table'] . ' on ' . self::_table() . '.' . $dependency['dependent_col'] . '=' . $dependency['dependency_table'] . '.' . $dependency['dependency_col'] . ' WHERE ' . $dependency['dependency_table'] . '.' . $dependency['col_name'] . '=' . $dependency['col_value'];
			if ( isset( $dependency['automation_id'] ) ) {
				$sql = 'SELECT COUNT(*) FROM ' . self::_table() . ' INNER JOIN ' . $dependency['dependency_table'] . ' on ' . self::_table() . '.' . $dependency['dependent_col'] . '=' . $dependency['dependency_table'] . '.' . $dependency['dependency_col'] . ' WHERE ' . $dependency['dependency_table'] . '.' . $dependency['col_name'] . '=' . $dependency['col_value'] . ' AND ' . $dependency['automation_table'] . '.' . $dependency['automation_col'] . '=' . $dependency['automation_id'];
				if ( 'any' === $dependency['col_value'] ) {
					$sql = 'SELECT COUNT(*) FROM ' . self::_table() . ' INNER JOIN ' . $dependency['dependency_table'] . ' on ' . self::_table() . '.' . $dependency['dependent_col'] . '=' . $dependency['dependency_table'] . '.' . $dependency['dependency_col'] . ' WHERE ' . $dependency['automation_table'] . '.' . $dependency['automation_col'] . '=' . $dependency['automation_id'];
				}
			}
		}

		return $wpdb->get_var( $sql ); // WPCS: unprepared SQL OK
	}

	static function count( $data = array() ) {
		global $wpdb;

		$sql        = 'SELECT COUNT(*) as `count` FROM ' . self::_table() . ' WHERE 1=1';
		$sql_params = [];
		if ( is_array( $data ) && count( $data ) > 0 ) {
			foreach ( $data as $key => $val ) {
				$sql          .= " AND `{$key}` LIKE {$val['operator']}";
				$sql_params[] = $val['value'];
			}

			if ( ! empty( $sql_params ) ) {
				$sql = $wpdb->prepare( $sql, $sql_params ); // WPCS: unprepared SQL OK
			}
		}

		return $wpdb->get_var( $sql ); // WPCS: unprepared SQL OK
	}

	static function get_specific_rows( $where_key, $where_value, $offset = 0, $limit = 0 ) {
		$pagination = '';
		if ( ! empty( $offset ) && ! empty( $limit ) ) {
			$pagination = " LIMIT $offset, $limit";
		}

		global $wpdb;
		$table_name = self::_table();
		$results    = $wpdb->get_results( "SELECT * FROM $table_name WHERE $where_key = '$where_value'$pagination", ARRAY_A ); // WPCS: unprepared SQL OK

		return $results;
	}

	static function get_rows( $only_query = false, $automation_ids = array() ) {
		global $wpdb;

		$table_name     = self::_table();
		$page_number    = 1;
		$count_per_page = self::$count;
		$next_offset    = ( $page_number - 1 ) * $count_per_page;
		$sql_query      = $wpdb->prepare( "SELECT * FROM $table_name ORDER BY c_date DESC LIMIT %d OFFSET %d", $count_per_page, $next_offset ); // WPCS: unprepared SQL OK

		if ( isset( $_GET['paged'] ) && $_GET['paged'] > 1 ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			$page_number = sanitize_text_field( $_GET['paged'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
			$next_offset = ( $page_number - 1 ) * $count_per_page;
			$sql_query   = $wpdb->prepare( "SELECT * FROM $table_name ORDER BY c_date DESC LIMIT %d OFFSET %d", $count_per_page, $next_offset ); // WPCS: unprepared SQL OK
		}

		if ( isset( $_GET['status'] ) && 'all' !== $_GET['status'] ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			$status    = sanitize_text_field( $_GET['status'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
			$status    = ( 'active' === $status ) ? 1 : 2;
			$sql_query = $wpdb->prepare( "SELECT * FROM $table_name WHERE status = %d ORDER BY c_date DESC LIMIT %d OFFSET %d", $status, $count_per_page, $next_offset ); // WPCS: unprepared SQL OK
		}

		if ( ( isset( $_GET['paged'] ) && $_GET['paged'] > 0 ) && ( isset( $_GET['status'] ) && '' !== $_GET['status'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			$page_number = sanitize_text_field( $_GET['paged'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
			$next_offset = ( $page_number - 1 ) * $count_per_page;
			$status      = sanitize_text_field( $_GET['status'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
			$sql_query   = $wpdb->prepare( "SELECT * FROM $table_name WHERE status = %d ORDER BY c_date DESC LIMIT %d OFFSET %d", $status, $count_per_page, $next_offset ); // WPCS: unprepared SQL OK
		}

		$result = $wpdb->get_results( $sql_query, ARRAY_A ); // WPCS: unprepared SQL OK

		return $result;
	}

	static function get_results( $query ) {
		global $wpdb;
		$query   = str_replace( '{table_name}', self::_table(), $query );
		$results = $wpdb->get_results( $query, ARRAY_A ); // WPCS: unprepared SQL OK

		return $results;
	}

	static function delete_multiple( $query ) {
		self::query( $query );
	}

	static function query( $query ) {
		global $wpdb;
		$query = str_replace( '{table_name}', self::_table(), $query );
		$wpdb->query( $query ); // WPCS: unprepared SQL OK
	}

	static function update_multiple( $query ) {
		self::query( $query );
	}

	static function get_current_date_time() {
		return date( 'Y-m-d H:i:s' );
	}

	static function insert_multiple( $values, $keys, $formats = [] ) {
		if ( ( ! is_array( $keys ) || empty( $keys ) ) || ( ! is_array( $values ) || empty( $values ) ) ) {
			return false;
		}

		global $wpdb;

		$values = array_map( function ( $value ) use ( $keys, $formats ) {
			global $wpdb;
			$return = array();
			foreach ( $keys as $index => $key ) {
				$format   = is_array( $formats ) && isset( $formats[ $index ] ) ? $formats[ $index ] : false;
				$format   = ! empty( $format ) ? $format : ( is_numeric( $value[ $key ] ) ? '%d' : '%s' );
				$return[] = $wpdb->prepare( $format, $value[ $key ] );
			}

			return '(' . implode( ',', $return ) . ')';
		}, $values );
		$values = implode( ', ', $values );
		$keys   = '(' . implode( ', ', $keys ) . ')';
		$query  = 'INSERT INTO ' . self::_table() . ' ' . $keys . ' VALUES ' . $values;

		return $wpdb->query( $wpdb->prepare( "$query ", $values ) );
	}
}
