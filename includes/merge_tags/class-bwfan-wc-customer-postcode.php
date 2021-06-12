<?php

class BWFAN_WC_Customer_Postcode extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'customer_postcode';
		$this->tag_description = __( 'Customer Postcode', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_customer_postcode', array( $this, 'parse_shortcode' ) );
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

		$customer_postcode = '';
		if ( isset( $get_data['wc_order'] ) && ! empty( $get_data['wc_order'] ) ) {
			$customer_postcode = BWFAN_Woocommerce_Compatibility::get_order_billing_postcode( $get_data['wc_order'] );
		} elseif ( isset( $get_data['user_id'] ) && ! empty( $get_data['user_id'] ) ) {
			$customer_postcode = get_user_meta( $get_data['user_id'], 'billing_postcode', true );
		} elseif ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
			$data = json_decode( $get_data['cart_details']['checkout_data'], true );
			if ( isset( $data['fields'] ) && isset( $data['fields']['billing_postcode'] ) ) {
				$customer_postcode = $data['fields']['billing_postcode'];
			}
		}

		return $this->parse_shortcode_output( $customer_postcode, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return '113245';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_Postcode' );
}
