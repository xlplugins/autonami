<?php

class BWFAN_WC_Order_View_Url extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'order_view_url';
		$this->tag_description = __( 'Order View Url', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_view_url', array( $this, 'parse_shortcode' ) );
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

		$order = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
		if ( ! $order instanceof WC_Order ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		$order_view_url = $order->get_view_order_url();

		return $this->parse_shortcode_output( $order_view_url, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return wc_get_endpoint_url( 'view-order', 1, wc_get_page_permalink( 'myaccount' ) );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_View_Url' );
}