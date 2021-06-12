<?php

class BWFAN_API_Automation_Export_Single extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::READABLE;
		$this->route  = '/automations/(?P<automation_id>[\\d]+)/export/';
	}

	public function default_args_values() {
		$args = [
			'automation_id' => null,
		];

		return $args;
	}

	public function process_api_call() {
		$automation_id = $this->get_sanitized_arg( 'automation_id', 'key' );

		$get_export_automations_data = BWFAN_Core()->automations->get_json( $automation_id );

		$this->response_code = 200;

		return $this->success_response( $get_export_automations_data, __( 'Got Single Automation Json Data.', 'wp-marketing-automations' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Automation_Export_Single' );