<?php

class BWFAN_WC_Customer_Custom_Field extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'customer_custom_field';
		$this->tag_description = __( 'Customer Custom Field', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_customer_custom_field', array( $this, 'parse_shortcode' ) );
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
		$get_data = BWFAN_Merge_Tag_Loader::get_data();

		if ( isset( $get_data['wc_order'] ) && $get_data['wc_order'] instanceof WC_Order ) {
			$data = BWFAN_Woocommerce_Compatibility::get_order_data( $get_data['wc_order'], $attr['key'] );
			if ( '' != $data ) {
				return $this->parse_shortcode_output( $data, $attr );
			}
		}

		$email = $get_data['email'];

		$user_id = ! empty( $get_data['user_id'] ) ? $get_data['user_id'] : 0;
		$wp_user = false;
		if ( is_email( $email ) ) {
			$wp_user = get_user_by( 'email', $email );
			$user_id = $wp_user instanceof WP_User ? $wp_user->ID : 0;
		}

		if ( false === $wp_user && absint( $user_id ) > 0 ) {
			$wp_user = get_user_by( 'id', $user_id );
			$email   = $wp_user instanceof WP_User ? $wp_user->user_email : '';
		}

		if ( ! is_email( $email ) && ! absint( $user_id ) > 0 ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		$contact = bwf_get_contact( $user_id, $email );
		if ( ! $contact instanceof WooFunnels_Contact && $contact instanceof WooFunnels_Customer ) {
			$contact = $contact->contact;
			/** @var WooFunnels_Contact $contact */
			$contact = $contact->get_id() > 0 ? $contact : false;
		}

		if ( ! $contact instanceof WooFunnels_Contact || ! $contact->get_id() > 0 ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		return $this->parse_shortcode_output( $contact->get_meta( $attr['key'] ), $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'Dummy Custom Field Value', 'wp-marketing-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Customer_Custom_Field' );
}