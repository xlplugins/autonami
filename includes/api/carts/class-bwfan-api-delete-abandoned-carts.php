<?php

class BWFAN_API_Delete_Abandoned_Carts extends BWFAN_API_Base {
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
		$this->method = WP_REST_Server::DELETABLE;
		$this->route = '/carts/';
	}

	public function default_args_values() {
		$args = [
			'abandoned_ids' => []
		];

		return $args;
	}

	public function process_api_call() {
		$abandoned_ids = $this->args['abandoned_ids'];

		if ( empty( $abandoned_ids ) || ! is_array( $abandoned_ids ) ) {
			return $this->error_response( __( 'Abandoned ids is missing.', 'wp-marketing-automations-crm' ) );
		}

		$result = BWFAN_Recoverable_Carts::delete_abandoned_cart( $abandoned_ids );

		if ( true !== $result && is_array( $result ) ) {
			$message = 'Unable to Delete Abandoned with id :' . implode( ',', $result );

			return $this->success_response( [], $message );
		}

		return $this->success_response( [], __( 'Cart abandoned deleted successfully.', 'wp-marketing-automations-crm' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Delete_Abandoned_Carts' );
