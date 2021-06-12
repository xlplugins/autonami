<?php

class BWFAN_WC_Product_Parent_Sku extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'product_parent_sku';
		$this->tag_description = __( 'Product Parent Sku', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_product_parent_sku', array( $this, 'parse_shortcode' ) );
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
		$parent_id = BWFAN_Woocommerce_Compatibility::get_parent_id( BWFAN_Merge_Tag_Loader::get_data( 'product' ) );
		$parent    = wc_get_product( $parent_id );
		$sku       = '';

		if ( $parent ) {
			$sku = $parent->get_sku();
		}

		return $this->parse_shortcode_output( $sku, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'sku6556';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_product', 'BWFAN_WC_Product_Parent_Sku' );
}