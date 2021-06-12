<?php

class bwfan_Input_Term_Select extends bwfan_Input_Text {

	public function __construct() {
		// vars
		$this->type = 'Term_Select';

		$this->defaults = array(
			'multiple'      => 0,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => '',
			'class'         => '',
		);
	}

	public function render( $field, $value = null ) {

		$field = array_merge( $this->defaults, $field );
		if ( ! isset( $field['id'] ) ) {
			$field['id'] = sanitize_title( $field['id'] );
		}

		$args = array(
			'name'             => $field['name'],
			'id'               => $field['id'],
			'show_option_none' => __( 'Select category' ),
			'show_count'       => 0,
			'orderby'          => 'name',
			'echo'             => 0,
			'taxonomy'         => 'product_cat',
			'selected'         => absint( $value ),
		);

		echo wp_dropdown_categories( $args ); //phpcs:ignore WordPress.Security.EscapeOutput
	}

}
