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

if ( ! class_exists( 'BWF_WC_Compatibility' ) ) :

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
	class BWF_WC_Compatibility {

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
			} else {
				global $woocommerce;

				return $woocommerce->attribute_label( $label );
			}
		}

		public static function wc_attribute_taxonomy_name( $name ) {
			if ( self::is_wc_version_gte_2_1() ) {
				return wc_attribute_taxonomy_name( $name );
			} else {
				global $woocommerce;

				return $woocommerce->attribute_taxonomy_name( $name );
			}
		}

		public static function wc_get_attribute_taxonomies() {
			global $woocommerce;
			if ( self::is_wc_version_gte_2_1() ) {
				return wc_get_attribute_taxonomies();
			} else {
				return $woocommerce->get_attribute_taxonomies();
			}
		}

		public static function wc_placeholder_img_src() {
			if ( self::is_wc_version_gte_2_1() ) {
				return wc_placeholder_img_src();
			} else {
				return woocommerce_placeholder_img_src();
			}
		}

		/**
		 * @param WC_Product $product
		 *
		 * @return string
		 */
		public static function woocommerce_get_formatted_product_name( $product ) {

			if ( ! $product instanceof WC_Product ) {
				return __( 'No Title', 'woofunnels' );
			}
			if ( self::is_wc_version_gte_2_1() ) {
				return $product->get_formatted_name();
			} else {
				return woocommerce_get_formatted_product_name( $product );
			}
		}

		/**
		 * @param $order
		 * @param $item
		 *
		 * @return WC_Product
		 */
		public static function get_product_from_item( $order, $item ) {

			if ( self::is_wc_version_gte_3_0() ) {
				return $item->get_product();
			} else {
				return $order->get_product_from_item( $item );
			}
		}


		public static function get_short_description( $product ) {

			if ( $product === false ) {
				return '';
			}
			if ( self::is_wc_version_gte_3_0() ) {
				return apply_filters( 'woocommerce_short_description', $product->get_short_description() );
			} else {
				return apply_filters( 'woocommerce_short_description', $product->get_post_data()->post_excerpt );

			}
		}

		public static function get_productname_from_item( $order, $item ) {

			if ( self::is_wc_version_gte_3_0() ) {
				return $item->get_name();
			} else {
				return $item['name'];

			}
		}

		public static function get_qty_from_item( $order, $item ) {

			if ( self::is_wc_version_gte_3_0() ) {
				return $item->get_quantity();
			} else {
				return $item['qty'];

			}
		}


		public static function get_display_item_meta( $order, $item ) {
			if ( self::is_wc_version_gte_3_0() ) {
				wc_display_item_meta( $item );
			} else {
				$order->display_item_meta( $item );

			}
		}

		public static function get_display_item_downloads( $order, $item ) {
			if ( self::is_wc_version_gte_3_0() ) {
				wc_display_item_downloads( $item );
			} else {
				$order->display_item_downloads( $item );

			}
		}


		public static function get_purchase_note( $product ) {

			if ( self::is_wc_version_gte_3_0() ) {
				return $product ? $product->get_purchase_note() : '';
			} else {
				return $product ? get_post_meta( $product->id, '_purchase_note', true ) : '';

			}
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed|string
		 */
		public static function get_order_currency( $order ) {

			if ( self::is_wc_version_gte_3_0() ) {
				return $order ? $order->get_currency() : get_woocommerce_currency();
			} else {
				return $order ? $order->get_order_currency() : get_woocommerce_currency();

			}
		}

		public static function get_payment_gateway_from_order( $order ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_payment_method();
			} else {
				return $order->payment_method;

			}
		}

		public static function get_item_subtotal( $order, $item ) {
			if ( self::is_wc_version_gte_3_0() ) {
				return $item->get_subtotal();
			} else {
				return $order->get_line_subtotal( $item );

			}
		}

		public static function get_shipping_country_from_order( $order ) {

			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_shipping_country();
			} else {
				return get_post_meta( $order->id, '_shipping_country', true );

			}

		}


		public static function get_billing_country_from_order( $order ) {

			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_country();
			} else {
				return get_post_meta( $order->id, '_billing_country', true );

			}

		}

		public static function get_order_id( $order ) {

			if ( ! $order instanceof WC_Order ) {
				return $order;
			}
			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_id();
			} else {
				return $order->id;

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
			} else {
				return $order->billing_address_1;

			}

		}


		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_order_data( $order, $key ) {

			if ( self::is_wc_version_gte_3_0() ) {

				if ( method_exists( $order, 'get_' . $key ) ) {

					return call_user_func( array( $order, 'get_' . $key ) );
				} else {
					$maybe_meta = $order->get_meta( $key, true );
					if ( ! empty( $maybe_meta ) ) {
						return $maybe_meta;

					}

					return get_post_meta( self::get_order_id( $order ), $key, true );
				}
			} else {

				$value = $order->{$key};
				if ( $value && ! empty( $value ) ) {
					return $value;
				} else {
					return get_post_meta( self::get_order_id( $order ), $key, true );
				}
			}

			return __return_empty_string();

		}


		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_billing_first_name( $order ) {

			if ( self::is_wc_version_gte_3_0() ) {
				return $order->get_billing_first_name();
			} else {
				return $order->billing_first_name;

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
			} else {
				return $order->billing_last_name;

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
			} else {
				return $order->billing_address_2;

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
			} else {
				return $order->shipping_address_1;

			}

		}

		/**
		 * Returns true if the installed version of WooCommerce is 2.6 or greater
		 *
		 * @return boolean true if the installed version of WooCommerce is 2.1 or greater
		 * @since 1.0
		 */
		public static function is_wc_version_gte_3_7() {

			return version_compare( self::get_wc_version(), '3.7.0', 'ge' );
		}

		/**
		 * @param WC_Order $order
		 *
		 * @return mixed
		 */
		public static function get_used_coupons( $order ) {
			if ( self::is_wc_version_gte_3_7() ) {
				return $order->get_coupon_codes();
			} else {
				return $order->get_used_coupons();

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
			} else {
				return $order->get_total_shipping();

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
			} else {
				return $order->shipping_address_2;

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
			} else {
				return $order->order_date;

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
			} else {
				return $order->payment_method_title;

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
			} else {
				return $order->customer_ip_address;
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
			} else {
				return $order->post->post_excerpt;
			}
		}

		/**
		 * @param $date
		 * @param string $format
		 *
		 * @return string
		 */
		public static function get_formatted_date( $date, $format = '' ) {
			if ( empty( $format ) ) {
				$format = get_option( 'date_format' );
			}

			if ( self::is_wc_version_gte_3_0() ) {
				return wc_format_datetime( $date, $format );
			} else {
				return date_i18n( $format, strtotime( $date ) );
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
			} else {
				global $woocommerce;

				if ( 'error' === $notice_type ) {
					$woocommerce->add_error( $message );
				} else {
					$woocommerce->add_message( $message );
				}
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
			} else {
				global $woocommerce;
				$woocommerce->show_messages();
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
			} else {
				global $woocommerce;
				$woocommerce->add_inline_js( $code );
			}
		}

		/**
		 * Sets WooCommerce messages
		 *
		 * @since 1.0
		 */
		public static function set_messages() {

			if ( ! self::is_wc_version_gte_2_1() ) {
				global $woocommerce;
				$woocommerce->set_messages();
			}
		}

		/**
		 * Returns a new instance of the woocommerce logger
		 *
		 * @return WC_Logger logger
		 * @since 1.0
		 */
		public static function new_wc_logger() {

			if ( self::is_wc_version_gte_2_1() ) {
				return new WC_Logger();
			} else {
				global $woocommerce;

				return $woocommerce->logger();
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
			} else {
				return woocommerce_format_total( $number );
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
			} else {
				global $woocommerce;

				if ( 'error' === $notice_type ) {
					return $woocommerce->error_count();
				} else {
					return $woocommerce->message_count();
				}
			}
		}

		/**
		 * Compatibility function to use the new WC_Admin_Meta_Boxes class for the save_errors() function
		 *
		 * @return old save_errors function or new class
		 * @since 1.0-1
		 */
		public static function save_errors() {

			if ( self::is_wc_version_gte_2_1() ) {
				WC_Admin_Meta_Boxes::save_errors();
			} else {
				woocommerce_meta_boxes_save_errors();
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
			} else {
				global $woocommerce;

				return $woocommerce;
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
			} else {
				return class_exists( 'Woocommerce' );
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
		public static function is_wc_version_gte_2_6() {

			return version_compare( self::get_wc_version(), '2.6.0', 'ge' );
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
		 * @param WC_Order_Item_Shipping $method
		 *
		 * @return string
		 */
		public static function get_method_id( $method ) {

			$method_id = $method->get_method_id();
			if ( $method_id !== '' ) {
				$method_exp = explode( ':', $method_id );

				return $method_exp[0];

			}

			return '';
		}

		/**
		 * @param WC_Order_Item_Shipping $method
		 *
		 * @return string
		 */
		public static function get_instance_id( $method ) {

			$method_id = $method->get_method_id();

			if ( $method_id !== '' ) {

				$method_exp = explode( ':', $method_id );

				if ( is_array( $method_exp ) ) {

					if ( 2 === count( $method_exp ) ) {
						return $method_exp[1];
					} else {
						if ( is_callable( array( $method, 'get_instance_id' ) ) ) {
							return $method->get_instance_id();
						} else {
							return '';
						}
					}
				}
			}

			return $method_id;
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

		public static function display_prices_including_tax() {
			if ( version_compare( self::get_wc_version(), '3.3.0', 'ge' ) ) {
				return 'incl' === get_option( 'woocommerce_tax_display_cart' );
			}

		}

	}

endif; // Class exists check
