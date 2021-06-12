<?php

class BWFAN_API_Get_Dashboard_Data extends BWFAN_API_Base {
	public static $ins;

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	public $contact;

	public function __construct() {
		parent::__construct();
		$this->method = WP_REST_Server::READABLE;
		$this->route  = '/dashboard';
	}

	public function default_args_values() {
		return array();
	}

	public function process_api_call() {

		$response = $this->prepare_item_for_response();

		$this->response_code = 200;

		return $this->success_response( $response );
	}

	/**
	 *
	 * @return array
	 */
	public function prepare_item_for_response() {
		$recovered_carts  = BWFAN_Automations::get_recovered_carts( 0, 5 );
		$recovered_carts  = $this->get_recovered( $recovered_carts );
		$recent_abandoned = BWFAN_Automations::get_recent_abandoned();
		$unsubsribers     = BWFAN_Dashboards::get_recent_unsubsribers();

		$data = [
			'recovered_carts'    => $recovered_carts,
			'recent_abandoned'   => $recent_abandoned,
			'recent_unsubscribe' => $unsubsribers
		];

		if ( ! bwfan_is_autonami_pro_active() ) {
			$analytics_data = [
				'total_contact' => 0,
				'email_sents'   => 0,
				'sms_sent'      => 0,
				'total_orders'  => 0,
				'total_revenue' => 0,
			];

			return array_merge( $data, [
				'pro_active'      => false,
				'analytics_data'  => $analytics_data,
				'recent_contacts' => [],
				'top_automations' => [],
				'top_broadcast'   => [],
				'popular_emails'  => [],
			] );
		}

		$additional_info = [
			'grab_totals' => true,
			'only_count' => true
		];

		$contacts_count  = BWFCRM_Contact::get_contacts( '', 0, 0, [], $additional_info );

		$get_total_sents = BWFAN_Dashboards::get_total_engagement_sents( '', '', '', '' );

		$get_total_orders = BWFAN_Dashboards::get_total_orders( '', '', '', '' );

		$analytics_data = [
			'total_contact' => ! isset( $contacts_count['total_count'] ) ? 0 : $contacts_count['total_count'],
			'email_sents'   => ! isset( $get_total_sents[0]['email_sents'] ) ? 0 : $get_total_sents[0]['email_sents'],
			'sms_sent'      => ! isset( $get_total_sents[0]['sms_sent'] ) ? 0 : $get_total_sents[0]['sms_sent'],
			'total_orders'  => ! isset( $get_total_orders[0]['total_orders'] ) ? 0 : $get_total_orders[0]['total_orders'],
			'total_revenue' => ! isset( $get_total_orders[0]['total_revenue'] ) ? 0 : $get_total_orders[0]['total_revenue'],
		];

		$contacts        = BWFAN_Dashboards::get_recent_contacts();
		$top_automations = BWFCRM_Automations::get_top_automations();
		$top_broadcast   = BWFCRM_Campaigns::get_top_broadcast();
		$popular_emails  = BWFCRM_Conversation::get_popular_emails();

		return array_merge( $data, [
			'pro_active'      => true,
			'analytics_data'  => $analytics_data,
			'recent_contacts' => $contacts,
			'top_automations' => $top_automations['top_automations'],
			'top_broadcast'   => $top_broadcast['top_broadcast'],
			'popular_emails'  => $popular_emails['popular_emails'],
		] );
	}

	public function get_recovered( $recovered_carts ) {
		$result = [];

		if ( empty( $recovered_carts ) ) {
			return $result;
		}

		foreach ( $recovered_carts['items'] as $item ) {
			if ( ! $item instanceof WC_Order ) {
				continue;
			}
			$order_date = $item->get_date_created();
			$result[]   = [
				'order_id'          => $item->get_id(),
				'billing_full_name' => $this->get_full_name( $item ),
				'email'             => $item->get_billing_email(),
				'phone'             => $item->get_billing_phone(),
				'date_created'      => ( $order_date instanceof WC_DateTime ) ? ( $order_date->date( 'Y-m-d H:i:s' ) ) : '',
				'items'             => $this->get_items( $item ),
				'total'             => $item->get_total(),
				'actions'           => ''
			];
		}

		return $result;
	}

	public function get_full_name( $item ) {
		$buyer = '';

		if ( ! $item instanceof WC_Order ) {
			return '';
		}

		if ( $item->get_billing_first_name() || $item->get_billing_last_name() ) {
			/* translators: 1: first name 2: last name */
			$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'woocommerce' ), $item->get_billing_first_name(), $item->get_billing_last_name() ) );
		} elseif ( $item->get_billing_company() ) {
			$buyer = trim( $item->get_billing_company() );
		} elseif ( $item->get_customer_id() ) {
			$user  = get_user_by( 'id', $item->get_customer_id() );
			$buyer = ucwords( $user->display_name );
		}

		return apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $item );
	}

	public function get_items( $item ) {
		$names = [];
		foreach ( $item->get_items() as $value ) {
			$names[] = $value->get_name();
		}

		return $names;
	}

}

BWFAN_API_Loader::register( 'BWFAN_API_Get_Dashboard_Data' );
