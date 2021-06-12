<?php

class BWFAN_API_Get_Recent_Recovered extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $contact;
	public $total_count;

	public function __construct() {
		parent::__construct();
		$this->method             = WP_REST_Server::READABLE;
		$this->route              = '/dashboard/recent-recovered';
		$this->pagination->limit  = 5;
		$this->pagination->offset = 0;
	}

	public function default_args_values() {
	}

	public function process_api_call() {
		$offset = ! empty( $this->get_sanitized_arg( 'offset', 'text_field' ) ) ? $this->get_sanitized_arg( 'offset', 'text_field' ) : 0;
		$limit  = ! empty( $this->get_sanitized_arg( 'limit', 'text_field' ) ) ? $this->get_sanitized_arg( 'limit', 'text_field' ) : 5;

		$recovered_carts = BWFAN_Automations::get_recovered_carts( $offset, $limit );

		if ( empty( $recovered_carts ) ) {
			$this->response_code = 200;
			$this->total_count   = 0;

			return $this->success_response( [], __( 'Got all recovered carts.', 'wp-marketing-automations-crm' ) );
		}

		$result = [];
		foreach ( $recovered_carts['items'] as $item ) {

			if ( ! $item instanceof WC_Order ) {
				continue;
			}
			$order_date = $item->get_date_created();
			$result[]   = [
				'order_id'          => $item->get_id(),
				'billing_full_name' => $this->get_full_name( $item ),
				'email'             => $item->get_billing_email(),
				'phone'             => $item->get_billing_phone(),
				'date_created'      => ( $order_date instanceof WC_DateTime ) ? ( $order_date->date( 'Y-m-d H:i:s' ) ) : '',
				'items'             => $this->get_items( $item ),
				'total'             => $item->get_total(),
				'actions'           => ''
			];
		}
		$this->response_code = 200;
		$this->total_count   = $recovered_carts['total_record'];


		return $this->success_response( $result, __( 'Got all recovered carts.', 'wp-marketing-automations-crm' ) );
	}

	public function get_full_name( $item ) {
		$buyer = '';

		if ( ! $item instanceof WC_Order ) {
			return '';
		}

		if ( $item->get_billing_first_name() || $item->get_billing_last_name() ) {
			/* translators: 1: first name 2: last name */
			$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $item->get_billing_first_name(), $item->get_billing_last_name() ) );
		} elseif ( $item->get_billing_company() ) {
			$buyer = trim( $item->get_billing_company() );
		} elseif ( $item->get_customer_id() ) {
			$user  = get_user_by( 'id', $item->get_customer_id() );
			$buyer = ucwords( $user->display_name );
		}

		return apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $item );
	}

	public function get_email( $item ) {
		return $item->get_billing_email();
	}

	public function get_items( $item ) {
		$names = [];
		foreach ( $item->get_items() as $value ) {
			$names[] = $value->get_name();
		}

		return $names;
	}

	public function get_result_total_count() {
		return $this->total_count;
	}
}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Recent_Recovered' );