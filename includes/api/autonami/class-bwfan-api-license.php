<?php

class BWFAN_API_Automation_License extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::CREATABLE;
		$this->route  = '/license';
	}

	public function default_args_values() {
		$args = [
			'action' => '',
			'key'    => '',
			'name'   => '',
		];

		return $args;
	}

	public function process_api_call() {
		$action      = $this->get_sanitized_arg( 'action', 'text_field', $this->args['action'] );
		$key         = $this->get_sanitized_arg( 'key', 'text_field', $this->args['key'] );
		$plugin_name = $this->get_sanitized_arg( 'name', 'text_field', $this->args['name'] );

		if ( empty( $key ) || empty( $plugin_name ) ) {
			$this->response_code = 400;
			return $this->error_response( __( 'License key is missing', 'wp-marketing-automations' ) );
		}

		$resp = call_user_func_array( array( $this, $plugin_name . '_license' ), [ 'action' => $action, 'key' => $key ] );

		if ( isset( $resp['code'] ) && 200 === $resp['code'] ) {
			$this->response_code = 200;
			return $this->success_response( BWFAN_Common::get_setting_schema(), $resp['msg'] );
		}

		$this->response_code = 400;

		return $this->error_response( __( 'Some error occurred', 'wp-marketing-automations' ) );
	}

	protected function autonami_pro_license( $action, $key ) {
		$return = [ 'code' => 400 ];

		if ( false === class_exists( 'BWFAN_Pro_WooFunnels_Support' ) ) {
			return $return;
		}
		$ins  = BWFAN_Pro_WooFunnels_Support::get_instance();
		$resp = $this->process_license_call( $ins, $key, $action );

		return $resp;
	}

	protected function autonami_connector_license( $action, $key ) {
		$return = [ 'code' => 400 ];

		if ( false === class_exists( 'BWFAN_Basic_Connector_Support' ) ) {
			return $return;
		}
		$ins  = BWFAN_Basic_Connector_Support::get_instance();
		$resp = $this->process_license_call( $ins, $key, $action );

		return $resp;
	}

	protected function process_license_call( $ins, $key, $action ) {
		/** Deactivate call */
		if ( 'deactivate' === $action ) {
			$result = $ins->process_deactivation_api();
			if ( isset( $result[ 'deactivated' ] ) && $result['deactivated'] == true ){
				$msg = __( 'License deactivated successfully.', 'wp-marketing-automations' );

				return [ 'code' => 200, 'msg' => $msg ];
			} else {
				return [ 'code' => 400 ];
			}
		}

		/** Activate call */
		if ( 'activate' === $action ) {
			$data = $ins->process_activation_api( $key );
			if ( isset( $data['error'] ) ) {
				return [ 'code' => 400 ];
			}
			$license_data = '';
			if ( isset( $data['activated'] ) && true === $data['activated'] && isset( $data['data_extra'] ) ) {
				$license_data = $data['data_extra'];
			}

			$msg = __( 'License activated successfully.', 'wp-marketing-automations' );

			return [ 'code' => 200, 'msg' => $msg, 'license_data' => $license_data ];
		}
	}

}

BWFAN_API_Loader::register( 'BWFAN_API_Automation_License' );