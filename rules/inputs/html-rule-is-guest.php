<?php

class bwfan_Input_Html_Rule_Is_Guest {
	public function __construct() {
		// vars
		$this->type = 'Html_Rule_Is_Guest';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		esc_html_e( 'This Funnel will initiate on guest orders.', 'wp-marketing-automations' );
	}

}
