<?php

/**
 * Class WFCO_AJAX_Controller
 * Handles All the request came from Backend
 */
class WFCO_AJAX_Controller {

	public static function init() {
		/**
		 * Backend AJAX actions
		 */
		if ( is_admin() ) {
			self::handle_admin_ajax();
		}
	}

	public static function handle_admin_ajax() {
		add_action( 'wp_ajax_bwf_save_connector', array( __CLASS__, 'save_connector' ) );
		add_action( 'wp_ajax_bwf_sync_connector', array( __CLASS__, 'sync_connector' ) );
		add_action( 'wp_ajax_bwf_delete_connector', array( __CLASS__, 'delete_connector' ) );
		add_action( 'wp_ajax_bwf_update_connector', array( __CLASS__, 'update_connector' ) );
		add_action( 'wp_ajax_bwf_connector_install', array( __CLASS__, 'connector_install' ) );
		add_action( 'wp_ajax_bwf_create_connector_license', array( __CLASS__, 'create_connector_license' ) );
	}

	/**
	 * Update Connectors settings
	 */
	public static function update_connector() {
		$resp = array(
			'status'  => false,
			'message' => __( 'Something is wrong, no data exists.', 'woofunnels' ),
		);

		if ( empty( $_REQUEST ) ) {
			wp_send_json( $resp );
		}

		/** Connector slug and connector saved id doesn't exist */
		if ( ! isset( $_REQUEST['wfco_connector'] ) || empty( $_REQUEST['wfco_connector'] ) || ! isset( $_REQUEST['id'] ) || empty( $_REQUEST['id'] ) ) {
			$resp['message'] = 'Connector saved data missing, kindly disconnect and connect again.';
			wp_send_json( $resp );
		}

		/** Couldn't able to verify the nonce */
		if ( isset( $_REQUEST['edit_nonce'] ) && false === wp_verify_nonce( sanitize_text_field( $_REQUEST['edit_nonce'] ), 'wfco-connector-edit' ) ) {
			$resp['message'] = 'Something is wrong, couldn\'t verify the call.';
			wp_send_json( $resp );
		}

		$active_connectors = WFCO_Load_Connectors::get_active_connectors();
		if ( ! $active_connectors[ sanitize_text_field( $_REQUEST['wfco_connector'] ) ] instanceof BWF_CO ) {
			$resp['message'] = 'Something is wrong, connector isn\'t available.';
			wp_send_json( $resp );
		}

		/** @var $connector_ins BWF_CO */
		$connector_ins   = $active_connectors[ sanitize_text_field( $_REQUEST['wfco_connector'] ) ];
		$response        = $connector_ins->handle_settings_form( $_REQUEST, 'update' );
		$resp['status']  = ( 'success' === $response['status'] ) ? true : false;
		$resp['message'] = $response['message'];

		if ( false === $resp['status'] ) {
			wp_send_json( $resp );
		}

		$resp['status']       = true;
		$resp['id']           = $response['id'];
		$resp['data_changed'] = $response['data_changed'];
		$resp['redirect_url'] = add_query_arg( array(
			'page' => 'autonami',
			'tab'  => 'connector',
		), admin_url( 'admin.php' ) );

		wp_send_json( $resp );
	}

	/**
	 * save connectors settings
	 */
	public static function save_connector() {
		$resp = array(
			'status'       => false,
			'message'      => __( 'Something is wrong, no data exists.', 'woofunnels' ),
			'data_changed' => false,
		);

		if ( empty( $_REQUEST ) ) {
			wp_send_json( $resp );
		}

		/** Connector slug and connector saved id doesn't exist */
		if ( ! isset( $_REQUEST['wfco_connector'] ) || empty( $_REQUEST['wfco_connector'] ) ) {
			$resp['message'] = 'Something is wrong, couldn\'t find the connector.';
			wp_send_json( $resp );
		}

		/** Couldn't able to verify the nonce */
		if ( isset( $_REQUEST['_wpnonce'] ) && false === wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ), 'wfco-connector' ) ) {
			$resp['message'] = 'Something is wrong, couldn\'t verify the call.';
			wp_send_json( $resp );
		}

		$active_connectors = WFCO_Load_Connectors::get_active_connectors();
		if ( ! $active_connectors[ sanitize_text_field( $_REQUEST['wfco_connector'] ) ] instanceof BWF_CO ) {
			$resp['message'] = 'Something is wrong, connector isn\'t available.';
			wp_send_json( $resp );
		}

		$response        = $active_connectors[ sanitize_text_field( $_REQUEST['wfco_connector'] ) ]->handle_settings_form( $_REQUEST, 'save' );
		$resp['status']  = ( 'success' === $response['status'] ) ? true : false;
		$resp['message'] = $response['message'];

		/** Error occurred */
		if ( false === $resp['status'] ) {
			wp_send_json( $resp );
		}

		/** Call succeeded */
		$resp['status']       = true;
		$resp['id']           = $response['id'];
		$resp['redirect_url'] = add_query_arg( array(
			'page' => 'autonami',
			'tab'  => 'connector',
		), admin_url( 'admin.php' ) );

		wp_send_json( $resp );
	}

	/**
	 * sync connectors settings
	 */
	public static function sync_connector() {
		$resp = array(
			'status'  => false,
			'message' => __( 'Something is wrong, no data exists.', 'woofunnels' ),
		);

		if ( empty( $_REQUEST ) ) {
			wp_send_json( $resp );
		}

		/** Connector slug and connector saved id doesn't exist */
		if ( ! isset( $_REQUEST['slug'] ) || empty( $_REQUEST['slug'] ) || ! isset( $_REQUEST['id'] ) || empty( $_REQUEST['id'] ) ) {
			$resp['message'] = 'Connector saved data missing, kindly disconnect and connect again.';
			wp_send_json( $resp );
		}

		/** Couldn't able to verify the nonce */
		if ( isset( $_REQUEST['sync_nonce'] ) && false === wp_verify_nonce( sanitize_text_field( $_REQUEST['sync_nonce'] ), 'wfco-connector-sync' ) ) {
			$resp['message'] = 'Something is wrong, couldn\'t verify the call.';
			wp_send_json( $resp );
		}

		$slug              = trim( sanitize_text_field( $_REQUEST['slug'] ) );
		$current_connector = WFCO_Load_Connectors::get_connector( $slug );
		if ( ! $current_connector instanceof BWF_CO ) {
			$resp['message'] = 'Something is wrong, connector isn\'t available.';
			wp_send_json( $resp );
		}

		try {
			$response = $current_connector->handle_settings_form( $_REQUEST, 'sync' );
		} catch ( Exception $exception ) {
			$resp['message'] = $exception->getMessage();
			wp_send_json( $resp );
		}
		$resp['status']  = ( 'success' === $response['status'] ) ? true : false;
		$resp['message'] = $response['message'];

		if ( false === $resp['status'] ) {
			wp_send_json( $resp );
		}

		$resp['status']       = true;
		$resp['id']           = $response['id'];
		$resp['data_changed'] = $response['data_changed'];
		$resp['redirect_url'] = add_query_arg( array(
			'page' => 'autonami',
			'tab'  => 'connector',
		), admin_url( 'admin.php' ) );

		wp_send_json( $resp );
	}

	/**
	 * Delete connector
	 */
	public static function delete_connector() {
		if ( empty( $_REQUEST ) ) {
			wp_send_json_error( new WP_Error( 'Bad Request' ) );
		}
		global $wpdb;
		$resp = array();

		$resp['status'] = false;

		/** Couldn't able to verify the nonce */
		if ( isset( $_REQUEST['delete_nonce'] ) && false === wp_verify_nonce( sanitize_text_field( $_REQUEST['delete_nonce'] ), 'wfco-connector-delete' ) ) {
			$resp['message'] = 'Something is wrong, couldn\'t verify the call.';
			wp_send_json( $resp );
		}

		if ( isset( $_REQUEST['id'] ) && '' !== sanitize_text_field( $_REQUEST['id'] ) && isset( $_REQUEST['delete_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_REQUEST['delete_nonce'] ), 'wfco-connector-delete' ) ) {

			$connector_id      = sanitize_text_field( $_REQUEST['id'] );
			$connector_details = WFCO_Model_Connectors::get( $connector_id );
			$connector_slug    = $connector_details['slug'];
			$sql_query         = 'DELETE from {table_name} where connector_id = %d';
			$sql_query         = $wpdb->prepare( $sql_query, $connector_id ); //phpcs:ignore WordPress.DB.PreparedSQL
			WFCO_Model_ConnectorMeta::delete_multiple( $sql_query );
			WFCO_Model_Connectors::delete( $connector_id );
			do_action( 'connector_disconnected', $connector_slug, true );

			$resp['status']       = true;
			$resp['redirect_url'] = add_query_arg( array(
				'page' => 'autonami',
				'tab'  => 'connector',
			), admin_url( 'admin.php' ) );

			wp_send_json( $resp );
		}
	}

	public static function connector_install() {
		if ( empty( $_REQUEST ) ) {
			wp_send_json_error( new WP_Error( 'Bad Request' ) );
		}

		$resp = array();

		$resp['status'] = false;
		$resp['msg']    = __( 'There was some error. Please try again later.', 'woofunnels-autonami-automation' );

		/** Couldn't able to verify the nonce */
		if ( isset( $_REQUEST['install_nonce'] ) && false === wp_verify_nonce( sanitize_text_field( $_REQUEST['install_nonce'] ), 'wfco-connector-install' ) ) {
			$resp['message'] = 'Something is wrong, couldn\'t verify the call.';
			wp_send_json( $resp );
		}

		if ( isset( $_REQUEST['connector_slug'] ) && '' !== sanitize_text_field( $_REQUEST['connector_slug'] ) && isset( $_REQUEST['install_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_REQUEST['install_nonce'] ), 'wfco-connector-install' ) ) {

			$encode_slug       = isset( $_REQUEST['slug'] ) ? trim( sanitize_text_field( $_REQUEST['slug'] ) ) : '';
			$type              = isset( $_REQUEST['type'] ) ? trim( sanitize_text_field( $_REQUEST['type'] ) ) : '';
			$resp['connector'] = $encode_slug;
			$resp['type']      = $type;

			$defined_vars = get_defined_constants( true );
			$licence_data = [];
			if ( isset( $defined_vars['user'][ $encode_slug ] ) ) {
				$encoded_plugin_basename = $defined_vars['user'][ $encode_slug ];

				$temp_license = WooFunnels_License_check::find_licence_data_using_basename( $encoded_plugin_basename );
				if ( ! is_null( $temp_license ) ) {
					$licence_data = $temp_license;
				}
			}

			if ( empty( $licence_data ) ) {
				$resp['msg']        = __( 'No License found', 'woofunnels-autonami-automation' );
				$resp['error_code'] = 101;
				wp_send_json( $resp );
			}

			$licence       = $licence_data['data_extra']['api_key'];
			$connector_api = new WFCO_Connector_api( $licence, $type );
			$connector_api->find_connector();
			$response_data = $connector_api->get_package();

			if ( ! wc_string_to_bool( $response_data['status'] ) ) {
				$msg = $response_data['msg'];
				if ( '' !== $msg ) {
					$resp['msg'] = $msg;
				}
				$resp['error_code'] = $response_data['error_code'];
				wp_send_json( $resp );

			}

			$connector_download_link = $response_data['data']['package_url'];
			$connector_plugin_file   = $response_data['data']['file'];
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
			//includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
			include_once( ABSPATH . 'wp-admin/includes/file.php' );
			include_once( ABSPATH . 'wp-admin/includes/misc.php' );
			include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

			$upgrader = new Plugin_Upgrader();
			$result   = $upgrader->install( $connector_download_link );

			if ( $result ) {
				$resp['status'] = true;
				try {
					$activation_result = activate_plugin( $connector_plugin_file );
					if ( is_wp_error( $activation_result ) ) {
						throw new Exception();
					}

					$resp['msg'] = __( 'Plugin installed and activated successfully.', 'woofunnels-autonami-automation' );

				} catch ( Exception $error ) {
					$resp['msg'] = __( 'Plugin installed successfully. Please activate plugin from plugins screen', 'woofunnels-autonami-automation' );
				}
			} else {
				$resp['status'] = false;
				$resp['msg']    = __( 'Some error occurred during downloading & installing the connector, kindly contact woofunnels team.', 'woofunnels-autonami-automation' );
			}

			$resp['redirect_url'] = add_query_arg( array(
				'page' => 'autonami',
				'tab'  => 'connector',
			), admin_url( 'admin.php' ) );

			wp_send_json( $resp );
		}
	}

	public static function create_connector_license() {
		if ( empty( $_REQUEST ) ) {
			wp_send_json_error( new WP_Error( 'Bad Request' ) );
		}

		$resp = array();

		$resp['status'] = false;

		/** Couldn't able to verify the nonce */
		if ( isset( $_REQUEST['install_nonce'] ) && false === wp_verify_nonce( sanitize_text_field( $_REQUEST['install_nonce'] ), 'wfco-connector-install' ) ) {
			$resp['message'] = 'Something is wrong, couldn\'t verify the call.';
			wp_send_json( $resp );
		}

		if ( isset( $_REQUEST['connector_slug'] ) && '' !== sanitize_text_field( $_REQUEST['connector_slug'] ) ) {
			$connector_slug  = trim( sanitize_text_field( $_REQUEST['connector_slug'] ) );
			$connector_slugs = isset( $_REQUEST['slug'] ) ? trim( sanitize_text_field( $_REQUEST['slug'] ) ) : '';
			$type            = isset( $_REQUEST['type'] ) ? trim( sanitize_text_field( $_REQUEST['type'] ) ) : '';
			$connector_slugs = explode( ',', $connector_slugs );

			if ( count( $connector_slugs ) === 0 ) {
				$resp['msg'] = __( 'There was some error. Please try again later.', 'woofunnels-autonami-automation' );
				wp_send_json( $resp );
			}

			$defined_vars = get_defined_constants( true );
			$licence_data = [];
			foreach ( $connector_slugs as $connector_slug ) {
				if ( isset( $defined_vars['user'][ $connector_slug ] ) ) {
					$encoded_plugin_basename = $defined_vars['user'][ $connector_slug ];
					$temp_license            = WooFunnels_License_check::find_licence_data_using_basename( $encoded_plugin_basename );
					if ( ! is_null( $temp_license ) ) {
						$licence_data = $temp_license;
						break;
					}
				}
			}

			if ( empty( $licence_data ) ) {
				$resp['msg'] = __( 'No License found', 'woofunnels-autonami-automation' );
				wp_send_json( $resp );
			}

			$licence       = $licence_data['data_extra']['api_key'];
			$connector_api = new WFCO_Connector_api( $licence, $type, 'create_connector_license' );
			$connector_api->create_license();
			$response_data = $connector_api->get_package();
			if ( ! is_array( $response_data ) || ! wc_string_to_bool( $response_data['status'] ) ) {
				$resp['msg'] = __( 'There was some error. Please try again later.1', 'woofunnels-autonami-automation' );
				wp_send_json( $resp );
			}
			//Activate connector licences
			$defined_vars           = get_defined_constants( true );
			$plugin_encode_constant = $response_data['plugin_encode_constant'];

			if ( $defined_vars['user'][ $plugin_encode_constant ] ) {
				$license_instance = new WooFunnels_License_check();
				$license_instance->set_hash( $defined_vars['user'][ $plugin_encode_constant ] );
				$available_plugins                        = get_option( 'woofunnels_plugins_info', array() );
				$hash                                     = $license_instance->get_hash();
				$available_plugins[ $hash ]['activated']  = 1;
				$available_plugins[ $hash ]['instance']   = $response_data['data_extra']['instance'];
				$available_plugins[ $hash ]['message']    = $response_data['data_extra']['message'];
				$available_plugins[ $hash ]['data_extra'] = $response_data['data_extra'];
				WooFunnels_License_check::update_plugins( $available_plugins );
				$resp['status'] = true;
				$resp['msg']    = __( 'License Activated', 'woofunnels-autonami-automation' );
			}
			wp_send_json( $resp );
		}
	}
}

WFCO_AJAX_Controller::init();
