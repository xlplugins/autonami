<?php

class BWFAN_API_Get_Task_Filters extends BWFAN_API_Base {
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
		$this->route  = '/automations/task-filters';
	}

	public function default_args_values() {
		$args = [];

		return $args;
	}

	public function process_api_call() {

		$all_actions           = BWFAN_Common::get_actions_filter_data();
		$all_automations       = BWFAN_Common::get_automations_filter_data();
		$result['automations'] = $all_automations;
		$result['actions']     = $all_actions;

		return $this->success_response( $result, __( 'Got Task Filters data.', 'wp-marketing-automations' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Task_Filters' );
