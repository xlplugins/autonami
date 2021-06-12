<?php

class BWFAN_API_Get_Recent_Contacts_Unsubscribe extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $contact;
	public $total_count;

	public function __construct() {
		parent::__construct();
		$this->method             = WP_REST_Server::READABLE;
		$this->route              = '/dashboard/recent-contacts-unsubscribe';
		$this->pagination->limit  = 5;
		$this->pagination->offset = 0;
	}

	/**
	 * API Call
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function process_api_call() {
		global $wpdb;

		$contact_table = $wpdb->prefix.'bwf_contact';
		$unsubscribe_table = $wpdb->prefix.'bwfan_message_unsubscribe';

		$query = "SELECT sub.recipient as email, COALESCE(con.id, 0) as id, COALESCE(con.f_name, '') as f_name, COALESCE(con.l_name, '') as l_name, sub.c_date from $unsubscribe_table as sub LEFT JOIN $contact_table as con ON sub.recipient = con.email ORDER BY sub.ID DESC LIMIT 5 OFFSET 0";

		$unsubscribes = $wpdb->get_results($query);

		$contacts['contacts'] = $unsubscribes;

		if ( ! is_array( $contacts ) ) {
			$this->response_code = 500;

			return $this->error_response( is_string( $contacts ) ? $contacts : __( 'Unknown Error', 'wp-marketing-automations-crm' ) );
		}

		if ( ! isset( $contacts['contacts'] ) || empty( $contacts['contacts'] ) ) {
			return $this->success_response( [] );
		}

		$this->response_code = 200;
		$this->total_count   = count( $contacts['contacts'] );

		return $this->success_response( $contacts['contacts'] );
	}

	/**
	 * Get total counts
	 *
	 * @return int
	 */
	public function get_result_total_count() {
		return $this->total_count;
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Recent_Contacts_Unsubscribe' );