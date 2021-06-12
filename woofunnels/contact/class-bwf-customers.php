<?php
/**
 * Customer Controller Class
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class BWF_Customers
 */
class BWF_Customers {
	/**
	 * public db_operations $db_operations
	 */
	public $db_operations;

	/**
	 * Get the customer details for the email passed if this email exits other create a new customer with this email
	 * BWF_Customers constructor.
	 */
	public function __construct() {
		$this->db_operations = WooFunnels_DB_Operations::get_instance();
	}

	/**
	 * Get customers based on different criteria
	 *
	 * @param array $args
	 *
	 * @return array|object|null
	 */
	public function get_customers( $args = array() ) {
		$default_args = array(
			'min_order_count'     => 0,
			'max_order_count'     => 999999,
			'min_order_value'     => 0,
			'max_order_value'     => 999999999,
			'customer_limit'      => - 1,
			'min_last_order_date' => '',
			'max_last_order_date' => '',
			'min_creation_date'   => '',
			'max_creation_date'   => '',
		);

		$args = wp_parse_args( $args, $default_args );

		if ( ! empty( $args['min_last_order_date'] ) ) {
			$args['min_last_order_date'] = strtotime( 'midnight', strtotime( sanitize_text_field( $args['min_last_order_date'] ) ) );
		}

		if ( ! empty( $args['max_last_order_date'] ) ) {
			$args['max_last_order_date'] = strtotime( 'midnight', strtotime( sanitize_text_field( $args['max_last_order_date'] ) ) );
		}

		if ( ! empty( $args['min_creation_date'] ) ) {
			$args['min_creation_date'] = strtotime( 'midnight', strtotime( sanitize_text_field( $args['min_creation_date'] ) ) );
		}

		if ( ! empty( $args['max_creation_date'] ) ) {
			$args['max_creation_date'] = strtotime( 'midnight', strtotime( sanitize_text_field( $args['max_creation_date'] ) ) );
		}

		$customers = $this->db_operations->get_customers( $args );

		return $customers;
	}

	/**
	 * Get customer by given field
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return WooFunnels_Customer
	 */
	public function get_customer_by( $field, $value ) {
		$customer = new stdClass();
		if ( 'id' === $field ) {
			$customer = $this->db_operations->get_customer_by_customer_id( $value );
		}

		if ( 'cid' === $field ) {
			$customer = $this->db_operations->get_customer_by_cid( $value );
		}

		$cid = $customer->cid;

		return bwf_get_customer( $cid );
	}

	/**
	 * Getting date range
	 *
	 * @param $range
	 *
	 * @return array
	 */
	public function get_date_range( $range ) {
		$result = array();

		$result['start_date'] = max( strtotime( '-20 years' ), strtotime( sanitize_text_field( $range['start_date'] ) ) );

		$result['end_date'] = strtotime( 'midnight', current_time( 'timestamp' ) );

		if ( ! empty( $range['end_date'] ) ) {
			$result['end_date'] = strtotime( 'midnight', strtotime( sanitize_text_field( $range['end_date'] ) ) );
		}

		return $result;
	}
}
