<?php

class BWFAN_WC_Order_Used_Coupons extends BWFAN_Merge_Tag {

	private static $instance = null;

	public $supports_order_table = true;

	public function __construct() {
		$this->tag_name        = 'used_coupon';
		$this->tag_description = __( 'Used Coupons in Order', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_used_coupon', array( $this, 'parse_shortcode' ) );
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
		$templates = $this->get_coupon_view_data();
		$this->get_back_button();
		?>
        <label for="" class="bwfan-label-title"><?php echo esc_html__( 'Select Type', 'wp-marketing-automations' ); ?></label>
        <select id="" class="bwfan-input-wrapper bwfan-mb-15 bwfan_tag_select" name="type">
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

	public function get_coupon_view_data() {
		$templates = array(
			''             => __( 'Comma Separated', 'wp-marketing-automations' ),
			'first-coupon' => __( 'First Coupon', 'wp-marketing-automations' ),

		);

		return $templates;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		$parameters = [];
		if ( isset( $attr['type'] ) ) {
			$parameters['type'] = $attr['type'];
		}
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview( $parameters );
		}

		$order = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
		if ( version_compare( WC()->version, 3.7, '>=' ) ) {
			$coupons = $order->get_coupon_codes();
		} else {
			$coupons = $order->get_used_coupons();
		}

		if ( ! is_array( $coupons ) || count( $coupons ) === 0 ) {
			return $this->parse_shortcode_output( '', $attr );
		}
		$result = $this->get_coupon( $coupons, $parameters );

		return $this->parse_shortcode_output( $result, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @param $parameters
	 *
	 * @return string
	 */
	public function get_dummy_preview( $parameters ) {
		$coupons = array( 'ce10', 'ce15' );

		return $this->get_coupon( $coupons, $parameters );
	}

	public function get_coupon( $coupons, $parameters ) {
		$coupons = array_map( 'strtoupper', $coupons );

		if ( isset( $parameters['type'] ) && 'first-coupon' === $parameters['type'] ) {
			return $coupons[0];
		}

		return implode( ', ', $coupons );
	}

}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_order', 'BWFAN_WC_Order_Used_Coupons' );
}