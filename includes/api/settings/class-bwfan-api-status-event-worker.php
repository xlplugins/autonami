<?php

class BWFAN_API_Status_Event_Worker extends BWFAN_API_Base {
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
		$this->route  = '/settings/status/event';
	}

	public function process_api_call() {
		$url                   = rest_url( 'autonami/v1/events' );
		$body_data             = array(
			'worker'     => true,
			'unique_key' => get_option( 'bwfan_u_key', false ),
		);
		$args2                 = bwf_get_remote_rest_args( $body_data );
		$autonami_event_worker = wp_remote_post( $url, $args2 );
		$timing                = isset( $_GET['time'] ) ? $_GET['time'] : '';

		if ( $autonami_event_worker instanceof WP_Error ) {
			$this->response_code = 404;

			return $this->error_response( $autonami_event_worker->get_error_message() );
		}

		if ( isset( $autonami_event_worker['response'] ) && 200 === absint( $autonami_event_worker['response']['code'] ) ) {
			$this->response_code = 200;
			$body                = ! empty( $autonami_event_worker['body'] ) ? json_decode( $autonami_event_worker['body'], true ) : '';
			$time                = isset( $body['time'] ) ? $body['time'] : time();
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

BWFAN_API_Loader::register( 'BWFAN_API_Status_Event_Worker' );