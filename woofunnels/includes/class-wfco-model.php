<?php

abstract class WFCO_Model {
	static $primary_key = 'id';
	static $count = 20;

	static function get( $value ) {
		global $wpdb;

		return $wpdb->get_row( self::_fetch_sql( $value ), ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL
	}

	private static function _fetch_sql( $value ) {
		global $wpdb;
		$sql = sprintf( 'SELECT * FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );

		return $wpdb->prepare( $sql, $value ); //phpcs:ignore WordPress.DB.PreparedSQL
	}

	private static function _table() {
		global $wpdb;
		$tablename = strtolower( get_called_class() );

		$tablename = str_replace( 'wfco_model_', 'wfco_', $tablename );

		return $wpdb->prefix . $tablename;
	}

	static function insert( $data ) {
		global $wpdb;
		$wpdb->insert( self::_table(), $data );
	}

	static function update( $data, $where ) {
		global $wpdb;
		$wpdb->update( self::_table(), $data, $where );
	}

	static function delete( $value ) {
		global $wpdb;
		$sql = sprintf( 'DELETE FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );

		return $wpdb->query( $wpdb->prepare( $sql, $value ) ); //phpcs:ignore WordPress.DB.PreparedSQL
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

			$sql .= ' INNER JOIN ' . $dependency['dependency_table'];
			$sql .= ' on ' . self::_table() . '.' . $dependency['dependent_col'];
			$sql .= ' =' . $dependency['dependency_table'] . '.' . $dependency['dependency_col'];
			$sql .= ' WHERE ' . $dependency['dependency_table'] . '.' . $dependency['col_name'];
			$sql .= ' =' . $dependency['col_value'];
			if ( isset( $dependency['connector_id'] ) ) {
				$sql .= ' AND ' . $dependency['connector_table'] . '.' . $dependency['connector_col'] . '=' . $dependency['connector_id'];
			}
		}

		return $wpdb->get_var( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL
	}

	static function get_specific_rows( $where_key, $where_value ) {
		global $wpdb;
		$table_name = self::_table();
		$results    = $wpdb->get_results( "SELECT * FROM $table_name WHERE $where_key = '$where_value'", ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		return $results;
	}

	static function get_results( $query ) {
		global $wpdb;
		$query   = str_replace( '{table_name}', self::_table(), $query );
		$results = $wpdb->get_results( $query, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		return $results;
	}

	static function delete_multiple( $query ) {
		global $wpdb;
		$query = str_replace( '{table_name}', self::_table(), $query );
		$wpdb->query( $query ); //phpcs:ignore WordPress.DB.PreparedSQL
	}

	static function update_multiple( $query ) {
		global $wpdb;
		$query = str_replace( '{table_name}', self::_table(), $query );
		$wpdb->query( $query ); //phpcs:ignore WordPress.DB.PreparedSQL
	}
}
