<?php

class BWFAN_WC_Order_Total extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'order_total';
		$this->tag_description = __( 'Order Total', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_total', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = false;
	}


	/**
	 * Show the html in popup for the merge tag.
	 */
	public function get_view() {
		$this->get_back_button();
		$this->get_price_inclusive_html();

		if ( $this->support_fallback ) {
			$this->get_fallback();
		}

		$this->get_preview();
		$this->get_copy_button();
	}

	public function get_price_inclusive_html() {
		$templates = array(
			''    => __( 'Inclusive', 'wp-marketing-automations' ),
			'exc' => __( 'Exclusive', 'wp-marketing-automations' ),
		);
		?>
        <label for="" class="bwfan-label-title"><?php esc_html_e( 'Tax', 'wp-marketing-automations' ); ?></label>
        <select id="" class="bwfan-input-wrapper bwfan-mb-15 bwfan_tag_select" name="price" required>
			<?php
			foreach ( $templates as $slug => $name ) {
				echo '<option value="' . esc_attr__( $slug ) . '">' . esc_html__( $name ) . '</option>';
			}
			?>
        </select>
		<?php
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return int|mixed|void
	 */
	public function parse_shortcode( $attr ) {
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$order = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );

		if ( ! $order instanceof WC_Order ) {
			return $this->parse_shortcode_output( '', $attr );
		}

		/** if price exc than minus total tax from the order total */
		if ( isset( $attr['price'] ) && 'exc' === $attr['price'] ) {
			$order_total = $order->get_total() - $order->get_total_tax();
			$order_total = apply_filters( 'bwfan_order_total_merge_format', $order_total );

			return $this->parse_shortcode_output( $order_total, $attr );
		}

		$order_total = $order->get_total();
		$order_total = apply_filters( 'bwfan_order_total_merge_format', $order_total );

		return $this->parse_shortcode_output( $order_total, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return integer
	 */
	public function get_dummy_preview() {
		return 255;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Total' );
}