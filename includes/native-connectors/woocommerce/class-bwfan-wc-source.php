<?php

final class BWFAN_WC_Source extends BWFAN_Source {
	// source type contains slug of current source. this helps events to become a child of a source
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	protected function __construct() {
		$this->event_dir  = __DIR__;
		$this->nice_name  = __( 'WooCommerce', 'wp-marketing-automations' );
		$this->group_slug = 'wc';
		$this->group_name = __( 'WooCommerce', 'wp-marketing-automations' );
		$this->priority   = 10;
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_WC_Source|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

/**
 * Register this as a source.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Load_Sources::register( 'BWFAN_WC_Source' );
}
