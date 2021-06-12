<?php

class bwfan_Input_Time extends bwfan_Input_Text {

	public function __construct() {
		$this->type = 'Time';

		parent::__construct();
	}

	public function render( $field, $value = null ) {
		$field = array_merge( $this->defaults, $field );
		if ( ! isset( $field['id'] ) ) {
			$field['id'] = sanitize_title( $field['id'] );
		}
		$value = ( null === $value ) ? '00:00' : $value;
		echo '<input placeholder="For eg: 23:59" name="' . $field['name'] . '" type="time" id="' . esc_attr( $field['id'] ) . '" class="bwfan-time-picker-field' . esc_attr( $field['class'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" value="' . $value . '" />'; //phpcs:ignore WordPress.Security.EscapeOutput, WordPress.Security.EscapeOutput
	}

}
