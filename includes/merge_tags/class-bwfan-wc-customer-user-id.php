<?php

class BWFAN_WC_Customer_User_Id extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'customer_user_id';
		$this->tag_description = __( 'Customer User Id', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_customer_user_id', array( $this, 'parse_shortcode' ) );
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
		$get_data = BWFAN_Merge_Tag_Loader::get_data();
		if ( true === $get_data['is_preview'] ) {
			return $this->get_dummy_preview();
		}

		$customer_user_id = '';
		if ( ! empty( $get_data['wc_order'] ) ) {
			$customer_user_id = $get_data['wc_order']->get_user_id();
		} elseif ( ! empty( $get_data['user_id'] ) ) {
			$customer_user_id = $get_data['user_id'];
		}

		return $this->parse_shortcode_output( $customer_user_id, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 26;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_User_Id' );
}