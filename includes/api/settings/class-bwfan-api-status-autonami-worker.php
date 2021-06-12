<?php

class BWFAN_API_Status_Autonami_Worker extends BWFAN_API_Base {
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
		$this->route  = '/settings/status/autonami';
	}

	public function process_api_call() {
		$url             = rest_url( 'autonami/v1/worker' ) . '?' . time();
		$body_data       = array(
			'worker'     => true,
			'unique_key' => get_option( 'bwfan_u_key', false ),
		);
		$timing          = isset( $_GET['time'] ) ? $_GET['time'] : '';
		$args1           = bwf_get_remote_rest_args( $body_data );
		$autonami_worker = wp_remote_post( $url, $args1 );

		if ( $autonami_worker instanceof WP_Error ) {
			$this->response_code = 404;

			return $this->error_response( $autonami_worker->get_error_message() );
		}

		if ( isset( $autonami_worker['response'] ) && 200 === absint( $autonami_worker['response']['code'] ) ) {
			$this->response_code = 200;
			$body                = ! empty( $autonami_worker['body'] ) ? json_decode( $autonami_worker['body'], true ) : '';
			$time                = isset( $body['time'] ) ? strtotime( $body['time'] ) : time();
			if ( ! empty( $timing ) ) {
				if ( $time > $timing ) {
					return $this->success_response( array( 'time' => $time, 'cached' => false ), 'Not Cached' );
				} else {
					return $this->success_response( array( 'time' => $time, 'cached' => true ), 'Cached' );
				}
			}

			return $this->success_response( array( 'time' => $time, 'cached' => false ), "Working" );
		}

		$message             = 'Not working';
		$this->response_code = 404;

		return $this->error_response( $message );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Status_Autonami_Worker' );