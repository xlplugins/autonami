<?php

class BWFAN_WC_Product_Featured_Image extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'product_featured_image';
		$this->tag_description = __( 'Product Featured Image', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_product_featured_image', array( $this, 'parse_shortcode' ) );
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

		$this->initialize_product_details();
		$product_image = BWFAN_Merge_Tag_Loader::get_data( 'product' )->get_image( 'shop_catalog' );

		return $this->parse_shortcode_output( $product_image, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		$featured_image = wc_placeholder_img();

		return $featured_image;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_product', 'BWFAN_WC_Product_Featured_Image' );
}