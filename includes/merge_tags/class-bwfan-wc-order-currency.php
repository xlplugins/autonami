<?php

class BWFAN_WC_Order_Currency extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'order_currency';
		$this->tag_description = __( 'Order Currency', 'wp-marketing-automations' );

		// actual decoding of the merge tag
		add_shortcode( 'bwfan_order_currency', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = false;
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

		$order_id       = BWFAN_Merge_Tag_Loader::get_data( 'wc_order_id' );
		$order_currency = BWFAN_Woocommerce_Compatibility::get_order_meta( $order_id, '_order_currency' );

		if ( empty( $order_currency ) ) {
			$order_currency = get_woocommerce_currency();
		}

		return $this->parse_shortcode_output( strtoupper( $order_currency ), $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'GBP';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Currency' );
}