<?php

class BWFAN_API_Get_Autonami_Analytics extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $contact;

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::READABLE;
		$this->route  = '/dashboard/autonami-analytics';
	}

	public function default_args_values() {
		return array();
	}

	public function process_api_call() {

		$response['totals'] = $this->prepare_item_for_response();

		$this->response_code = 200;

		return $this->success_response( $response );
	}

	/**
	 *
	 * @return array
	 */
	public function prepare_item_for_response() {


		$get_total_contacts = BWFAN_Dashboards::get_total_contacts( '', '', '', '' );

		$get_total_sents = BWFAN_Dashboards::get_total_engagement_sents( '', '', '', '' );

		$get_total_orders = BWFAN_Dashboards::get_total_orders( '', '', '', '' );

		$result = [
			'total_contact' => ! isset( $get_total_contacts[0]['contact_counts'] ) ? 0 : $get_total_contacts[0]['contact_counts'],
			'email_sents'   => ! isset( $get_total_sents[0]['email_sents'] ) ? 0 : $get_total_sents[0]['email_sents'],
			'sms_sent'      => ! isset( $get_total_sents[0]['sms_sent'] ) ? 0 : $get_total_sents[0]['sms_sent'],
			'total_orders'  => ! isset( $get_total_orders[0]['total_orders'] ) ? 0 : $get_total_orders[0]['total_orders'],
			'total_revenue' => ! isset( $get_total_orders[0]['total_revenue'] ) ? 0 : $get_total_orders[0]['total_revenue'],
		];


		return $result;
	}

}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Autonami_Analytics' );
