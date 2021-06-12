<?php

class BWF_AS {
	private static $instance;
	protected $start_time = 0;

	public function __construct() {
		global $wpdb;
		$wpdb->bwf_actions      = $wpdb->prefix . 'bwf_actions';
		$wpdb->bwf_action_claim = $wpdb->prefix . 'bwf_action_claim';

		$this->start_time = time();
	}

	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Change the data store
	 */
	public function change_data_store() {
		/** Removing all action data store change filter and then assign ours */
		remove_all_filters( 'action_scheduler_store_class' );
		add_filter( 'action_scheduler_store_class', [ $this, 'set_store_class' ], 1000000, 1 );

		/** Removing all log data store change filter and then assign ours */
		remove_all_filters( 'action_scheduler_logger_class' );
		add_filter( 'action_scheduler_logger_class', [ $this, 'set_logger_class' ], 1000000, 1 );

		/** Removing all AS memory exceeds filter */
		remove_all_filters( 'action_scheduler_memory_exceeded' );
		add_filter( 'action_scheduler_memory_exceeded', [ $this, 'check_memory_exceeded' ], 1000000, 1 );
	}

	/**
	 * Override the action store with our own
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	public function set_store_class( $class ) {
		return BWF_AS_Action_Store::class;
	}

	/**
	 * Override the logger with our own
	 *
	 * @param string $class
	 *
	 * @return string
	 */
	public function set_logger_class( $class ) {
		return BWF_AS_Log_Store::class;
	}

	/**
	 * Override memory exceeded filter value
	 *
	 * @param $memory_exceeded
	 *
	 * @return bool
	 */
	public function check_memory_exceeded( $memory_exceeded ) {
		if ( true === $memory_exceeded ) {
			return $memory_exceeded;
		}

		return $this->validate_time_breach();
	}

	/**
	 * Validate if call input time reached
	 *
	 * @return bool
	 */
	public function validate_time_breach() {
		$per_call_time = apply_filters( 'bwfan_as_per_call_time', 30 );
		if ( ( time() - $this->start_time ) >= $per_call_time || $this->memory_exceeded() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check server memory limit.
	 * Using 75% max
	 *
	 * @return bool
	 */
	public function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.75;
		$current_memory = memory_get_usage( true );

		return ( $current_memory >= $memory_limit );
	}

	/**
	 * Get Server memory limit value
	 *
	 * @return int|mixed
	 */
	public function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			$memory_limit = '128M'; // Sensible default, and minimum required by WooCommerce
		}

		if ( ! $memory_limit || - 1 === $memory_limit || '-1' === $memory_limit ) {
			// Unlimited, set to 32GB.
			$memory_limit = '32G';
		}

		return $this->convert_hr_to_bytes( $memory_limit );
	}

	public function convert_hr_to_bytes( $value ) {
		if ( function_exists( 'wp_convert_hr_to_bytes' ) ) {
			return wp_convert_hr_to_bytes( $value );
		}

		$value = strtolower( trim( $value ) );
		$bytes = (int) $value;

		if ( false !== strpos( $value, 'g' ) ) {
			$bytes *= GB_IN_BYTES;
		} elseif ( false !== strpos( $value, 'm' ) ) {
			$bytes *= MB_IN_BYTES;
		} elseif ( false !== strpos( $value, 'k' ) ) {
			$bytes *= KB_IN_BYTES;
		}

		// Deal with large (float) values which run into the maximum integer size.
		return min( $bytes, PHP_INT_MAX );
	}
}