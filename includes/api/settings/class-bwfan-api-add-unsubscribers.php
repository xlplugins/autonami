<?php

class BWFAN_API_Add_Unsubscribers extends BWFAN_API_Base {
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
		$this->route  = '/settings/unsubscribers';
	}

	public function default_args_values() {
		$args = [
			'unsubscribers' => '',
		];

		return $args;
	}

	public function process_api_call() {
		$unsubscribers = $this->args['unsubscribers'];

		if ( empty( $unsubscribers ) ) {
			return $this->error_response( __( 'Unsubscriber data missing', 'wp-marketing-automations' ) );
		}

		$already_unsubscribe = array();
		foreach ( $unsubscribers as $email ) {
			$unsubscribe_data = BWFAN_Model_Message_Unsubscribe::get_specific_rows( 'recipient', $email );
			if ( ! empty( $unsubscribe_data[0] ) ) {
				$already_unsubscribe[] = $email;
				continue;
			}
			$insert_data = array(
				'recipient' => sanitize_email( $email ),
				'c_date'    => current_time( 'mysql' ),
			);

			BWFAN_Model_Message_Unsubscribe::insert( $insert_data );
		}

		if ( ! empty( $already_unsubscribe ) && is_array( $already_unsubscribe ) ) {
			$message = implode( ',', $already_unsubscribe );

			return $this->error_response( __( 'Recipient already unsubscribe : ' . $message, 'wp-marketing-automations' ) );
		}

		$this->response_code = 200;

		return $this->success_response( [], __( 'Recipient unsubscribe successfully.', 'wp-marketing-automations' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Add_Unsubscribers' );