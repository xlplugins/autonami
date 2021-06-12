<?php

class BWFAN_API_Table_Status extends BWFAN_API_Base {
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
		$this->route  = '/settings/status/tables';
	}

	public function process_api_call() {
		$automations_table_result = BWFAN_Common::checking_all_tables_exists();
		if ( is_array( $automations_table_result ) ) {
			$this->response_code = 404;
			$message             = 'Some tables are not created. ' . implode( ',', $automations_table_result );

			return $this->error_response( $message );
		}

		$this->response_code = 200;

		return $this->success_response( [], 'All tables are created' );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Table_Status' );