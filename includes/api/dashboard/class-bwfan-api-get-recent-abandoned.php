<?php

class BWFAN_API_Get_Recent_Abandoned extends BWFAN_API_Base {
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
		$this->route              = '/dashboard/recent-contacts-abandoned';
		$this->pagination->limit  = 10;
		$this->pagination->offset = 0;
	}

	public function default_args_values() {
	}

	public function process_api_call() {
		global $wpdb;
		$abandoned_table = $wpdb->prefix.'bwfan_abandonedcarts';
		$contact_table   = $wpdb->prefix.'bwf_contact';

		$query = "SELECT abandon.email,abandon.checkout_data, abandon.total as revenue, COALESCE(con.id, 0) as id, COALESCE(con.f_name, '') as f_name, COALESCE(con.l_name, '') as l_name from $abandoned_table as abandon LEFT JOIN $contact_table as con ON abandon.email = con.email ORDER BY abandon.ID DESC LIMIT 5 OFFSET 0";

		$abandoned = $wpdb->get_results($query);

		if ( ! is_array( $abandoned ) ) {
			$this->response_code = 500;

			return $this->error_response( is_string( $abandoned ) ? $abandoned : __( 'Unknown Error', 'wp-marketing-automations-crm' ) );
		}

		$this->response_code = 200;
		$this->total_count   = count( $abandoned );

		return $this->success_response( $abandoned );
	}

	public function get_result_total_count() {
		return $this->total_count;
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Recent_Abandoned' );