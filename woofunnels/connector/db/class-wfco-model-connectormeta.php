<?php

class WFCO_Model_ConnectorMeta extends WFCO_Model {
	static $primary_key = 'ID';

	public static function get_meta( $id, $key ) {
		$rows  = self::get_connector_meta( $id );
		$value = '';
		if ( count( $rows ) > 0 && isset( $rows[ $key ] ) ) {
			$value = $rows[ $key ];
		}

		return $value;
	}

	public static function get_connector_meta( $id ) {
		if ( 0 === $id ) {
			return [];
		}

		global $wpdb;
		$table = self::_table();

		$sql_query = "SELECT * FROM $table WHERE connector_id =%d";
		$sql_query = $wpdb->prepare( $sql_query, $id ); //phpcs:ignore WordPress.DB.PreparedSQL
		$result    = $wpdb->get_results( $sql_query, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL
		$meta      = [];

		if ( is_array( $result ) && count( $result ) > 0 ) {
			foreach ( $result as $meta_values ) {
				$key          = $meta_values['meta_key'];
				$meta[ $key ] = maybe_unserialize( $meta_values['meta_value'] );
			}
		}

		return $meta;
	}

	private static function _table() {
		global $wpdb;
		$table_name = strtolower( get_called_class() );
		$table_name = str_replace( 'wfco_model_', 'wfco_', $table_name );

		return $wpdb->prefix . $table_name;
	}

	public static function get_connectors_meta( $ids = [] ) {
		$meta = [];
		if ( empty( $ids ) || ! is_array( $ids ) ) {
			return $meta;
		}

		global $wpdb;
		$table = self::_table();
		$count = count( $ids );

		$placeholders = array_fill( 0, $count, '%d' );
		$placeholders = implode( ', ', $placeholders );

		/** Fetching connectors meta - single query */
		$query  = "Select * FROM $table WHERE connector_id IN ($placeholders)";
		$query  = $wpdb->prepare( $query, $ids ); // WPCS: unprepared SQL OK
		$result = self::get_results( $query );

		if ( is_array( $result ) && count( $result ) > 0 ) {
			foreach ( $result as $meta_values ) {
				$meta[ $meta_values['connector_id'] ][ $meta_values['meta_key'] ] = maybe_unserialize( $meta_values['meta_value'] );
			}
		}

		return $meta;
	}


}
