<?php

class bwfan_Input_Html_Rule_Is_Upgrade {
	public function __construct() {
		// vars
		$this->type = 'Html_Rule_Is_Upgrade';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		esc_html_e( 'This Page will show on orders that have upgraded subscriptions.', 'wp-marketing-automations' );
	}

}
