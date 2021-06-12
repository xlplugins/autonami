<?php

final class BWFAN_WP_Integration extends BWFAN_Integration {

	private static $ins = null;

	private function __construct() {
		$this->action_dir         = __DIR__;
		$this->native_integration = true;
		$this->nice_name          = __( 'WordPress', 'wp-marketing-automations' );
		$this->group_name         = __( 'WordPress', 'wp-marketing-automations' );
		$this->group_slug         = 'wp';
		$this->priority           = 25;

		add_filter( 'bwfan_email_services', array( $this, 'add_as_email_service' ), 10, 1 );
	}

	public static function get_instance() {
		if ( null === self::$ins ) {
			self::$ins = new self();
		}

		return self::$ins;
	}

	/**
	 * Add this integration to email services list.
	 *
	 * @param $email_services
	 *
	 * @return array
	 */
	public function add_as_email_service( $email_services ) {
		$integration                    = $this->get_slug();
		$email_services[ $integration ] = $this->nice_name;

		return $email_services;
	}
}

/**
 * Register this class as an integration.
 */
BWFAN_Load_Integrations::register( 'BWFAN_WP_Integration' );
