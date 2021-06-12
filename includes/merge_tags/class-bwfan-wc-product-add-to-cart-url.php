<?php

class BWFAN_WC_Product_Add_To_Cart_Url extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'product_add_to_cart_url';
		$this->tag_description = __( 'Product Add To Cart Url', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_product_add_to_cart_url', array( $this, 'parse_shortcode' ) );
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

		$this->initialize_product_details();
		$product_link = BWFAN_Merge_Tag_Loader::get_data( 'product' )->get_permalink();

		return $this->parse_shortcode_output( $product_link, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		$products = wc_get_products( array(
				'numberposts' => 1,
				'post_status' => 'published', // Only published products
		) );

		$product = $products[0];

		return $product->get_permalink();
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_product', 'BWFAN_WC_Product_Add_To_Cart_Url' );
}