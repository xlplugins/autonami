<?php
if ( ! bwfan_is_autonami_pro_active() || version_compare( BWFAN_PRO_VERSION, '2.0.2', '>' ) ) {
	class BWFAN_Contact_Phone extends BWFAN_Merge_Tag {

		private static $instance = null;

		public function __construct() {
			$this->tag_name        = 'contact_phone';
			$this->tag_description = __( 'Contact Phone', 'autonami-automations-pro' );
			add_shortcode( 'bwfan_contact_phone', array( $this, 'parse_shortcode' ) );
			add_shortcode( 'bwfan_customer_phone', array( $this, 'parse_shortcode' ) );
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
				return $this->parse_shortcode_output( $this->get_dummy_preview(), $attr );
			}

			/** If phone number */
			if ( isset( $get_data['phone'] ) && ! empty( $get_data['phone'] ) ) {
				return $this->parse_shortcode_output( $get_data['phone'], $attr );
			}

			/** If Contact ID */
			if ( bwfan_is_autonami_pro_active() ) {
				$cid   = isset( $get_data['contact_id'] ) ? $get_data['contact_id'] : '';
				$phone = $this->get_phone_by_cid( $cid );
				if ( false !== $phone ) {
					return $this->parse_shortcode_output( $phone, $attr );
				}
			}

			/** If order */
			$order = isset( $get_data['wc_order'] ) ? $get_data['wc_order'] : '';
			if ( bwfan_is_woocommerce_active() && $order instanceof WC_Order ) {
				$phone   = BWFAN_Woocommerce_Compatibility::get_order_data( $order, '_billing_phone' );
				$country = BWFAN_Woocommerce_Compatibility::get_order_data( $order, '_billing_country' );
				if ( ! empty( $phone ) && ! empty( $country ) ) {
					$phone = BWFAN_Phone_Numbers::add_country_code( $phone, $country );
				}

				return $this->parse_shortcode_output( $phone, $attr );
			}

			/** If user ID or email */
			$user_id = isset( $get_data['user_id'] ) ? $get_data['user_id'] : '';
			$email   = isset( $get_data['email'] ) ? $get_data['email'] : '';

			$contact = bwf_get_contact( $user_id, $email );
			if ( absint( $contact->get_id() ) > 0 ) {
				$phone   = $contact->get_contact_no();
				$country = $contact->get_country();
				if ( ! empty( $phone ) && ! empty( $country ) ) {
					$phone = BWFAN_Phone_Numbers::add_country_code( $phone, $country );
				}

				return $this->parse_shortcode_output( $phone, $attr );
			}

			/** If cart */
			if ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
				$data = json_decode( $get_data['cart_details']['checkout_data'], true );
				if ( isset( $data['fields'] ) && isset( $data['fields']['billing_phone'] ) && ! empty( $data['fields']['billing_phone'] ) ) {
					$phone   = $data['fields']['billing_phone'];
					$country = ( isset( $data['fields']['billing_country'] ) && ! empty( $data['fields']['billing_country'] ) ) ? $data['fields']['billing_country'] : '';
					if ( ! empty( $phone ) && ! empty( $country ) ) {
						$phone = BWFAN_Phone_Numbers::add_country_code( $phone, $country );
					}

					return $this->parse_shortcode_output( $phone, $attr );
				}
			}

			return $this->parse_shortcode_output( '', $attr );
		}

		public function get_phone_by_cid( $cid ) {
			$cid = absint( $cid );
			if ( 0 === $cid ) {
				return false;
			}
			$contact = new BWFCRM_Contact( $cid );
			if ( ! $contact->is_contact_exists() ) {
				return false;
			}

			$phone   = $contact->contact->get_contact_no();
			$country = $contact->contact->get_country();
			if ( ! empty( $phone ) && ! empty( $country ) ) {
				$phone = BWFAN_Phone_Numbers::add_country_code( $phone, $country );
			}

			return $phone;
		}

		/**
		 * Show dummy value of the current merge tag.
		 *
		 * @return string
		 */
		public function get_dummy_preview() {
			return '8451001000';
		}
	}

	/**
	 * Register this merge tag to a group.
	 */
	BWFAN_Merge_Tag_Loader::register( 'bwf_contact', 'BWFAN_Contact_Phone' );
}
