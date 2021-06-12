<?php

class BWFAN_Shortcodes {
	private static $ins = null;

	public function __construct() {

		$shortcodes = $this->get_shortcodes();

		foreach ( $shortcodes as $shortcode ) {
			add_shortcode( $shortcode, array( $this, $shortcode . '_output' ) );
		}
	}

	public function get_shortcodes() {
		return apply_filters(
			'bwfan_shortcodes', array(
				'bwfan_yes_link',
				'bwfan_no_link',
				'bwfan_variation_selector_form',
			)
		);
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self;
		}

		return self::$ins;
	}

	public function bwfan_yes_link_output( $atts, $html = '' ) {
		$atts = shortcode_atts(
			array(
				'key'   => '',
				'class' => '',
			), $atts
		);

		if ( '' === $atts['key'] ) {
			return __( 'Key is a required parameter in this shortcode', 'wp-marketing-automations' );
		}

		return sprintf( '<a href="javascript:void(0);" class="%s" data-key="%s">%s</a>', 'bwfan_upsell ' . $atts['class'], $atts['key'], do_shortcode( $html ) );
	}

	public function bwfan_no_link_output( $atts, $html = '' ) {
		$atts = shortcode_atts(
			array(
				'key'   => '',
				'class' => '',
			), $atts
		);

		if ( '' === $atts['key'] ) {
			return __( 'Key is a required parameter in this shortcode', 'wp-marketing-automations' );
		}

		return sprintf( '<a href="javascript:void(0);" class="%s" data-key="%s">%s</a>', 'bwfan_skip_offer ' . $atts['class'], $atts['key'], do_shortcode( $html ) );
	}

	public function bwfan_variation_selector_form_output( $atts ) {
		$atts = shortcode_atts(
			array(
				'key'   => '',
				'label' => __( 'No, thanks', 'wp-marketing-automations' ),
			), $atts
		);

		if ( '' === $atts['key'] ) {
			return __( 'Key is a required parameter in this shortcode', 'wp-marketing-automations' );
		}

		$data = BWFAN_Core()->data->get( '_current_offer_data' );
		if ( false === $data ) {
			return '';
		}

		if ( ! isset( $data->products->{$atts['key']} ) ) {
			return '';
		}

		if ( ! isset( $data->products->{$atts['key']}->variations_data ) ) {
			return '';
		}
		$product_raw = array(
			'key'     => $atts['key'],
			'product' => $data->products->{$atts['key']},
		);
		ob_start();
		BWFAN_Core()->template_loader->get_template_part( 'product/variation-form', $product_raw );

		return ob_get_clean();
	}


}

if ( class_exists( 'BWFAN_Core' ) ) {
	BWFAN_Core::register( 'shortcodes', 'BWFAN_Shortcodes' );
}
