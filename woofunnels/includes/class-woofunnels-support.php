<?php

/**
 * @author woofunnels
 * @package WooFunnels
 */
class WooFunnels_Support {

	protected static $instance;
	public $validation = true;
	public $is_submitted;

	/**
	 *
	 * WooFunnels_Support constructor.
	 */
	public function __construct() {
	}

	/**
	 * Creates and instance of the class
	 * @return WooFunnels_Support
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Processing support request
	 *
	 * @param $posted_data
	 *
	 * @uses WooFunnels_API used to fire api request to generate request
	 * @uses WooFunnels_admin_notifications pushing success and failure notifications
	 * @since 1.0.4
	 */
	public function woofunnels_maybe_push_support_request( $posted_data ) {
	}


	public function fetch_tools_data() {
	}

	public function js_script() {
	}


}
