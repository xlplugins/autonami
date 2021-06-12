<?php

class BWFAN_API_Get_Task_History extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $total_count = 0;
	public $count_data = 0;

	public function __construct() {
		parent::__construct();
		$this->method             = WP_REST_Server::READABLE;
		$this->route              = '/automations/task-history';
		$this->pagination->offset = 0;
		$this->pagination->limit  = 25;
		$this->request_args       = array(
			'status'        => array(
				'description' => __( 'Task Status', 'wp-marketing-automations-crm' ),
				'type'        => 'string',
			),
			'search'        => array(
				'description' => __( 'Search', 'wp-marketing-automations-crm' ),
				'type'        => 'string',
			),
			'automation_id' => array(
				'description' => __( 'Autonami ID', 'wp-marketing-automations-crm' ),
				'type'        => 'integer',
			),
			'action_slug'   => array(
				'description' => __( 'Action Slug', 'wp-marketing-automations-crm' ),
				'type'        => 'string',
			),
			'offset'        => array(
				'description' => __( 'Task list Offset', 'wp-marketing-automations-crm' ),
				'type'        => 'integer',
			),
			'limit'         => array(
				'description' => __( 'Per page limit', 'wp-marketing-automations-crm' ),
				'type'        => 'integer',
			)
		);
	}

	public function default_args_values() {
		$args = [
			'status'        => 't_0',
			'offset'        => 0,
			'automation_id' => null,
			'action_slug'   => null,
			'search'        => '',
		];

		return $args;
	}

	public function process_api_call() {
		$status        = $this->get_sanitized_arg( 'status', 'text_field' );
		$automation_id = $this->get_sanitized_arg( 'automation_id', 'text_field' );
		$action_slug   = $this->get_sanitized_arg( 'action_slug', 'text_field' );
		$search        = $this->get_sanitized_arg( 'search', 'text_field' );
		$offset        = ! empty( $this->get_sanitized_arg( 'offset', 'text_field' ) ) ? $this->get_sanitized_arg( 'offset', 'text_field' ) : 0;
		$limit         = ! empty( $this->get_sanitized_arg( 'limit', 'text_field' ) ) ? $this->get_sanitized_arg( 'limit', 'text_field' ) : 25;

		if ( $status === 't_0' || $status === 't_1' ) {
			$get_task_history = BWFAN_Core()->tasks->get_history( $status, $automation_id, $action_slug, $search, $offset, $limit );
		} else {
			$get_task_history = BWFAN_Core()->logs->get_history( $status, $automation_id, $action_slug, $search, $offset, $limit );
		}

		$get_task_history['scheduled_count'] = BWFAN_Core()->tasks->fetch_tasks_count( 0, 0 );
		$get_task_history['paused_count']    = BWFAN_Core()->tasks->fetch_tasks_count( 0, 1 );
		$get_task_history['completed_count'] = BWFAN_Core()->logs->fetch_logs_count( 1 );
		$get_task_history['failed_count']    = BWFAN_Core()->logs->fetch_logs_count( 0 );

		$this->count_data = BWFAN_Common::get_automation_data_count();

		if ( empty( $get_task_history ) ) {
			$this->response_code = 200;

			return $this->success_response( $get_task_history, __( 'No record found', 'wp-marketing-automations' ) );
		}


		if ( isset( $get_task_history['found_posts'] ) ) {
			$this->total_count = $get_task_history['found_posts'];
			unset( $get_task_history['found_posts'] );
		}

		$this->response_code = 200;

		return $this->success_response( $get_task_history, __( 'Got all task history.', 'wp-marketing-automations' ) );
	}

	public function get_result_total_count() {
		return $this->total_count;
	}

	public function get_result_count_data() {
		return $this->count_data;
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Task_History' );