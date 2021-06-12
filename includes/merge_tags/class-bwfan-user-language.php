<?php

class BWFAN_User_Language extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'user_language';
		$this->tag_description = __( 'User Language', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_user_language', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = true;
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
	 * @return mixed|string|void
	 */
	public function parse_shortcode( $attr ) {
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$language = BWFAN_Merge_Tag_Loader::get_data( 'user_language' );

		return $this->parse_shortcode_output( $language, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'en';
	}


}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'bwfan_default', 'BWFAN_User_Language' );
