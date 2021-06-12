<?php

class BWFAN_Rule_General_Always extends BWFAN_Rule_Base {
	public $supports = array( 'cart', 'order' );

	public function __construct() {
		parent::__construct( 'general_always' );
	}

	public function get_possible_rule_operators() {
		return null;
	}

	public function get_possible_rule_values() {
		return null;
	}

	public function get_condition_input_type() {
		return 'Html_Always';
	}

	public function is_match( $rule_data ) {
		return true;
	}

}
