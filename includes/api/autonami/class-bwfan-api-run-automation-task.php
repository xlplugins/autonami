<?php

class BWFAN_API_Run_Automation_Task extends BWFAN_API_Base {
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
		$this->route  = '/automations/task/';
	}

	public function default_args_values() {
		$args = [
			'task_id' => '',
		];

		return $args;
	}

	public function process_api_call() {
		$task_id = $this->args['task_id'];

		if ( empty( $task_id ) ) {
			return $this->error_response( __( 'Task Id is missing', 'wp-marketing-automations' ) );
		}

		$resp = array(
			'msg'    => __( 'Task Executed Successfully', 'wp-marketing-automations' ),
			'status' => true,
		);

		try {
			BWFAN_Core()->tasks->bwfan_ac_execute_task( $task_id );

		} catch ( Exception $exception ) {

			$resp['status'] = false;
			$resp['msg']    = $exception->getMessage();

			return $this->error_response( 'Unable to Execute tasks', $resp );
		}

		if ( BWFAN_Core()->tasks->ajax_status ) {
			return $this->success_response( $resp );
		}

		$resp = array(
			'msg'    => BWFAN_Core()->tasks->ajax_msg,
			'status' => BWFAN_Core()->tasks->ajax_status,
		);

		$this->response_code = 200;

		return $this->success_response( $resp, __( 'Tasks Executed successfully.', 'wp-marketing-automations' ) );
	}

}

BWFAN_API_Loader::register( 'BWFAN_API_Run_Automation_Task' );