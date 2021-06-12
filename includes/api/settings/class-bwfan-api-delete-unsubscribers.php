<?php

class BWFAN_API_Delete_Unsubscribers extends BWFAN_API_Base {
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
		$this->route  = '/settings/unsubscribers';
	}

	public function default_args_values() {
		$args = [
			'unsubscribers_ids' => '',
		];

		return $args;
	}

	public function process_api_call() {
		$unsubscribers_ids = $this->args['unsubscribers_ids'];

		if ( empty( $unsubscribers_ids ) ) {
			return $this->error_response( __( 'Unsubscribers ids missing', 'wp-marketing-automations' ) );
		}

		$no_unsubsribers = array();

		foreach ( $unsubscribers_ids as $id ) {
			$unsubscriber_data = BWFAN_Model_Message_Unsubscribe::get( $id );
			if ( empty( $unsubscriber_data ) ) {
				$no_unsubsribers[] = $id;
				continue;
			}

			$where = array(
				'ID' => $id,
			);
			BWFAN_Model_Message_Unsubscribe::delete_message_unsubscribe_row( $where );
		}

		if ( ! empty( $no_unsubsribers ) ) {
			return $this->error_response( __( 'Unable to delete some of unsubscribers wih id :' . implode( ',', $no_unsubsribers ), 'wp-marketing-automations' ) );
		}

		do_action( 'bwfan_bulk_delete_unsubscribers' );

		$this->response_code = 200;

		return $this->success_response( [], __( 'Unsubscribers deleted successfully.', 'wp-marketing-automations' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Delete_Unsubscribers' );