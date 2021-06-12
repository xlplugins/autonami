<?php

abstract class BWFAN_API_Base {

	/**
	 * @var string $route
	 */
	public $route = null;

	/**
	 * @var string $method
	 */
	public $method = null;

	/**
	 * @var stdClass $pagination
	 *
	 * It contains two keys: Limit and Offset, for pagination purposes
	 */
	public $pagination = null;

	public $response_code = 200;

	public $args = array();

	public $request_args = array();

	public function __construct() {
		$this->pagination         = new stdClass();
		$this->pagination->limit  = 0;
		$this->pagination->offset = 0;
	}

	public function api_call( WP_REST_Request $request ) {
		$params = WP_REST_Server::EDITABLE === $this->method ? $request->get_params() : false;

		if ( false === $params ) {
			$query_params   = $request->get_query_params();
			$query_params   = is_array( $query_params ) ? $query_params : array();
			$request_params = $request->get_params();
			$request_params = is_array( $request_params ) ? $request_params : array();
			$params         = array_replace( $query_params, $request_params );
		}

		$params['files'] = $request->get_file_params();

		$this->pagination->limit  = ! empty( $params['limit'] ) ? absint( $params['limit'] ) : $this->pagination->limit;
		$this->pagination->offset = ! empty( $params['offset'] ) ? absint( $params['offset'] ) : 0;
		$this->args               = wp_parse_args( $params, $this->default_args_values() );

		try {
			return $this->process_api_call();
		} catch ( Exception $e ) {
			$this->response_code = 500;

			return $this->error_response( $e->getMessage() );
		}
	}

	public function default_args_values() {
		return array();
	}

	/** To be implemented in Child Class. Override in Child Class */
	public function get_result_total_count() {
		return 0;
	}

	/** To set count data */
	public function get_result_count_data() {
		return 0;
	}

	public function error_response( $message = '', $wp_error = null, $code = 0 ) {
		if ( 0 !== absint( $code ) ) {
			$this->response_code = $code;
		}

		$data = array();
		if ( $wp_error instanceof WP_Error ) {
			$message = $wp_error->get_error_message();
			$data    = $wp_error->get_error_data();
		}

		return new WP_Error( $this->response_code, $message, array( 'status' => $this->response_code, 'error_data' => $data ) );
	}

	public function success_response( $result_array, $message = '' ) {
		$response = BWFAN_Common::format_success_response( $result_array, $message, $this->response_code );

		/** Total Count */
		$total_count = $this->get_result_total_count();
		if ( ! empty( $total_count ) ) {
			$response['total_count'] = $total_count;
		}

		/** Count Data */
		$count_data = $this->get_result_count_data();
		if ( ! empty( $count_data ) ) {
			$response['count_data'] = $count_data;
		}

		/** Pagination */
		if ( isset( $this->pagination->limit ) && ( 0 === $this->pagination->limit || ! empty( $this->pagination->limit ) ) ) {
			$response['limit'] = absint( $this->pagination->limit );
		}

		if ( isset( $this->pagination->offset ) && ( 0 === $this->pagination->offset || ! empty( $this->pagination->offset ) ) ) {
			$response['offset'] = absint( $this->pagination->offset );
		}

		return rest_ensure_response( $response );
	}

	/**
	 * @param string $key
	 * @param string $is_a
	 * @param string $collection
	 *
	 * @return bool|array|mixed
	 */
	public function get_sanitized_arg( $key = '', $is_a = 'key', $collection = '' ) {
		$sanitize_method = ( 'bool' === $is_a ? 'rest_sanitize_boolean' : 'sanitize_' . $is_a );
		if ( ! is_array( $collection ) ) {
			$collection = $this->args;
		}

		if ( ! empty( $key ) && isset( $collection[ $key ] ) && ! empty( $collection[ $key ] ) ) {
			return call_user_func( $sanitize_method, $collection[ $key ] );
		}

		if ( ! empty( $key ) ) {
			return false;
		}

		return array_map( $sanitize_method, $collection );
	}

	abstract public function process_api_call();

}
