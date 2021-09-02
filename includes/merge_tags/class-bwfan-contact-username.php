<?php
if ( ! bwfan_is_autonami_pro_active() || version_compare( BWFAN_PRO_VERSION, '2.0.2', '>' ) ) {
	class BWFAN_Contact_UserName extends BWFAN_Merge_Tag {

		private static $instance = null;

		public function __construct() {
			$this->tag_name        = 'contact_username';
			$this->tag_description = __( 'Customer Username', 'wp-marketing-automations' );
			add_shortcode( 'bwfan_customer_username', array( $this, 'parse_shortcode' ) );
			add_shortcode( 'bwfan_contact_username', array( $this, 'parse_shortcode' ) );
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

			/** If user */
			$user = isset( $get_data['wp_user'] ) ? $get_data['wp_user'] : '';
			if ( $user instanceof WP_User ) {
				return $this->parse_shortcode_output( $user->user_login, $attr );
			}

			/** If order */
			$order = isset( $get_data['wc_order'] ) ? $get_data['wc_order'] : '';
			if ( bwfan_is_woocommerce_active() && $order instanceof WC_Order ) {
				$user_id = $order->get_user_id();
				if ( absint( $user_id ) > 0 ) {
					$user = get_userdata( $user_id );
					if ( $user instanceof WP_User ) {
						return $this->parse_shortcode_output( $user->user_login, $attr );
					}
				}
			}

			/** If user id */
			$user_id = isset( $get_data['user_id'] ) ? $get_data['user_id'] : '';
			if ( absint( $user_id ) > 0 ) {
				$user = get_userdata( absint( $user_id ) );
				if ( $user instanceof WP_User ) {
					return $this->parse_shortcode_output( $user->user_login, $attr );
				}
			}

			/** If email */
			$email = isset( $get_data['email'] ) ? $get_data['email'] : '';
			if ( ! empty( $email ) ) {
				$user = get_user_by( 'email', $email );
				if ( $user instanceof WP_User ) {
					return $this->parse_shortcode_output( $user->user_login, $attr );
				}
			}

			return $this->parse_shortcode_output( '', $attr );
		}

		/**
		 * Show dummy value of the current merge tag.
		 *
		 * @return string
		 */
		public function get_dummy_preview() {
			return 'john.doe';
		}
	}

	/**
	 * Register this merge tag to a group.
	 */
	BWFAN_Merge_Tag_Loader::register( 'bwf_contact', 'BWFAN_Contact_UserName' );
}