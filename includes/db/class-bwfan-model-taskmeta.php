<?php

class BWFAN_Model_Taskmeta extends BWFAN_Model {
	static $primary_key = 'ID';

	public static function get_meta( $id, $key ) {
		$rows  = self::get_task_meta( $id );
		$value = '';
		if ( count( $rows ) > 0 && isset( $rows[ $key ] ) ) {
			$value = $rows[ $key ];
		}

		return $value;
	}

	public static function get_task_meta( $task_id ) {
		if ( empty( $task_id ) ) {
			return [];
		}

		global $wpdb;
		$table     = self::_table();
		$sql_query = "SELECT * FROM $table WHERE bwfan_task_id =%d";
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
			return array();
		}

		global $wpdb;

		$statuses = $wpdb->get_results(
			$wpdb->prepare(
				"
								SELECT t.status, COUNT(t.status) as count
								FROM {$wpdb->prefix}bwfan_tasks as t
								LEFT JOIN {$wpdb->prefix}bwfan_taskmeta as m
								ON t.ID = m.bwfan_task_id
								WHERE meta_key = %s
								AND meta_value = %d
								GROUP BY t.status
								", 'sync_id', $sync_id
			), ARRAY_A
		);

		$count = array(
			'pending' => 0,
			'paused'  => 0,
		);
		if ( ! empty( $statuses ) ) {
			foreach ( $statuses as $status ) {

				if ( 0 === intval( $status['status'] ) ) {
					$count['pending'] = intval( $status['count'] );
				} else {
					$count['paused'] = intval( $status['count'] );
				}
			}
		}

		return $count;
	}


}
