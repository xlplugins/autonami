<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_names = [];
foreach ( $products as $product ) {
	if ( ! $product instanceof WC_Product ) {
		continue;
	}
	$product_names[] = esc_html__( BWFAN_Common::get_name( $product ) );
}

echo implode( ', ', $product_names ); //phpcs:ignore WordPress.Security.EscapeOutput
