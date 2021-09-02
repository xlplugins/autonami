<?php
if ( ! bwfan_is_autonami_pro_active() || version_compare( BWFAN_PRO_VERSION, '2.0.2', '>' ) ) {
	class BWFAN_Contact_WPID extends BWFAN_Merge_Tag {

		private static $instance = null;

		public function __construct() {
			$this->tag_name        = 'contact_wpid';
			$this->tag_description = __( 'Contact WPID', 'autonami-automations-pro' );
			add_shortcode( 'bwfan_contact_wpid', array( $this, 'parse_shortcode' ) );
			add_shortcode( 'bwfan_customer_user_id', array( $this, 'parse_shortcode' ) );
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

			/** If user */
			$user = isset( $get_data['wp_user'] ) ? $get_data['wp_user'] : '';
			if ( $user instanceof WP_User ) {
				return $this->parse_shortcode_output( $user->ID, $attr );
			}

			/** If user id */
			$user_id = isset( $get_data['user_id'] ) ? $get_data['user_id'] : '';
			if ( absint( $user_id ) > 0 ) {
				return $this->parse_shortcode_output( absint( $user_id ), $attr );
			}

			/** If order */
			$order = isset( $get_data['wc_order'] ) ? $get_data['wc_order'] : '';
			if ( bwfan_is_woocommerce_active() && $order instanceof WC_Order ) {
				$user_id = $order->get_user_id();
				if ( absint( $user_id ) > 0 ) {
					return $this->parse_shortcode_output( absint( $user_id ), $attr );
				}
			}

			/** If user ID or email */
			$user_id = isset( $get_data['user_id'] ) ? $get_data['user_id'] : '';
			$email   = isset( $get_data['email'] ) ? $get_data['email'] : '';

			$contact = bwf_get_contact( $user_id, $email );
			if ( absint( $contact->get_id() ) > 0 ) {
				$user_id = $contact->get_wpid();
				if ( absint( $user_id ) > 0 ) {
					return $this->parse_shortcode_output( absint( $user_id ), $attr );
				}
			}

			return $this->parse_shortcode_output( '', $attr );
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
	BWFAN_Merge_Tag_Loader::register( 'bwf_contact', 'BWFAN_Contact_WPID' );
}