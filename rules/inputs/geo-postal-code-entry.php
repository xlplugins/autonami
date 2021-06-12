<?php

class bwfan_Input_Geo_Postal_Code_Entry {

	public function __construct() {
		// vars
		$this->type = 'Geo_Postal_Code_Entry';

		$this->defaults = array(
			'multiple'      => 0,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => '',
			'class'         => '',
			'placeholder'   => '',
		);
	}

	public function render( $field, $value = null ) {
		$field = array_merge( $this->defaults, $field );
		if ( ! isset( $field['id'] ) ) {
			$field['id'] = sanitize_title( $field['id'] );
		}
		?>

        <table style="width:100%;">
            <tr>
                <td style="width:162px;"><?php esc_html_e( 'Distance ( km )', 'wp-marketing-automations' ); ?></td>
                <td><?php esc_html_e( 'Zip/Postalcode ( One per line )', 'wp-marketing-automations' ); ?></td>
            </tr>
            <tr>
                <td style="width:162px; vertical-align:top;">
                    <input type="text" id="<?php echo esc_attr( $field['id'] ); ?>_qty" name="<?php echo $field['name']; //phpcs:ignore WordPress.Security.EscapeOutput ?>[qty]" value="<?php echo isset( $value['qty'] ) ? esc_attr__( sanitize_text_field( $value['qty'] ) ) : 1; ?>"/>
                </td>
                <td>
					<?php echo '<textarea style="width:100%" rows="20" name="' . $field['name'] . '[codes]" type="text" id="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '">' . esc_textarea( $value['codes'] ) . '</textarea>'; //phpcs:ignore WordPress.Security.EscapeOutput ?>
                </td>
                </td>
            </tr>
        </table>
		<?php
	}

}
