<?php

class BWFAN_Contact_ID extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'contact_id';
		$this->tag_description = __( 'Contact ID', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_contact_id', array( $this, 'parse_shortcode' ) );
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

		$contact_id = 0;
		$user_id    = 0;
		$email      = '';

		/** If Contact ID available then return it */
		if ( isset( $get_data['contact_id'] ) && ! empty( $get_data['contact_id'] ) ) {
			$contact_id = absint( $get_data['contact_id'] );

			return $this->parse_shortcode_output( $contact_id, $attr );
		}

		/** Getting user ID and Email */
		if ( isset( $get_data['user_id'] ) && ! empty( $get_data['user_id'] ) ) {
			$user_id = $get_data['user_id'];
		}
		if ( isset( $get_data['email'] ) ) {
			$email = $get_data['email'];
		}

		if ( ! $user_id || ! $email ) {
			$order = null;
			if ( isset( $get_data['wc_order'] ) ) {
				$order = $get_data['wc_order'];
			}
			if ( ! $order instanceof WC_Order && isset( $get_data['order_id'] ) ) {
				$order = wc_get_order( $get_data['order_id'] );
			}
			if ( $order instanceof WC_Order ) {
				if ( ! $user_id ) {
					$user_id = $order->get_user_id();
				}
				if ( ! $email ) {
					$email = $order->get_billing_email();
				}
			}
		}

		$contact = bwf_get_contact( $user_id, $email );

		if ( $contact instanceof WooFunnels_Contact && absint( $contact->get_id() ) > 0 ) {
			$contact_id = $contact->get_id();
		}

		return $this->parse_shortcode_output( $contact_id, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 *
	 */
	public function get_dummy_preview() {
		return '1';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( ! class_exists( 'BWFAN_BWF_Contact_ID' ) ) {
	BWFAN_Merge_Tag_Loader::register( 'bwf_contact', 'BWFAN_Contact_ID' );
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_Contact_ID' );
}
