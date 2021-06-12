<?php

class BWFAN_API_Execute_Automation_Tasks extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::EDITABLE;
		$this->route  = '/automations/execute-tasks/';
	}

	public function default_args_values() {
		$args = [
			'task_ids' => '',
		];

		return $args;
	}

	public function process_api_call() {
		$task_ids = $this->args['task_ids'];

		if ( empty( $task_ids ) ) {
			return $this->error_response( __( 'Task Id is missing', 'wp-marketing-automations' ) );
		}

		BWFAN_Core()->tasks->rescheduled_tasks( true, $task_ids );
		BWFAN_Core()->logs->rescheduled_logs( $task_ids );

		$this->response_code = 200;

		return $this->success_response( [], __( 'Tasks Rescheduled successfully.', 'wp-marketing-automations' ) );
	}

}

BWFAN_API_Loader::register( 'BWFAN_API_Execute_Automation_Tasks' );