<?php

class BWFAN_WC_Customer_State extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'customer_state';
		$this->tag_description = __( 'Customer State', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_customer_state', array( $this, 'parse_shortcode' ) );
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

		$customer_state = '';
		if ( isset( $get_data['wc_order'] ) && ! empty( $get_data['wc_order'] ) ) {
			$customer_state = BWFAN_Woocommerce_Compatibility::get_order_billing_state( $get_data['wc_order'] );
			$country        = BWFAN_Woocommerce_Compatibility::get_billing_country_from_order( $get_data['wc_order'] );
		} elseif ( isset( $get_data['user_id'] ) && ! empty( $get_data['user_id'] ) ) {
			$customer_state = get_user_meta( $get_data['user_id'], 'billing_state', true );
			$country        = get_user_meta( $get_data['user_id'], 'billing_country', true );
		} elseif ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
			$data = json_decode( $get_data['cart_details']['checkout_data'], true );
			if ( isset( $data['fields'] ) ) {
				if ( isset( $data['fields']['billing_state'] ) ) {
					$customer_state = $data['fields']['billing_state'];
				}
				if ( isset( $data['fields']['billing_country'] ) ) {
					$country = $data['fields']['billing_country'];
				}
			}
		}

		if ( empty( $country ) ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		$states         = WC()->countries->get_states( $country );
		$customer_state = ( is_array( $states ) && isset( $states[ $customer_state ] ) ) ? $states[ $customer_state ] : '';

		return $this->parse_shortcode_output( $customer_state, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'Illinois', 'wp-marketing-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_State' );
}

