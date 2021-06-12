<?php

class bwfan_Input_Html_Rule_Is_Downgrade {
	public function __construct() {
		// vars
		$this->type = 'Html_Rule_Is_Downgrade';

		$this->defaults = array(
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		esc_html_e( 'This Funnel will initiate on orders that have downgraded subscriptions.', 'wp-marketing-automations' );
	}

}
