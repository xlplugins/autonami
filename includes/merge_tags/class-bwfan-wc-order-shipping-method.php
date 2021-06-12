<?php

class BWFAN_WC_Order_Shipping_Method extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'order_shipping_method';
		$this->tag_description = __( 'Order Shipping Method', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_order_shipping_method', array( $this, 'parse_shortcode' ) );
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
		$templates = array(
			''   => __( 'Title', 'wp-marketing-automations' ),
			'id' => __( 'ID', 'wp-marketing-automations' ),
		);
		$this->get_back_button();
		?>
		<label for="" class="bwfan-label-title"><?php echo esc_html__( 'Select Format', 'wp-marketing-automations' ); ?></label>
		<select id="" class="bwfan-input-wrapper bwfan-mb-15 bwfan_tag_select" name="template">
			<?php
			foreach ( $templates as $slug => $name ) {
				echo '<option value="' . esc_attr__( $slug ) . '">' . esc_attr__( $name ) . '</option>';
			}
			?>
		</select>
		<?php
		if ( $this->support_fallback ) {
			$this->get_fallback();
		}

		$this->get_preview();
		$this->get_copy_button();
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
			return $this->get_dummy_preview( $attr );
		}

		$shipping_method = '';
		$display         = isset( $attr['format'] ) ? $attr['format'] : 'title';
		switch ( $display ) {
			case 'id':
				// get id of first method
				$methods = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' )->get_shipping_methods();
				$item    = current( $methods );
				$id      = $item->get_method_id();
				// extract method base id only, discard instance id
				$split = strpos( $id, ':' );
				if ( $split ) {
					$id = substr( $id, 0, $split );
				}
				$shipping_method = $id;
				break;
			case 'title':
				$shipping_method = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' )->get_shipping_method();
				break;
		}

		return $this->parse_shortcode_output( $shipping_method, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public function get_dummy_preview( $attr ) {
		$display         = isset( $attr['format'] ) ? $attr['format'] : 'title';
		$shipping_method = '';
		switch ( $display ) {
			case 'id':
				$shipping_method = 'flat_rate';
				break;
			case 'title':
				$shipping_method = 'Flat rate';
				break;
		}

		return $shipping_method;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Shipping_Method' );
}