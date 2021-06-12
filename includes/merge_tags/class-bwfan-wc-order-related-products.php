<?php

class BWFAN_WC_Order_Related_Products extends Merge_Tag_Abstract_Product_Display {

	private static $instance = null;

	public $supports_order_table = true;

	public function __construct() {
		$this->tag_name        = 'order_related_products';
		$this->tag_description = __( 'Order Related Products', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_related_products', array( $this, 'parse_shortcode' ) );
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
			$this->order = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
			$related     = [];
			$in_order    = [];
			$items       = $this->order->get_items();

			foreach ( $items as $item ) {
				$product = $item->get_product();

				if ( $product ) {
					$parent_product_id = BWFAN_Common::is_variation( $product ) ? $product->get_parent_id() : $product->get_id();
					$in_order[]        = $parent_product_id;
					$related           = array_merge( wc_get_related_products( $parent_product_id, 5 ), $related );
				}
			}

			$related = array_diff( $related, $in_order );
			if ( empty( $related ) ) {
				return false;
			}

			$products       = $this->prepare_products( $related, 'date', 'DESC' );
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
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Related_Products' );
}