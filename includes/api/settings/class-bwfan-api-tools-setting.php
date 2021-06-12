<?php

class BWFAN_API_Tools_Setting extends BWFAN_API_Base {
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
		$this->route  = '/settings/tools';
	}

	public function default_args_values() {
		$args = [
			'action_type' => '',
		];

		return $args;
	}

	public function process_api_call() {
		$action_type = $this->args['action_type'];

		if ( empty( $action_type ) ) {
			return $this->error_response( __( 'Action type is missing', 'wp-marketing-automations' ) );
		}

		$result = BWFAN_Common::run_global_tools( $action_type );
		if ( isset( $result['status'] ) && false === $result['status'] ) {
			return $this->success_response( $result, __( 'Unable to execute action.', 'wp-marketing-automations' ) );
		}

		$this->response_code = 200;

		return $this->success_response( $result, __( 'Action executed successfully.', 'wp-marketing-automations' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Tools_Setting' );