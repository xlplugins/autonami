<?php

class BWFAN_Compatibility_With_Bonanza {

	public function __construct() {

		/**
		 * Checking Bonanza existence
		 */
		if ( false === class_exists( 'XLWCFG_Core' ) ) {
			return;
		}

		add_filter( 'bwfan_exclude_cart_items_to_restore', [ $this, 'exclude_gifts' ], 99, 3 );

	}

	public function exclude_gifts( $bool, $key, $data ) {
		if ( isset( $data['xlwcfg_gift_id'] ) ) {
			$bool = true;
		}

		return $bool;
	}


}

new BWFAN_Compatibility_With_Bonanza();
