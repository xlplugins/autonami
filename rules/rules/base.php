<?php

/**
 * Base class for a Conditional_Content rule.
 */
class BWFAN_Rule_Base {

	public $supports = array();
	public $name = '';
	public $description = '';
	protected $need_order_sync = false;
	protected $sync_order = null;

	public function __construct( $name ) {
		$this->name = $name;
	}

	public function get_name() {
		return $this->name;
	}

	/**
	 * Checks if the conditions defined for this rule object have been met.
	 *
	 * @return boolean
	 */
	public function is_match( $rule_data ) {
		return false;
	}

	/**
	 * Helper function to wrap the return value from is_match and apply filters or other modifications in sub classes.
	 *
	 * @param boolean $result The result that should be returned.
	 * @param array $rule_data The array config object for the current rule.
	 *
	 * @return boolean
	 */
	public function return_is_match( $result, $rule_data ) {
		return apply_filters( 'bwfan_rules_is_match', $result, $rule_data );
	}

	/*
	 * Gets the input object type slug for this rule object.
	 */
	public function supports( $env ) {
		return in_array( $env, $this->supports, true );
	}

	public function operators_view() {
		$operators = $this->get_possible_rule_operators();
		if ( empty( $operators ) ) {
			return;
		}

		$operator_args = array(
			'input'   => 'select',
			'name'    => 'bwfan_rule[<%= groupId %>][<%= ruleId %>][operator]',
			'choices' => $operators,
		);
		bwfan_Input_Builder::create_input_field( $operator_args );
	}

	/**
	 * Gets the list of possible rule operators available for this rule object.
	 *
	 * Override to return your own list of operators.
	 *
	 * @return array
	 */
	public function get_possible_rule_operators() {
		return array(
			'==' => __( 'is equal to', 'wp-marketing-automations' ),
			'!=' => __( 'is not equal to', 'wp-marketing-automations' ),
		);
	}

	public function conditions_view() {
		$condition_input_type = $this->get_condition_input_type();
		$values               = $this->get_possible_rule_values();
		$value_args           = array(
			'input'   => $condition_input_type,
			'name'    => 'bwfan_rule[<%= groupId %>][<%= ruleId %>][condition]',
			'choices' => $values,
		);

		bwfan_Input_Builder::create_input_field( $value_args );
		echo $this->add_description(); //phpcs:ignore WordPress.Security.EscapeOutput
	}

	public function get_condition_input_type() {
		return 'Select';
	}

	/**
	 * Get's the list of possible values for the rule.
	 *
	 * Override to return the correct list of possible values for your rule object.
	 * @return array
	 */
	public function get_possible_rule_values() {
		return array();
	}

	public function ui_view() {
		esc_html_e( 'Rule Preview here..', 'wp-marketing-automations' );
	}

	public function get_ui_preview_data() {
		return $this->get_possible_rule_values();
	}

	public function add_description() {
		ob_start();
		if ( true === $this->need_order_sync && 0 === $this->sync_order() ) {
			echo '<div class="error" style="position:relative;" class="bwfan-display-none">';
			echo '<p>' . esc_html__( 'This rule requires indexing of previous orders. Kindly ', 'wp-marketing-automations' );
			echo '<a href="javascript:void(0)" class="bwfan_sync_customer_order">' . esc_html__( 'Sync Now', 'wp-marketing-automations' ) . '</a></p>';
			echo '</div>';
		}

		if ( empty( $this->description ) ) {
			return ob_get_clean();
		}

		echo '<div class="clearfix bwfan_field_desc">' . esc_html__( $this->description ) . '</div>';

		return ob_get_clean();
	}

	protected function sync_order() {
		if ( null !== $this->sync_order ) {
			return $this->sync_order;
		}
		$this->sync_order = get_option( '_bwf_db_upgrade', 0 );

		return $this->sync_order;
	}

}
