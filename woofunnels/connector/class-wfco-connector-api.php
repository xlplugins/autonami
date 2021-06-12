<?php

if ( ! defined( 'WF_API_ENDPOINT' ) ) {
	define( 'WF_API_ENDPOINT', 'https://account.buildwoofunnels.com/' );
}

class WFCO_Connector_api {
	private $license = '';
	private $connector = '';
	private $action = 'find_connector';
	private $package = [];
	private $api_params = [];
	private $api_url = WF_API_ENDPOINT;
	private $response_data = [];

	public function __construct( $license = '', $connector = '', $action = 'find_connector' ) {

		if ( '' !== $license ) {
			$this->license = trim( $license );
		}
		if ( '' !== $connector ) {
			$this->connector = trim( $connector );
		}
		if ( '' !== $action ) {
			$this->action = trim( $action );
		}

		$this->api_url = add_query_arg( array(
			'wc-api' => 'am-connector',
		), $this->api_url );

		$this->api_params = [
			'connector_api' => 'yes',
			'action'        => $this->action,
			'data'          => [],
		];
	}

	public function set_action( $action ) {

		$this->action = trim( $action );

		return $this;
	}

	/**
	 * @param array $data Associative array
	 * @param bool $reset
	 */
	public function set_data( $data = [], $reset = false ) {
		if ( is_array( $data ) && count( $data ) > 0 ) {

			foreach ( $data as $key => $d ) {
				$this->api_params['data'][ $key ] = $d;
			}
		}

		return $this;
	}


	public function get_license() {
		return $this->license;

	}

	public function get_connector() {
		return $this->connector;
	}


	public function get_package() {
		return $this->package;
	}

	public function create_license() {
		$this->find_connector();
	}

	public function find_connector() {

		$this->api_params['data']['connector'] = $this->connector;
		$this->api_params['data']['license']   = $this->license;
		$this->fetch_data();

		return $this;

	}

	private function fetch_data() {
		$this->api_params['action']           = $this->action;
		$this->api_params['data']['platform'] = home_url();
		$request                              = wp_remote_post( $this->api_url, [
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $this->api_params,
		] );

		if ( ! is_wp_error( $request ) ) {
			$this->response_data = json_decode( wp_remote_retrieve_body( $request ), true );
		}

		if ( ! empty( $this->response_data ) ) {
			$this->package = $this->response_data;
		}
	}

	public function get() {
		$this->fetch_data();

		return $this;
	}


}
