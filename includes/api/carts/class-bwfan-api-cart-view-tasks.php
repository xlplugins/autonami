<?php

class BWFAN_API_Carts_View_Tasks extends BWFAN_API_Base {
	public static $ins;
	public $task_localized = [];

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $total_count = 0;

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::READABLE;
		$this->route  = '/carts/(?P<abandoned_id>[\\d]+)/tasks/';
	}

	public function default_args_values() {
		$args = [
			'abandoned_id' => '',
		];

		return $args;
	}

	public function process_api_call() {
		$abandoned_id = $this->args['abandoned_id'];
		if ( empty( $abandoned_id ) ) {
			return $this->error_response( __( 'Abandoned id is missing.', 'wp-marketing-automations-crm' ) );
		}

		$get_cart_tasks = BWFAN_Recoverable_Carts::get_cart_tasks( $abandoned_id );
		if ( empty( $get_cart_tasks ) ) {
			return $this->success_response( $get_cart_tasks, __( 'No Tasks Details Available for abandoned id:.' . $abandoned_id, 'wp-marketing-automations-crm' ) );
		}

		return $this->success_response( $get_cart_tasks, __( 'Cart Tasks Details.', 'wp-marketing-automations-crm' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Carts_View_Tasks' );