<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * BWFAN_Public class
 */
class BWFAN_Abandoned_Cart {

	public static $is_cart_changed = false;
	private static $ins = null;
	public $is_cart_restored = false;
	public $is_aerocheckout_page = false;

	protected $aero_product_data = [];
	protected $items = array();
	protected $coupon_data = array();
	protected $fees = array();
	protected $restored_cart_details = array();

	public function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'bwfan_global_setting_page', [ $this, 'display_cart_wc_deactivate_notice' ] );
			add_filter( 'bwfan_main_tab_array', [ $this, 'remove_carts_tab' ] );

			return;
		}

		add_action( 'wfacp_get_fragments', [ $this, 'update_items_in_abandoned_table' ], 10, 2 );
		add_action( 'wc_ajax_bwfan_insert_abandoned_cart', [ $this, 'insert_abandoned_cart' ] );
		add_action( 'wc_ajax_bwfan_delete_abandoned_cart', [ $this, 'delete_abandoned_cart' ] );
		add_action( 'bwfan_remove_abandoned_data_from_table', [ $this, 'remove_abandoned_data_from_table' ] );

		$global_settings = BWFAN_Common::get_global_settings();

		if ( empty( $global_settings['bwfan_ab_enable'] ) || is_admin() ) {
			return;
		}

		add_action( 'woocommerce_checkout_order_processed', [ $this, 'unset_session_key' ], 1 );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'attach_order_id_to_abandoned_row' ] );
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'maybe_set_recovered_key' ] );

		add_action( 'woocommerce_checkout_order_processed', [ $this, 'save_order_total_base_in_order_meta' ], 30, 3 );

		add_action( 'woocommerce_order_status_changed', [ $this, 'maybe_delete_abandoned_row_and_tasks' ], 10, 3 );

		// update events for cart
		add_action( 'woocommerce_add_to_cart', [ $this, 'update_reports_view' ], 1 );
		add_action( 'woocommerce_add_to_cart', [ $this, 'woocommerce_add_to_cart' ], 300 );
		add_action( 'woocommerce_add_to_cart', [ $this, 'cart_updated' ] );
		add_action( 'woocommerce_applied_coupon', [ $this, 'cart_updated' ] );
		add_action( 'woocommerce_removed_coupon', [ $this, 'cart_updated' ] );
		add_action( 'woocommerce_cart_item_removed', [ $this, 'cart_updated' ] );
		add_action( 'woocommerce_cart_item_restored', [ $this, 'cart_updated' ] );
		add_action( 'woocommerce_before_cart_item_quantity_zero', [ $this, 'cart_updated' ] );
		add_action( 'woocommerce_after_cart_item_quantity_update', [ $this, 'cart_updated' ] );

		add_action( 'wp_login', [ $this, 'cart_updated_with_cookie' ], 20 );
		add_action( 'wp', [ $this, 'check_for_cart_update_cookie' ], 99 );
		add_action( 'wp', [ $this, 'set_session_cookies' ], 9 );

		// restore cart when user clicks on restore cart link and lands on site
		add_action( 'wp', [ $this, 'set_session_for_recovered_cart' ], 1 );
		add_action( 'wp', [ $this, 'handle_restore_cart' ], 10 );

		add_action( 'woocommerce_after_calculate_totals', [ $this, 'trigger_update_on_cart_and_checkout_pages' ] );
		// prefill the checkout fields after the cart is restored
		add_filter( 'woocommerce_billing_fields', [ $this, 'prefill_billing_fields' ], 20 );
		add_filter( 'woocommerce_shipping_fields', [ $this, 'prefill_shipping_fields' ], 20 );

		add_action( 'bwfanac_checkout_data', [ $this, 'set_data_for_js' ], 10, 3 );
		add_action( 'bwfanac_cart_details', [ $this, 'remove_data_js' ], 10, 1 );

		add_filter( 'wfacp_default_values', [ $this, 'prefill_embed_forms' ], 15, 2 );
		add_filter( 'wfacp_skip_add_to_cart', [ $this, 'check_aerocheckout_page' ], 12, 2 );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'disable_geolocation_recovery' ] );
		add_action( 'wfacp_after_checkout_page_found', [ $this, 'wfacp_country_fields_on_recovery' ] );

		add_filter( 'execute_cart_abandonment_for_email', [ $this, 'bwfan_disable_abandoned_for_email' ], 10, 3 );
		/** filter for disable abandoned of cart for user with last order date **/
	}

	public function disable_geolocation_recovery() {
		if ( ! isset( $_GET['bwfan-ab-id'] ) && ! isset( $_GET['bwfan-cart-restored'] ) ) {
			return;
		}

		if ( isset( $_GET['bwfan-ab-id'] ) && ( empty( $_GET['bwfan-ab-id'] ) || is_ajax() || is_admin() ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		if ( isset( $_GET['bwfan-cart-restored'] ) && 'success' !== sanitize_text_field( $_GET['bwfan-cart-restored'] ) ) {
			return;
		}

		if ( ! function_exists( 'wfacp_template' ) || ! class_exists( 'WFACP_Template_Common' ) ) {
			return;
		}

		$template = wfacp_template();
		if ( $template instanceof WFACP_Template_Common ) {
			remove_action( 'wfacp_outside_header', [ $template, 'get_base_country' ] );
		}
	}

	public function wfacp_country_fields_on_recovery() {
		add_filter( 'default_checkout_billing_country', [ $this, 'wfacp_assign_country' ], 10, 2 );
		add_filter( 'default_checkout_shipping_country', [ $this, 'wfacp_assign_country' ], 10, 2 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function display_cart_wc_deactivate_notice() {
		echo '<fieldset class="bwfan-tab-content bwfan-activeTab" setting-id="tab-abandonment">';
		echo '<div><strong>' . esc_html__( 'Cart abandonment tracking is a feature associated with WooCommerce, kindly enable the WooCommerce to use it.', 'wp-marketing-automations' ) . '</strong></div>';
		echo '</fieldset>';
	}

	public function remove_carts_tab( $tabs ) {
		if ( isset( $tabs['carts'] ) ) {
			unset( $tabs['carts'] );
		}

		return $tabs;
	}

	/**
	 * Saving order base total for displaying correct revenue in cart analytics screen
	 *
	 * @param $order_id
	 * @param $posted_data
	 * @param $order WC_Order
	 */
	public function save_order_total_base_in_order_meta( $order_id, $posted_data, $order ) {
		if ( ! $order instanceof WC_Order ) {
			$order = wc_get_order( $order_id );
		}
		$total_base = apply_filters( 'bwfan_ab_cart_total_base', $order->get_total() );
		update_post_meta( $order_id, '_bwfan_order_total_base', $total_base );
	}

	public function maybe_delete_abandoned_row_and_tasks( $order_id, $old_status, $new_status ) {
		$failed_statuses = [ 'pending', 'failed', 'cancelled' ];

		if ( in_array( $old_status, $failed_statuses, true ) && ! in_array( $new_status, $failed_statuses, true ) ) {
			$data             = [];
			$data['order_id'] = $order_id;
			bwf_schedule_single_action( time(), 'bwfan_remove_abandoned_data_from_table', $data, 'abandoned' );
		}
	}

	/**
	 * Remove abandoned data from table and also delete it's tasks
	 *
	 * @param $order_id
	 */
	public function remove_abandoned_data_from_table( $order_id ) {
		global $wpdb;

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			return;
		}
		$billing_email = BWFAN_Woocommerce_Compatibility::get_billing_email( $order );
		$sql_where     = 'email = %s';
		$sql_where     = $wpdb->prepare( $sql_where, $billing_email ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$cart_details  = $this->get_cart_by_multiple_key( $sql_where );

		$order_meta_automation = get_post_meta( $order_id, '_bwfan_ab_cart_recovered_a_id', true );
		if ( ! empty( $order_meta_automation ) ) {
			$order_recovered_id = get_post_meta( $order_id, '_bwfan_recovered_ab_id', true );
			if ( empty( $order_recovered_id ) ) {
				/** save abandoned id in order meta */
				update_post_meta( $order_id, '_bwfan_recovered_ab_id', $cart_details['ID'] );

				do_action( 'abandoned_cart_recovered', $cart_details, $order_id, $order );
			}
		} else {
			if ( true === apply_filters( 'bwfan_opt_cart_recovery_adv_logic', false, $billing_email, $order ) ) {
				/** Maybe attribute */
				$this->maybe_attribute_cart_recovery( $order_id, $order, $billing_email );
			}
		}

		if ( is_array( $cart_details ) ) {
			BWFAN_Model_Abandonedcarts::delete( $cart_details['ID'] );
			BWFAN_Common::delete_abandoned_cart_tasks( $cart_details['ID'] );
		}
	}

	public function get_cart_by_multiple_key( $where ) {
		$query        = "Select * from {table_name} WHERE $where";
		$cart_details = BWFAN_Model_Abandonedcarts::get_results( $query );
		if ( is_array( $cart_details ) && count( $cart_details ) > 0 ) {
			$cart_details = $cart_details[0];

			return $cart_details;
		} else {
			return false;
		}
	}

	/**
	 * Attribute sale when automation ran on Abandonment cart that have status in-progress
	 *
	 * @param $order_id
	 * @param $order WC_Order
	 * @param $email
	 */
	public function maybe_attribute_cart_recovery( $order_id, $order, $email ) {
		global $wpdb;

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		/** Status 1: In-Progress (Has Tasks) */
		$sql_where    = 'email = %s AND status = 1';
		$sql_where    = $wpdb->prepare( $sql_where, $email );
		$cart_details = $this->get_cart_by_multiple_key( $sql_where );

		if ( false == $cart_details ) {
			return;
		}

		$cart_creation_time = $cart_details['created_time'];

		$query = $wpdb->prepare( "SELECT log.automation_id, logm.meta_value as track_id FROM 
					( SELECT l.automation_id, l.ID as log_id 
				    FROM {$wpdb->prefix}bwfan_logs l JOIN {$wpdb->prefix}bwfan_logmeta lm 
				    Where l.ID = lm.bwfan_log_id 
				    AND lm.meta_key = 'integration_data' 
				    AND lm.meta_value LIKE '%$email%' 
				    AND lm.meta_value LIKE '%ab_cart_abandoned%'
				    AND l.status = 1
				    AND l.c_date > '$cart_creation_time' ) 
				as log JOIN {$wpdb->prefix}bwfan_logmeta logm
				WHERE log.log_id = logm.bwfan_log_id
				AND logm.meta_key = 't_track_id'" );

		$result = $wpdb->get_results( $query, ARRAY_A );

		if ( ! is_array( $result ) || count( $result ) === 0 ) {
			return;
		}

		$result = $result[0];
		update_post_meta( $order_id, '_bwfan_ab_cart_recovered_a_id', $result['automation_id'] );
		update_post_meta( $order_id, '_bwfan_ab_cart_recovered_t_id', $result['track_id'] );
		update_post_meta( $order_id, '_bwfan_recovered_ab_id', $cart_details['ID'] );

		if ( is_array( $cart_details ) ) {
			BWFAN_Model_Abandonedcarts::delete( $cart_details['ID'] );
			BWFAN_Common::delete_abandoned_cart_tasks( $cart_details['ID'] );
		}

		do_action( 'abandoned_cart_recovered', $cart_details, $order_id, $order );
	}

	public function cart_updated_with_cookie() {
		if ( ! headers_sent() ) {
			BWFAN_Common::set_cookie( 'bwfan_do_cart_update', 1 );
		}
	}

	public function check_for_cart_update_cookie() {
		if ( BWFAN_Common::get_cookie( 'bwfan_do_cart_update' ) ) {
			$this->cart_updated();
			BWFAN_Common::clear_cookie( 'bwfan_do_cart_update' );
		}
	}

	public function cart_updated() {
		self::$is_cart_changed = true;
	}

	public function trigger_update_on_cart_and_checkout_pages() {
		if ( defined( 'WOOCOMMERCE_CART' ) || is_checkout() || did_action( 'woocommerce_before_checkout_form' ) //  support for one page checkout plugins
		) {
			$this->cart_updated();
		}
	}

	/**
	 * Create session cookies for tracking logged in and users
	 */
	public function set_session_cookies() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		if ( '1' !== BWFAN_Common::get_cookie( 'bwfan_session' ) ) {
			$token = BWFAN_Common::create_token( 16 );
			BWFAN_Common::set_cookie( 'bwfan_session', 1, time() + DAY_IN_SECONDS * 365 ); // set tracking cookie for 1 year from now
			BWFAN_Common::set_cookie( 'bwfan_visitor', $token, time() + DAY_IN_SECONDS * 365 );
		}
	}

	public function handle_restore_cart() {
		if ( ! isset( $_GET['bwfan-ab-id'] ) || empty( $_GET['bwfan-ab-id'] ) || is_ajax() || is_admin() ) { //phpcs:ignore WordPress.Security.NonceVerification
			return;
		}

		add_filter( 'wfacp_skip_add_to_cart', '__return_true', 999 );

		$this->restore_cart();
	}

	/**
	 * Restore cart when users land on site after clicking on abandoned cart link.
	 */
	public function restore_cart() {
		/** in case of getting cart_restore_test from url then add dummy product to cart **/
		if ( isset( $_GET['cart_restore_test'] ) && 'yes' === $_GET['cart_restore_test'] ) {
			$args            = array(
				'posts_per_page' => 2,
				'orderby'        => 'rand',
				'post_type'      => 'product',
				'fields'         => 'ids',
			);
			$random_products = get_posts( $args );

			WC()->cart->empty_cart();

			$this->is_cart_restored = true;
			foreach ( $random_products as $product ) {
				WC()->cart->add_to_cart( $product, 1 );
			}

			$url = wc_get_page_permalink( 'checkout' );
			$url = add_query_arg( array(
				'bwfan-cart-restored' => 'success',
			), $url );

			/** Clear show notices for added products */
			if ( ! is_null( WC()->session ) ) {
				WC()->session->set( 'wc_notices', array() );
			}

			wp_safe_redirect( $url );
			exit;
		}

		$token    = sanitize_text_field( $_GET['bwfan-ab-id'] ); //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$restored = $this->restore_abandoned_cart( $token );
		$redirect = 'cart';
		$url      = wc_get_page_permalink( $redirect );

		if ( false === $restored ) {
			$global_settings = BWFAN_Common::get_global_settings();
			if ( ! empty( $global_settings['bwfan_ab_restore_cart_message_failure'] ) ) {
				wc_add_notice( $global_settings['bwfan_ab_restore_cart_message_failure'], 'notice' );
			}

			$url = add_query_arg( array(
				'bwfan-cart-restored' => 'fail',
			), $url );
			wp_safe_redirect( $url );
			exit;
		}

		$checkout_data = $this->get_checkout_data( $this->restored_cart_details );
		if ( isset( $checkout_data['aero_data'] ) && ! is_null( WC()->session ) ) {
			foreach ( $checkout_data['aero_data'] as $key => $value ) {
				$value = maybe_unserialize( $value );
				if ( ! is_array( $value ) || 0 === count( $value ) ) {
					continue;
				}
				if ( false !== strpos( $key, 'wfacp_product_data_' ) ) {
					foreach ( $value as $k => $v ) {
						if ( isset( $this->aero_product_data[ $k ] ) ) {
							$value[ $k ]['is_added_cart'] = $this->aero_product_data[ $k ];
						}
					}
				}

				WC()->session->set( $key, $value );
			}
		}

		do_action( 'bwfan_ab_handle_checkout_data_externally', $checkout_data );

		if ( is_array( $checkout_data ) && count( $checkout_data ) > 0 ) {
			$page_id = $checkout_data['current_page_id'];
			do_action( 'bwfanac_checkout_data', $page_id, $checkout_data, $this->restored_cart_details );
			$url = get_permalink( $page_id );
		}

		$is_checkout_override = isset( $checkout_data['aero_data'] ) && isset( $checkout_data['aero_data']['wfacp_is_checkout_override'] ) ? $checkout_data['aero_data']['wfacp_is_checkout_override'] : false;

		$global_settings = BWFAN_Common::get_global_settings();
		if ( ! empty( $global_settings['bwfan_ab_restore_cart_message_success'] ) ) {
			wc_add_notice( $global_settings['bwfan_ab_restore_cart_message_success'] );
		}

		/** if checkout override then passed native checkout page url **/
		if ( $is_checkout_override ) {
			$url = wc_get_checkout_url();
		}

		$url = add_query_arg( array(
			'bwfan-cart-restored' => 'success',
		), $url );

		$is_redirect = apply_filters( 'bwfan_after_cart_restored_redirect', false );
		if ( false === $is_redirect ) {
			wp_safe_redirect( $url );
			exit;
		}
	}

	/**
	 * Restore the cart.
	 *
	 * @param $token
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function restore_abandoned_cart( $token ) {
		$cart_details = $this->get_cart_by_key( 'token', $token, '%s' );
		if ( false === $cart_details ) {
			return false;
		}

		$coupons    = maybe_unserialize( $cart_details['coupons'] );
		$cart_items = maybe_unserialize( $cart_details['items'] );
		WC()->cart->empty_cart();

		if ( 0 === count( $cart_items ) ) {
			return false;
		}

		$this->is_cart_restored = true;

		/** Before adding products to cart */
		do_action( 'bwfan_pre_abandoned_cart_restored', $cart_details );

		if ( class_exists( 'WFCH_Public' ) && method_exists( WFCH_Public::get_instance(), 'woocommerce_add_to_cart' ) ) {
			remove_action( 'woocommerce_add_to_cart', array( WFCH_Public::get_instance(), 'woocommerce_add_to_cart' ), 99 );
		}

		foreach ( $cart_items as $item_key => $item_data ) {
			/**
			 * Exclude cart items to restore for devs
			 */
			if ( true === apply_filters( 'bwfan_exclude_cart_items_to_restore', false, $item_key, $item_data ) ) {
				continue;
			}

			$product_id     = 0;
			$quantity       = 0;
			$variation_id   = 0;
			$variation_data = [];

			if ( isset( $item_data['product_id'] ) ) {
				$product_id = $item_data['product_id'];
				unset( $item_data['product_id'] );
			}
			if ( isset( $item_data['quantity'] ) ) {
				$quantity = $item_data['quantity'];
				unset( $item_data['quantity'] );
			}
			if ( isset( $item_data['variation_id'] ) ) {
				$variation_id = $item_data['variation_id'];
				unset( $item_data['variation_id'] );
			}
			if ( isset( $item_data['variation'] ) ) {
				$variation_data = $item_data['variation'];
				unset( $item_data['variation'] );
			}

			$hash = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_data, $item_data );
			if ( isset( $item_data['_wfacp_product_key'] ) ) {
				$this->aero_product_data[ $item_data['_wfacp_product_key'] ] = $hash;
			}
		}

		/** Restore coupons */
		if ( is_array( $coupons ) && count( $coupons ) > 0 ) {
			$coupons = array_keys( $coupons );
			foreach ( $coupons as $coupon_code ) {
				if ( ! WC()->cart->has_discount( $coupon_code ) ) {
					WC()->cart->add_discount( $coupon_code );
				}
			}
		}

		/** Clear show notices for added coupons or products */
		if ( ! is_null( WC()->session ) ) {
			WC()->session->set( 'wc_notices', array() );
		}
		BWFAN_Common::clear_cookie( 'bwfan_visitor' );
		BWFAN_Common::clear_cookie( 'bwfan_session' );

		BWFAN_Common::set_cookie( 'bwfan_visitor', $cart_details['cookie_key'], time() + DAY_IN_SECONDS * 365 );
		BWFAN_Common::set_cookie( 'bwfan_session', 1, time() + DAY_IN_SECONDS * 365 ); // set tracking cookie for 1 year from now
		BWFAN_Common::set_cookie( 'bwfan_cart_restored', 1, time() + MINUTE_IN_SECONDS * 30 ); // set restored tracking cookie for 30 minutes, this will help in firing cart recovered event

		/** If any order_id is found for this abandoned row, then set this order_id in woocommerce session */
		$order_id = $cart_details['order_id'];
		if ( 0 < (int) $order_id && ! is_null( WC()->session ) ) {
			WC()->session->set( 'order_awaiting_payment', $order_id );
		}

		$this->restored_cart_details = $cart_details;
		if ( ! is_null( WC()->session ) ) {
			WC()->session->set( 'restored_cart_details', $cart_details );
		}

		/** Apply coupon if available through link - code is in common class auto_apply_wc_coupon() */
		do_action( 'bwfan_abandoned_cart_restored', $cart_details );

		return true;
	}

	public function get_checkout_data( $data ) {
		$checkout_data = $data['checkout_data'];
		if ( ! empty( $checkout_data ) ) {
			$checkout_data = json_decode( $checkout_data, true );
		}

		return $checkout_data;
	}

	public function set_session_for_recovered_cart() {
		if ( isset( $_GET['bwfan-ab-id'] ) && isset( $_GET['automation-id'] ) && ! is_null( WC()->session ) ) { //phpcs:ignore WordPress.Security.NonceVerification
			$ab_data = array(
				'automation_id' => sanitize_text_field( $_GET['automation-id'] ), //phpcs:ignore WordPress.Security.NonceVerification
			);
			if ( isset( $_GET['track-id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
				$ab_data['track_id'] = sanitize_text_field( $_GET['track-id'] ); //phpcs:ignore WordPress.Security.NonceVerification
			}
			WC()->session->set( 'bwfan_abandoned_order_data', $ab_data );
		}
	}

	/**
	 * Add order_id to abandoned row after checkout is processed.
	 *
	 * @param $order_id
	 */
	public function attach_order_id_to_abandoned_row( $order_id ) {
		global $wpdb;
		$tracking_cookie = BWFAN_Common::get_cookie( 'bwfan_visitor' );
		$order           = wc_get_order( $order_id );
		$billing_email   = BWFAN_Woocommerce_Compatibility::get_billing_email( $order );
		$sql_where       = 'email = %s OR cookie_key = %s';
		$sql_where       = $wpdb->prepare( $sql_where, $billing_email, $tracking_cookie ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$cart_details    = $this->get_cart_by_multiple_key( $sql_where );

		if ( is_array( $cart_details ) ) {
			$abandoned_cart_id = $cart_details['ID'];
			$data              = array(
				'order_id' => $order_id,
			);
			$where             = array(
				'ID' => $abandoned_cart_id,
			);
			BWFAN_Model_Abandonedcarts::update( $data, $where );
		}
	}

	/**
	 * Set order meta to know it was restored from abandoned cart
	 *
	 * @param $order_id
	 */
	public function maybe_set_recovered_key( $order_id ) {
		if ( is_null( WC()->session ) ) {
			return;
		}

		$abandoned_data = WC()->session->get( 'bwfan_abandoned_order_data', 'no' );
		if ( is_array( $abandoned_data ) && ! empty( $abandoned_data ) ) {

			update_post_meta( $order_id, '_bwfan_ab_cart_recovered_a_id', $abandoned_data['automation_id'] );
			if ( isset( $abandoned_data['track_id'] ) ) {
				update_post_meta( $order_id, '_bwfan_ab_cart_recovered_t_id', $abandoned_data['track_id'] );
			}

			WC()->session->set( 'bwfan_abandoned_order_data', [] );
		}
	}

	/**
	 * Prefill billing fields on checkout page.
	 *
	 * @param $address_fields
	 *
	 * @return mixed
	 */
	public function prefill_billing_fields( $address_fields ) {
		if ( is_null( WC()->session ) ) {
			return $address_fields;
		}
		$data = WC()->session->get( 'restored_cart_details' );
		if ( ! is_array( $data ) || 0 === count( $data ) ) {
			return $address_fields;
		}

		$address_fields['billing_email']['default'] = $data['email'];

		$checkout_data = $this->get_checkout_data( $data );
		if ( is_array( $checkout_data ) && count( $checkout_data ) > 0 && isset( $checkout_data['fields'] ) && is_array( $checkout_data['fields'] ) ) {
			foreach ( $checkout_data['fields'] as $field_name => $field_value ) {
				if ( false !== strpos( $field_name, 'shipping' ) ) {
					continue;
				}

				if ( ! isset( $address_fields[ $field_name ] ) ) {
					continue;
				}
				$address_fields[ $field_name ]['default'] = $field_value;
			}
		}

		return $address_fields;
	}

	/**
	 * Prefill shipping fields on checkout page.
	 *
	 * @param $address_fields
	 *
	 * @return mixed
	 */
	public function prefill_shipping_fields( $address_fields ) {
		if ( is_null( WC()->session ) ) {
			return $address_fields;
		}
		$data = WC()->session->get( 'restored_cart_details' );

		if ( ! is_array( $data ) || 0 === count( $data ) ) {
			return $address_fields;
		}

		$checkout_data = $this->get_checkout_data( $data );
		if ( is_array( $checkout_data ) && count( $checkout_data ) > 0 && isset( $checkout_data['fields'] ) && is_array( $checkout_data['fields'] ) ) {
			unset( $checkout_data['current_page_id'] );
			foreach ( $checkout_data['fields'] as $field_name => $field_value ) {
				if ( false !== strpos( $field_name, 'billing' ) ) {
					continue;
				}

				if ( ! isset( $address_fields[ $field_name ] ) ) {
					continue;
				}
				$address_fields[ $field_name ]['default'] = $field_value;
			}
		}

		return $address_fields;
	}

	public function set_data_for_js( $page_id, $checkout_data, $restored_data ) {
		if ( ! is_null( WC()->session ) ) {
			$checkout_data['billing_email'] = $restored_data['email'];
			WC()->session->set( 'bwfan_data_for_js', $checkout_data );
		}
	}

	public function remove_data_js( $abandoned_cart_id ) {
		if ( ! is_null( WC()->session ) ) {
			WC()->session->set( 'bwfan_data_for_js', [] );
		}
	}

	public function prefill_embed_forms( $field_value, $key ) {
		if ( is_null( WC()->session ) ) {
			return $field_value;
		}
		$restored_data = WC()->session->get( 'bwfan_data_for_js' );

		if ( 'billing_email' === $key && isset( $restored_data['billing_email'] ) && ! empty( $restored_data['billing_email'] ) ) {
			return $restored_data['billing_email'];
		}
		if ( isset( $restored_data['fields'] ) && is_array( $restored_data['fields'] ) && isset( $restored_data['fields'][ $key ] ) ) {
			return $restored_data['fields'][ $key ];
		}

		return $field_value;
	}

	public function check_aerocheckout_page( $bool, $obj ) {
		/** This filter hook run on every AeroCheckout page */
		$this->is_aerocheckout_page = true;
		if ( isset( $_GET['bwfan-cart-restored'] ) && 'success' === sanitize_text_field( $_GET['bwfan-cart-restored'] ) ) {
			$bool = true;
		}

		return $bool;
	}

	public function update_reports_view() {
		$status = $this->get_session_key();

		/** Already captured */
		if ( true === $status ) {
			return;
		}

		/** Cart is restored, don't require cart initiate increment */
		if ( true === $this->is_cart_restored ) {
			$this->update_session_key();

			return;
		}

		$url = rest_url( '/autonami/v1/update-generated-increment' );

		$body_data = array(
			'id'         => ( true === $this->is_aerocheckout_page ) ? WFACP_Common::get_id() : 0, // 0 means global checkout
			'unique_key' => get_option( 'bwfan_u_key', false ),
		);
		$args      = bwf_get_remote_rest_args( $body_data );

		wp_remote_post( $url, $args );

		$this->update_session_key();
	}

	private function get_session_key() {
		if ( is_null( WC()->session ) ) {
			return true;
		}

		return WC()->session->get( 'bwfan_generated_cart_session', false );
	}

	private function update_session_key() {
		if ( ! is_null( WC()->session ) ) {
			WC()->session->set( 'bwfan_generated_cart_session', true );
		}
	}

	public function woocommerce_add_to_cart() {
		$user    = wp_get_current_user();
		$user_id = ( isset( $user->ID ) ? (int) $user->ID : 0 );
		if ( 0 === $user_id ) {
			return;
		}

		$global_settings = BWFAN_Common::get_global_settings();
		if ( 0 === absint( $global_settings['bwfan_ab_track_on_add_to_cart'] ) ) {
			return;
		}
		if ( 0 !== absint( $global_settings['bwfan_ab_exclude_users_cart_tracking'] ) ) {
			/** Check excluded emails or user roles */
			if ( isset( $global_settings['bwfan_ab_exclude_emails'] ) && ! empty( $global_settings['bwfan_ab_exclude_emails'] ) ) {
				$global_settings['bwfan_ab_exclude_emails'] = str_replace( ' ', '', $global_settings['bwfan_ab_exclude_emails'] );
				$exclude_emails = [];
				if ( strpos( $global_settings['bwfan_ab_exclude_emails'], ',' ) ) {
					$exclude_emails = explode( ',', $global_settings['bwfan_ab_exclude_emails'] );
				}

				if ( empty( $exclude_emails ) ) {
					$exclude_emails = preg_split ( '/$\R?^/m', $global_settings['bwfan_ab_exclude_emails'] );
				}

				if ( in_array( $user->user_email, $exclude_emails, true ) ) {
					return;
				}
			}
			if ( isset( $global_settings['bwfan_ab_exclude_roles'] ) && ! empty( $global_settings['bwfan_ab_exclude_roles'] ) ) {
				$exclude_roles = array_intersect( (array) $user->roles, $global_settings['bwfan_ab_exclude_roles'] );

				if ( ! empty( $exclude_roles ) ) {
					return;
				}
			}
		}

		$coupon_data = [];
		foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) {
			$coupon_data[ $coupon_code ] = [
				'discount_incl_tax' => WC()->cart->get_coupon_discount_amount( $coupon_code, false ),
				'discount_excl_tax' => WC()->cart->get_coupon_discount_amount( $coupon_code ),
				'discount_tax'      => WC()->cart->get_coupon_discount_tax_amount( $coupon_code ),
			];
		}

		$url = rest_url( '/autonami/v1/wc-add-to-cart' );

		$body_data = array(
			'id'          => $user_id,
			'coupon_data' => maybe_serialize( $coupon_data ),
			'items'       => maybe_serialize( WC()->cart->get_cart() ),
			'fees'        => maybe_serialize( WC()->cart->get_fees() ),
			'unique_key'  => get_option( 'bwfan_u_key', false ),
		);
		$args      = bwf_get_remote_rest_args( $body_data );
		wp_remote_post( $url, $args );
	}

	public function unset_session_key() {
		if ( ! is_null( WC()->session ) ) {
			WC()->session->set( 'bwfan_generated_cart_session', false );
		}
	}

	public function update_items_in_abandoned_table( $wfacp_id, $request ) {
		$tracking_cookie = BWFAN_Common::get_cookie( 'bwfan_visitor' );
		$cart_details    = $this->get_cart_by_key( 'cookie_key', $tracking_cookie, '%s' ); // check cart by cookie


		if ( false === $cart_details || ! is_array( $cart_details ) || empty( $cart_details ) ) {
			return;
		}
		$this->update_abandoned_cart( $cart_details );
	}

	public function insert_abandoned_cart() {
		BWFAN_Common::check_nonce();

		$email = sanitize_email( $_POST['email'] ); //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		if ( empty( $email ) ) {
			wp_send_json( array(
				'success' => false,
			) );
		}
		/** Check excluded emails or user roles */
		$global_settings = BWFAN_Common::get_global_settings();
		if ( 0 !== absint( $global_settings['bwfan_ab_exclude_users_cart_tracking'] ) ) {
			if ( isset( $global_settings['bwfan_ab_exclude_emails'] ) && ! empty( $global_settings['bwfan_ab_exclude_emails'] ) ) {
				$global_settings['bwfan_ab_exclude_emails'] = str_replace( ' ', '', $global_settings['bwfan_ab_exclude_emails'] );
				$exclude_emails = [];
				if ( strpos( $global_settings['bwfan_ab_exclude_emails'], ',' ) ) {
					$exclude_emails = explode( ',', $global_settings['bwfan_ab_exclude_emails'] );
				}

				if ( empty( $exclude_emails ) ) {
					$exclude_emails = preg_split ( '/$\R?^/m', $global_settings['bwfan_ab_exclude_emails'] );
				}

				if ( $this->email_exists_in_patterns( $email, $exclude_emails ) ) {
					wp_send_json( array(
						'success' => false,
					) );
				}
			}
			if ( isset( $global_settings['bwfan_ab_exclude_roles'] ) && ! empty( $global_settings['bwfan_ab_exclude_roles'] ) && is_user_logged_in() ) {
				$user          = wp_get_current_user();
				$exclude_roles = array_intersect( (array) $user->roles, $global_settings['bwfan_ab_exclude_roles'] );

				if ( ! empty( $exclude_roles ) ) {
					wp_send_json( array(
						'success' => false,
					) );
				}
			}
		}

		$exclude_checkout_fields = apply_filters( 'bwfan_ab_exclude_checkout_fields', array() );
		$data                    = [
			'fields'               => $_POST['checkout_fields_data'], //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			'current_page_id'      => sanitize_text_field( $_POST['current_page_id'] ), //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			'aerocheckout_page_id' => sanitize_text_field( $_POST['aerocheckout_page_id'] ), //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			'last_edit_field'      => sanitize_text_field( $_POST['last_edit_field'] ), //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			'current_step'         => sanitize_text_field( $_POST['current_step'] ), //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		];

		if ( ! empty( $exclude_checkout_fields ) ) {
			foreach ( $exclude_checkout_fields as $field ) {
				unset( $data['fields'][ $field ] );
			}
		}

		/** Remove empty fields */
		$data['fields'] = array_filter( $data['fields'] );
		$data['fields'] = array_intersect_key( $data['fields'], self::get_woocommerce_default_checkout_nice_names() );

		/**
		 * Set AeroCheckout session keys
		 */
		if ( class_exists( 'WFACP_Common' ) && ! is_null( WC()->session ) ) {
			$aero_id              = WFACP_Common::get_id();
			$aero_hash            = WC()->session->get( 'wfacp_cart_hash' );
			$aero_product_objects = WC()->session->get( 'wfacp_product_objects_' . $aero_id );
			$aero_product_data    = WC()->session->get( 'wfacp_product_data_' . $aero_id );
			$checkout_override    = WFACP_Core()->public->is_checkout_override();
			$data['aero_data']    = array(
				'wfacp_id'                          => maybe_serialize( $aero_id ),
				'wfacp_cart_hash'                   => maybe_serialize( $aero_hash ),
				'wfacp_product_objects_' . $aero_id => maybe_serialize( $aero_product_objects ),
				'wfacp_product_data_' . $aero_id    => maybe_serialize( $aero_product_data ),
				'wfacp_is_checkout_override'        => $checkout_override,
			);
		}

		$data['fields']['timezone'] = $_POST['timezone']; //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$data                       = apply_filters( 'bwfan_ab_change_checkout_data_for_external_use', array_filter( $data ) );

		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$data['lang'] = ICL_LANGUAGE_CODE;
		} elseif ( function_exists( 'pll_current_language' ) ) {
			$data['lang'] = pll_current_language();
		} elseif ( bwfan_is_translatepress_active() ) {
			global $TRP_LANGUAGE;
			$data['lang'] = $TRP_LANGUAGE;
		} elseif ( function_exists( 'bwfan_is_weglot_active' ) && bwfan_is_weglot_active() ) {
			$data['lang'] = weglot_get_current_language();
		}

		$abandoned_cart_id = $this->process_abandoned_cart( $email, $data );

		if ( 0 === absint( $abandoned_cart_id ) ) {
			wp_send_json( array(
				'success' => false,
				'id'      => 0,
				'message' => esc_html__( 'Unable to create cart for this email `' . $email . '`.', 'wp-marketing-automations' ),
			) );
		}

		do_action( 'bwfan_insert_abandoned_cart', $abandoned_cart_id );

		$resp = array(
			'id'     => $abandoned_cart_id,
			'status' => true,
		);

		wp_send_json( $resp );
	}

	public function email_exists_in_patterns( $email, $email_patterns ) {
		foreach ( $email_patterns as $pattern ) {
			if ( false !== strpos( $email, trim( $pattern ) ) ) {
				return true;
			}
		}

		return false;
	}

	public static function get_woocommerce_default_checkout_nice_names() {
		return apply_filters( 'bwfan_ab_default_checkout_nice_names', array(
			'billing_first_name' => __( 'First Name', 'woocommerce' ),
			'billing_last_name'  => __( 'Last Name', 'woocommerce' ),
			'billing_company'    => __( 'Company', 'woocommerce' ),
			'billing_address_1'  => __( 'Address 1', 'woocommerce' ),
			'billing_address_2'  => __( 'Address 2', 'woocommerce' ),
			'billing_city'       => __( 'City', 'woocommerce' ),
			'billing_postcode'   => __( 'Postal/Zip Code', 'woocommerce' ),
			'billing_state'      => __( 'State', 'woocommerce' ),
			'billing_country'    => __( 'Country', 'woocommerce' ),
			'billing_phone'      => __( 'Phone Number', 'woocommerce' ),
			'billing_email'      => __( 'Email Address', 'woocommerce' ),

			'shipping_first_name' => __( 'First Name', 'woocommerce' ),
			'shipping_last_name'  => __( 'Last Name', 'woocommerce' ),
			'shipping_company'    => __( 'Company', 'woocommerce' ),
			'shipping_address_1'  => __( 'Address 1', 'woocommerce' ),
			'shipping_address_2'  => __( 'Address 2', 'woocommerce' ),
			'shipping_city'       => __( 'City', 'woocommerce' ),
			'shipping_postcode'   => __( 'Postal/Zip Code', 'woocommerce' ),
			'shipping_state'      => __( 'State', 'woocommerce' ),
			'shipping_country'    => __( 'Country', 'woocommerce' ),

			'last_edit_field'      => __( 'Checkout Form Last Edit Field', 'wp-marketing-automations' ),
			'current_step'         => __( 'Checkout Form Current Step', 'wp-marketing-automations' ),
			'aerocheckout_page_id' => __( 'Checkout Page ID', 'wp-marketing-automations' ),
			'current_page_id'      => __( 'WordPress Page ID', 'wp-marketing-automations' ),
			'wfacp_source'         => __( 'Checkout Page Source', 'wp-marketing-automations' ),
			'payment_method'       => __( 'Payment Method', 'wp-marketing-automations' ),
		) );
	}

	/**
	 * Return cart ID or INT 0
	 *
	 * @param $email
	 * @param $checkout_fields_data
	 *
	 * @return int
	 */
	public function process_abandoned_cart( $email, $checkout_fields_data ) {
		if ( '1' === BWFAN_Common::get_cookie( 'bwfan_session' ) ) {
			return $this->process_guest_cart_details( $email, $checkout_fields_data );
		}

		return 0;
	}

	/**
	 * Process the abandoned cart for guest users
	 *
	 * @param null $email
	 *
	 * @return int|mixed
	 */
	public function process_guest_cart_details( $email = null, $checkout_fields_data = null ) {
		$data            = [];
		$tracking_cookie = BWFAN_Common::get_cookie( 'bwfan_visitor' );

		if ( ! is_null( WC()->session ) && ! is_null( $email ) ) {
			/** If already checked and order found and no need to create abandonment */
			$email_status = WC()->session->get( 'bwfan_ab_email_status' );
			if ( is_array( $email_status ) && isset( $email_status[ $email ] ) ) {
				if ( $email_status[ $email ]['status'] === false ) {
					return 0;
				}
			}
		}

		$cart_details = $this->get_cart_by_key( 'cookie_key', $tracking_cookie, '%s' ); // check cart by cookie
		if ( ! is_null( $email ) ) {
			$data['email'] = $email;

			/** check cart by guest email id */
			if ( false === $cart_details ) {
				global $wpdb;
				$sql_where    = 'email = %s';
				$sql_where    = $wpdb->prepare( $sql_where, $email ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$cart_details = $this->get_cart_by_multiple_key( $sql_where );
				if ( is_array( $cart_details ) && count( $cart_details ) > 0 ) {
					$cart_details['cookie_key'] = $tracking_cookie;
				} else {
					$cart_details = false;
				}
			}
		}

		/** There is no abandoned cart row for this user, create a new row and insert in table */
		if ( false === $cart_details && false === $this->is_empty() ) {
			$data['cookie_key'] = $tracking_cookie;
			if ( is_array( $checkout_fields_data ) && count( $checkout_fields_data ) > 0 ) {
				$data['checkout_data']    = $checkout_fields_data;
				$data['checkout_page_id'] = $checkout_fields_data['current_page_id'];
			}

			if ( ! apply_filters( 'execute_cart_abandonment_for_email', true, $email, $cart_details ) ) {
				return 0;
			}

			return $this->create_abandoned_cart( $data );
		}

		$cart_details['email'] = ( ! is_null( $email ) ) ? $email : $cart_details['email'];

		if ( is_array( $checkout_fields_data ) && count( $checkout_fields_data ) > 0 ) {
			$cart_details['checkout_data'] = $checkout_fields_data;
		}
		if ( 2 === intval( $cart_details['status'] ) ) {
			$cart_details['status'] = 0;
		}

		$this->update_abandoned_cart( $cart_details );

		return $cart_details['ID'];
	}

	public function get_cart_by_key( $key, $value, $value_type ) {
		global $wpdb;
		$query        = $wpdb->prepare( "Select * from {table_name} WHERE {$key} LIKE {$value_type}", $value ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders
		$cart_details = BWFAN_Model_Abandonedcarts::get_results( $query );

		if ( is_array( $cart_details ) && count( $cart_details ) > 0 ) {
			$cart_details = $cart_details[0];

			return $cart_details;
		} else {
			return false;
		}
	}

	/**
	 * Check if user has some items in cart
	 *
	 * @return bool
	 */
	public function is_empty() {
		return 0 === sizeof( WC()->cart->get_cart() );
	}

	/**
	 * Create a new abandoned cart row
	 *
	 * @param $details
	 *
	 * @return int
	 */
	public function create_abandoned_cart( $details ) {
		$data          = $this->get_current_cart_details();
		$data['email'] = $details['email'];
		if ( isset( $details['checkout_page_id'] ) ) {
			$data['checkout_page_id'] = $details['checkout_page_id'];
		}
		$data['status']        = 0;
		$data['user_id']       = ( isset( $details['user_id'] ) && 0 !== $details['user_id'] ) ? $details['user_id'] : 0;
		$data['created_time']  = current_time( 'mysql', 1 );
		$data['last_modified'] = current_time( 'mysql', 1 );
		$data['token']         = BWFAN_Common::create_token( 32 );
		$data['cookie_key']    = ( isset( $details['cookie_key'] ) ) ? $details['cookie_key'] : '';

		if ( isset( $details['checkout_data'] ) && is_array( $details['checkout_data'] ) && count( $details['checkout_data'] ) > 0 ) {
			$data['checkout_data'] = wp_json_encode( $details['checkout_data'] );
		}

		BWFAN_Model_Abandonedcarts::insert( $data );
		$inserted_id = BWFAN_Model_Abandonedcarts::insert_id();

		return $inserted_id;
	}

	public function get_current_cart_details() {
		$data        = [];
		$coupon_data = [];
		$this->items = WC()->cart->get_cart();

		foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) {
			$coupon_data[ $coupon_code ] = [
				'discount_incl_tax' => WC()->cart->get_coupon_discount_amount( $coupon_code, false ),
				'discount_excl_tax' => WC()->cart->get_coupon_discount_amount( $coupon_code ),
				'discount_tax'      => WC()->cart->get_coupon_discount_tax_amount( $coupon_code ),
			];
		}

		$this->coupon_data          = $coupon_data;
		$this->fees                 = WC()->cart->get_fees();
		$data['items']              = maybe_serialize( $this->items );
		$data['coupons']            = empty( $coupon_data ) ? '' : maybe_serialize( $coupon_data );
		$data['fees']               = empty( $this->fees ) ? '' : maybe_serialize( $this->fees );
		$data['shipping_tax_total'] = WC()->cart->shipping_tax_total;
		$data['shipping_total']     = WC()->cart->shipping_total;
		$data['currency']           = get_woocommerce_currency();
		$data['total']              = WC()->cart->get_total( 'raw' );
		$data['total_base']         = apply_filters( 'bwfan_ab_cart_total_base', $data['total'] );

		return $data;
	}

	/**
	 * Update the abandoned cart details in db table
	 *
	 * @param $old_cart_details
	 */
	public function update_abandoned_cart( $old_cart_details ) {
		$data                  = $this->get_current_cart_details();
		$data['email']         = $old_cart_details['email'];
		$data['user_id']       = $old_cart_details['user_id'];
		$data['status']        = isset( $old_cart_details['status'] ) ? $old_cart_details['status'] : 0;
		$data['cookie_key']    = ( isset( $old_cart_details['cookie_key'] ) ) ? $old_cart_details['cookie_key'] : '';
		$data['last_modified'] = current_time( 'mysql', 1 );

		if ( isset( $old_cart_details['checkout_data'] ) && is_array( $old_cart_details['checkout_data'] ) && count( $old_cart_details['checkout_data'] ) > 0 ) {
			$data['checkout_data'] = wp_json_encode( $old_cart_details['checkout_data'] );
		}

		$where = array(
			'ID' => $old_cart_details['ID'],
		);

		BWFAN_Model_Abandonedcarts::update( $data, $where );
	}

	public function delete_abandoned_cart() {
		BWFAN_Common::check_nonce();

		if ( isset( $_POST['email'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			$email = sanitize_email( $_POST['email'] ); //phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			if ( ! empty( $email ) ) {
				$where = array(
					'email' => $email,
				);
				BWFAN_Model_Abandonedcarts::delete_abandoned_cart_row( $where );
			}
		}

		wp_die();
	}

	public function bwfan_disable_abandoned_for_email( $should_execute, $email, $cart_details ) {
		$global_settings = BWFAN_Common::get_global_settings();
		$days_to_check   = isset( $global_settings['bwfan_disable_abandonment_days'] ) && ! empty( $global_settings['bwfan_disable_abandonment_days'] ) ? esc_attr( $global_settings['bwfan_disable_abandonment_days'] ) : 0;
		$email_status    = [];

		/** If session key found then set status */
		if ( ! is_null( WC()->session ) ) {
			$email_status = WC()->session->get( 'bwfan_ab_email_status' );
		}

		/** If not is array then set as array */
		$email_status = ( ! is_array( $email_status ) ? [] : $email_status );

		/** Email session key, email don't have a value  */
		if ( empty( $email_status ) || ! isset( $email_status[ $email ] ) || empty( $email_status[ $email ] ) ) {
			global $current_user;
			$user_id = ( $current_user instanceof WP_User ) ? $current_user->ID : null;
			$contact = bwf_get_contact( $user_id, $email );
			if ( ! $contact instanceof WooFunnels_Contact || $contact->get_id() === 0 ) {
				if ( ! is_null( WC()->session ) ) {
					$email_status[ $email ] = [ 'time' => false, 'status' => true ];
					WC()->session->set( 'bwfan_ab_email_status', $email_status );
				}

				return true;
			}

			$customer        = ( ! $contact instanceof WooFunnels_Customer ) ? bwf_get_customer( $contact ) : $contact;
			$last_order_date = $customer->get_l_order_date();

			if ( empty( $last_order_date ) || '0000-00-00' === $last_order_date ) {
				if ( ! is_null( WC()->session ) ) {
					$email_status[ $email ] = [ 'time' => false, 'status' => true ];
					WC()->session->set( 'bwfan_ab_email_status', $email_status );
				}

				return true;
			}
			/** Set email data in session */
			$email_status[ $email ] = [ 'time' => $last_order_date ];

			if ( ! is_null( WC()->session ) ) {
				WC()->session->set( 'bwfan_ab_email_status', $email_status );
			}
		}

		$last_updated = new DateTime( $email_status[ $email ]['time'] );
		$current      = new DateTime();
		$current->modify( "-$days_to_check days" );
		if ( $current < $last_updated ) {
			$email_status[ $email ]['status'] = false;
			if ( ! is_null( WC()->session ) ) {
				WC()->session->set( 'bwfan_ab_email_status', $email_status );
			}

			return false;
		}
		$email_status[ $email ]['status'] = true;
		if ( ! is_null( WC()->session ) ) {
			WC()->session->set( 'bwfan_ab_email_status', $email_status );
		}

		return true;
	}

	public function wfacp_assign_country( $country, $input ) {
		if ( is_null( WC()->session ) ) {
			return $country;
		}
		$restored_data = WC()->session->get( 'bwfan_data_for_js' );
		if ( empty( $restored_data ) ) {
			return $country;
		}
		if ( isset( $restored_data['fields'] ) && is_array( $restored_data['fields'] ) && isset( $restored_data['fields'][ $input ] ) ) {
			$country = $restored_data['fields'][ $input ];
		}

		return $country;
	}

}

if ( class_exists( 'BWFAN_Core' ) ) {
	BWFAN_Core::register( 'abandoned', 'BWFAN_Abandoned_Cart' );
}
