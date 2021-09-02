<?php

abstract class WFCO_Call {

	protected $data = array();
	protected $allowed_responses = array( 200, 201, 202 );
	protected $connector_slug = null;
	protected $required_fields = array();
	protected $missing_field = false;

	public function get_slug() {
		return sanitize_title( get_class( $this ) );
	}

	public function get_connector_slug() {
		return $this->connector_slug;
	}

	public function set_connector_slug( $slug ) {
		$this->connector_slug = $slug;
	}

	public function get_random_api_error() {
		return __( 'Api Error: No response from API', ' woofunnels' );
	}

	public function process() {

		return [];
	}

	/**
	 * Checks the required fields for every action
	 *
	 * @param $data
	 * @param $required_fields
	 *
	 * @return bool
	 */
	public function check_fields( $data, $required_fields ) {
		$failed = false;
		foreach ( $required_fields as $single_field ) {

			$failed = ! isset( $data[ $single_field ] );
			/** Existence Checking */
			$failed = $failed || ( is_array( $data[ $single_field ] ) && empty( $data[ $single_field ] ) );
			/** Array Checking */
			$failed = $failed || ( is_null( $data[ $single_field ] ) || '' === $data[ $single_field ] );
			/** Null or Empty String Checking */

			if ( true === $failed ) {
				$this->missing_field = $single_field;
				break;
			}
		}

		return ! $failed;
	}

	/**
	 * Return the error
	 *
	 * @return array
	 */
	public function show_fields_error() {
		return array(
			'response' => 502,
			'body'     => array( 'Required Field Missing' . ( false !== $this->missing_field ? ' : ' . $this->missing_field : '' ) ),
		);
	}

	/**
	 * Set the data for every action
	 *
	 * @param $data
	 */
	public function set_data( $data ) {
		$data       = apply_filters( 'modify_set_data', $data );
		$this->data = $data;
	}

	/**
	 * Sends a wp remote call to Third party softwares.
	 *
	 * @param $url
	 * @param array $params
	 * @param int $req_method
	 *
	 * @return array|mixed|object|string
	 */
	public function make_wp_requests( $url, $params = array(), $headers = array(), $req_method = 1 ) {
		$body = array(
			'response' => 500,
			'body'     => array( __( 'CURL Error', 'woofunnels' ) ),
		);

		// $req_method
		// 1 stands for get
		// 2 stands for post
		// 3 stands for delete

		$args = array(
			'timeout'     => 45,
			'httpversion' => '1.0',
			'blocking'    => true,
			'body'        => $params,
		);

		if ( is_array( $headers ) && count( $headers ) > 0 ) {
			$args['headers'] = $headers;
		}

		switch ( $req_method ) {
			case 2:
				$args['method'] = 'POST';
				break;
			case 3:
				$args['method'] = 'DELETE';
				break;
			case 4:
				$args['method'] = 'PUT';
				break;
			case 5:
				$args['method'] = 'PATCH';
				break;
			default:
				$args['method'] = 'GET';
				break;
		}

		$response = wp_remote_request( $url, $args );

		if ( ! is_wp_error( $response ) ) {
			$body    = wp_remote_retrieve_body( $response );
			$headers = wp_remote_retrieve_headers( $response );
			if ( $this->is_json( $body ) ) {
				$body = json_decode( $body, true );
			}
			$body = maybe_unserialize( $body );
			if ( in_array( $response['response']['code'], $this->allowed_responses, true ) ) {
				$response_code = 200;
			} else {
				$response_code = $response['response']['code'];
			}

			$body = array(
				'response' => intval( $response_code ),
				'body'     => $body,
				'headers'  => $headers,
			);

			return $body;
		}

		$body['body'] = [ $response->get_error_message() ];

		return $body;
	}

	/**
	 * check if a string is json or not
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public function is_json( $string ) {
		json_decode( $string );

		return ( json_last_error() === JSON_ERROR_NONE );
	}
}

