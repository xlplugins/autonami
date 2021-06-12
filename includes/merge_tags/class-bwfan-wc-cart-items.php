<?php

class BWFAN_WC_Cart_Items extends Merge_Tag_Abstract_Product_Display {

	private static $instance = null;

	public $supports_cart_table = true;

	public function __construct() {
		$this->tag_name        = 'cart_items';
		$this->tag_description = __( 'Cart Items', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_cart_items', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = false;
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
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		if ( false !== BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			$args = array(
				'posts_per_page' => 1,
				'orderby'        => 'rand',
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'fields'         => 'ids',
			);

			$random_products = get_posts( $args );
			$products        = [];
			foreach ( $random_products as $product ) {
				if ( absint( $product ) > 0 ) {
					$products[] = wc_get_product( $product );
				}
			}
			$this->products = $products;
			$result         = $this->process_shortcode( $attr );

			return $this->parse_shortcode_output( $result, $attr );
		}

		$cart_details = BWFAN_Merge_Tag_Loader::get_data( 'cart_details' );
		$items        = maybe_unserialize( $cart_details['items'] );
		$products     = [];

		foreach ( $items as $item ) {
			$products[] = $item['data'];
		}
		$this->cart     = $items;
		$this->data     = [
			'coupons'            => maybe_unserialize( $cart_details['coupons'] ),
			'fees'               => maybe_unserialize( $cart_details['fees'] ),
			'shipping_total'     => maybe_unserialize( $cart_details['shipping_total'] ),
			'shipping_tax_total' => maybe_unserialize( $cart_details['shipping_tax_total'] ),
			'total'              => maybe_unserialize( $cart_details['total'] ),
			'currency'           => maybe_unserialize( $cart_details['currency'] ),
		];
		$this->products = $products;
		$result         = $this->process_shortcode( $attr );

		return $this->parse_shortcode_output( $result, $attr );
	}


}

/**
 * Register this merge tag to a group.
 *
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_cart', 'BWFAN_WC_Cart_Items' );
	BWFAN_Merge_Tag_Loader::register( 'wc_ab_cart', 'BWFAN_WC_Cart_Items' );
}