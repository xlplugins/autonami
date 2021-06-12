<?php

class BWFAN_WC_Order_Items_Total_Count extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'order_items_total_count';
		$this->tag_description = __( 'Order Items Total Count', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_items_quantitycount', array( $this, 'parse_shortcode' ) );
		add_shortcode( 'bwfan_order_items_total_count', array( $this, 'parse_shortcode' ) );
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
	 * @return int|mixed|void
	 */
	public function parse_shortcode( $attr ) {
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$order = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );

		if ( ! $order instanceof WC_Order ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		$total_quantity = 0;
		$order_items    = $order->get_items();
		if ( empty( $order_items ) ) {
			return $this->parse_shortcode_output( $total_quantity, $attr );
		}

		foreach ( $order_items as $item ) {
			$total_quantity += $item->get_quantity();
		}

		return $this->parse_shortcode_output( $total_quantity, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return integer
	 */
	public function get_dummy_preview() {
		return 3;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Items_Total_Count' );
}