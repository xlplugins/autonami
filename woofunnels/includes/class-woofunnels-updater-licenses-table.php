<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class WooFunnels_Updater_Licenses_Table
 * @package WooFunnels
 */
class WooFunnels_Updater_Licenses_Table extends WP_List_Table {

	public $per_page = 100;
	public $data;

	/**
	 * Constructor.
	 * @since  1.0.0
	 */
	public function __construct( $args = array() ) {
		global $status, $page;

		parent::__construct( array(
			'singular' => 'license', //singular name of the listed records
			'plural'   => 'licenses', //plural name of the listed records
			'ajax'     => false,        //does this table support ajax?
		) );
		$status = 'all';

		$page = $this->get_pagenum();

		$this->data = array();

		// Make sure this file is loaded, so we have access to plugins_api(), etc.
		require_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );

		parent::__construct( $args );
	}

	// End __construct()

	/**
	 * Text to display if no items are present.
	 * @return  void
	 * @since  1.0.0
	 */
	public function no_items() {
		echo wpautop( __( 'No plugins available for activation.', 'woofunnels' ) );
	}

	// End no_items(0)

	/**
	 * The content of each column.
	 *
	 * @param array $item The current item in the list.
	 * @param string $column_name The key of the current column.
	 *
	 * @return string              Output for the current column.
	 * @since  1.0.0
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'plugin':
			case 'product_status':
			case 'product_version':
			case 'license_expiry':
				return $item[ $column_name ];
				break;
		}
	}

	// End column_default()

	/**
	 * Content for the "product_name" column.
	 *
	 * @param array $item The current item.
	 *
	 * @return string       The content of this column.
	 * @since  1.0.0
	 */
	public function column_plugin( $item ) {
		return wpautop( '<strong>' . $item['plugin'] . '</strong>' );
	}

	// End get_sortable_columns()

	/**
	 * Content for the "product_version" column.
	 *
	 * @param array $item The current item.
	 *
	 * @return string       The content of this column.
	 * @since  1.0.0
	 */
	public function column_product_version( $item ) {

		if ( isset( $item['latest_version'], $item['product_version'] ) && version_compare( $item['product_version'], $item['latest_version'], '<' ) ) {
			$version_text = '<strong>' . $item['product_version'] . '<span class="update-available"> - ' . sprintf( __( 'version %1$s available', 'woofunnels' ), esc_html( $item['latest_version'] ) ) . '</span></strong>' . "\n";
		} else {
			$version_text = '<strong class="latest-version">' . $item['product_version'] . '</strong>' . "\n";
		}

		return wpautop( $version_text );
	}
	// End get_columns()

	/**
	 * Content for the "status" column.
	 *
	 * @param array $item The current item.
	 *
	 * @return string       The content of this column.
	 * @since  1.0.0
	 */
	public function column_product_status( $item ) {
		$response   = '';
		$input_text = '<input name="license_keys[' . esc_attr( $item['product_file_path'] ) . '][key]" id="license_keys-' . esc_attr( $item['product_file_path'] ) . '" type="text" size="37" aria-required="true" value="' . $item['existing_key']['key'] . '" placeholder="' . esc_attr__( '
Place your license key here', 'woofunnels' ) . '" />' . "\n";

		if ( $this->is_license_expire( $item ) ) {
			$response_notice = '';
			$response_notice .= $input_text;
			$response_notice .= '<span class="below_input_message">' . sprintf( __( 'This license has expired. Login to <a target="_blank" href="%s">Your Account</a> and renew your license.', 'woofunnels' ), 'https://account.buildwoofunnels.com/' ) . '</span>';
			$response        .= apply_filters( 'woofunnels_license_notice_bewlow_field', $response_notice, $item );
		} elseif ( 'active' === $item['product_status'] ) {
			if ( empty( $item['_data']['activated'] ) ) {
				$response_notice = '';
				$response_notice .= $input_text;
				$response_notice .= '<span class="below_input_message">' . sprintf( __( 'This license is no longer valid. Login to <a target="_blank" href="%s">Your Account</a> and renew your license.', 'woofunnels' ), 'https://account.buildwoofunnels.com/' ) . '</span>';
				$response        .= apply_filters( 'woofunnels_license_notice_bewlow_field', $response_notice, $item );

			} else {
				$deactivate_url = wp_nonce_url( add_query_arg( 'action', 'woofunnels_deactivate-product', add_query_arg( 'filepath', $item['product_file_path'], add_query_arg( 'page', filter_input(INPUT_GET,'page', FILTER_SANITIZE_STRING), add_query_arg( 'tab', 'licenses' ), network_admin_url( 'admin.php' ) ) ) ), 'bwf-deactivate-product' );
				if ( isset( $item['existing_key'] ) && isset( $item['existing_key']['key'] ) ) {
					$license_obj = WooFunnels_Licenses::get_instance();
					$license_key = $license_obj->get_secret_license_key( $item['existing_key']['key'] );
					$response    = $license_key . '<br/>';
				}
				$response .= '<a href="' . esc_url( $deactivate_url ) . '">' . __( 'Deactivate', 'woofunnels' ) . '</a>' . "\n";
			}
		} else {
			$response = $input_text;
		}

		return $response;
	}

	public function is_license_expire( $item ) {
		if ( isset( $item['existing_key']['expires'] ) && $item['existing_key']['expires'] !== '' && ( strtotime( $item['existing_key']['expires'] ) < current_time( 'timestamp' ) ) ) {
			return true;
		}

		if ( isset( $item['_data']['expired'] ) && ! empty( $item['_data']['expired'] ) ) {
			return true;
		}

		return false;
	}

	public function column_product_expiry( $item ) {
		if ( $this->is_license_expire( $item ) ) {
			$date_string = __( 'Expire', 'woofunnels' );
			try {
				$date        = new DateTime( $item['existing_key']['expires'] );
				$date_string = $date->format( get_option( 'date_format' ) );
			} catch ( Exception $e ) {

			}

			return $date_string;
		} else {
			if ( '' === $item['existing_key']['key'] ) {
				return __( 'N/A', 'woofunnels-upstroke-one-click-upsell' );
			} elseif ( '' === $item['existing_key']['expires'] ) {
				return __( 'Lifetime', 'woofunnels-upstroke-one-click-upsell' );
			} else {
				$date        = new DateTime( $item['existing_key']['expires'] );
				$date_string = $date->format( get_option( 'date_format' ) );

				return $date_string;
			}
		}
	}

	/**
	 * Retrieve an array of possible bulk actions.
	 * @return array
	 * @since  1.0.0
	 */
	public function get_bulk_actions() {
		$actions = array();

		return $actions;    // End column_status()
	}

	/**
	 * Prepare an array of items to be listed.
	 * @since  1.0.0
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$total_items = is_array( $this->data ) ? count( $this->data ) : 0;

		$this->set_pagination_args( array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $total_items,                   //WE have to determine how many items to show on a page
		) );
		$this->items = $this->data;
	}

	public function get_columns() {
		$columns = array(
			'plugin'          => __( 'Plugin', 'woofunnels' ),
			'product_version' => __( 'Version', 'woofunnels' ),
			'product_status'  => __( 'Key', 'woofunnels' ),
			'product_expiry'  => __( 'Renews On', 'woofunnels' ),
		);

		return $columns;
	}
	// End get_bulk_actions()

	public function get_sortable_columns() {
		return array();
	}

}
