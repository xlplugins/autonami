<?php

class BWFAN_WC_Customer_Email extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'customer_email';
		$this->tag_description = __( 'Customer Email Address', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_customer_email', array( $this, 'parse_shortcode' ) );
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

		$email = '';
		if ( isset( $get_data['email'] ) && ! empty( $get_data['email'] ) ) {
			$email = $get_data['email'];
		} elseif ( isset( $get_data['wc_order'] ) && ! empty( $get_data['wc_order'] ) ) {
			$email = BWFAN_Woocommerce_Compatibility::get_order_data( $get_data['wc_order'], '_billing_email' );
		} elseif ( isset( $get_data['user_id'] ) && ! empty( $get_data['user_id'] ) ) {
			$user = get_user_by( 'ID', $get_data['user_id'] );
			if ( $user instanceof WP_User ) {
				$email = $user->user_email;
			}
		} elseif ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
			$data = json_decode( $get_data['cart_details']['checkout_data'], true );
			if ( isset( $data['fields'] ) && isset( $data['fields']['billing_email'] ) ) {
				$email = $data['fields']['billing_email'];
			}
		}

		return $this->parse_shortcode_output( $email, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'customer123@gmail.com';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_Email' );
}