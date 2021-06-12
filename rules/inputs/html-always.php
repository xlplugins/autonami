<?php

class bwfan_Input_Html_Always {
	public function __construct() {
		// vars
		$this->type = 'Html_Always';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		esc_html_e( 'Funnel will run on every order on your store. This will override any other rule you define.', 'wp-marketing-automations' );
	}

}
