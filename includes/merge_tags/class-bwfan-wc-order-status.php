<?php

class BWFAN_WC_Order_Status extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'order_status';
		$this->tag_description = __( 'Order Status', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_status', array( $this, 'parse_shortcode' ) );
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

		$order_status = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' )->get_status();
		if ( strpos( $order_status, 'wc-' ) === false ) {
			$order_status = 'wc-' . $order_status;
		}
		$all_status   = wc_get_order_statuses();
		$order_status = $all_status[ $order_status ];

		return $this->parse_shortcode_output( $order_status, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'Processing', 'wp-marketing-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Status' );
}