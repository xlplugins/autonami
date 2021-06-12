<?php

class BWFAN_Input_Cart_Product_Select {
	public function __construct() {
		// vars
		$this->type = 'Cart_Product_Select';

		$this->defaults = array(
			'multiple'      => false,
			'allow_null'    => 0,
			'choices'       => array(),
			'default_value' => '',
			'ajax'          => 'true',
			'class'         => 'ajax_chosen_select_products',
		);
	}

	public function render( $field, $value = null ) {
		$field        = array_merge( $this->defaults, $field );
		$data_attr    = '';
		$chosen_class = '';
		if ( ! isset( $field['id'] ) ) {
			$field['id'] = sanitize_title( $field['id'] );
		}
		if ( true === $field['ajax'] ) {
			$chosen_class = 'bwfan-select2ajax-single'; // bwfan_select2_ajax
			$data_attr    = 'data-search="product_search" data-search-text="' . esc_attr__( 'Select Product', 'wp-marketing-automations' ) . '"';
		}
		if ( ! empty( $field['rule_type'] ) ) {
			$data_attr .= ' data-rule-type="' . $field['rule_type'] . '"';
		}
		?>
        <table class="bwfan-rules-condition_qty" style="width:100%;">
            <tr>
                <td style="width:32px;"><?php esc_html_e( 'Quantity', 'wp-marketing-automations' ); ?></td>
                <td><?php esc_html_e( 'Products', 'wp-marketing-automations' ); ?></td>
            </tr>
            <tr>
                <td style="width:32px; vertical-align:top;">
                    <input type="text" id="<?php echo esc_attr( $field['id'] ); ?>_qty" name="<?php echo $field['name']; //phpcs:ignore WordPress.Security.EscapeOutput ?>[qty]" value="<?php echo isset( $value['qty'] ) ? esc_attr__( sanitize_text_field( $value['qty'] ) ) : 1; ?>"/>
                </td>
                <td>
                    <select <?php echo( $data_attr ); //phpcs:ignore WordPress.Security.EscapeOutput ?> id="<?php echo esc_attr( $field['id'] ); ?>" name="<?php echo $field['name']; //phpcs:ignore WordPress.Security.EscapeOutput ?>[products]" class="<?php echo $chosen_class; ?>" data-placeholder="<?php echo( isset( $field['placeholder'] ) ? esc_attr__( sanitize_text_field( $field['placeholder'] ) ) : esc_html( 'Search...', 'wp-marketing-automations' ) ); ?>">
                        <option value=""><?php esc_html_e( 'Choose Product', 'wp-marketing-automations' ); ?></option>
						<?php
						$current     = isset( $value['products'] ) ? $value['products'] : array();
						$product_ids = ! empty( $current ) ? array_map( 'absint', $current ) : null;

						if ( $product_ids ) {
							foreach ( $product_ids as $product_id ) {

								$product      = wc_get_product( $product_id );
								$product_name = BWFAN_WooCommerce_Compatibility::woocommerce_get_formatted_product_name( $product );

								echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . esc_html( $product_name ) . '</option>';
							}
						}
						?>
                    </select>
                </td>
            </tr>
        </table>


		<?php

	}

}
