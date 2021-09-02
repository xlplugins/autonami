<?php

/**
 * WC Dependency Checker
 */
class BWFAN_Plugin_Dependency {

	private static $active_plugins;

	public static function init() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}

	public static function woocommerce_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
	}

	public static function edd_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			return true;
		}

		return in_array( 'easy-digital-downloads/easy-digital-downloads.php', self::$active_plugins, true ) || array_key_exists( 'easy-digital-downloads/easy-digital-downloads.php', self::$active_plugins );
	}

	public static function woocommerce_subscriptions_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'WC_Subscriptions' ) ) {
			return true;
		}

		return in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce-subscriptions/woocommerce-subscriptions.php', self::$active_plugins );
	}

	public static function woocommerce_membership_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'woocommerce-memberships/woocommerce-memberships.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce-memberships/woocommerce-memberships.php', self::$active_plugins );
	}

	/** checking paid membership pro is active
	 *
	 * @return bool
	 */
	public static function paid_membership_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'paid-memberships-pro/paid-memberships-pro.php', self::$active_plugins, true ) || array_key_exists( 'paid-memberships-pro/paid-memberships-pro.php', self::$active_plugins );
	}


	public static function woofunnels_upstroke_one_click_upsell() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'WFOCU_Core' ) ) {
			return true;
		}

		return in_array( 'woofunnels-upstroke-one-click-upsell/woofunnels-upstroke-one-click-upsell.php', self::$active_plugins, true ) || array_key_exists( 'woofunnels-upstroke-one-click-upsell/woofunnels-upstroke-one-click-upsell.php', self::$active_plugins );
	}

	public static function autonami_pro_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'BWFAN_Pro' ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$reflector = new \ReflectionClass( 'BWFAN_Pro' );
			$pro_data  = get_plugin_data( $reflector->getFileName() );

			return is_array( $pro_data ) && isset( $pro_data['Version'] ) && version_compare( $pro_data['Version'], '1.9' ) > 0;
		}

		return in_array( 'autonami-automations-pro/autonami-automations-pro.php', self::$active_plugins, true ) || array_key_exists( 'autonami-automations-pro/autonami-automations-pro.php', self::$active_plugins );
	}

	public static function autonami_connector_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'WFCO_Autonami_Connectors_Core' ) ) {
			return true;
		}

		return in_array( 'autonami-automations-connectors/autonami-automations-connectors.php', self::$active_plugins, true ) || array_key_exists( 'autonami-automations-connectors/autonami-automations-connectors.php', self::$active_plugins );
	}

	public static function affiliatewp_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'Affiliate_WP' ) ) {
			return true;
		}

		return in_array( 'affiliate-wp/affiliate-wp.php', self::$active_plugins, true ) || array_key_exists( 'affiliate-wp/affiliate-wp.php', self::$active_plugins );
	}

	public static function gforms_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'GFForms' ) ) {
			return true;
		}

		return in_array( 'gravityforms/gravityforms.php', self::$active_plugins, true ) || array_key_exists( 'gravityforms/gravityforms.php', self::$active_plugins );
	}

	public static function elementorpro_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			return true;
		}

		return in_array( 'elementor-pro/elementor-pro.php', self::$active_plugins, true ) || array_key_exists( 'elementor-pro/elementor-pro.php', self::$active_plugins );
	}

	public static function learndash_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( defined( 'LEARNDASH_VERSION' ) ) {
			return true;
		}

		return in_array( 'sfwd-lms/sfwd_lms.php', self::$active_plugins, true ) || array_key_exists( 'sfwd-lms/sfwd_lms.php', self::$active_plugins );
	}

	public static function wpforms_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( defined( 'WPFORMS_VERSION' ) ) {
			return true;
		}

		return in_array( 'wpforms-lite/wpforms.php', self::$active_plugins, true ) || array_key_exists( 'wpforms-lite/wpforms.php', self::$active_plugins );
	}

	public static function cf7_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( defined( 'WPCF7_VERSION' ) ) {
			return true;
		}

		return in_array( 'contact-form-7/wp-contact-form-7.php', self::$active_plugins, true ) || array_key_exists( 'contact-form-7/wp-contact-form-7.php', self::$active_plugins );
	}

	public static function tve_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'thrive-leads/thrive-leads.php', self::$active_plugins, true ) || array_key_exists( 'thrive-leads/thrive-leads.php', self::$active_plugins );
	}

	public static function translatepress_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'translatepress-multilingual/index.php', self::$active_plugins, true ) || array_key_exists( 'translatepress-multilingual/index.php', self::$active_plugins );
	}

	/**
	 * Checking if tutorlms plugin active
	 * @return bool
	 */
	public static function tutorlms_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}
		if ( class_exists( '\TUTOR\Tutor' ) ) {
			return true;
		}

		return in_array( 'tutor/tutor.php', self::$active_plugins, true ) || array_key_exists( 'tutor/tutor.php', self::$active_plugins );
	}

	/**
	 * Checking if memberpress plugin active
	 * @return bool
	 */
	public static function mepr_active_check() {
		if ( ! self::$active_plugins ) {
			self::init();
		}

		if ( class_exists( 'MeprCtrlFactory' ) ) {
			return true;
		}

		return in_array( 'memberpress/memberpress.php', self::$active_plugins, true ) || array_key_exists( 'memberpress/memberpress.php', self::$active_plugins );
	}

}
