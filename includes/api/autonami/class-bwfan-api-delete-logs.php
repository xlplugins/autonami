<?php

class BWFAN_API_Delete_Logs extends BWFAN_API_Base {
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
		$this->method = WP_REST_Server::DELETABLE;
		$this->route  = '/automations/logs/';
	}

	public function default_args_values() {
		$args = [
			'log_ids' => []
		];

		return $args;
	}

	public function process_api_call() {
		$log_ids = $this->args['log_ids'];

		if ( empty( $log_ids ) || ! is_array( $log_ids ) ) {
			return $this->error_response( __( 'Logs ids is missing.', 'wp-marketing-automations' ) );
		}

		BWFAN_Core()->logs->delete_logs( $log_ids );

		return $this->success_response( [], __( 'Logs deleted successfully.', 'wp-marketing-automations' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Delete_Logs' );