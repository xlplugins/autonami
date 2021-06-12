<?php

class bwfan_Input_Chosen_Select {

	public function __construct() {
		// vars
		$this->type = 'Chosen_Select';

		$this->defaults = array(
			'multiple'      => 1,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => array(),
			'class'         => '',
			'ajax'          => false,
			'rule_type'     => '',

		);
	}

	public function render( $field, $value = null ) {
		$field        = array_merge( $this->defaults, $field );
		$current      = $value ? $value : array();
		$choices      = $field['choices'];
		$chosen_class = 'bwfan_select2 ';
		$data_attr    = '';
		$multiple     = $field['multiple'] ? 'multiple' : '';

		if ( true === $field['ajax'] ) {
			$chosen_class = 'bwfan_select2_ajax ';
			$data_attr    = 'data-search-type="' . $field['search_type'] . '"';
		}
		if ( ! empty( $field['rule_type'] ) ) {
			$data_attr .= ' data-rule-type="' . $field['rule_type'] . '"';
		}

		?>

        <select <?php echo( $data_attr ); //phpcs:ignore WordPress.Security.EscapeOutput ?> id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo $field['name']; //phpcs:ignore WordPress.Security.EscapeOutput ?>[]" class="<?php echo esc_attr( $chosen_class ) . esc_attr( $field['class'] ); ?>" <?php echo esc_attr__( $multiple ); ?> data-placeholder="<?php echo( isset( $field['placeholder'] ) ? esc_attr__( sanitize_text_field( $field['placeholder'] ) ) : esc_html( 'Search...', 'wp-marketing-automations' ) ); ?>">
			<?php
			foreach ( $choices as $choice => $title ) {
				$selected = in_array( $choice, $current, true );
				echo '<option value="' . ( esc_attr( $choice ) ) . '" ' . selected( $selected, true, false ) . '>' . esc_html( $title ) . '</option>';
			}
			?>
        </select>

		<?php
	}

}

?>
