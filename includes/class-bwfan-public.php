<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * BWFAN_Public class
 */
class BWFAN_Public {

	private static $ins = null;
	public $event_data = [];

	public function __construct() {
		/**
		 * Enqueue scripts
		 */
		add_action( 'wp_head', array( $this, 'enqueue_assets' ), 99 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function enqueue_assets() {
		$data                   = BWFAN_Common::get_global_settings();
		$unsubscribe_page_check = false;
		if ( isset( $data['bwfan_unsubscribe_page'] ) && ! empty( $data['bwfan_unsubscribe_page'] ) ) {
			$unsubscribe_page_check = is_page( absint( $data['bwfan_unsubscribe_page'] ) );
		}

		if ( ( false === bwfan_is_woocommerce_active() || ! is_checkout() ) && ! $unsubscribe_page_check ) {
			return;
		}

		if ( false === apply_filters( 'bwfan_public_scripts_include', true ) ) {
			return;
		}

		global $post;
		$data['bwfan_checkout_js_data'] = 'no';
		$data['bwfan_no_thanks']        = __( 'No Thanks', 'woofunnels-autonami-automation-abandoned-cart' );
		$data['is_user_loggedin']       = 0;
		$data['ajax_url']               = admin_url( 'admin-ajax.php' );
		$data['wc_ajax_url']            = class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( '%%endpoint%%' ) : '';
		$data['current_page_id']        = ( $post instanceof WP_Post ) ? $post->ID : 0;
		$data['ajax_nonce']             = wp_create_nonce( 'bwfan-action-admin' );

		if ( ! empty( $data['bwfan_ab_enable'] ) ) {
			$checkout_data = class_exists( 'WC' ) ? WC()->session->get( 'bwfan_data_for_js' ) : '';
			if ( ! empty( $checkout_data ) ) {
				$data['bwfan_checkout_js_data'] = $checkout_data;
			}
		}
		if ( is_user_logged_in() ) {
			$data['is_user_loggedin'] = 1;
			$user                     = wp_get_current_user();
			$bwf_contact              = bwf_get_contact( $user->ID, $user->user_email );
			$data['marketing_status'] = 0;
			if ( isset( $bwf_contact->meta->marketing_status ) ) {
				$data['marketing_status'] = 1;
			}
		}

		$data = apply_filters( 'bwfan_external_checkout_custom_data', $data );

		/**
		 * Bind on checkout page and when woocommerce active
		 */
		if ( bwfan_is_woocommerce_active() && is_checkout() ) {
			wp_enqueue_style( 'bwfan-public', BWFAN_PLUGIN_URL . '/assets/css/bwfan-public.css', array(), BWFAN_VERSION_DEV );

			/** unsetting the unsubscribe data from the bwfanParamsPublic only on checkout page */
			if ( isset( $data['bwfan_unsubscribe_button'] ) ) {
				unset( $data['bwfan_unsubscribe_button'] );
			}

			if ( isset( $data['bwfan_subscriber_recipient'] ) ) {
				unset( $data['bwfan_subscriber_recipient'] );
			}

			if ( isset( $data['bwfan_subscriber_name'] ) ) {
				unset( $data['bwfan_subscriber_name'] );
			}

		}

		wp_enqueue_script( 'bwfan-public', BWFAN_PLUGIN_URL . '/assets/js/bwfan-public.js', array(), BWFAN_VERSION_DEV, true );
		wp_localize_script( 'bwfan-public', 'bwfanParamspublic', $data );
	}

	/**
	 * Load all the active automations so that there event function can be registered
	 *
	 * @param $event_slug
	 */
	public function load_active_automations( $event_slug ) {
		if ( empty( $event_slug ) ) {
			return;
		}

		$automations = BWFAN_Core()->automations->get_active_automations();

		if ( empty( $automations ) ) {
			return;
		}

		$lifecycle_automation_id = BWFAN_Core()->automations->current_lifecycle_automation_id;
		$events_data             = [];
		foreach ( $automations as $automation_id => $automation ) {
			if ( $event_slug !== $automation['event'] ) {
				continue;
			}

			if ( false !== $lifecycle_automation_id && ( absint( $automation_id ) !== absint( $lifecycle_automation_id ) ) ) {
				continue;
			}

			$meta = $automation['meta'];
			unset( $automation['meta'] );
			$merge_data                                   = array_merge( $automation, $meta );
			$events_data[ $event_slug ][ $automation_id ] = $merge_data;
		}

		if ( isset( $events_data[ $event_slug ] ) && count( $events_data[ $event_slug ] ) > 0 ) {
			$this->set_automation_event_data( $events_data );
		}

	}

	/**
	 * Register the actions for every event of every active automation
	 *
	 * @param $events_data
	 */
	private function set_automation_event_data( $events_data ) {
		foreach ( $events_data as $event_slug => $event_data ) {
			/**
			 * @var $event_instance BWFAN_Event
			 */
			$event_instance                             = BWFAN_Core()->sources->get_event( $event_slug );
			$source                                     = $event_instance->get_source();
			$this->event_data[ $source ][ $event_slug ] = $event_data; // Source Slug
			$event_instance->set_automations_data( $event_data );
		}
	}

}

if ( class_exists( 'BWFAN_Core' ) ) {
	BWFAN_Core::register( 'public', 'BWFAN_Public' );
}
