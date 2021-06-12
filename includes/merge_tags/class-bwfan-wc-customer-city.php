<?php

class BWFAN_WC_Customer_City extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'customer_city';
		$this->tag_description = __( 'Customer City', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_customer_city', array( $this, 'parse_shortcode' ) );
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

		$customer_city = $this->get_customer_city();
		if ( empty( $customer_city ) ) {
			$customer_city = $this->fallback;
		}

		return $this->parse_shortcode_output( $customer_city, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'New Delhi', 'wp-marketing-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */

if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_City' );
}

