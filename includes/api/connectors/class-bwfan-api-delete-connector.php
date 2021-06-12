<?php

class BWFAN_API_Delete_Connector extends BWFAN_API_Base {
	public static $ins;

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::DELETABLE;
		$this->route  = '/connector/disconnect';
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public function process_api_call() {
		$slug = $this->get_sanitized_arg( 'wfco_connector', 'text_field' );
		if ( empty( $slug ) ) {
			return $this->error_response( __( 'Connector saved data missing, kindly disconnect and connect again.', 'wp-marketing-automations-crm' ), null, 400 );
		}

		/** Handling for Bitly */
		if ( 'bwfco_bitly' === $slug ) {
			$bitly = BWFCO_Bitly::get_instance();
			if ( $bitly->is_connected() ) {
				$bitly->disconnect();
				return $this->success_response( array(), __( 'Connector deleted successfully.', 'wp-marketing-automations-crm' ) );
			}
		}

		$connector_details = WFCO_Model_Connectors::get_specific_rows( 'slug', $slug );
		if ( empty( $connector_details ) ) {
			return $this->error_response( __( 'Connector not exist.', 'wp-marketing-automations-crm' ), null, 500 );
		}

		$connector_ids = implode(
			',',
			array_map(
				function( $connector ) {
					return $connector['ID'];
				},
				$connector_details
			)
		);

		$connector_sql     = "DELETE from {table_name} where ID IN ($connector_ids)";
		$connectormeta_sql = "DELETE from {table_name} where connector_id IN ($connector_ids)";
		WFCO_Model_ConnectorMeta::delete_multiple( $connectormeta_sql );
		WFCO_Model_Connectors::delete_multiple( $connector_sql );
		do_action( 'connector_disconnected', $slug, true );

		return $this->success_response( array(), __( 'Connector deleted successfully.', 'wp-marketing-automations-crm' ) );
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Delete_Connector' );
