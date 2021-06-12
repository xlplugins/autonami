<?php

class BWFAN_WC_Customer_Phone extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'customer_phone';
		$this->tag_description = __( 'Customer Phone', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_customer_phone', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		$get_data = BWFAN_Merge_Tag_Loader::get_data();
		if ( true === $get_data['is_preview'] ) {
			return $this->get_dummy_preview();
		}

		$customer_phone   = $this->get_phone( $get_data );
		$customer_country = $this->get_country( $get_data );
		if ( ! empty( $customer_country ) && ! empty( $customer_phone ) ) {
			$customer_phone = BWFAN_Phone_Numbers::add_country_code( $customer_phone, $customer_country );
		}

		return $this->parse_shortcode_output( $customer_phone, $attr );
	}

	public function get_phone( $get_data ) {
		/** If phone is set already and passed */
		if ( isset( $get_data['phone'] ) && ! empty( $get_data['phone'] ) ) {
			return $get_data['phone'];
		}

		/** From WC_Order */
		if ( isset( $get_data['wc_order'] ) && $get_data['wc_order'] instanceof WC_Order ) {
			$phone = BWFAN_Woocommerce_Compatibility::get_order_data( $get_data['wc_order'], '_billing_phone' );
			if ( ! empty( $phone ) ) {
				return $phone;
			}
		}

		/** From User Meta */
		if ( isset( $get_data['user_id'] ) && ! empty( $get_data['user_id'] ) ) {
			$user = get_user_by( 'id', $get_data['user_id'] );
			if ( $user instanceof WP_User ) {
				$phone = get_user_meta( $user->ID, 'billing_phone', true );
				if ( ! empty( $phone ) ) {
					return $phone;
				}
			}
		}

		/** From Email */
		if ( isset( $get_data['email'] ) && is_email( $get_data['email'] ) ) {
			$user = get_user_by( 'email', $get_data['email'] );
			if ( $user instanceof WP_User ) {
				$phone = get_user_meta( $user->ID, 'billing_phone', true );
				if ( ! empty( $phone ) ) {
					return $phone;
				}
			} else {
				$order = BWFAN_Common::get_latest_order_by_email( $get_data['email'] );
				if ( $order instanceof WC_Order && ! empty( $order->get_billing_phone() ) ) {
					return $order->get_billing_phone();
				}
			}
		}

		/** From Abandoned Cart Details */
		if ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
			$data = json_decode( $get_data['cart_details']['checkout_data'], true );
			if ( isset( $data['fields'] ) && isset( $data['fields']['billing_phone'] ) && ! empty( $data['fields']['billing_phone'] ) ) {
				return $data['fields']['billing_phone'];
			}
		}

		return '';
	}

	public function get_country( $get_data ) {
		/** From WC_Order */
		if ( isset( $get_data['wc_order'] ) && $get_data['wc_order'] instanceof WC_Order ) {
			$country = BWFAN_Woocommerce_Compatibility::get_order_data( $get_data['wc_order'], '_billing_country' );
			if ( ! empty( $country ) ) {
				return $country;
			}
		}

		/** From User Meta */
		if ( isset( $get_data['user_id'] ) && ! empty( $get_data['user_id'] ) ) {
			$user = get_user_by( 'id', $get_data['user_id'] );
			if ( $user instanceof WP_User ) {
				$country = get_user_meta( $user->ID, 'billing_country', true );
				if ( ! empty( $country ) ) {
					return $country;
				}
			}
		}

		/** From Email */
		if ( isset( $get_data['email'] ) && is_email( $get_data['email'] ) ) {
			$user = get_user_by( 'email', $get_data['email'] );
			if ( $user instanceof WP_User ) {
				$country = get_user_meta( $user->ID, 'billing_country', true );
				if ( ! empty( $country ) ) {
					return $country;
				}
			} else {
				$order = BWFAN_Common::get_latest_order_by_email( $get_data['email'] );
				if ( $order instanceof WC_Order && ! empty( $order->get_billing_country() ) ) {
					return $order->get_billing_country();
				}
			}
		}

		/** From Abandoned Cart Details */
		if ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
			$data = json_decode( $get_data['cart_details']['checkout_data'], true );
			if ( isset( $data['fields'] ) && isset( $data['fields']['billing_country'] ) && ! empty( $data['fields']['billing_country'] ) ) {
				return $data['fields']['billing_country'];
			}
		}

		return '';
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return '9999888777';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_Phone' );
}

