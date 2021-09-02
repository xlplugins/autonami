<?php

/**
 * This class handled all the functions related to Rules that belongs to this plugin only
 * @author BuildWooFunnels
 */
class BWFAN_Rules_Loader extends BWFAN_Rules {
	private static $ins = null;
	public $is_executing_rule = false;
	public $environments = array();
	public $excluded_rules = array();
	public $excluded_rules_categories = array();
	public $processed = array();
	public $record = array();
	public $skipped = array();
	public $rules_data = null;
	public $select2names = array();

	public function __construct() {

		parent::__construct();
		add_filter( 'bwfan_rule_get_rule_types', array( $this, 'default_rule_types' ), 10 );

		add_filter( 'bwfan_admin_builder_localized_data', array( $this, 'add_rule_groups_to_js' ) );
		add_filter( 'bwfan_admin_builder_localized_data', array( $this, 'add_rules_to_js' ) );
		add_filter( 'bwfan_admin_builder_localized_data', array( $this, 'add_rules_ui_prev_data' ) );
		add_action( 'bwfan_automation_data_set_automation', array( $this, 'maybe_set_rules_ajax_select_names' ) );
		add_action( 'init', array( $this, 'maybe_initiate_all_rules' ) );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function get_default_rule_groups() {
		return apply_filters( 'bwfan_rules_default_groups', array() );
	}

	public function add_rule_groups_to_js( $localized_data ) {
		$localized_data['rule_groups'] = $this->get_all_groups();

		return $localized_data;
	}

	public function get_all_groups() {
		return apply_filters( 'bwfan_rules_groups', array(
			'wc_items'       => array(
				'title' => __( 'Product', 'wp-marketing-automations' ),
			),
			'wc_order'       => array(
				'title' => __( 'Order', 'wp-marketing-automations' ),
			),
			'wc_order_state' => array(
				'title' => __( 'Order Status', 'wp-marketing-automations' ),
			),
			'wc_order_note'  => array(
				'title' => __( 'Order Note', 'wp-marketing-automations' ),
			),
			'wc_customer'    => array(
				'title' => __( 'Contact', 'wp-marketing-automations' ),
			),
			'wp_user'        => array(
				'title' => __( 'User', 'wp-marketing-automations' ),
			),
			'automation'     => array(
				'title' => __( 'Automation', 'wp-marketing-automations' ),
			),
			'wc_comment'     => array(
				'title' => __( 'Reviews', 'wp-marketing-automations' ),
			),
			'ab_cart'        => array(
				'title' => __( 'Cart', 'wp-marketing-automations' ),
			),
			'cf7'            => array(
				'title' => __( 'Contact Form 7', 'wp-marketing-automations' )
			)
		) );
	}

	public function add_rules_to_js( $localized_data ) {
		$localized_data['rules'] = apply_filters( 'bwfan_rule_get_rule_types', array() );

		return $localized_data;
	}

	public function default_rule_types( $types ) {
		$types = array(
			'wc_items'         => array(
				'product_item'              => __( 'Items', 'wp-marketing-automations' ),
				'product_category'          => __( 'Item Categories', 'wp-marketing-automations' ),
				'product_tags'              => __( 'Item Tags', 'wp-marketing-automations' ),
				'product_item_count'        => __( 'Item Count', 'wp-marketing-automations' ),
				'product_item_type'         => __( 'Item Type', 'wp-marketing-automations' ),
				'product_item_price'        => __( 'Item Price', 'wp-marketing-automations' ),
				'product_stock'             => __( 'Item Stock', 'wp-marketing-automations' ),
				'product_item_custom_field' => __( 'Item Custom Field', 'wp-marketing-automations' ),
			),
			'wc_order'         => array(
				'order_total'             => __( 'Total', 'wp-marketing-automations' ),
				'product_item'            => __( 'Items', 'wp-marketing-automations' ),
				'order_coupons'           => __( 'Coupons', 'wp-marketing-automations' ),
				'order_coupon_text_match' => __( 'Coupon Text', 'wp-marketing-automations' ),
				'order_payment_gateway'   => __( 'Payment Gateway', 'wp-marketing-automations' ),
				'order_shipping_method'   => __( 'Shipping Method', 'wp-marketing-automations' ),
				'order_billing_country'   => __( 'Billing Country', 'wp-marketing-automations' ),
				'order_shipping_country'  => __( 'Shipping Country', 'wp-marketing-automations' ),
				'order_custom_field'      => __( 'Custom Field', 'wp-marketing-automations' ),
				'is_guest'                => __( 'Guest Order', 'wp-marketing-automations' ),
				'is_first_order'          => __( 'First Order (New Customer)', 'wp-marketing-automations' ),
			),
			'wc_order_note'    => array(
				'order_note_text_match' => __( 'Note Text', 'wp-marketing-automations' ),
			),
			'wc_order_state'   => array(
				'order_status_change' => __( 'Older Order Status', 'wp-marketing-automations' ),
			),
			'bwf_contact_user' => array(
				'customer_user' => __( 'User', 'wp-marketing-automations' ),
			),
			'wp_user'          => array(
				'users_role' => __( 'User Role', 'wp-marketing-automations' ),
				'users_user' => __( 'User', 'wp-marketing-automations' ),
			),
			'wc_comment'       => array(
				'comment_count' => __( 'Review Rating Count', 'wp-marketing-automations' ),
			),
			'ab_cart'          => array(
				'cart_total'               => __( 'Cart Total', 'wp-marketing-automations' ),
				'cart_product'             => __( 'Cart Items', 'wp-marketing-automations' ),
				'cart_category'            => __( 'Cart Items Category', 'wp-marketing-automations' ),
				'cart_coupons'             => __( 'Cart Coupons', 'wp-marketing-automations' ),
				'cart_coupon_text_match'   => __( 'Cart Coupon Text', 'wp-marketing-automations' ),
				'all_cart_items_purchased' => __( 'All Cart Items Purchased (in past)', 'wp-marketing-automations' ),
				'cart_contains_coupon'     => __( 'Cart Contains Any Coupon', 'wp-marketing-automations' ),
				'cart_item_count'          => __( 'Cart Item Count', 'wp-marketing-automations' ),
			),
			'cf7'              => array(
				'cf7_form_field' => __( 'Form Field', 'wp-marketing-automations' ),
			)
		);

		return $types;
	}

	public function add_rules_ui_prev_data( $localized_data ) {
		if ( ! BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			return $localized_data;
		}
		$types                          = apply_filters( 'bwfan_rule_get_rule_types', array() );
		$localized_data['rule_ui_data'] = array();

		foreach ( $types as $ruleset ) {
			foreach ( $ruleset as $key => $rules ) { //phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis
				$get_rule_object = $this->woocommerce_bwfan_rule_get_rule_object( $key );

				if ( $get_rule_object instanceof BWFAN_Rule_Base && is_callable( array( $get_rule_object, 'get_ui_preview_data' ) ) ) {

					$localized_data['rule_ui_data'][ $get_rule_object->get_name() ] = $get_rule_object->get_ui_preview_data();

					if ( isset( $this->select2names[ $get_rule_object->get_name() ] ) ) {
						$localized_data['rule_ui_data'][ $get_rule_object->get_name() ] = $this->select2names[ $get_rule_object->get_name() ];
					}
				}
			}
		}

		foreach ( $localized_data['rule_ui_data'] as $rule_key => $rule_value ) {
			if ( is_array( $rule_value ) && 0 === count( $rule_value ) ) {
				$localized_data['rule_ui_data'][ $rule_key ] = new stdClass();
			}
		}

		return $localized_data;
	}

	/**
	 * @hooked over 'init'
	 * Initiate the class of each rule object so that respective filters gets registered
	 * In the __construct() of some of the rule classes exists hook that need to be initialize on load
	 */
	public function maybe_initiate_all_rules() {
		$rules_data = apply_filters( 'bwfan_rule_get_rule_types', array() );
		foreach ( $rules_data as $group_rules ) {

			foreach ( $group_rules as $rule => $title ) { //phpcs:ignore WordPressVIPMinimum.Variables.VariableAnalysis
				$this->woocommerce_bwfan_rule_get_rule_object( $rule );
			}
		}
	}

	/**
	 * @hooked over `bwfan_automation_data_set_automation`
	 * Iterate over all the conditions saved against an automation to get nice names of the IDs saved as rule data to show in ADMIN UI.
	 * It also registers this nice name data to register for the localization.
	 */
	public function maybe_set_rules_ajax_select_names() {
		$data = BWFAN_Core()->automations->get_automation_details();
		if ( isset( $data['condition'] ) && is_array( $data['condition'] ) && count( $data['condition'] ) > 0 ) {
			foreach ( $data['condition'] as $groups ) {
				if ( is_array( $groups ) && count( $groups ) > 0 ) {
					foreach ( $groups as $rulegroups ) {
						foreach ( $rulegroups as $rules ) {
							foreach ( $rules as $rule ) {
								$get_rule_object = $this->woocommerce_bwfan_rule_get_rule_object( $rule['rule_type'] );
								if ( $get_rule_object instanceof BWFAN_Rule_Base && is_callable( array( $get_rule_object, 'get_condition_values_nice_names' ) ) ) {
									BWFAN_Core()->admin->set_select2ajax_js_data( $get_rule_object->get_search_type_name(), $get_rule_object->get_condition_values_nice_names( $rule['condition'] ) );
									if ( ! isset( $this->select2names[ $rule['rule_type'] ] ) ) {
										$this->select2names[ $rule['rule_type'] ] = [];
									}
									$this->select2names[ $rule['rule_type'] ] = array_replace( $this->select2names[ $rule['rule_type'] ], $get_rule_object->get_condition_values_nice_names( $rule['condition'] ) );
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param string $key
	 *
	 * @return |null
	 */
	public function getRulesData( $key = '' ) {
		return ( ! empty( $key ) ) ? $this->rules_data[ $key ] : $this->rules_data;
	}

	/**
	 * @param string $rules_data
	 * @param string $key
	 */
	public function setRulesData( $rules_data = '', $key = '' ) {
		$this->rules_data[ $key ] = $rules_data;
	}


}

if ( class_exists( 'BWFAN_Rules_Loader' ) ) {
	BWFAN_Core::register( 'rules', 'BWFAN_Rules_Loader' );
}
