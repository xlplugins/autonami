<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class BWFCRM_Base_React_Page {
	public $frontend_dir = ( 1 === BWFCRM_REACT_ENVIRONMENT ) ? BWFAN_REACT_PROD_URL : BWFCRM_REACT_DEV_URL;
	public $admin_dir = BWFAN_PLUGIN_DIR . '/admin';
	public $build_dir = BWFAN_PLUGIN_DIR . '/admin/frontend/dist';

	public function get_header_data() {
		if ( class_exists( 'BWFAN_Header' ) ) {
			$header_ins = new BWFAN_Header();

			return $header_ins->get_render_data();
		}

		return array();
	}

	public function prepare_data_for_enqueue() {
		/** Menu Data */
		$this->page_data['header_data'] = $this->get_header_data();

		/** Forms */
		$this->page_data['form_nice_names'] = class_exists( 'BWFCRM_Core' ) ? BWFCRM_Core()->forms->get_forms_nice_names() : '';
		$this->page_data['available_forms'] = class_exists( 'BWFCRM_Core' ) ? BWFCRM_Core()->forms->get_available_forms() : array();

		/** Broadcast */
		$this->page_data['default_email_settings'] = $this->get_global_email_settings();
		$this->page_data['editor_settings']        = $this->get_editor_settings();
		$this->page_data['daily_email_limit']      = class_exists( 'BWFCRM_Core' ) ? BWFCRM_Core()->campaigns->get_daily_limit_status_array() : 0;

		/** Carts */
		$this->page_data['abandoned_wait_time']  = $this->get_abandoned_wait_time();
		$this->page_data['is_tax_enabled']       = bwfan_is_woocommerce_active() && wc_tax_enabled();
		$this->page_data['siteTitle']            = get_bloginfo();
		$this->page_data['is_wc_active']         = false;
		$this->page_data['is_connector_active']  = false;
		$this->page_data['is_funnel_active']     = false;
		$this->page_data['disable_wp_importer']  = apply_filters( 'bwfcrm_disable_wp_importer', false );
		$this->page_data['date_format']          = get_option( 'date_format' );
		$this->page_data['time_format']          = get_option( 'time_format' );
		$this->page_data['timezone']             = get_option( 'timezone_string' );
		$this->page_data['gmt_offset']           = get_option( 'gmt_offset' );
		$this->page_data['currrent_logged_user'] = get_current_user_id();

		if ( bwfan_is_autonami_pro_active() ) {
			$this->page_data['is_twilio_connected'] = class_exists( 'BWFCRM_Core' ) ? BWFCRM_Core()->sms->is_twilio_connected() : false;
			$this->page_data['active_sms_provider'] = method_exists( 'BWFCRM_Common', 'get_sms_provider_slug' ) ? BWFCRM_Common::get_sms_provider_slug() : false;
		}

		$this->page_data['crm_contact_note_types']  = apply_filters( 'bwfcrm_contact_add_note_types', array(
			array(
				'value' => 'billing',
				'label' => __( 'Billing', 'wp-marketing-automations-crm' ),
			),
			array(
				'value' => 'shipping',
				'label' => __( 'Shipping', 'wp-marketing-automations-crm' ),
			),
			array(
				'value' => 'refund',
				'label' => __( 'Refund', 'wp-marketing-automations-crm' ),
			),
			array(
				'value' => 'subscription',
				'label' => __( 'Subscription', 'wp-marketing-automations-crm' ),
			),
			array(
				'value' => 'feedback',
				'label' => __( 'Feedback', 'wp-marketing-automations-crm' ),
			),
			array(
				'value' => 'others',
				'label' => __( 'Others', 'wp-marketing-automations-crm' ),
			),
		) );
		$this->page_data['is_conversation_enabled'] = ( class_exists( 'BWFAN_Email_Conversations' ) && isset( BWFAN_Core()->conversations ) && BWFAN_Core()->conversations instanceof BWFAN_Email_Conversations );
		$this->page_data['app_path']                = BWFAN_REACT_PROD_URL . '/';

		$this->page_data['icons'] = array(
			'error'   => '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2" class="wffn_loader wffn_loader_error">
                        <circle fill="none" stroke="#ffb7bf" stroke-width="6" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1" class="path circle"></circle>
                        <line fill="none" stroke="#e64155" stroke-width="8" stroke-linecap="round" stroke-miterlimit="10" x1="34.4" y1="37.9" x2="95.8" y2="92.3" class="path line"></line>
                        <line fill="none" stroke="#e64155" stroke-width="8" stroke-linecap="round" stroke-miterlimit="10" x1="95.8" y1="38" x2="34.4" y2="92.2" class="path line"></line>
                    </svg>',
			'success' => '<svg class="wffn_loader wffn_loader_ok" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 130.2 130.2">
                                <circle class="path circle" fill="none" stroke="#baeac5" stroke-width="5" stroke-miterlimit="10" cx="65.1" cy="65.1" r="62.1"></circle>
                                <polyline class="path check" fill="none" stroke="#39c359" stroke-width="9" stroke-linecap="round" stroke-miterlimit="10" points="100.2,40.2 51.5,88.8 29.8,67.5 "></polyline>
                            </svg>',
		);

		$first_contact_id = $first_broadcast_id = $first_form_id = $first_automation_id = $first_export_id = $first_template_id = null;
		if ( apply_filters( 'bwf_set_zero_state', true ) ) {
			if ( class_exists( 'BWFCRM_Core' ) ) {
				$first_contact_id   = BWFCRM_Model_Contact::get_first_contact_id();
				$first_broadcast_id = BWFAN_Model_Broadcast::get_first_broadcast_id();
				$first_form_id      = BWFAN_Model_Form_Feeds::get_first_form_id();
				$first_export_id    = BWFAN_Model_Import_Export::get_first_export_id();
				$first_template_id  = BWFAN_Model_Templates::get_first_template_id();
			}
			$first_automation_id = BWFAN_Model_Automations::get_first_automation_id();
		}

		$this->page_data['first_contact_id']    = $first_contact_id;
		$this->page_data['first_broadcast_id']  = $first_broadcast_id;
		$this->page_data['first_form_id']       = $first_form_id;
		$this->page_data['first_automation_id'] = $first_automation_id;
		$this->page_data['first_export_id']     = $first_export_id;
		$this->page_data['first_template_id']   = $first_template_id;
		$this->page_data['bwfan_nonce']         = get_option( 'bwfan_u_key', '' );

		$this->page_data['user_display_name']    = get_user_by( 'id', get_current_user_id() )->display_name;
		$this->page_data['table_column_data']    = array(
			'contact'  => get_user_meta( get_current_user_id(), '_bwfan_contact_columns', true ),
			'campaign' => get_user_meta( get_current_user_id(), '_bwfan_broadcast_columns', true ),
		);
		$this->page_data['welcome_note_dismiss'] = get_user_meta( get_current_user_id(), '_bwfan_welcome_note_dismissed', true );

		if ( class_exists( 'WooCommerce' ) ) {
			$this->page_data['currency']        = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';
			$this->page_data['currency_symbol'] = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';
			$this->page_data['is_wc_active']    = true;
			$this->page_data['currency']        = array(
				'code'              => $this->page_data['currency'],
				'precision'         => wc_get_price_decimals(),
				'symbol'            => html_entity_decode( get_woocommerce_currency_symbol( $this->page_data['currency'] ) ),
				'symbolPosition'    => get_option( 'woocommerce_currency_pos' ),
				'decimalSeparator'  => wc_get_price_decimal_separator(),
				'thousandSeparator' => wc_get_price_thousand_separator(),
				'priceFormat'       => html_entity_decode( get_woocommerce_price_format() ),
			);
		}

		$this->page_data['timezoneList'] = $this->get_timezone_list();

		if ( class_exists( 'WFFN_Core' ) ) {
			$this->page_data['is_funnel_active'] = true;
		}
		if ( bwfan_is_autonami_pro_active() ) {
			$this->page_data['is_autonami_pro_active'] = true;
			$this->page_data['contacts_count']         = BWFAN_PRO_Common::get_contacts_count();
		}

		if ( class_exists( 'WFCO_Autonami_Connectors_Core' ) ) {
			$this->page_data['is_connector_active'] = true;
		}

		$this->page_data['is_whatsapp_service_available'] = bwfan_is_autonami_pro_active() ? BWFCRM_Core()->conversation->is_whatsapp_service_available() : false;

		$this->page_data['is_whatsapp_enabled'] = BWFAN_Common::is_whatsapp_services_enabled();

		if ( class_exists( 'WFCO_Autonami_Connectors_Core' ) ) {
			$this->page_data['is_autonami_connectors_active'] = true;
		}

		$supported_editors                    = defined( 'BWFCRM_BROADCAST_SUPPORTED_EDITORS' ) ? BWFCRM_BROADCAST_SUPPORTED_EDITORS : apply_filters( 'bwfcrm_broadcast_supported_editors', array() );
		$this->page_data['supported_editors'] = $supported_editors;
	}

	public function get_abandoned_wait_time() {
		$global_settings = BWFAN_Common::get_global_settings();
		$wait_time       = ( isset( $global_settings['bwfan_ab_init_wait_time'] ) ) ? $global_settings['bwfan_ab_init_wait_time'] : 15;
		$wait_time       = absint( $wait_time );
		$wait_time       = ( 1 === $wait_time ) ? $wait_time . ' min' : $wait_time . ' mins';

		return $wait_time;
	}

	public function get_deps( $app_name ) {
		$assets_path = BWFAN_PLUGIN_DIR . "/admin/frontend/dist/$app_name.asset.php";
		$assets      = require_once $assets_path;
		$deps        = ( isset( $assets['dependencies'] ) ? array_merge( $assets['dependencies'], array( 'jquery' ) ) : array( 'jquery' ) );
		$version     = ( isset( $assets['version'] ) ? $assets['version'] : BWFAN_VERSION );

		$script_deps = array_filter( $deps, function ( $dep ) use ( &$style_deps ) {
			return false === strpos( $dep, 'css' );
		} );

		return array(
			'dependencies' => $script_deps,
			'version'      => $version,
		);
	}

	public function enqueue_app_assets( $app_name ) {
		if ( ! is_dir( $this->build_dir ) || ! file_exists( $this->build_dir . "/$app_name.js" ) || ! file_exists( $this->build_dir . "/$app_name.css" ) ) {
			?>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    var appLoader = document.getElementById('bwfcrm-page');
                    if (appLoader) {
                        appLoader.innerHTML = "<div class='notice notice-error'>" +
                            "<p><strong>Warning! Build files are missing.</strong></p>" +
                            "</div>";
                    }
                });
            </script>
			<?php
			return;
		}

		/** Broadcasts */
		wp_enqueue_editor();
		wp_tinymce_inline_scripts();

		/** Common */
		if ( class_exists( 'WooCommerce' ) ) {
			wp_dequeue_style( 'woocommerce_admin_styles' );
			wp_dequeue_style( 'wc-components' );
		}

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'bwfcrm_material_icons', 'https://fonts.googleapis.com/icon?family=Material+Icons+Outlined' );

		$deps    = $this->get_deps( $app_name );
		$version = ( isset( $deps['version'] ) ? $deps['version'] : time() );
		wp_register_script( "bwfcrm_$app_name", $this->frontend_dir . "/$app_name.js", $deps['dependencies'], $version, true );
		wp_enqueue_style( "bwfcrm_{$app_name}_css", $this->frontend_dir . "/$app_name.css", array(), $version );

		wp_localize_script( "bwfcrm_$app_name", 'bwfcrm_contacts_data', $this->page_data );
		wp_enqueue_script( "bwfcrm_$app_name" );
	}

	public function get_global_email_settings() {
		$global_email_settings = BWFAN_Common::get_global_email_settings();

		return array(
			'from_email'     => $global_email_settings['bwfan_email_from'],
			'from_name'      => $global_email_settings['bwfan_email_from_name'],
			'reply_to_email' => $global_email_settings['bwfan_email_reply_to'],
		);
	}

	public function get_editor_settings() {
		if ( ! bwfan_is_autonami_pro_active() ) {
			return array();
		}

		$settings                    = BWFCRM_Core()->email_editor->get_editor_localize_settings();
		$settings['default']         = array();
		$settings['default']['form'] = $this->get_default_form_email_content();

		return $settings;
	}

	public function get_default_form_email_content() {
		$default_email_body = '<p>Hi {{contact_first_name}},</p>
<p>Thanks for signing up. Click the link below to confirm your subscription and you\'ll be on your way.</p>
<p><a href="{{contact_confirmation_link}}" data-wplink-url-error="true">Confirm your subscription</a></p>
<p>It\'s good to have you!</p>
<p></p>
<p><a href="{{unsubscribe_link}}">Unsubscribe</a> | {{business_name}}, {{business_address}}</p>';

		return array(
			'body'   => $default_email_body,
			'editor' => array(
				'body'   => file_get_contents( plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'admin/email-editor-json/default-form.html' ),
				'design' => file_get_contents( plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'admin/email-editor-json/default-form.json' ),
			),
		);
	}

	public function get_timezone_list() {
		static $regions = array(
			DateTimeZone::AFRICA,
			DateTimeZone::AMERICA,
			DateTimeZone::ANTARCTICA,
			DateTimeZone::ASIA,
			DateTimeZone::ATLANTIC,
			DateTimeZone::AUSTRALIA,
			DateTimeZone::EUROPE,
			DateTimeZone::INDIAN,
			DateTimeZone::PACIFIC,
		);

		$timezones = array();
		foreach ( $regions as $region ) {
			$timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
		}

		$timezone_offsets = array();
		foreach ( $timezones as $timezone ) {
			$tz                            = new DateTimeZone( $timezone );
			$timezone_offsets[ $timezone ] = $tz->getOffset( new DateTime() );
		}

		asort( $timezone_offsets );

		$timezone_list = array();
		foreach ( $timezone_offsets as $timezone => $offset ) {
			$offset_prefix    = $offset < 0 ? '-' : '+';
			$offset_formatted = gmdate( 'H:i', abs( $offset ) );

			$pretty_offset = "UTC${offset_prefix}${offset_formatted}";

			$timezone_list[ $timezone ] = "(${pretty_offset}) $timezone";
		}

		return $timezone_list;
	}

	abstract public function render();
}
