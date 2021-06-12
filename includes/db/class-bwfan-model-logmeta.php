<?php

class BWFAN_Model_Logmeta extends BWFAN_Model {
	static $primary_key = 'ID';

	public static function get_rows( $only_query = false, $automation_ids = array() ) {
		global $wpdb;

		$table_name = self::_table();

		if ( $only_query ) {
			// For Fetching the meta of automations
			$automationCount        = count( $automation_ids );
			$stringPlaceholders     = array_fill( 0, $automationCount, '%s' );
			$placeholdersautomation = implode( ', ', $stringPlaceholders );
			$sql_query              = "SELECT bwfan_log_id, meta_value, meta_key FROM $table_name WHERE bwfan_log_id IN ($placeholdersautomation)";
			$sql_query              = $wpdb->prepare( $sql_query, $automation_ids ); // WPCS: unprepared SQL OK
		}

		$result = $wpdb->get_results( $sql_query, ARRAY_A ); // WPCS: unprepared SQL OK

		return $result;
	}

	public static function get_meta( $id, $key ) {
		$rows  = self::get_log_meta( $id );
		$value = '';
		if ( count( $rows ) > 0 && isset( $rows[ $key ] ) ) {
			$value = $rows[ $key ];
		}

		return $value;
	}

	public static function get_log_meta( $task_id ) {
		if ( empty( $task_id ) ) {
			return [];
		}

		global $wpdb;
		$table     = self::_table();
		$sql_query = "SELECT * FROM $table WHERE bwfan_log_id =%d";
		$sql_query = $wpdb->prepare( $sql_query, $task_id ); // WPCS: unprepared SQL OK
		$result    = $wpdb->get_results( $sql_query, ARRAY_A ); // WPCS: unprepared SQL OK
		$meta      = [];

		if ( is_array( $result ) && count( $result ) > 0 ) {
			foreach ( $result as $meta_values ) {
				$key          = $meta_values['meta_key'];
				$meta[ $key ] = maybe_unserialize( $meta_values['meta_value'] );
			}
		}

		return $meta;
	}

	public static function get_sync_count( $sync_id ) {
		if ( empty( $sync_id ) ) {
			return 0;
		}

		global $wpdb;

		$statuses = $wpdb->get_results(
			$wpdb->prepare(
				"
								SELECT l.status, COUNT(l.status) as count
								FROM {$wpdb->prefix}bwfan_logs as l
								LEFT JOIN {$wpdb->prefix}bwfan_logmeta as m
								ON l.ID = m.bwfan_log_id
								WHERE meta_key = %s
								AND meta_value = %d
								GROUP BY l.status
								", 'sync_id', $sync_id
			), ARRAY_A
		);

		$count = array(
			'success' => 0,
			'failed'  => 0,
		);
		if ( ! empty( $statuses ) ) {
			foreach ( $statuses as $status ) {

				if ( 0 === intval( $status['status'] ) ) {
					$count['failed'] = intval( $status['count'] );
				} else {
					$count['success'] = intval( $status['count'] );
				}
			}
		}

		return $count;
	}

}
