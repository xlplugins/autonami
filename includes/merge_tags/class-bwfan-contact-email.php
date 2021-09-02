<?php
if ( ! bwfan_is_autonami_pro_active() || version_compare( BWFAN_PRO_VERSION, '2.0.2', '>' ) ) {
	class BWFAN_Contact_Email extends BWFAN_Merge_Tag {

		private static $instance = null;

		public function __construct() {
			$this->tag_name        = 'contact_email';
			$this->tag_description = __( 'Contact Email', 'autonami-automations-pro' );
			add_shortcode( 'bwfan_contact_email', array( $this, 'parse_shortcode' ) );
			add_shortcode( 'bwfan_customer_email', array( $this, 'parse_shortcode' ) );
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

			/** If email */
			if ( isset( $get_data['email'] ) && ! empty( $get_data['email'] ) ) {
				return $this->parse_shortcode_output( $get_data['email'], $attr );
			}

			/** If order */
			$order = isset( $get_data['wc_order'] ) ? $get_data['wc_order'] : '';
			if ( bwfan_is_woocommerce_active() && $order instanceof WC_Order ) {
				$email = BWFAN_Woocommerce_Compatibility::get_order_data( $order, '_billing_email' );

				return $this->parse_shortcode_output( $email, $attr );
			}

			/** If Contact ID */
			if ( bwfan_is_autonami_pro_active() ) {
				$cid   = isset( $get_data['contact_id'] ) ? $get_data['contact_id'] : '';
				$email = $this->get_email( $cid );
				if ( false !== $email ) {
					return $this->parse_shortcode_output( $email, $attr );
				}
			}

			/** If User ID */
			$user_id = isset( $get_data['user_id'] ) ? $get_data['user_id'] : '';
			if ( absint( $user_id ) > 0 ) {
				$user = get_user_by( 'ID', absint( $user_id ) );
				if ( $user instanceof WP_User ) {
					return $this->parse_shortcode_output( $user->user_email, $attr );
				}
			}

			/** If cart */
			if ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
				$data = json_decode( $get_data['cart_details']['checkout_data'], true );
				if ( isset( $data['fields'] ) && isset( $data['fields']['billing_email'] ) ) {
					$email = $data['fields']['billing_email'];

					return $this->parse_shortcode_output( $email, $attr );
				}
			}

			return $this->parse_shortcode_output( '', $attr );
		}

		public function get_email( $cid ) {
			$cid = absint( $cid );
			if ( 0 === $cid ) {
				return false;
			}
			$contact = new BWFCRM_Contact( $cid );
			if ( ! $contact->is_contact_exists() ) {
				return false;
			}

			return $contact->contact->get_email();
		}

		/**
		 * Show dummy value of the current merge tag.
		 *
		 * @return string
		 *
		 */
		public function get_dummy_preview() {
			return get_bloginfo( 'admin_email' );
		}
	}

	/**
	 * Register this merge tag to a group.
	 */
	BWFAN_Merge_Tag_Loader::register( 'bwf_contact', 'BWFAN_Contact_Email' );
}