<?php

class bwfan_Input_Select {

	public function __construct() {
		// vars
		$this->type = 'Select';

		$this->defaults = array(
			'multiple'      => 0,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => '',
			'class'         => '',
			'null_text'     => __( '- Select -', '' ),
			'disabled'      => false,
		);
	}

	public function render( $field, $value = null ) {
		$field          = array_merge( $this->defaults, $field );
		$field['value'] = $value;
		$optgroup       = false;

		// determine if choices are grouped (2 levels of array)
		if ( is_array( $field['choices'] ) ) {
			foreach ( $field['choices'] as $v ) {
				if ( is_array( $v ) ) {
					$optgroup = true;
				}
			}
		}

		// value must be array
		if ( ! is_array( $field['value'] ) ) {
			// perhaps this is a default value with new lines in it?
			if ( strpos( $field['value'], "\n" ) !== false ) {
				// found multiple lines, explode it
				$field['value'] = explode( "\n", $field['value'] );
			} else {
				$field['value'] = array( $field['value'] );
			}
		}

		// trim value
		$field['value'] = array_map( 'trim', $field['value'] );
		$multiple       = '';

		if ( $field['multiple'] ) {
			$multiple      = ' multiple="multiple" size="5" ';
			$field['name'] .= '[]';
		}
		$disabled = '';

		if ( true === $field['disabled'] ) {
			$disabled = 'disabled';
		}
		echo '<select ' . esc_attr( $disabled ) . ' id="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . $field['name'] . '" ' . $multiple . ' >'; //phpcs:ignore WordPress.Security.EscapeOutput

		// null
		if ( $field['allow_null'] ) {
			echo '<option value="null"> ' . esc_attr( $field['null_text'] ) . ' </option>';
		}

		// loop through values and add them as options
		if ( is_array( $field['choices'] ) ) {
			foreach ( $field['choices'] as $key => $value ) {
				if ( $optgroup ) {
					// this select is grouped with optgroup
					if ( '' !== $key ) {
						echo '<optgroup label="' . esc_attr( $key ) . '">';
					}

					if ( is_array( $value ) ) {
						foreach ( $value as $id => $label ) {
							$selected = in_array( $id, $field['value'], true ) ? 'selected="selected"' : '';

							echo '<option value="' . esc_attr( $id ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>'; //phpcs:ignore WordPress.Security.EscapeOutput
						}
					}

					if ( '' !== $key ) {
						echo '</optgroup>';
					}
				} else {
					$selected = in_array( $key, $field['value'], true ) ? 'selected="selected"' : '';
					echo '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $value ) . '</option>'; //phpcs:ignore WordPress.Security.EscapeOutput
				}
			}
		}

		echo '</select>';
	}

}
