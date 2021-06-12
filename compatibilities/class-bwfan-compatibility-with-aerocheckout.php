<?php

class BWFAN_Compatibility_With_Aero_Checkout {

	public function __construct() {

		/**
		 * Checking AeroCheckout existence
		 */
		if ( false === class_exists( 'WFACP_Common' ) || false === method_exists( 'WFACP_Common', 'is_theme_builder' ) ) {
			return;
		}
		add_filter( 'bwfan_get_global_settings', [ $this, 'disable_abandonment' ], 99 );
	}

	public function disable_abandonment( $global_settings ) {
		if ( true === WFACP_Common::is_theme_builder() ) {
			$global_settings['bwfan_ab_enable'] = 0;
		}

		return $global_settings;
	}
}

new BWFAN_Compatibility_With_Aero_Checkout();
