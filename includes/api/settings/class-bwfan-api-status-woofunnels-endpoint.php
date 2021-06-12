<?php

class BWFAN_API_Status_Woofunnels_Endpoints extends BWFAN_API_Base {
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
		$this->route  = '/settings/status/woofunnels';
	}

	public function process_api_call() {
		$url               = rest_url( '/woofunnels/v1/worker' ) . '?' . time();
		$args              = [ 'method' => 'GET', 'sslverify' => false, ];
		$woofunnels_worker = wp_remote_post( $url, $args );
		$timing            = isset( $_GET['time'] ) ? $_GET['time'] : '';

		if ( $woofunnels_worker instanceof WP_Error ) {
			$this->response_code = 404;

			return $this->error_response( $woofunnels_worker->get_error_message() );
		}

		if ( isset( $woofunnels_worker['response'] ) && 200 === absint( $woofunnels_worker['response']['code'] ) ) {
			$this->response_code = 200;
			$body                = ! empty( $woofunnels_worker['body'] ) ? json_decode( $woofunnels_worker['body'], true ) : '';
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

BWFAN_API_Loader::register( 'BWFAN_API_Status_Woofunnels_Endpoints' );