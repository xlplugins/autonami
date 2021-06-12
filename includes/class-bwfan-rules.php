<?php

/**
 * @author XLPlugins
 */
class BWFAN_Rules {
	private static $ins = null;
	public $is_executing_rule = false;
	public $excluded_rules = array();
	public $excluded_rules_categories = array();
	public $processed = array();
	public $record = array();
	public $environments = [];

	public function __construct() {

		add_action( 'init', array( $this, 'load_rules_classes' ) );
		add_filter( 'bwfan_rule_get_rule_types', array( $this, 'default_rule_types' ), 1 );
		add_action( 'init', array( $this, 'maybe_save_rules' ) );
		add_action( 'admin_head', array( $this, 'maybe_load_scripts_templates' ) );

	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public static function woocommerce_bwfan_rule_get_input_object( $input_type ) {
		global $woocommerce_bwfan_rule_inputs;
		if ( isset( $woocommerce_bwfan_rule_inputs[ $input_type ] ) ) {
			return $woocommerce_bwfan_rule_inputs[ $input_type ];
		}
		$class = 'bwfan_Input_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $input_type ) ) );
		if ( class_exists( $class ) ) {
			$woocommerce_bwfan_rule_inputs[ $input_type ] = new $class;
		} else {
			$woocommerce_bwfan_rule_inputs[ $input_type ] = apply_filters( 'woocommerce_bwfan_rule_get_input_object', $input_type );
		}

		return $woocommerce_bwfan_rule_inputs[ $input_type ];
	}

	/**
	 * Match the rules groups based on the environment its called on
	 * Iterate over the setof rules set against each offer and validates for the rules set
	 * Now this function also powered in a way that it can hold some rule for the next environment to run on
	 *
	 * @param $groups
	 *
	 * @return bool|mixed|void
	 */
	public function match_groups( $groups ) {
		$this->is_executing_rule = true;

		//allowing rules to get manipulated using external logic
		$external_rules = apply_filters( 'bwfan_before_rules', true, $groups );
		if ( ! $external_rules ) {
			$this->is_executing_rule = false;

			return false;
		}

		$result                  = $this->_validate( $groups );
		$display                 = apply_filters( 'bwfan_after_rules', $result, $groups, $this );
		$this->processed[]       = $display;
		$this->is_executing_rule = false;

		return $display;
	}

	/**
	 * Validates and group whole block
	 *
	 * @param $groups
	 *
	 * @return bool
	 */
	protected function _validate( $groups ) {
		if ( $groups && is_array( $groups ) && count( $groups ) ) {
			foreach ( $groups as $type => $groups_category ) {
				if ( in_array( $type, $this->excluded_rules_categories, true ) ) {
					continue;
				}
				$result = $this->_validate_rule_block( $groups_category, $type );

				if ( false === $result ) {
					return false;
				}
			}
		}

		return true;
	}

	protected function _validate_rule_block( $groups_category, $type ) {
		$iteration_results = array();

		if ( $groups_category && is_array( $groups_category ) && count( $groups_category ) ) {

			foreach ( $groups_category as $group_id => $group ) {

				foreach ( $group as $rule ) {
					//just skipping the rule if excluded, so that it wont play any role in final judgement
					if ( in_array( $rule['rule_type'], $this->excluded_rules, true ) ) {

						continue;
					}
					$rule_object = $this->woocommerce_bwfan_rule_get_rule_object( $rule['rule_type'] );

					if ( is_object( $rule_object ) ) {
						$match = $rule_object->is_match( $rule );

						//assigning values to the array.
						//on false, as this is single group (bind by AND), one false would be enough to declare whole result as false so breaking on that point
						if ( false === $match ) {
							$iteration_results[ $group_id ] = 0;
							break;
						} else {
							$iteration_results[ $group_id ] = 1;
						}
					}
				}

				//checking if current group iteration combine returns true, if its true, no need to iterate other groups
				if ( isset( $iteration_results[ $group_id ] ) && 1 === $iteration_results[ $group_id ] ) {

					break;
				}
			}

			//checking count of all the groups iteration
			if ( count( $iteration_results ) > 0 ) {

				//checking for the any true in the groups
				if ( array_sum( $iteration_results ) > 0 ) {
					$display = true;
				} else {
					$display = false;
				}
			} else {

				//handling the case where all the rules got skipped
				$display = true;
			}
		} else {
			$display = true; //Always display the content if no rules have been configured.
		}

		return $display;
	}

	/**
	 * Creates an instance of a rule object
	 *
	 * @param $rule_type : The slug of the rule type to load.
	 *
	 * @return bwfan_Rule_Base or superclass of bwfan_Rule_Base
	 * @global array $woocommerce_bwfan_rule_rules
	 *
	 */
	public function woocommerce_bwfan_rule_get_rule_object( $rule_type ) {
		global $woocommerce_bwfan_rule_rules;
		if ( isset( $woocommerce_bwfan_rule_rules[ $rule_type ] ) ) {
			return $woocommerce_bwfan_rule_rules[ $rule_type ];
		}
		$class = 'bwfan_rule_' . $rule_type;

		if ( class_exists( $class ) ) {
			$woocommerce_bwfan_rule_rules[ $rule_type ] = new $class;

			return $woocommerce_bwfan_rule_rules[ $rule_type ];
		} else {
			return null;
		}
	}

	public function find_match() {
		$get_processed = $this->get_processed_rules();

		foreach ( $get_processed as $id => $results ) {

			if ( false === is_bool( $results ) ) {
				return false;
			}
			if ( true === $results ) {
				return $id;
			}
		}

		return false;
	}

	public function get_processed_rules() {
		return $this->processed;
	}

	public function load_rules_classes() {
		//Include our default rule classes
		include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/rules/base.php';
		include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/rules/abstracts.php';
		include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/rules/general.php';
		include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/rules/users.php';

		if ( class_exists( 'WooCommerce' ) ) {
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/rules/ab_cart.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/rules/order.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/rules/wc-customer.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/rules/cf7.php';
		}

		do_action( 'bwfan_rules_included', $this );

		if ( is_admin() || defined( 'DOING_AJAX' ) ) {
			//Include the admin interface builder
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/class-bwfan-input-builder.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/html-always.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/text.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/select.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/product-select.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/chosen-select.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/cart-category-select.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/cart-product-select.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-renewal.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-first-order.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-guest.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/date.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/time.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-upgrade.php';
			include plugin_dir_path( BWFAN_PLUGIN_FILE ) . 'rules/inputs/html-rule-is-downgrade.php';

			do_action( 'bwfan_rules_input_included', $this );
		}
	}

	public function get_all_groups() {
		return apply_filters(
			'bwfan_rules_groups', array(
				'wc_order'    => array(
					'title' => __( 'Order', 'wp-marketing-automations' ),
				),
				'wc_customer' => array(
					'title' => __( 'Customer', 'wp-marketing-automations' ),
				),
			)
		);
	}

	public function default_rule_types( $types ) {
		$types = array(
			'wc_order'    => array(
				'order_total'           => __( 'Total', 'wp-marketing-automations' ),
				'order_item_count'      => __( 'Item Count', 'wp-marketing-automations' ),
				'order_item_type'       => __( 'Item Type', 'wp-marketing-automations' ),
				'order_coupons'         => __( 'Coupons', 'wp-marketing-automations' ),
				'order_payment_gateway' => __( 'Payment Gateway', 'wp-marketing-automations' ),
				'order_shipping_method' => __( 'Shipping Method', 'wp-marketing-automations' ),
			),
			'wc_customer' => array(
				'is_first_order' => __( 'Is First Order', 'wp-marketing-automations' ),
				'customer_user' => __( 'Customer', 'wp-marketing-automations' ),
				'customer_role' => __( 'Customer User Role', 'wp-marketing-automations' ),
			),
		);

		return $types;
	}

	public function maybe_save_rules() {
		if ( null !== filter_input( INPUT_POST, 'bwfan_rule' ) ) {
			$automation_id = filter_input( INPUT_POST, 'automation_id' );

			update_post_meta( $automation_id, '_bwfan_rules', filter_input( INPUT_POST, 'bwfan_rule' ) );//phpcs:ignore WordPress.Security.NonceVerification
		}
	}

	public function get_environment_var( $key = 'order' ) {
		return isset( $this->environments[ $key ] ) ? $this->environments[ $key ] : false;
	}

	public function render_rules() {
		/**
		 * Call views here
		 */
		include_once( $this->rule_views_path() . '/rules-footer.php' );
	}

	public function rule_views_path() {
		return BWFAN_PLUGIN_DIR . '/rules/views';
	}

	public function maybe_load_scripts_templates() {
		/** Only run on Automation single page only */
		if ( false === BWFAN_Common::is_load_admin_assets( 'automation' ) ) {
			return;
		}

		$types = apply_filters( 'bwfan_rule_get_rule_types', array() );

		foreach ( $types as $ruleset ) {
			$rules_keys = array_keys( $ruleset );
			foreach ( $rules_keys as $key ) {
				$get_rule_object = $this->woocommerce_bwfan_rule_get_rule_object( $key );

				/**
				 * Get operator view each of the rule
				 *
				 * We added templates in single line to avoid unnecessary blank spaces
				 */
				if ( $get_rule_object instanceof BWFAN_Rule_Base && is_callable( array( $get_rule_object, 'operators_view' ) ) ) {
					?>
					<script type="text/template" id="tmpl-bwfan-rules-operator-<?php echo esc_html( $key ); ?>"><?php $get_rule_object->operators_view(); ?></script>
					<?php
				}

				if ( $get_rule_object instanceof BWFAN_Rule_Base && is_callable( array( $get_rule_object, 'conditions_view' ) ) ) {
					?>
					<script type="text/template" id="tmpl-bwfan-rules-conditions-<?php echo esc_html( $key ); ?>"><?php $get_rule_object->conditions_view(); ?></script>
					<?php
				}

				if ( $get_rule_object instanceof BWFAN_Rule_Base && is_callable( array( $get_rule_object, 'ui_view' ) ) ) {
					?>
					<script type="text/template" id="tmpl-bwfan-rules-ui-view-<?php echo esc_html( $key ); ?>"><?php $get_rule_object->ui_view(); ?></script>
					<?php
				}
			}
		}
	}


}
