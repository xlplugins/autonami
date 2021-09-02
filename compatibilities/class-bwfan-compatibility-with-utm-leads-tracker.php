<?php

/**
 * UTM Leads Tracker - XLPlugins
 * https://wordpress.org/plugins/utm-leads-tracker-lite/
 */
class BWFAN_Compatibility_With_UTM_Leads_Tracker {

	public function __construct() {
		/**
		 *  adding lead utm tracker data in the cart abandoned before saving
		 */
		add_filter( 'bwfan_ab_change_checkout_data_for_external_use', array( $this, 'bwfan_populate_utm_lead_data_cart' ), 99 );

		add_action( 'bwfan_ab_handle_checkout_data_externally', array( $this, 'bwfan_set_utm_lead_cookie' ), 9 );
	}


	/**
	 * @param $checkout_data
	 */
	public function bwfan_set_utm_lead_cookie( $checkout_data ) {
		if ( ! isset( $checkout_data['xlutm_data'] ) || empty( $checkout_data['xlutm_data'] ) ) {
			return;
		}
		$xlutm_data = $checkout_data['xlutm_data'];

		$cookie_val = json_encode( $xlutm_data );
		$secure     = is_ssl();
		setcookie( 'xlutm_params_utm', $cookie_val, time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, $secure, true );
	}

	/** passing xlutm_data before saving cart abandoned data */
	public function bwfan_populate_utm_lead_data_cart( $abandoned_data ) {

		if ( ! isset( $_COOKIE['xlutm_params_utm'] ) ) {
			return $abandoned_data;
		}

		$abandoned_data['xlutm_data'] = json_decode( stripslashes( $_COOKIE['xlutm_params_utm'] ), true );

		return $abandoned_data;
	}
}

/**
 * Checking Xlutm_Core existence
 */
if ( class_exists( 'Xlutm_Core' ) ) {
	new BWFAN_Compatibility_With_UTM_Leads_Tracker();
}

