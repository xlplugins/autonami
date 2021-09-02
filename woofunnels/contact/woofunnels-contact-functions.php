<?php
/**
 * Contact functions
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Providing a contact object
 *
 * @param $email
 * @param $wp_id
 *
 * @return WooFunnels_Contact|WooFunnels_Customer
 */
if ( ! function_exists( 'bwf_get_contact' ) ) {
	function bwf_get_contact( $wp_id, $email ) {
		return new WooFunnels_Contact( $wp_id, $email );
	}
}

if ( ! function_exists( 'bwf_create_update_contact' ) ) {
	/**
	 * Creating updating contact and customer table
	 * On offer accepted, on order status change and on order indexing
	 *
	 * @param $order_id
	 * @param $products
	 * @param $total
	 * @param false $force
	 *
	 * @return int|void
	 */
	function bwf_create_update_contact( $order_id, $products, $total, $force = false ) {
		$order = wc_get_order( $order_id );

		$wp_id    = $order->get_customer_id();
		$wp_email = '';

		if ( $wp_id > 0 ) {
			$wp_user  = get_user_by( 'id', $wp_id );
			$wp_email = $wp_user->user_email;
		}

		$email = empty( $wp_email ) ? $order->get_billing_email() : $wp_email;

		if ( empty( $email ) ) {
			return;
		}

		$bwf_contact = bwf_get_contact( $wp_id, $email );

		$bwf_email = isset( $bwf_contact->db_contact->email ) ? $bwf_contact->db_contact->email : '';
		$bwf_wpid  = isset( $bwf_contact->db_contact->wpid ) ? $bwf_contact->db_contact->wpid : 0;

		if ( $wp_id > 0 && ( $bwf_wpid !== $wp_id ) ) {
			$bwf_contact->set_wpid( $wp_id );
		}

		if ( ( empty( $bwf_email ) && ! empty( $email ) ) || ( ! empty( $wp_email ) && ( $bwf_email !== $email ) ) ) {
			$bwf_contact->set_email( $email );
		}

		if ( true === $force ) {
			bwf_create_update_contact_object( $bwf_contact, $order );
		}

		bwf_contact_maybe_update_creation_date( $bwf_contact, $order );
		bwf_create_update_customer( $bwf_contact, $order, $order_id, $products, $total );

		if ( true === $force ) {
			do_action( 'bwf_normalize_contact_meta_before_save', $bwf_contact, $order_id, $order );
		}

		$bwf_contact->save();

		if ( true === $force ) {
			do_action( 'bwf_normalize_contact_meta_after_save', $bwf_contact, $order_id, $order );
		}

		$cid = $bwf_contact->get_id();
		update_post_meta( $order_id, '_woofunnel_cid', $cid );

		return $cid;
	}
}

/**
 * Indexing orders and create/update contacts and customers on user login
 *
 * @param $user_login
 * @param $user
 *
 * @hooked wp_login
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
 */
if ( ! function_exists( 'bwf_update_contact_on_login' ) ) {
	function bwf_update_contact_on_login( $user_id ) {
		$wp_id     = $user_id;
		$wp_user   = get_user_by( 'id', $user_id );
		$wp_email  = $wp_user->user_email;
		$wp_f_name = $wp_user->user_firstname;
		$wp_l_name = $wp_user->user_lastname;

		$numberposts = 200;

		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'woofunnels_handle_indexed_orders', 10, 2 );

		// Get all orders of this login user which are not indexed yet
		$customer_orders_ids = wc_get_orders( array(
			'return'      => 'ids',
			'meta_key'    => '_customer_user', //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => $wp_id, //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'numberposts' => $numberposts,
			'post_type'   => 'shop_order',
			'offset'      => null,
			'orderby'     => 'ID',
			'order'       => 'DESC',
			'status'      => wc_get_is_paid_statuses(),
		) );
		wp_reset_query();

		$order_count = count( $customer_orders_ids );

		remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'woofunnels_handle_indexed_orders', 10, 2 );

		$bwf_contact = bwf_get_contact( $wp_id, $wp_email );
		$cid         = $bwf_contact->get_id();
		$bwf_wpid    = isset( $bwf_contact->db_contact->wpid ) ? $bwf_contact->db_contact->wpid : '';
		$bwf_email   = isset( $bwf_contact->db_contact->email ) ? $bwf_contact->db_contact->email : '';
		$bwf_f_name  = $bwf_contact->get_f_name();
		$bwf_l_name  = $bwf_contact->get_l_name();

		$email = $f_name = $l_name = '';

		$last_order_id = end( $customer_orders_ids );
		if ( $last_order_id > 0 ) {
			$last_order = wc_get_order( $last_order_id );

			$email  = empty( $wp_email ) ? $last_order->get_billing_email() : $wp_email;
			$f_name = empty( $wp_f_name ) ? $last_order->get_billing_first_name() : $wp_f_name;
			$l_name = empty( $wp_l_name ) ? $last_order->get_billing_last_name() : $wp_l_name;

			WooFunnels_Dashboard::$classes['BWF_Logger']->log( "Last order id:$last_order_id, CID: $cid, WPID: $wp_id", 'woofunnels_indexing' );
		}

		if ( $wp_id > 0 && ( $bwf_wpid !== $wp_id ) ) {
			$bwf_contact->set_wpid( $wp_id );
		}

		if ( ( empty( $bwf_email ) && ! empty( $email ) ) || ( ! empty( $email ) && ( $bwf_email !== $email ) ) ) {
			$bwf_contact->set_email( $email );
		}

		if ( ( empty( $bwf_f_name ) && ! empty( $f_name ) ) || ( ! empty( $f_name ) && $bwf_f_name !== $f_name ) ) {
			$bwf_contact->set_f_name( $f_name );
		}

		if ( ( empty( $bwf_l_name ) && ! empty( $l_name ) ) || ( ! empty( $l_name ) && $bwf_l_name !== $l_name ) ) {
			$bwf_contact->set_l_name( $l_name );
		}

		WooFunnels_Dashboard::$classes['BWF_Logger']->log( "These $order_count orders for user: $wp_id, CID: $cid are retrieved: " . print_r( $customer_orders_ids, true ), 'woofunnels_indexing' ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		foreach ( $customer_orders_ids as $order_id ) {
			$order                = wc_get_order( $order_id );
			$subscription_renewal = BWF_WC_Compatibility::get_order_data( $order, '_subscription_renewal', true );
			if ( empty( $subscription_renewal ) && 'upstroke' !== $order->get_created_via() ) {
				$bwf_contact = bwf_create_update_contact_object( $bwf_contact, $order );
			}
			bwf_contact_maybe_update_creation_date( $bwf_contact, $order );
			bwf_create_update_customer_object_login( $bwf_contact, $order );
		}

		$bwf_contact->save();
		$bwf_contact->save_meta();

		do_action( 'bwf_contact_login_saved', $bwf_contact );

		$cid = $bwf_contact->get_id();
		foreach ( $customer_orders_ids as $order_id ) {
			update_post_meta( $order_id, '_woofunnel_cid', $cid );
			update_post_meta( $order_id, '_woofunnel_custid', $cid );
		}

		return $cid;
	}
}

/**
 * @param WooFunnels_Contact $bwf_contact
 * @param WC_order $order
 */
if ( ! function_exists( 'bwf_contact_maybe_update_creation_date' ) ) {
	function bwf_contact_maybe_update_creation_date( $bwf_contact, $order ) {
		$get_creation_date = $bwf_contact->get_creation_date();

		if ( empty( $get_creation_date ) || $get_creation_date === '0000-00-00' || ( ! empty( $get_creation_date ) && $order->get_date_created() instanceof DateTime && ( strtotime( $get_creation_date ) > $order->get_date_created()->getTimestamp() ) ) ) {
			$bwf_contact->set_creation_date( $order->get_date_created()->format( 'Y-m-d H:i:s' ) );
		}
	}
}


if ( ! function_exists( 'bwf_create_update_contact_object' ) ) {

	/**
	 * Called on login, & checkout order processed
	 *
	 * @param $bwf_contact WooFunnels_Contact
	 * @param $order WC_Order
	 *
	 * @return mixed
	 */
	function bwf_create_update_contact_object( $bwf_contact, $order ) {
		$wp_id = $order->get_customer_id();

		/** If false then update the fields only when empty */
		$force     = ( true === WooFunnels_DB_Updater::$indexing ) ? false : true;
		$wp_f_name = '';
		$wp_l_name = '';
		if ( $wp_id > 0 ) {
			$wp_user   = get_user_by( 'id', $wp_id );
			$wp_f_name = $wp_user->user_firstname;
			$wp_l_name = $wp_user->user_lastname;
		}
		$f_name = $order->get_billing_first_name();
		$l_name = $order->get_billing_last_name();

		$f_name = empty( $f_name ) ? $wp_f_name : $f_name;
		$l_name = empty( $l_name ) ? $wp_l_name : $l_name;

		if ( ! empty( $f_name ) ) {
			$f_name = ( false === $force && ! empty( $bwf_contact->get_f_name() ) ) ? '' : $f_name;
			$bwf_contact->set_f_name( $f_name );
		}

		if ( ! empty( $l_name ) ) {
			$l_name = ( false === $force && ! empty( $bwf_contact->get_l_name() ) ) ? '' : $l_name;
			$bwf_contact->set_l_name( $l_name );
		}

		if ( '' === $bwf_contact->get_status() ) {
			$bwf_contact->set_status( 1 );
		}

		$order_country = $order->get_billing_country();

		if ( ! empty( $order_country ) ) {
			$order_country = ( false === $force && ! empty( $bwf_contact->get_country() ) ) ? '' : $order_country;
			$bwf_contact->set_country( $order_country );
		}

		$order_state = $order->get_billing_state();

		if ( ! empty( $order_country ) && ! empty( $order_state ) ) {
			$state = bwf_get_states( $order_country, $order_state );
			$state = ( false === $force && ! empty( $bwf_contact->get_state() ) ) ? '' : $state;
			$bwf_contact->set_state( $state );
		}

		$bwf_contact->set_type( 'customer' );
		$contact_no = $order->get_billing_phone();
		$timezone   = bwf_get_timezone_from_order( $order );

		if ( ! empty( $contact_no ) ) {
			$contact_no = ( false === $force && ! empty( $bwf_contact->get_contact_no() ) ) ? '' : $contact_no;

			/** Appending country code in phone number if not added */
			if ( class_exists( 'BWFAN_Phone_Numbers' ) && ! empty( $contact_no ) && ! empty( $bwf_contact->get_country() ) ) {
				$contact_no = BWFAN_Phone_Numbers::add_country_code( $contact_no, $bwf_contact->get_country() );
			}

			$bwf_contact->set_contact_no( $contact_no );
		}
		if ( ! empty( $timezone ) ) {
			$timezone = ( false === $force && ! empty( $bwf_contact->get_timezone() ) ) ? '' : $timezone;
			$bwf_contact->set_timezone( $timezone );
		}

		if ( empty( $bwf_contact->get_source() ) ) {
			$bwf_contact->set_source( 'wc_order' );
		}

		return $bwf_contact;
	}
}

if ( ! function_exists( 'bwf_get_timezone_from_order' ) ) {

	/**
	 * @param $order WC_Order
	 *
	 * @return array|mixed|string
	 */
	function bwf_get_timezone_from_order( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return '';
		}

		$may_be_timezone = BWF_WC_Compatibility::get_order_data( $order, '_wfacp_timezone' );
		/** If set in order meta */
		if ( ! empty( $may_be_timezone ) && true === bwf_if_valid_timezone( $may_be_timezone ) ) {
			return $may_be_timezone;
		}

		/** Check for country */
		$country = $order->get_billing_country();

		ob_start();
		include dirname( __DIR__ ) . '/contact/data/contries-timzone.json'; //phpcs:ignore WordPressVIPMinimum.Files.IncludingNonPHPFile.IncludingNonPHPFile
		$list = ob_get_clean();
		$list = json_decode( $list, true );

		if ( ! is_array( $list ) || ! array_key_exists( $country, $list ) ) {
			return '';
		}

		if ( ! isset( $list[ $country ] ) || ! isset( $list[ $country ]['timezone'] ) || count( $list[ $country ]['timezone'] ) === 0 ) {
			return '';
		}

		return $list[ $country ]['timezone'][0];
	}
}

if ( ! function_exists( 'bwf_if_valid_timezone' ) ) {

	/**
	 * @param $timezone
	 *
	 * @return bool
	 */
	function bwf_if_valid_timezone( $timezone ) {
		if ( empty( $timezone ) ) {
			return false;
		}
		$zones = timezone_identifiers_list();

		return in_array( $timezone, $zones, true );
	}
}

if ( ! function_exists( 'bwf_get_countries_data' ) ) {
	/** countries data
	 * @return mixed|null
	 */
	function bwf_get_countries_data() {
		ob_start();
		include dirname( __DIR__ ) . '/contact/data/countries.json'; //phpcs:ignore WordPressVIPMinimum.Files.IncludingNonPHPFile.IncludingNonPHPFile
		$countries_data = ob_get_clean();
		$countries      = json_decode( $countries_data, true );

		return $countries;
	}
}

