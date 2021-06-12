<?php

class BWFAN_API_Create_Automation extends BWFAN_API_Base {
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
		$this->route  = '/automations/create';
	}

	public function default_args_values() {
		$args = [
			'title' => '',
		];

		return $args;
	}

	public function process_api_call() {
		$title = $this->args['title'];

		if ( empty( $title ) ) {
			return $this->error_response( __( 'Title is missing', 'wp-marketing-automations' ) );
		}

		$automation_id = BWFAN_Core()->automations->create_automation($title);

		$this->response_code = 200;

		return $this->success_response( ['automation_id' => $automation_id], __( 'Automation created successfully', 'wp-marketing-automations' ) );
	}

}

BWFAN_API_Loader::register( 'BWFAN_API_Create_Automation' );