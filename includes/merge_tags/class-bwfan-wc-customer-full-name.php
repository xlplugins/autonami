<?php

class BWFAN_WC_Customer_Full_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'customer_full_name';
		$this->tag_description = __( 'Customer Full Name', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_customer_full_name', array( $this, 'parse_shortcode' ) );
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

		$full_name = '';
		if ( isset( $get_data['first_name'] ) && isset( $get_data['last_name'] ) ) {
			$first_name = $get_data['first_name'];
			$last_name  = $get_data['last_name'];
			$full_name  = $first_name . ' ' . $last_name;
		} elseif ( isset( $get_data['wc_order'] ) && ! empty( $get_data['wc_order'] ) ) {
			$first_name = BWFAN_Woocommerce_Compatibility::get_order_data( $get_data['wc_order'], '_billing_first_name' );
			$last_name  = BWFAN_Woocommerce_Compatibility::get_order_data( $get_data['wc_order'], '_billing_last_name' );
			$full_name  = $first_name . ' ' . $last_name;
		} elseif ( isset( $get_data['user_id'] ) && ! empty( $get_data['user_id'] ) ) {
			$first_name = get_user_meta( $get_data['user_id'], 'first_name', true );
			$last_name  = get_user_meta( $get_data['user_id'], 'last_name', true );
			$full_name  = $first_name . ' ' . $last_name;
		} elseif ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
			$data       = json_decode( $get_data['cart_details']['checkout_data'], true );
			$first_name = '';
			$last_name  = '';
			if ( isset( $data['fields'] ) ) {
				if ( isset( $data['fields']['billing_first_name'] ) ) {
					$first_name = $data['fields']['billing_first_name'];
				}
				if ( isset( $data['fields']['billing_last_name'] ) ) {
					$last_name = $data['fields']['billing_last_name'];
				}
			}
			$full_name = $first_name . ' ' . $last_name;
		}

		return $this->parse_shortcode_output( ucwords( $full_name ), $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'John Marsh', 'wp-marketing-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_Full_Name' );
}