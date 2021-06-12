<?php

class BWFAN_Model_Logs extends BWFAN_Model {
	static $primary_key = 'ID';

	public static function get_logs( $all, $automation_ids, $status ) {
		global $wpdb;
		$all = absint( $all );

		if ( is_array( $automation_ids ) && count( $automation_ids ) > 0 && 1 === $all ) {
			$automationCount        = count( $automation_ids );
			$stringPlaceholders     = array_fill( 0, $automationCount, '%s' );
			$placeholdersautomation = implode( ', ', $stringPlaceholders );
			$sql_query              = "Select * FROM {table_name} WHERE automation_id IN ($placeholdersautomation)";
			$sql_query              = $wpdb->prepare( $sql_query, $automation_ids ); // WPCS: unprepared SQL OK
		} elseif ( is_array( $automation_ids ) && count( $automation_ids ) > 0 && 1 === $all && '' !== $status ) {
			$automationCount        = count( $automation_ids );
			$stringPlaceholders     = array_fill( 0, $automationCount, '%s' );
			$placeholdersautomation = implode( ', ', $stringPlaceholders );
			$sql_query              = "Select * FROM {table_name} WHERE automation_id IN ($placeholdersautomation) AND status = %d ";
			$automation_ids         = implode( ',', $automation_ids );
			$sql_query              = $wpdb->prepare( $sql_query, $automation_ids, $status ); // WPCS: unprepared SQL OK
		}

		$active_logs = self::get_results( $sql_query );
		$final_data  = [];

		if ( is_array( $active_logs ) && count( $active_logs ) > 0 ) {
			foreach ( $active_logs as $log ) {
				$log_id                = $log['id'];
				$log['meta']           = BWFAN_Model_Logmeta::get_log_meta( $log_id );
				$final_data[ $log_id ] = $log;
			}
		}

		return $active_logs;
	}

}
