<?php

class BWF_AS_Actions_Crud {
	public static $primary_key = 'id';

	/**
	 * Return single action data
	 *
	 * @param $action_id
	 * @param string $return_vars
	 *
	 * @return stdClass
	 */
	public static function get_single_action( $action_id, $return_vars = '*' ) {
		global $wpdb;
		$table = self::_table();
		$p_key = self::$primary_key;
		$sql   = "SELECT {$return_vars} FROM {$table} WHERE {$p_key}=%d";
		$sql   = $wpdb->prepare( $sql, $action_id ); //phpcs:ignore WordPress.DB.PreparedSQL

		$status = $wpdb->get_results( $sql, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		$return = new stdClass();
		if ( is_array( $status ) && count( $status ) > 0 && isset( $status[0] ) && is_array( $status[0] ) && count( $status[0] ) > 0 ) {
			foreach ( $status[0] as $key => $value ) {
				$value = maybe_unserialize( $value );
				if ( true === self::is_json( $value ) ) {
					$value = json_decode( $value, ARRAY_A );
				}
				$return->$key = $value;
			}
		}

		return $return;
	}

	public static function _table() {
		global $wpdb;
		$table_name = strtolower( 'BWF_Actions' );

		return $wpdb->prefix . $table_name;
	}

	public static function is_json( $string ) {
		json_decode( $string );

		return ( json_last_error() == JSON_ERROR_NONE );
	}

	public static function get_executable_actions( $status, $group ) {

	}

	/**
	 * Find actions based on column inputs
	 *
	 * @param $args
	 *
	 * @return array|bool
	 */
	public static function find_actions( $args ) {
		global $wpdb;

		$table = self::_table();
		$p_key = self::$primary_key;

		$sql        = "SELECT `{$p_key}` FROM $table WHERE 1=1";
		$sql_params = [];

		if ( isset( $args['hook'] ) && ! empty( trim( $args['hook'] ) ) ) {
			$sql          .= ' AND `hook` LIKE %s';
			$sql_params[] = trim( $args['hook'] );
		}

		if ( isset( $args['args'] ) && is_array( $args['args'] ) && count( $args['args'] ) > 0 ) {
			$sql          .= ' AND `args` LIKE %s';
			$sql_params[] = wp_json_encode( $args['args'] );
		}

		if ( isset( $args['group_slug'] ) && ! empty( trim( $args['group_slug'] ) ) ) {
			$sql          .= ' AND `group_slug` LIKE %s';
			$sql_params[] = trim( $args['group_slug'] );
		}

		if ( isset( $args['status'] ) && '' !== $args['status'] ) {
			$sql          .= ' AND `status` LIKE %d';
			$sql_params[] = $args['status'];
		}

		/** Always != hard */
		if ( isset( $args['recurring_interval'] ) && '' !== $args['recurring_interval'] ) {
			$sql          .= ' AND `recurring_interval` != %d';
			$sql_params[] = $args['recurring_interval'];
		}

		$sql = $wpdb->prepare( $sql, $sql_params ); //phpcs:ignore WordPress.DB.PreparedSQL

		$action_ids = $wpdb->get_results( $sql, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL
		if ( is_array( $action_ids ) && count( $action_ids ) > 0 ) {
			$action_ids = array_column( $action_ids, 'id' );

			return $action_ids;
		}

		return false;
	}

	public static function get_action_status( $action_id ) {
		global $wpdb;
		$table  = self::_table();
		$p_key  = self::$primary_key;
		$sql    = "SELECT `status` FROM {$table} WHERE {$p_key}=%d";
		$sql    = $wpdb->prepare( $sql, $action_id ); //phpcs:ignore WordPress.DB.PreparedSQL
		$status = $wpdb->get_var( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL

		return $status;
	}

	public static function insert( $data ) {
		global $wpdb;
		$wpdb->insert( self::_table(), $data );
	}

	public static function delete( $value ) {
		global $wpdb;
		if ( empty( $value ) ) {
			return;
		}

		$resp = $wpdb->delete( self::_table(), array( static::$primary_key => $value ), array( '%d' ) );

		return $resp;
	}

	public static function delete_actions( $action_ids ) {
		global $wpdb;
		$table = self::_table();
		$p_key = self::$primary_key;

		if ( ! is_array( $action_ids ) || 0 === count( $action_ids ) ) {
			BWFAN_Core()->logger->log( 'no action ids to delete, blank array passed', 'sync' );

			return false;
		}

		$type   = array_fill( 0, count( $action_ids ), '%d' );
		$format = implode( ', ', $type );
		$query  = "DELETE FROM {$table} WHERE {$p_key} IN ({$format})";
		$sql    = $wpdb->prepare( $query, $action_ids ); //phpcs:ignore WordPress.DB.PreparedSQL

		$rows_affected = $wpdb->query( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL

		return $rows_affected;
	}

	public static function query( $query ) {
		global $wpdb;
		$query   = str_replace( '{table_name}', self::_table(), $query );
		$results = $wpdb->query( $query ); //phpcs:ignore WordPress.DB.PreparedSQL

		return $results;
	}

	public static function insert_id() {
		global $wpdb;

		return $wpdb->insert_id;
	}

	public static function get_results( $query ) {
		global $wpdb;
		$query   = str_replace( '{table_name}', self::_table(), $query );
		$results = $wpdb->get_results( $query, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL

		return $results;
	}

}
