<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC Detection
 */
if ( ! function_exists( 'bwfan_is_woocommerce_active' ) ) {
	function bwfan_is_woocommerce_active() {
		return BWFAN_Plugin_Dependency::woocommerce_active_check();
	}
}

/**
 * EDD Detection
 */
if ( ! function_exists( 'bwfan_is_edd_active' ) ) {
	function bwfan_is_edd_active() {
		return BWFAN_Plugin_Dependency::edd_active_check();
	}
}

/**
 * WC Subscriptions Detection
 */
if ( ! function_exists( 'bwfan_is_woocommerce_subscriptions_active' ) ) {
	function bwfan_is_woocommerce_subscriptions_active() {
		return BWFAN_Plugin_Dependency::woocommerce_subscriptions_active_check();
	}
}

/**
 * WC Membership Detection
 */
if ( ! function_exists( 'bwfan_is_woocommerce_membership_active' ) ) {
	function bwfan_is_woocommerce_membership_active() {
		return BWFAN_Plugin_Dependency::woocommerce_membership_active_check();
	}
}

/**
 * woofunnels upstroke Detection
 */
if ( ! function_exists( 'bwfan_is_woofunnels_upstroke_active' ) ) {
	function bwfan_is_woofunnels_upstroke_active() {
		return BWFAN_Plugin_Dependency::woofunnels_upstroke_one_click_upsell();
	}
}

/**
 * autonami pro Detection
 */
if ( ! function_exists( 'bwfan_is_autonami_pro_active' ) ) {
	function bwfan_is_autonami_pro_active() {
		return BWFAN_Plugin_Dependency::autonami_pro_active_check();
	}
}

/**
 * Autonami Pro: is 2.0?
 */
if ( ! function_exists( 'bwfan_is_autonami_pro_old' ) ) {
	function bwfan_is_autonami_pro_old() {
		if ( ! class_exists( 'BWFAN_Pro' ) ) {
			/** If pro doesn't exists, then return false */
			return false;
		}

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$reflector = new \ReflectionClass( 'BWFAN_Pro' );
		$pro_data  = get_plugin_data( $reflector->getFileName() );

		return ! is_array( $pro_data ) || ! isset( $pro_data['Version'] ) || version_compare( $pro_data['Version'], '1.9' ) <= 0;
	}
}

/**
 * autonami connector Detection
 */
if ( ! function_exists( 'bwfan_is_autonami_connector_active' ) ) {
	function bwfan_is_autonami_connector_active() {
		return BWFAN_Plugin_Dependency::autonami_connector_active_check();
	}
}

/**
 * Affiliate wp Detection
 */
if ( ! function_exists( 'bwfan_is_affiliatewp_active' ) ) {
	function bwfan_is_affiliatewp_active() {
		return BWFAN_Plugin_Dependency::affiliatewp_active_check();
	}
}

/**
 * Gravity Forms Detection
 */
if ( ! function_exists( 'bwfan_is_gforms_active' ) ) {
	function bwfan_is_gforms_active() {
		return BWFAN_Plugin_Dependency::gforms_active_check();
	}
}

/**
 * Elementor Pro Detection
 */
if ( ! function_exists( 'bwfan_is_elementorpro_active' ) ) {
	function bwfan_is_elementorpro_active() {
		return BWFAN_Plugin_Dependency::elementorpro_active_check();
	}
}

/**
 * Learndash Detection
 */
if ( ! function_exists( 'bwfan_is_learndash_active' ) ) {
	function bwfan_is_learndash_active() {
		return BWFAN_Plugin_Dependency::learndash_active_check();
	}
}


/**
 * WP Forms Detection
 */
if ( ! function_exists( 'bwfan_is_wpforms_active' ) ) {
	function bwfan_is_wpforms_active() {
		return BWFAN_Plugin_Dependency::wpforms_active_check();
	}
}

/**
 * Contact Form 7 Detection
 */
if ( ! function_exists( 'bwfan_is_cf7_active' ) ) {
	function bwfan_is_cf7_active() {
		return BWFAN_Plugin_Dependency::cf7_active_check();
	}
}

/**
 * Paid Membership Pro Detection
 */
if ( ! function_exists( 'bwfan_is_paid_membership_pro_active' ) ) {
	function bwfan_is_paid_membership_pro_active() {
		return BWFAN_Plugin_Dependency::paid_membership_active_check();
	}
}

/**
 * Thrive Lead Form Detection
 */
if ( ! function_exists( 'bwfan_is_tve_active' ) ) {
	function bwfan_is_tve_active() {
		return BWFAN_Plugin_Dependency::tve_active_check();
	}
}

/**
 * TranslatePress Detection
 */
if ( ! function_exists( 'bwfan_is_translatepress_active' ) ) {
	function bwfan_is_translatepress_active() {
		return BWFAN_Plugin_Dependency::translatepress_active_check();
	}
}

/**
 * TutorLMS Detection
 */
if ( ! function_exists( 'bwfan_is_tutorlms_active' ) ) {
	function bwfan_is_tutorlms_active() {
		return BWFAN_Plugin_Dependency::tutorlms_active_check();
	}
}

/**
 * MemberPress Detection
 */
if ( ! function_exists( 'bwfan_is_mepr_active' ) ) {
	function bwfan_is_mepr_active() {
		return BWFAN_Plugin_Dependency::mepr_active_check();
	}
}

