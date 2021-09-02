<?php

class BWFAN_API_Get_Recovered_Carts extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $total_count = 0;
	public $count_data = [];

	public function __construct() {
		parent::__construct();
		$this->method             = WP_REST_Server::READABLE;
		$this->route              = '/carts/recovered/';
		$this->pagination->offset = 0;
		$this->pagination->limit  = 10;
		$this->request_args       = array(
			'search' => array(
				'description' => __( '', 'wp-marketing-automations-crm' ),
				'type'        => 'string',
			),
			'offset' => array(
				'description' => __( 'Recovered carts list Offset', 'wp-marketing-automations-crm' ),
				'type'        => 'integer',
			),
			'limit'  => array(
				'description' => __( 'Per page limit', 'wp-marketing-automations-crm' ),
				'type'        => 'integer',
			)
		);
	}

	public function default_args_values() {
		$args = [
			'search' => '',
			'offset' => 0,
			'limit'  => 10
		];

		return $args;
	}

	public function process_api_call() {
		$search = $this->get_sanitized_arg( 'search', 'text_field' );
		$offset = ! empty( $this->get_sanitized_arg( 'offset', 'text_field' ) ) ? $this->get_sanitized_arg( 'offset', 'text_field' ) : 0;
		$limit  = ! empty( $this->get_sanitized_arg( 'limit', 'text_field' ) ) ? $this->get_sanitized_arg( 'limit', 'text_field' ) : 25;

		$recovered_carts  = BWFAN_Recoverable_Carts::get_recovered_carts( $search, $offset, $limit );
		$result           = [];
		$this->count_data = BWFAN_Common::get_carts_count();
		/** @var WC_Order[] $orders */
		if ( ! isset( $recovered_carts['items'] ) ) {
			return $this->success_response( [], __( 'No recovered carts found.', 'wp-marketing-automations-crm' ) );
		}
		$orders  = $recovered_carts['items'];
		$nowDate = new DateTime( 'now', new DateTimeZone( "UTC" ) );
		foreach ( $orders as $item ) {
			if ( ! $item instanceof WC_Order ) {
				continue;
			}
			$cartDate = new DateTime( $item->get_date_created()->date( 'Y-m-d H:i:s' ) );

			$diff    = date_diff( $nowDate, $cartDate, true );
			$diffstr = $diff ? $this->get_difference_string( $diff ) : '';

			$result[] = [
				'id'            => get_post_meta( $item->get_id(), '_bwfan_recovered_ab_id', true ),
				'order_id'      => $item->get_id(),
				'email'         => $item->get_billing_email(),
				'phone'         => $item->get_billing_phone(),
				'preview'       => $this->get_preview( $item ),
				'diffstring'    => $diffstr,
				'date'          => $item->get_date_created()->date( 'Y-m-d H:i:s' ),
				'items'         => $this->get_items( $item ),
				'total'         => $item->get_total(),
				'currency'      => $this->get_currency( $item ),
				'buyer_name'    => $this->get_order_name( $item ),
				'user_id'       => ! empty( $item->get_customer_id() ) ? $item->get_customer_id() : 0,
				'checkout_data' => ! is_null( $item->get_meta() ) ? $item->get_meta() : '',
			];
		}

		$result = BWFAN_Recoverable_Carts::populate_contact_info( $result );

		if ( isset( $recovered_carts['total_count'] ) ) {
			$this->total_count = $recovered_carts['total_count'];
			unset( $result['total_record'] );
		}

		return $this->success_response( $result, __( 'Got all recovered carts.', 'wp-marketing-automations-crm' ) );
	}

	/**
	 * @param $diff
	 *
	 * @return string
	 */
	public function get_difference_string( $difftime ) {
		$dif_str = '';
		if ( $difftime->y > 0 ) {
			$dif_str = $difftime->y . ' year ago';
		} elseif ( $difftime->m > 0 ) {
			$dif_str = $difftime->m . ' months ago';
		} elseif ( $difftime->d > 0 ) {
			$dif_str = $difftime->d . ' days ago';
		} elseif ( $difftime->h > 0 ) {
			$dif_str = $difftime->h . ' hours ago';
		} elseif ( $difftime->i > 0 ) {
			$dif_str = $difftime->i . ' minutes ago';
		} elseif ( $difftime->m > 0 ) {
			$dif_str = $difftime->s . ' seconds ago';
		}

		return $dif_str;
	}

	public function get_result_total_count() {
		return $this->total_count;
	}

	public function get_result_count_data() {
		return $this->count_data;
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

	public function get_preview( $item ) {
		$data        = array();
		$products    = array();
		$order_items = $item->get_items();
		foreach ( $order_items as $product ) {
			$products[] = array(
				'name'  => $product->get_name(),
				'qty'   => $product->get_quantity(),
				'price' => number_format( $item->get_line_subtotal( $product ), 2, '.', '' ),
			);
		}
		$data['order_id'] = $item->get_id();
		$data['products'] = $products;
		$data['billing']  = $item->get_formatted_billing_address();
		$data['shipping'] = $item->get_formatted_shipping_address();
		$data['discount'] = $item->get_total_discount();
		$data['total']    = $item->get_total();

		return $data;
	}

	public function get_items( $item ) {
		$names = [];
		foreach ( $item->get_items() as $value ) {
			if ( ! $value instanceof WC_Order_Item ) {
				continue;
			}

			$product_name = $value->get_name();
			$product_id   = $value->get_product_id();

			if ( $value->is_type( 'variable' ) ) {
				$product_id = $value->get_variation_id();
			}

			$names[ $product_id ] = $product_name;
		}

		return $names;
	}

	public function get_currency( $item ) {
		return [
			'code'              => $item->get_currency(),
			'precision'         => wc_get_price_decimals(),
			'symbol'            => html_entity_decode( get_woocommerce_currency_symbol( $item->get_currency() ) ),
			'symbolPosition'    => get_option( 'woocommerce_currency_pos' ),
			'decimalSeparator'  => wc_get_price_decimal_separator(),
			'thousandSeparator' => wc_get_price_thousand_separator(),
			'priceFormat'       => html_entity_decode( get_woocommerce_price_format() ),
		];
	}

	function get_order_name( $obj ) {
		$buyer  = '';
		$output = '';

		if ( ! $obj instanceof WC_Order ) {
			return $output;
		}

		if ( $obj->get_billing_first_name() || $obj->get_billing_last_name() ) {
			/* translators: 1: first name 2: last name */
			$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $obj->get_billing_first_name(), $obj->get_billing_last_name() ) );
		} elseif ( $obj->get_billing_company() ) {
			$buyer = trim( $obj->get_billing_company() );
		} elseif ( $obj->get_customer_id() ) {
			$user  = get_user_by( 'id', $obj->get_customer_id() );
			$buyer = ucwords( $user->display_name );
		}

		$buyer = apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $obj );

		return $buyer;
	}

	public function get_user_display_name( $item ) {
		if ( empty( $item->get_customer_id() ) ) {
			return '';
		}

		$user = get_user_by( 'id', absint( $item->get_customer_id() ) );

		return $user instanceof WP_User ? $user->display_name : '';
	}

	/**
	 * @param $contact_id
	 * @param $checkout_data
	 *
	 * @return string[]
	 */
	public function get_name( $contact_id, $order_id ) {
		$data          = array( 'f_name' => '', 'l_name' => '' );
		$contact_array = array();
		if ( ! empty( $contact_id ) ) {
			if ( class_exists( 'BWFCRM_Contact' ) ) {
				$contact        = new BWFCRM_Contact( $contact_id );
				$contact_array  = $contact->get_array();
				$data['f_name'] = $contact_array['f_name'];
				$data['l_name'] = $contact_array['l_name'];

				return $data;
			}

			$contact_array  = ( new WooFunnels_Contact( null, null ) )->get_contact_by_contact_id( $contact_id );
			$data['f_name'] = $contact_array->f_name;
			$data['l_name'] = $contact_array->l_name;

			return $data;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return $data;
		}

		$data['f_name'] = $order->get_billing_first_name();
		$data['l_name'] = $order->get_billing_last_name();

		return $data;
	}

}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Recovered_Carts' );
