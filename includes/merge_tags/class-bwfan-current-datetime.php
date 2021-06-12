<?php

class BWFAN_Current_Datetime extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'current_datetime';
		$this->tag_description = __( 'Current datetime as per your website\'s specified timezone', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_current_datetime', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = false;
		$this->support_date     = true;
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
		$parameters           = [];
		$parameters['format'] = isset( $attr['format'] ) ? $attr['format'] : 'j M Y';
		if ( isset( $attr['modify'] ) ) {
			$parameters['modify'] = $attr['modify'];
		}

		$date_time = $this->format_datetime( date( 'Y-m-d H:i:s' ), $parameters );

		return $this->parse_shortcode_output( $date_time, $attr );
	}

}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'bwfan_default', 'BWFAN_Current_Datetime' );
