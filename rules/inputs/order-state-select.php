<?php

class bwfan_Input_Order_State_Select {
	public function __construct() {
		// vars
		$this->type = 'Order_State_Select';

		$this->defaults = array(
			'multiple'      => 0,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => array(),
			'class'         => '',
		);
	}

	public function render( $field, $value = null ) {

		$field = array_merge( $this->defaults, $field );
		if ( ! isset( $field['id'] ) ) {
			$field['id'] = sanitize_title( $field['id'] );
		}
		$chosen_states = $value;

		?>

        <select id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo $field['name']; //phpcs:ignore WordPress.Security.EscapeOutput ?>[states][]" class="chosen_select <?php echo esc_attr( $field['class'] ); ?>" multiple="multiple" data-placeholder="<?php echo( isset( $field['placeholder'] ) ? esc_attr__( sanitize_text_field( $field['placeholder'] ) ) : esc_html( 'Search...', 'wp-marketing-automations' ) ); ?>">
			<?php
			WC()->countries->country_dropdown_options( '', $chosen_states );
			?>
        </select>

		<?php
	}

}

?>
