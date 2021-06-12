<?php

class BWFAN_API_Duplicate_Automation extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::CREATABLE;
		$this->route  = '/automations/(?P<automation_id>[\\d]+)/duplicate/';
	}

	public function default_args_values() {
		$args = [
			'automation_id' => null,
		];

		return $args;
	}

	public function process_api_call() {
		$automation_id = $this->get_sanitized_arg( 'automation_id', 'key' );

		if ( empty( $automation_id ) ) {
			$this->response_code = 400;

			return $this->error_response( __( 'Automation id is missing', 'wp-marketing-automations' ) );
		}

		$id = BWFAN_Core()->automations->duplicate( $automation_id );

		if ( empty( $id ) ) {
			$this->response_code = 400;

			return $this->error_response( __( 'Unable to create Duplicate automation for id: ' . $automation_id, 'wp-marketing-automations' ) );
		}

		$automation_data = BWFAN_Model_Automations::get( $id );

		$this->response_code = 200;

		return $this->success_response( $automation_data, __( 'Duplicate automation created sucessfully', 'wp-marketing-automations' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Duplicate_Automation' );