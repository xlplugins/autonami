<?php

class BWFAN_Model_Automations extends BWFAN_Model {
	static $primary_key = 'ID';

	public static function count_rows( $dependency = null ) {
		global $wpdb;
		$table_name = self::_table();
		$sql        = 'SELECT COUNT(*) FROM ' . $table_name;

		if ( isset( $_GET['status'] ) && 'all' !== sanitize_text_field( $_GET['status'] ) ) { // WordPress.CSRF.NonceVerification.NoNonceVerification
			$status = sanitize_text_field( $_GET['status'] ); // WordPress.CSRF.NonceVerification.NoNonceVerification
			$status = ( 'active' === $status ) ? 1 : 2;
			$sql    = $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE status = %d", $status ); // WPCS: unprepared SQL OK
		}

		return $wpdb->get_var( $sql ); // WPCS: unprepared SQL OK
	}

	/**
	 * Return Automation detail with its meta details
	 *
	 * @param $automation_id
	 *
	 * @return array|object|void|null
	 */
	public static function get_automation_with_data( $automation_id ) {
		$data = self::get( $automation_id );
		if ( ! is_array( $data ) || empty( $data ) ) {
			return [];
		}

		$data['meta'] = BWFAN_Model_Automationmeta::get_automation_meta( $automation_id );

		return $data;
	}

	/**
	 * Get first automation id
	 *
	 * @param $id
	 */
	public static function get_first_automation_id() {
		global $wpdb;
		$query = "SELECT id from ".self::_table();
		return $wpdb->get_var($query);
	}
}
