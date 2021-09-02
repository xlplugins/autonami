<?php
if ( ! bwfan_is_autonami_pro_active() || version_compare( BWFAN_PRO_VERSION, '2.0.2', '>' ) ) {
	class BWFAN_Contact_State extends BWFAN_Merge_Tag {

		private static $instance = null;

		public function __construct() {
			$this->tag_name        = 'contact_state';
			$this->tag_description = __( 'Contact State', 'autonami-automations-pro' );
			add_shortcode( 'bwfan_contact_state', array( $this, 'parse_shortcode' ) );
			add_shortcode( 'bwfan_customer_state', array( $this, 'parse_shortcode' ) );
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

			/** If Contact ID available */
			if ( bwfan_is_autonami_pro_active() ) {
				$cid   = isset( $get_data['contact_id'] ) ? $get_data['contact_id'] : '';
				$state = $this->get_state( $cid );
				if ( false !== $state ) {
					return $this->parse_shortcode_output( $state, $attr );
				}
			}

			/** If order */
			$order = isset( $get_data['wc_order'] ) ? $get_data['wc_order'] : '';
			if ( bwfan_is_woocommerce_active() && $order instanceof WC_Order ) {
				$state   = BWFAN_Woocommerce_Compatibility::get_order_billing_state( $order );
				$country = BWFAN_Woocommerce_Compatibility::get_billing_country_from_order( $order );

				if ( ! empty( $country ) ) {
					$states = WC()->countries->get_states( $country );
					$state  = ( is_array( $states ) && isset( $states[ $state ] ) ) ? $states[ $state ] : $state;
				}

				return $this->parse_shortcode_output( $state, $attr );
			}

			/** If user ID or email */
			if ( bwfan_is_autonami_pro_active() ) {
				$user_id = isset( $get_data['user_id'] ) ? $get_data['user_id'] : '';
				$email   = isset( $get_data['email'] ) ? $get_data['email'] : '';

				$contact = bwf_get_contact( $user_id, $email );
				if ( absint( $contact->get_id() ) > 0 ) {
					$state = $contact->get_state();

					return $this->parse_shortcode_output( $state, $attr );
				}
			}

			return $this->parse_shortcode_output( '', $attr );
		}

		public function get_state( $cid ) {
			$cid = absint( $cid );
			if ( 0 === $cid ) {
				return false;
			}
			$contact = new BWFCRM_Contact( $cid );
			if ( ! $contact->is_contact_exists() ) {
				return false;
			}

			return $contact->contact->get_state();
		}

		/**
		 * Show dummy value of the current merge tag.
		 *
		 * @return string
		 *
		 */
		public function get_dummy_preview() {
			return 'NE';
		}
	}

	/**
	 * Register this merge tag to a group.
	 */
	BWFAN_Merge_Tag_Loader::register( 'bwf_contact', 'BWFAN_Contact_State' );
}