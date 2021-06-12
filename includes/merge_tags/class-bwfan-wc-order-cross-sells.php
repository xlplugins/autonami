<?php

class BWFAN_WC_Order_Cross_Sells extends Merge_Tag_Abstract_Product_Display {

	private static $instance = null;

	public $supports_order_table = true;

	public function __construct() {
		$this->tag_name        = 'order_cross_sells';
		$this->tag_description = __( 'Order Cross Sells', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_cross_sells', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = false;
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_view_data() {
		$templates = array(
			''                   => __( 'Product Grid - 2 Column', 'wp-marketing-automations' ),
			'product-grid-3-col' => __( 'Product Grid - 3 Column', 'wp-marketing-automations' ),
			'product-rows'       => __( 'Product Rows', 'wp-marketing-automations' ),
			'order-table'        => __( 'WooCommerce Order Summary Layout', 'wp-marketing-automations' ),
		);

		return $templates;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		if ( false === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			$order       = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
			$this->order = $order;
			$cross_sells = BWFAN_Common::get_order_cross_sells( $order );

			if ( empty( $cross_sells ) ) {
				return '';
			}

			$products       = $this->prepare_products( $cross_sells, 'date', 'DESC' );
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
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Cross_Sells' );
}