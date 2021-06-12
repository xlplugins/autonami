<?php

class bwfan_Input_Html_Rule_Is_First_Order {
	public function __construct() {
		// vars
		$this->type = 'Html_Rule_Is_First_Order';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		esc_html_e( 'This Funnel will initiate on very first order for the customer.', 'wp-marketing-automations' );
	}

}
