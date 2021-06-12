<?php
//Updating contact and customer tables functions in background
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BWF_THRESHOLD_ORDERS', 0 ); //defining it more than 0 means you want the background to run only on "n" orders
define( 'BWF_ORDERS_PER_BATCH', 20 ); //defining it means how many orders to process per batch operation

/*** Updating customer tables ***/
if ( ! function_exists( 'bwf_create_update_contact_customer' ) ) {
	/**
	 *
	 * @return bool|string
	 */
	function bwf_create_update_contact_customer() {

		add_action( 'shutdown', [ WooFunnels_Dashboard::$classes['WooFunnels_DB_Updater'], 'capture_fatal_error' ] );
		/**
		 * get the offset and the threshold of max orders to process
		 */
		$offset = get_option( '_bwf_offset', 0 );

		$get_threshold_order = get_option( '_bwf_order_threshold', BWF_THRESHOLD_ORDERS );

		/**
		 * IF we do not find threshold, then query it
		 */

		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'woofunnels_handle_indexed_orders', 10, 2 );
		if ( 0 === $get_threshold_order ) {
			$all_order_ids = wc_get_orders( array(
				'return'      => 'ids',
				'numberposts' => '-1',
				'post_type'   => 'shop_order',
				'status'      => wc_get_is_paid_statuses(),
			) );

			$get_threshold_order = count( $all_order_ids );
			update_option( '_bwf_order_threshold', $get_threshold_order );
		}

		/**************** PROCESS BATCH STARTS ************/
		$numberposts = ( ( $offset > 0 ) && ( ( $get_threshold_order / $offset ) < 2 ) && ( ( $get_threshold_order % $offset ) < BWF_ORDERS_PER_BATCH ) ) ? ( $get_threshold_order % $offset ) : BWF_ORDERS_PER_BATCH;
		// Get n orders which are not indexed yet
		$order_ids = wc_get_orders( array(
			'return'      => 'ids',
			'numberposts' => $numberposts,
			'post_type'   => 'shop_order',
			'offset'      => null,
			'orderby'     => 'ID',
			'order'       => 'DESC',
			'status'      => wc_get_is_paid_statuses(),
		) );
		wp_reset_query();

		/**
		 * IF offset reached the threshold or no unindexed orders found, its time to terminate the batch process.
		 */
		if ( $offset >= $get_threshold_order || count( $order_ids ) < 1 ) {
			WooFunnels_Dashboard::$classes['BWF_Logger']->log( 'Terminated on ' . $get_threshold_order, 'woofunnels_indexing' );
			remove_action( 'shutdown', [ WooFunnels_Dashboard::$classes['WooFunnels_DB_Updater'], 'capture_fatal_error' ] );

			return false;
		}

		/**
		 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
		 */
		$retrieved_count = count( $order_ids );
		WooFunnels_Dashboard::$classes['BWF_Logger']->log( "These $retrieved_count orders are retrieved: " . implode( ',', $order_ids ), 'woofunnels_indexing' ); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r

		remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', 'woofunnels_handle_indexed_orders', 10, 2 );

		WooFunnels_DB_Updater::$indexing = true;
		foreach ( $order_ids as $order_id ) {
			WooFunnels_Dashboard::$classes['WooFunnels_DB_Updater']->set_order_id_in_process( $order_id );
			bwf_create_update_contact( $order_id, array(), 0, true );

			$offset ++;
			update_option( '_bwf_offset', $offset );
		}
		WooFunnels_DB_Updater::$indexing = null;

		wp_reset_query();
		/**************** PROCESS BATCH ENDS ************/

		WooFunnels_Dashboard::$classes['BWF_Logger']->log( "bwf_create_update_contact_customer function returned. Offset: $offset, Order Count: $get_threshold_order ", 'woofunnels_indexing' );
		remove_action( 'shutdown', [ WooFunnels_Dashboard::$classes['WooFunnels_DB_Updater'], 'capture_fatal_error' ] );

		return 'bwf_create_update_contact_customer';

	}
}

/**
 * Handle a custom '_woofunnel_cid' query var to get orders with the '_woofunnel_cid' meta.
 *
 * @param array $query - Args for WP_Query.
 * @param array $query_vars - Query vars from WC_Order_Query.
 *
 * @return array modified $query
 */
if ( ! function_exists( 'woofunnels_handle_indexed_orders' ) ) {
	function woofunnels_handle_indexed_orders( $query, $query_vars ) {
		if ( ! isset( $query_vars['_woofunnel_cid'] ) ) {
			$query['meta_query'][] = array(
				'key'      => '_woofunnel_cid',
				'compare'  => 'NOT EXISTS',
				'relation' => 'AND',
			);
		}
		$query['meta_query'][] = array(
			'key'      => '_billing_email',
			'value'    => '',
			'compare'  => '!=',
			'relation' => 'AND',
		);

		return $query;
	}
}

if ( ! function_exists( 'woofunnels_order_query_filter' ) ) {
	function woofunnels_order_query_filter( $query, $query_vars ) {
		if ( isset( $query_vars['_woofunnel_order_cid'] ) ) {
			$query['meta_query'][] = array(
				'key'     => '_woofunnel_cid',
				'compare' => '=',
				'value'   => $query_vars['_woofunnel_order_cid']
			);
		}

		return $query;
	}
}

/*
 * CONTACTS DATABASE STARTS
 */
if ( ! function_exists( 'bwf_contacts_v1_0_init_db_setup' ) ) {
	function bwf_contacts_v1_0_init_db_setup() {
		return 'bwf_contacts_v1_0_init_db_setup';
	}
}