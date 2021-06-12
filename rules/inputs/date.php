<?php

class bwfan_Input_Date extends bwfan_Input_Text {

	public function __construct() {
		$this->type = 'Date';

		parent::__construct();
	}

	public function render( $field, $value = null ) {
		$field = array_merge( $this->defaults, $field );
		if ( ! isset( $field['id'] ) ) {
			$field['id'] = sanitize_title( $field['id'] );
		}

		echo '<input name="' . $field['name'] . '" type="text" id="' . esc_attr( $field['id'] ) . '" class="bwfan-date-picker-field' . esc_attr( $field['class'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $value . '" />'; //phpcs:ignore WordPress.Security.EscapeOutput
	}

}
