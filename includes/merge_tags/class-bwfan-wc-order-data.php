<?php

class BWFAN_WC_Order_Data extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'order_data';
		$this->tag_description = __( 'Order Data', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_data', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Show the html in popup for the merge tag.
	 */
	public function get_view() {
		$this->get_back_button();
		$this->data_key();
		if ( $this->support_fallback ) {
			$this->get_fallback();
		}

		$this->get_preview();
		$this->get_copy_button();
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

		$order_id     = BWFAN_Woocommerce_Compatibility::get_order_id( BWFAN_Merge_Tag_Loader::get_data( 'wc_order' ) );
		$item_key     = $attr['key'];
		$return_value = get_post_meta( $order_id, $item_key, true );

		return $this->parse_shortcode_output( $return_value, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'Value of the key', 'wp-marketing-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Data' );
}