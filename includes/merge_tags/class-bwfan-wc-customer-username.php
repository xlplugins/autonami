<?php

class BWFAN_WC_Customer_UserName extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'customer_username';
		$this->tag_description = __( 'Customer Username', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_customer_username', array( $this, 'parse_shortcode' ) );
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

		$user_data        = array();
		$customer_user_id = null;

		if ( ! empty( $get_data['wp_user'] ) && $get_data['wp_user'] instanceof WP_User ) {
			$user_data = $get_data['wp_user'];
		} elseif ( ! empty( $get_data['wc_order'] ) ) {
			$customer_user_id = $get_data['wc_order']->get_user_id();
			$user_data        = get_userdata( $customer_user_id );
		} elseif ( ! empty( $get_data['user_id'] ) ) {
			$customer_user_id = $get_data['user_id'];
			$user_data        = get_userdata( $customer_user_id );
		} elseif ( ! empty( $get_data['email'] ) ) {
			$user_data = get_user_by( 'email', $get_data['email'] );
		}

		if ( ! $user_data instanceof WP_User ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		$username = $user_data->user_login;

		return $this->parse_shortcode_output( $username, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'johndoe';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_UserName' );
}
