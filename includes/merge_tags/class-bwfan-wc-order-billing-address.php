<?php

class BWFAN_WC_Order_Billing_Address extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'order_billing_address';
		$this->tag_description = __( 'Order Billing Address', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_billing_address', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Show the html in popup for the merge tag.
	 */
	public function get_view() {
		$this->get_back_button();
		$this->get_address_format_html();

		if ( $this->support_fallback ) {
			$this->get_fallback();
		}

		$this->get_preview();
		$this->get_copy_button();
	}

	public function get_address_format_html() {
		$templates = array(
			'default'   => __( 'Formatted Address', 'wp-marketing-automations' ),
			'address_1' => __( 'Address 1', 'wp-marketing-automations' ),
			'address_2' => __( 'Address 2', 'wp-marketing-automations' ),
		);
		?>
        <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Address Format', 'wp-marketing-automations' ); ?></label>
        <select id="" class="bwfan-input-wrapper bwfan-mb-15 bwfan_tag_select" name="format" required>
			<?php
			foreach ( $templates as $slug => $name ) {
				echo '<option value="' . esc_attr__( $slug ) . '">' . esc_html__( $name ) . '</option>';
			}
			?>
        </select>
		<?php
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$order = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
		if ( ! $order instanceof WC_Order ) {
			return $this->parse_shortcode_output( ' ', $attr );
		}
		if ( isset( $attr['format'] ) && 'address_1' == $attr['format'] ) {
			$billing_address_1 = $order->get_billing_address_1();

			return $this->parse_shortcode_output( $billing_address_1, $attr );
		}
		if ( isset( $attr['format'] ) && 'address_2' == $attr['format'] ) {
			$billing_address_2 = $order->get_billing_address_2();

			return $this->parse_shortcode_output( $billing_address_2, $attr );
		}

		$billing_address = $order->get_formatted_billing_address();

		return $this->parse_shortcode_output( $billing_address, $attr );
	}


	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return '2024 Morningview Lane, New York 10013, USA';
	}

}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Billing_Address' );
}
