<?php

class BWFAN_API_Sync_Connector extends BWFAN_API_Base {
	public static $ins;

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::CREATABLE;
		$this->route  = '/connector/sync';
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process_api_call() {
		$wfco_connector = $this->get_sanitized_arg( 'wfco_connector', 'text_field' );
		$id             = $this->get_sanitized_arg( 'id', 'text_field' );
		if ( empty( $wfco_connector ) || empty( ( $id ) ) ) {
			return $this->error_response( __( 'Connector saved data missing, kindly disconnect and connect again.', 'wp-marketing-automations-crm' ), null, 400 );
		}

		$current_connector = WFCO_Load_Connectors::get_connector( $wfco_connector );
		if ( ! $current_connector instanceof BWF_CO ) {
			$message = __( 'Something is wrong, connector isn\'t available.', 'wp-marketing-automations-crm' );

			return $this->error_response( $message, null, 500 );
		}
		try {
			$response = $current_connector->handle_settings_form( $this->args, 'sync' );
			$status   = ( 'success' === $response['status'] ) ? true : false;
			$message  = $response['message'];
		} catch ( Exception $exception ) {
			$message = $exception->getMessage();

			return $this->error_response( $message, null, 500 );
		}

		/** Error occurred */
		if ( false === $status ) {
			return $this->error_response( $message, null, 500 );
		}

		return $this->success_response( $response, __( 'Connector updated successfully.', 'wp-marketing-automations-crm' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Sync_Connector' );
