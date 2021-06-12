<?php

class BWFAN_API_Save_Settings extends BWFAN_API_Base {
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
		$this->method = WP_REST_Server::EDITABLE;
		$this->route  = '/settings';
	}

	public function default_args_values() {

		$args = [
			'bwfan_global_settings' => array(),
		];

		return $args;
	}

	public function process_api_call() {
		$settings = $this->args['bwfan_global_settings'];

		if ( empty( $settings ) ) {
			return $this->error_response( [], __( 'Settings Data is missing.', 'wp-marketing-automations' ) );
		}

		update_option( 'bwfan_global_settings', $settings );

		$this->response_code = 200;

		$updated_settings = get_option( 'bwfan_global_settings' );
		$setting_schema   = BWFAN_Common::get_setting_schema();
		return $this->success_response( array(
			'schema' => $setting_schema,
			'values' => $updated_settings,
		), __( 'Settings updated', 'wp-marketing-automations' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Save_Settings' );