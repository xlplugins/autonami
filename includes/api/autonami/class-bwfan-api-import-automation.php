<?php

class BWFAN_API_Import_Automations extends BWFAN_API_Base {
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
		$this->method = WP_REST_Server::CREATABLE;
		$this->route  = '/automations/import';
	}

	public function default_args_values() {
		$args = [
			'json' => null,
		];

		return $args;
	}

	public function process_api_call() {

		$files = $this->args['files'];

		if ( empty( $files ) ) {
			return $this->error_response( [], __( 'Import File missing.', 'wp-marketing-automations' ) );
		}

		$file_data = file_get_contents( $files['files']['tmp_name'] );

		$import_file_data = json_decode( $file_data, true );

		if ( empty( $import_file_data ) && ! is_array( $import_file_data ) ) {
			$this->response_code = 404;

			return $this->error_response( [], __( 'Import File data missing.', 'wp-marketing-automations' ) );
		}

		BWFAN_Core()->automations->import( $import_file_data );
		$this->response_code = 200;

		return $this->success_response( [], __( 'Automation Imported Successfully.', 'wp-marketing-automations' ) );
	}

}

BWFAN_API_Loader::register( 'BWFAN_API_Import_Automations' );