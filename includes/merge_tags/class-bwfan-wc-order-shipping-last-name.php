<?php

/**
 * Class BWFAN_WC_Order_Shipping_Last_Name
 *
 * Merge tag outputs order shipping last name
 *
 * Since 2.0.6
 */
class BWFAN_WC_Order_Shipping_Last_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'order_shipping_last_name';
		$this->tag_description = __( 'Order Shipping Last Name', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_shipping_last_name', array( $this, 'parse_shortcode' ) );
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
			return $this->parse_shortcode_output( $this->get_dummy_preview(), $attr );
		}

		$order = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );

		if ( ! $order instanceof WC_Order ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		$shipping_last_name = BWFAN_Woocommerce_Compatibility::get_order_data( $order, '_shipping_last_name' );

		if ( empty( $shipping_last_name ) ) {
			$shipping_last_name = BWFAN_Woocommerce_Compatibility::get_order_data( $order, '_billing_last_name' );
		}

		return $this->parse_shortcode_output( $shipping_last_name, $attr );
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
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Shipping_Last_Name' );
}
