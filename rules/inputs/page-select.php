<?php

class bwfan_Input_Page_Select extends bwfan_Input_Text {

	public function __construct() {
		// vars
		$this->type = 'Page_Select';

		$this->defaults = array(
			'multiple'      => 0,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => '',
			'class'         => 'ajax_chosen_select_products',
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
			'sort_column'      => 'menu_order',
			'sort_order'       => 'ASC',
			'show_option_none' => ' ',
			'class'            => '',
			'echo'             => false,
			'selected'         => absint( $value ),
		);

		echo wp_dropdown_pages( $args ); //phpcs:ignore WordPress.Security.EscapeOutput

	}

}
