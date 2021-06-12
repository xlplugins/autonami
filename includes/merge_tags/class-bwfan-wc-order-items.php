<?php

class BWFAN_WC_Order_Items extends Merge_Tag_Abstract_Product_Display {

	private static $instance = null;

	public $supports_order_table = true;

	public function __construct() {
		$this->tag_name        = 'order_items';
		$this->tag_description = __( 'Order Items', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_items', array( $this, 'parse_shortcode' ) );
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
	 * @return mixed|void
	 */
	public function parse_shortcode( $attr ) {
		if ( false === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			$order       = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
			$this->order = $order;

			if ( ! $this->order instanceof WC_Order ) {
				return $this->parse_shortcode_output( '', $attr );
			}

			$items    = $order->get_items();
			$products = [];

			foreach ( $items as $item ) {
				$products[] = $item->get_product();
			}
			$this->products = $products;
		}

		$output = $this->process_shortcode( $attr );

		return $this->parse_shortcode_output( $output, $attr );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Items' );
}