<?php

class BWFAN_Admin_Email extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'admin_email';
		$this->tag_description = __( 'Administration Email ', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_admin_email', array( $this, 'parse_shortcode' ) );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Parse the merge tag and return its value.
	 *
	 * @param $attr
	 *
	 * @return mixed|void
	 */
	public function parse_shortcode( $attr ) {
		return $this->parse_shortcode_output( BWFAN_Common::$admin_email, $attr );
	}


}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'bwfan_default', 'BWFAN_Admin_Email' );
