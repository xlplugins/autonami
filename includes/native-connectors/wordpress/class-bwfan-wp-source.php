<?php

final class BWFAN_WP_Source extends BWFAN_Source {
	private static $instance = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	protected function __construct() {
		$this->event_dir  = __DIR__;
		$this->nice_name  = __( 'WordPress', 'wp-marketing-automations' );
		$this->group_name = __( 'WordPress', 'wp-marketing-automations' );
		$this->group_slug = 'wp';
		$this->priority   = 20;
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return BWFAN_WP_Source|null
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
BWFAN_Load_Sources::register( 'BWFAN_WP_Source' );
