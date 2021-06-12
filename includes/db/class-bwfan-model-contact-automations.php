<?php

class BWFAN_Model_Contact_Automations extends BWFAN_Model {
	static $primary_key = '';

	/**
	 * Get last automation run time for a contact
	 *
	 * @param $contact_id
	 *
	 * @return mixed|string|void
	 */
	public static function get_contact_automation_last_run( $contact_id, $automation_id ) {
		global $wpdb;
		$sql_query = 'SELECT time FROM {table_name} WHERE contact_id = %d AND automation_id = %d ORDER BY time DESC LIMIT 1';
		$sql_query = $wpdb->prepare( $sql_query, $contact_id, $automation_id ); // WPCS: unprepared SQL OK
		$last_run  = self::get_results( $sql_query );

		if ( ! is_array( $last_run ) || 0 === count( $last_run ) ) {
			return __( 'N/A', 'wp-marketing-automations' );
		}

		return $last_run[0]['time'];
	}

	/**
	 * Get count for an automation run on a contact
	 *
	 * @param $contact_id
	 *
	 * @return int
	 */
	public static function get_contact_automations_run_count( $contact_id, $automation_id ) {
		global $wpdb;
		$sql_query = 'SELECT count(time) as count FROM {table_name} WHERE contact_id = %d AND automation_id = %d';
		$sql_query = $wpdb->prepare( $sql_query, $contact_id, $automation_id ); // WPCS: unprepared SQL OK
		$run_count = self::get_results( $sql_query );

		if ( ! is_array( $run_count ) || 0 === count( $run_count ) ) {
			return 0;
		}

		return $run_count[0]['count'];
	}

	/**
	 * Get all automations for a contact
	 *
	 * @param $contact_id
	 *
	 * @return array|int|object|null
	 */
	public static function get_all_automations_for_contact( $contact_id ) {
		global $wpdb;
		$sql_query   = 'SELECT automation_id FROM {table_name} WHERE contact_id = %d ';
		$sql_query   = $wpdb->prepare( $sql_query, $contact_id ); // WPCS: unprepared SQL OK
		$automations = self::get_results( $sql_query );

		if ( ! is_array( $automations ) || 0 === count( $automations ) ) {
			return 0;
		}

		return $automations;
	}

	/**
	 * Get all contact for an automation
	 *
	 * @param $automation_id
	 *
	 * @return array|int|object|null
	 */
	public static function get_all_contacts_for_automation( $automation_id ) {
		global $wpdb;
		$sql_query = 'SELECT contact_id FROM {table_name} WHERE automation_id = %d ';
		$sql_query = $wpdb->prepare( $sql_query, $automation_id ); // WPCS: unprepared SQL OK
		$contacts  = self::get_results( $sql_query );

		if ( ! is_array( $contacts ) || 0 === count( $contacts ) ) {
			return 0;
		}

		return $contacts;
	}

}
