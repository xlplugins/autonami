<?php

class BWFAN_WC_Cart_Recovery_Link extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'cart_recovery_link';
		$this->tag_description = __( 'Cart Recovery Link', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_cart_recovery_link', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = false;
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
		?>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Auto Apply Coupon Through Recovery Link', 'wp-marketing-automations' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <div class="radio-list">
                    <input type="radio" id="add_recovery_url_coupon" name="add_recovery_url_coupon" value="yes"><?php esc_html_e( 'Yes', 'wp-marketing-automations' ); ?>
                    <input type="radio" id="add_recovery_url_coupon" name="add_recovery_url_coupon" value="no" checked><?php esc_html_e( 'No', 'wp-marketing-automations' ); ?>
                </div>
            </div>
        </div>
        <div class="bwfan_mtag_wrap" style="display: none;">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Coupon Code', 'wp-marketing-automations' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <input type="text" class="bwfan-input-wrapper bwfan_tag_input recovery_url_coupon" id="recovery_url_coupon" name="recovery_url_coupon" placeholder="<?php esc_html_e( 'Enter Coupon Code', 'wp-marketing-automations' ); ?>">
            </div>
        </div>
		<?php

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
			return $this->get_dummy_preview();
		}

		$abandoned_row_details = BWFAN_Merge_Tag_Loader::get_data( 'cart_details' );
		$checkout_data         = json_decode( $abandoned_row_details['checkout_data'], true );
		$lang                  = isset( $checkout_data['lang'] ) ? $checkout_data['lang'] : '';

		if ( isset( $attr['coupon'] ) && ! empty( $attr['coupon'] ) ) {
			$cart_url = BWFAN_Common::wc_get_cart_recovery_url( $abandoned_row_details['token'], $attr['coupon'], $lang );
		} else {
			$cart_url = BWFAN_Common::wc_get_cart_recovery_url( $abandoned_row_details['token'], '', $lang );
		}

		return $this->parse_shortcode_output( $cart_url, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		$cart_url = add_query_arg( array(
			'bwfan-ab-id'       => md5( '123' ),
			'cart_restore_test' => 'yes',
		), wc_get_page_permalink( 'checkout' ) );

		return $cart_url;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_cart', 'BWFAN_WC_Cart_Recovery_Link' );
	BWFAN_Merge_Tag_Loader::register( 'wc_ab_cart', 'BWFAN_WC_Cart_Recovery_Link' );
}