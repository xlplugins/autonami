<?php

class BWFAN_WC_Cart_Billing_Last_Name extends Cart_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'cart_billing_last_name';
		$this->tag_description = __( 'Cart Billing Last Name', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_cart_billing_last_name', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = true;
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
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$cart_details = BWFAN_Merge_Tag_Loader::get_data( 'cart_details' );
		$field_value  = $this->get_cart_value( 'billing_last_name', $cart_details );

		return $this->parse_shortcode_output( ucwords( $field_value ), $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'Wright';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_cart', 'BWFAN_WC_Cart_Billing_Last_Name' );
	BWFAN_Merge_Tag_Loader::register( 'wc_ab_cart', 'BWFAN_WC_Cart_Billing_Last_Name' );
}
