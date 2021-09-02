<?php
if ( ! bwfan_is_autonami_pro_active() || version_compare( BWFAN_PRO_VERSION, '2.0.2', '>' ) ) {
	class BWFAN_Contact_Postcode extends BWFAN_Merge_Tag {

		private static $instance = null;

		public function __construct() {
			$this->tag_name        = 'contact_postcode';
			$this->tag_description = __( 'contact Postcode', 'wp-marketing-automations' );
			add_shortcode( 'bwfan_customer_postcode', array( $this, 'parse_shortcode' ) );
			add_shortcode( 'bwfan_contact_postcode', array( $this, 'parse_shortcode' ) );
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

			/** If Contact ID available */
			if ( bwfan_is_autonami_pro_active() ) {
				$cid      = isset( $get_data['contact_id'] ) ? $get_data['contact_id'] : '';
				$postcode = $this->get_postcode( $cid );
				if ( false !== $postcode ) {
					return $this->parse_shortcode_output( $postcode, $attr );
				}
			}

			/** If order */
			$order = isset( $get_data['wc_order'] ) ? $get_data['wc_order'] : '';
			if ( bwfan_is_woocommerce_active() && $order instanceof WC_Order ) {
				$postcode = BWFAN_Woocommerce_Compatibility::get_order_billing_postcode( $order );

				return $this->parse_shortcode_output( $postcode, $attr );
			}

			/** If user ID or email */
			if ( bwfan_is_autonami_pro_active() ) {
				$user_id = isset( $get_data['user_id'] ) ? $get_data['user_id'] : '';
				$email   = isset( $get_data['email'] ) ? $get_data['email'] : '';

				$contact = bwf_get_contact( $user_id, $email );
				if ( absint( $contact->get_id() ) > 0 ) {
					$cid      = $contact->get_id();
					$postcode = $this->get_postcode( $cid );
					if ( false !== $postcode ) {
						return $this->parse_shortcode_output( $postcode, $attr );
					}
				}
			}

			/** If cart */
			if ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
				$data = json_decode( $get_data['cart_details']['checkout_data'], true );
				if ( isset( $data['fields'] ) && isset( $data['fields']['billing_postcode'] ) ) {
					$postcode = $data['fields']['billing_postcode'];

					return $this->parse_shortcode_output( $postcode, $attr );
				}
			}

			return $this->parse_shortcode_output( '', $attr );
		}

		public function get_postcode( $cid ) {
			$cid = absint( $cid );
			if ( 0 === $cid ) {
				return false;
			}
			$contact = new BWFCRM_Contact( $cid );
			if ( ! $contact->is_contact_exists() ) {
				return false;
			}

			return $contact->get_postcode();
		}

		/**
		 * Show dummy value of the current merge tag.
		 *
		 * @return string
		 */
		public function get_dummy_preview() {
			return '10001';
		}
	}

	/**
	 * Register this merge tag to a group.
	 */
	BWFAN_Merge_Tag_Loader::register( 'bwf_contact', 'BWFAN_Contact_Postcode' );
}