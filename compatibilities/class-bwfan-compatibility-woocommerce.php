<?php

/**
 * WooCommerce Plugin Compatibility
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.skyverge.com
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2013, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'BWFAN_Woocommerce_Compatibility' ) ) :

	/**
	 * WooCommerce Compatibility Utility Class
	 *
	 * The unfortunate purpose of this class is to provide a single point of
	 * compatibility functions for dealing with supporting multiple versions
	 * of WooCommerce.
	 *
	 * The recommended procedure is to rename this file/class, replacing "my plugin"
	 * with the particular plugin name, so as to avoid clashes between plugins.
	 * Over time we expect to remove methods from this class, using the current
	 * ones directly, as support for older versions of WooCommerce is dropped.
	 *
	 * Current Compatibility: 2.0.x - 2.1
	 *
	 * @version 1.0
	 */
	class BWFAN_Woocommerce_Compatibility {

		/**
		 * Compatibility function for outputting a woocommerce attribute label
		 *
		 * @param string $label the label to display
		 *
		 * @return string the label to display
		 * @since 1.0
		 *
		 */
		public static function wc_attribute_label( $label ) {
			if ( self::is_wc_version_gte_2_1() ) {
				return wc_attribute_label( $label );
			}
		}

		/**
		 * @param $name
		 *
		 * @return string
		 */
		public static function wc_attribute_taxonomy_name( $name ) {
			if ( self::is_wc_version_gte_2_1() ) {
				return wc_attribute_taxonomy_name( $name );
			}
		}

		/**
		 * @return array
		 */
		public static function wc_get_attribute_taxonomies() {
			if ( self::is_wc_version_gte_2_1() ) {
				return wc_get_attribute_taxonomies();
			}
		}

		/**
		 * @return string
		 */
		public static function wc_placeholder_img_src() {
			if ( self::is_wc_version_gte_2_1() ) {
				return wc_placeholder_img_src();
			}
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		public static function woocommerce_get_formatted_product_name( $product ) {
			if ( self::is_wc_version_gte_2_1() ) {
				return $product->get_formatted_name();
			}
		}

		/**
		 * @param WC_Order $order
		 * @param WC_Order_Item $item
		 *
		 * @return WC_Product|bool|void
		 */
		public static function get_product_from_item( $order, $item ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $item->get_product();
			}
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return mixed|string|void
		 */
		public static function get_short_description( $product ) {
			if ( false === $product ) {
				return '';
			}
			if ( self::is_wc_version_gte_3_0() ) {
				return apply_filters( 'woocommerce_short_description', $product->get_short_description() );
			}
		}

		/**
		 * @param WC_Order $order
		 * @param WC_Order_Item $item
		 *
		 * @return mixed
		 */
		public static function get_productname_from_item( $order, $item ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $item->get_name();
			}
		}

		/**
		 * @param WC_Order $order
		 * @param WC_Order_Item $item
		 *
		 * @return mixed
		 */
		public static function get_qty_from_item( $order, $item ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $item->get_quantity();
			}
		}

		/**
		 * @param WC_Order $order
		 * @param $item
		 *
		 * @return string|void
		 */
		public static function get_display_item_meta( $order, $item ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return wc_display_item_meta( $item );
			}
		}

		/**
		 * @param WC_Order $order
		 * @param $item
		 *
		 * @return string|void
		 */
		public static function get_display_item_downloads( $order, $item ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return wc_display_item_downloads( $item );
			}
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return mixed|string
		 */
		public static function get_purchase_note( $product ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $product ? $product->get_purchase_note() : '';
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_payment_gateway_from_order( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_payment_method();
			}
		}

		/**
		 * @param WC_Order $order
		 * @param WC_Order_Item $item
		 *
		 * @return mixed
		 */
		public static function get_item_subtotal( $order, $item ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $item->get_subtotal();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_shipping_country_from_order( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_shipping_country();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_billing_country_from_order( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_country();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_billing_email( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_email();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_id( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_id();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_billing_1( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_address_1();
			}
		}

		/**
		 * @param WC_Order $order
		 * @param $key
		 *
		 * @return mixed
		 */
		public static function get_order_data( $order, $key ) {
			if ( self::is_wc_version_gte_3_0() ) {
				if ( method_exists( $order, 'get_' . $key ) ) {
					return call_user_func( array( $order, 'get_' . $key ) );
				} elseif ( method_exists( $order, 'get' . $key ) ) {
					return call_user_func( array( $order, 'get' . $key ) );
				} else {
					$data = $order->get_meta( $key );
					if ( ! empty( $data ) ) {
						return $data;
					}
					$data = $order->get_meta( '_' . $key );
					if ( ! empty( $data ) ) {
						return $data;
					}

					return get_post_meta( self::get_order_id( $order ), $key, true );
				}
			}
		}

		/**
		 * get order meta: currently done via get_post_meta, will change later to WC func
		 *
		 * @param $order_id
		 * @param $key
		 *
		 * @return mixed
		 */
		public static function get_order_meta( $order_id, $key ) {
			return get_post_meta( $order_id, $key, true );
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_billing_first_name( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_first_name();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_billing_last_name( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_last_name();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_status( $order ) {
			$status = $order->get_status();
			if ( strpos( $status, 'wc-' ) === false ) {
				return 'wc-' . $status;
			} else {
				return $status;
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_billing_2( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_address_2();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_shipping_1( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_shipping_address_1();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_shipping_total( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_shipping_total();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_shipping_2( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_shipping_address_2();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_billing_city( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_city();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_billing_state( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_state();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_billing_postcode( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_postcode();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_shipping_city( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_shipping_city();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_shipping_state( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_shipping_state();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_shipping_postcode( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_shipping_postcode();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_date( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_date_created();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_payment_method( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_payment_method_title();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_customer_ip_address( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_customer_ip_address();
			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_customer_note( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_customer_note();
			}
		}

		/**
		 * Compatibility function to add and store a notice
		 *
		 * @param string $message The text to display in the notice.
		 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
		 *
		 * @since 1.0
		 *
		 */
		public static function wc_add_notice( $message, $notice_type = 'success' ) {
			if ( self::is_wc_version_gte_2_1() ) {
				wc_add_notice( $message, $notice_type );
			}
		}

		/**
		 * Prints messages and errors which are stored in the session, then clears them.
		 *
		 * @since 1.0
		 */
		public static function wc_print_notices() {
			if ( self::is_wc_version_gte_2_1() ) {
				wc_print_notices();
			}
		}

		/**
		 * Compatibility function to queue some JavaScript code to be output in the footer.
		 *
		 * @param string $code javascript
		 *
		 * @since 1.0
		 *
		 */
		public static function wc_enqueue_js( $code ) {
			if ( self::is_wc_version_gte_2_1() ) {
				wc_enqueue_js( $code );
			}
		}

		/**
		 * Sets WooCommerce messages
		 *
		 * @since 1.0
		 */
		public static function set_messages() {
			global $woocommerce;

			if ( false === self::is_wc_version_gte_2_1() ) {
				$woocommerce->set_messages();
			}
		}

		/**
		 * Returns a new instance of the woocommerce logger
		 *
		 * @return object logger
		 * @since 1.0
		 */
		public static function new_wc_logger() {
			if ( self::is_wc_version_gte_2_1() ) {
				return new WC_Logger();
			}
		}

		/**
		 * Format decimal numbers ready for DB storage
		 *
		 * Sanitize, remove locale formatting, and optionally round + trim off zeros
		 *
		 * @param float|string $number Expects either a float or a string with a decimal separator only (no thousands)
		 * @param mixed $dp number of decimal points to use, blank to use woocommerce_price_num_decimals, or false to avoid all rounding.
		 * @param boolean $trim_zeros from end of string
		 *
		 * @return string
		 * @since 1.0
		 *
		 */
		public static function wc_format_decimal( $number, $dp = false, $trim_zeros = false ) {
			if ( self::is_wc_version_gte_2_1() ) {
				return wc_format_decimal( $number, $dp, $trim_zeros );
			}
		}

		/**
		 * Get the count of notices added, either for all notices (default) or for one particular notice type specified
		 * by $notice_type.
		 *
		 * @param string $notice_type The name of the notice type - either error, success or notice. [optional]
		 *
		 * @return int the notice count
		 * @since 1.0
		 *
		 */
		public static function wc_notice_count( $notice_type = '' ) {
			if ( self::is_wc_version_gte_2_1() ) {
				return wc_notice_count( $notice_type );
			}
		}

		/**
		 * Compatibility function to use the new WC_Admin_Meta_Boxes class for the save_errors() function
		 *
		 * @since 1.0-1
		 */
		public static function save_errors() {
			if ( self::is_wc_version_gte_2_1() ) {
				WC_Admin_Meta_Boxes::save_errors();
			}
		}

		/**
		 * Compatibility function to get the version of the currently installed WooCommerce
		 *
		 * @return string woocommerce version number or null
		 * @since 1.0
		 */
		public static function get_wc_version() {
			// WOOCOMMERCE_VERSION is now WC_VERSION, though WOOCOMMERCE_VERSION is still available for backwards compatibility, we'll disregard it on 2.1+
			if ( defined( 'WC_VERSION' ) && WC_VERSION ) {
				return WC_VERSION;
			}
			if ( defined( 'WOOCOMMERCE_VERSION' ) && WOOCOMMERCE_VERSION ) {
				return WOOCOMMERCE_VERSION;
			}

			return null;
		}

		/**
		 * Returns the WooCommerce instance
		 *
		 * @return WooCommerce woocommerce instance
		 * @since 1.0
		 */
		public static function WC() {
			if ( self::is_wc_version_gte_2_1() ) {
				return WC();
			}
		}

		/**
		 * Returns true if the WooCommerce plugin is loaded
		 *
		 * @return boolean true if WooCommerce is loaded
		 * @since 1.0
		 */
		public static function is_wc_loaded() {
			if ( self::is_wc_version_gte_2_1() ) {
				return class_exists( 'WooCommerce' );
			}
		}

		/**
		 * Returns true if the installed version of WooCommerce is 2.1 or greater
		 *
		 * @return boolean true if the installed version of WooCommerce is 2.1 or greater
		 * @since 1.0
		 */
		public static function is_wc_version_gte_2_1() {
			// can't use gte 2.1 at the moment because 2.1-BETA < 2.1
			return self::is_wc_version_gt( '2.0.20' );
		}

		/**
		 * Returns true if the installed version of WooCommerce is 2.6 or greater
		 *
		 * @return boolean true if the installed version of WooCommerce is 2.1 or greater
		 * @since 1.0
		 */
		public static function is_wc_version_gte_3_0() {
			return version_compare( self::get_wc_version(), '3.0.0', 'ge' );
		}

		/**
		 * Returns true if the installed version of WooCommerce is greater than $version
		 *
		 * @param string $version the version to compare
		 *
		 * @return boolean true if the installed version of WooCommerce is > $version
		 * @since 1.0
		 *
		 */
		public static function is_wc_version_gt( $version ) {
			return self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}

		/**
		 * @param $item
		 * @param $key
		 *
		 * @return mixed
		 */
		public static function get_item_data( $item, $key ) {
			if ( self::is_wc_version_gte_3_0() ) {

				if ( method_exists( $item, 'get_' . $key ) ) {

					return call_user_func( array( $item, 'get_' . $key ) );
				} else {
					return get_post_meta( self::get_order_id( $item ), $key, true );
				}
			}
		}

		public static function get_product_variation_length( $obj ) {
			if ( self::is_wc_version_gte_3_0() ) {
				$length = $obj->get_length() ? $obj->get_length() : '';
			}

			return $length;
		}

		public static function get_product_variation_width( $obj ) {
			if ( self::is_wc_version_gte_3_0() ) {
				$width = $obj->get_width() ? $obj->get_width() : '';
			}

			return $width;
		}

		public static function get_product_variation_height( $obj ) {
			if ( self::is_wc_version_gte_3_0() ) {
				$height = $obj->get_height() ? $obj->get_height() : '';
			}

			return $height;
		}

		public static function get_product_variation_weight( $obj ) {
			if ( self::is_wc_version_gte_3_0() ) {
				$weight = $obj->get_weight() ? $obj->get_weight() : '';
			}

			return $weight;
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return mixed
		 */
		public static function get_parent_id( $product ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $product->get_parent_id();
			}
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return mixed
		 */
		static function is_variation( $product ) {
			return $product->is_type( [ 'variation', 'subscription_variation' ] );
		}
	}

endif; // Class exists check
