<?php

final class BWFAN_CF7_Source extends BWFAN_Source {
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	protected function __construct() {
		$this->event_dir  = __DIR__;
		$this->nice_name  = __( 'Contact Form 7', 'wp-marketing-automations' );
		$this->group_name = __( 'Forms', 'wp-marketing-automations' );
		$this->group_slug = 'forms';
		$this->priority   = 110;
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_CF7_Source|null
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
if ( bwfan_is_cf7_active() ) {
	BWFAN_Load_Sources::register( 'BWFAN_CF7_Source' );
}
