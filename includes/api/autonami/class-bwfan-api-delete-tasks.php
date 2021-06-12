<?php

class BWFAN_API_Delete_Tasks extends BWFAN_API_Base {
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
		$this->route  = '/automations/tasks/';
	}

	public function default_args_values() {
		$args = [
			'task_ids' => []
		];

		return $args;
	}

	public function process_api_call() {
		$task_ids = $this->args['task_ids'];

		if ( empty( $task_ids ) || ! is_array( $task_ids ) ) {
			return $this->error_response( __( 'Tasks ids is missing.', 'wp-marketing-automations' ), null, 404 );
		}

		BWFAN_Core()->tasks->delete_tasks( $task_ids, array() );
		BWFAN_Core()->logs->delete_logs( $task_ids, array() );

		return $this->success_response( [], __( 'Task deleted successfully.', 'wp-marketing-automations' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Delete_Tasks' );