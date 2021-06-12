<?php

if ( ! function_exists( 'bwf_get_remote_rest_args' ) ) {
	function bwf_get_remote_rest_args( $data = '', $method = 'POST' ) {
		return apply_filters( 'bwf_get_remote_rest_args', [
			'method'    => $method,
			'body'      => $data,
			'timeout'   => 0.01,
			'sslverify' => false,
		] );
	}
}
if ( ! function_exists( 'bwf_clean' ) ) {
	function bwf_clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( 'bwf_clean', $var );
		} else {
			return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		}
	}
}
if ( ! function_exists( 'bwf_get_states' ) ) {
	function bwf_get_states( $country = '', $state = '' ) {
		$country_states = apply_filters( 'bwf_get_states', include WooFunnel_Loader::$ultimate_path . 'helpers/states.php' );

		if ( empty( $state ) ) {
			return '';
		}
		if ( empty( $country ) ) {
			return $state;
		}
		if ( ! isset( $country_states[ $country ] ) ) {
			return $state;
		}
		if ( ! isset( $country_states[ $country ][ $state ] ) ) {
			return $state;
		}

		return $country_states[ $country ][ $state ];
	}
}