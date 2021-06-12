<?php

class BWF_Compatibility_With_WOOCS {

	public function __construct() {

	}

	public function is_enable() {
		if ( isset( $GLOBALS['WOOCS'] ) && $GLOBALS['WOOCS'] instanceof WOOCS ) {
			return true;
		}

		return false;
	}


	/**
	 *
	 * Modifies the amount for the fixed discount given by the admin in the currency selected.
	 *
	 * @param integer|float $price
	 *
	 * @return float
	 */
	public function alter_fixed_amount( $price, $currency = null ) {

		return $GLOBALS['WOOCS']->woocs_exchange_value( $price );
	}

	function get_fixed_currency_price_reverse( $price, $from = null, $base = null ) {
		$currencies = get_option( 'woocs' );

		$from = ( is_null( $from ) ) ? $GLOBALS['WOOCS']->current_currency : $from;
		$base = ( is_null( $base ) ) ? $GLOBALS['WOOCS']->default_currency : $base;

		if ( is_array( $currencies ) && ! empty( $currencies ) && $currencies[ $base ]['rate'] == 1 ) { //phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			foreach ( $currencies as $key => $value ) {
				if ( $key === $from ) {
					$rate  = $value['rate'];
					$price = $price * ( 1 / $rate );
				}
			}
		}

		return $price;
	}


}

BWF_Plugin_Compatibilities::register( new BWF_Compatibility_With_WOOCS(), 'woocs' );



