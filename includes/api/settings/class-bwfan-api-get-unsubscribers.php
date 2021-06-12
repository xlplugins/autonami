<?php

class BWFAN_API_Get_Unsubscribers extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $total_count = 0;

	public function __construct() {
		parent::__construct();
		$this->method             = WP_REST_Server::READABLE;
		$this->route              = '/settings/unsubscribers';
		$this->pagination->offset = 0;
		$this->pagination->limit  = 25;
		$this->request_args       = array(
			'search' => array(
				'description' => __( 'Unsubscribers search', 'wp-marketing-automations-crm' ),
				'type'        => 'string',
			),
			'offset' => array(
				'description' => __( 'Unsubscribers list Offset', 'wp-marketing-automations-crm' ),
				'type'        => 'integer',
			),
			'limit'  => array(
				'description' => __( 'Per page limit', 'wp-marketing-automations-crm' ),
				'type'        => 'integer',
			)
		);
	}

	public function default_args_values() {
		$args = [
			'search' => '',
			'offset' => 0,
			'limit'  => 25
		];

		return $args;
	}

	public function process_api_call() {
		$search = $this->get_sanitized_arg( 'search', 'text_field' );
		$offset = ! empty( $this->get_sanitized_arg( 'offset', 'text_field' ) ) ? $this->get_sanitized_arg( 'offset', 'text_field' ) : 0;
		$limit  = ! empty( $this->get_sanitized_arg( 'limit', 'text_field' ) ) ? $this->get_sanitized_arg( 'limit', 'text_field' ) : 25;

		$get_unsubscribers = BWFAN_Common::get_unsubscribers( $search, $offset, $limit );
		if ( isset( $get_unsubscribers['found_posts'] ) ) {
			$this->total_count = $get_unsubscribers['found_posts'];
			unset( $get_unsubscribers['found_posts'] );
		}
		$this->response_code = 200;

		return $this->success_response( $get_unsubscribers, __( 'Got all unsubscribers list.', 'wp-marketing-automations' ) );
	}

	public function get_result_total_count() {
		return $this->total_count;
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Unsubscribers' );