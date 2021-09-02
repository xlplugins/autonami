<?php
if ( ! bwfan_is_autonami_pro_active() || version_compare( BWFAN_PRO_VERSION, '2.0.2', '>' ) ) {
	class BWFAN_Contact_LastName extends BWFAN_Merge_Tag {

		private static $instance = null;

		public function __construct() {
			$this->tag_name        = 'contact_last_name';
			$this->tag_description = __( 'Contact Last Name', 'autonami-automations-pro' );
			add_shortcode( 'bwfan_contact_lastname', array( $this, 'parse_shortcode' ) );
			add_shortcode( 'bwfan_contact_last_name', array( $this, 'parse_shortcode' ) );
			add_shortcode( 'bwfan_customer_last_name', array( $this, 'parse_shortcode' ) );
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

			/** If last name */
			if ( isset( $get_data['last_name'] ) && ! empty( $get_data['last_name'] ) ) {
				return $this->parse_shortcode_output( ucfirst( $get_data['last_name'] ), $attr );
			}

			/** If order */
			$order = isset( $get_data['wc_order'] ) ? $get_data['wc_order'] : '';
			if ( bwfan_is_woocommerce_active() && $order instanceof WC_Order ) {
				$last_name = BWFAN_Woocommerce_Compatibility::get_order_data( $order, '_billing_last_name' );

				return $this->parse_shortcode_output( ucwords( $last_name ), $attr );
			}

			/** If Contact ID */
			if ( bwfan_is_autonami_pro_active() ) {
				$cid       = isset( $get_data['contact_id'] ) ? $get_data['contact_id'] : '';
				$last_name = $this->get_last_name( $cid );
				if ( false !== $last_name ) {
					return $this->parse_shortcode_output( ucwords( $last_name ), $attr );
				}
			}

			/** If User ID */
			$user_id = isset( $get_data['user_id'] ) ? $get_data['user_id'] : '';
			if ( absint( $user_id ) > 0 ) {
				$last_name = get_user_meta( $user_id, 'last_name', true );

				return $this->parse_shortcode_output( ucwords( $last_name ), $attr );
			}

			/** If email */
			$email = isset( $get_data['email'] ) ? trim( $get_data['email'] ) : '';
			if ( is_email( $email ) ) {
				$user_data = get_user_by( 'email', $email );
				$last_name = $user_data instanceof WP_User ? get_user_meta( $user_data->ID, 'last_name', true ) : '';

				return $this->parse_shortcode_output( ucwords( $last_name ), $attr );
			}

			/** If cart */
			if ( isset( $get_data['cart_details'] ) && ! empty( $get_data['cart_details'] ) ) {
				$data = json_decode( $get_data['cart_details']['checkout_data'], true );
				if ( isset( $data['fields'] ) && isset( $data['fields']['billing_last_name'] ) ) {
					$last_name = $data['fields']['billing_last_name'];

					return $this->parse_shortcode_output( ucwords( $last_name ), $attr );
				}
			}

			return $this->parse_shortcode_output( '', $attr );
		}

		public function get_last_name( $cid ) {
			$cid = absint( $cid );
			if ( 0 === $cid ) {
				return false;
			}
			$contact = new BWFCRM_Contact( $cid );
			if ( ! $contact->is_contact_exists() ) {
				return false;
			}

			return $contact->contact->get_l_name();
		}

		/**
		 * Show dummy value of the current merge tag.
		 *
		 * @return string
		 *
		 */
		public function get_dummy_preview() {
			return 'Doe';
		}
	}

	/**
	 * Register this merge tag to a group.
	 */
	BWFAN_Merge_Tag_Loader::register( 'bwf_contact', 'BWFAN_Contact_LastName' );
}