<?php

class BWFAN_Business_Name extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'business_name';
		$this->tag_description = __( 'Business Name', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_business_name', array( $this, 'parse_shortcode' ) );
		$this->support_fallback = false;
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

		$global_settings = BWFAN_Common::get_global_settings();
		if ( ! isset( $global_settings['bwfan_setting_business_name'] ) || empty( $global_settings['bwfan_setting_business_name'] ) ) {
			return '';
		}

		$bussiness_name = $global_settings['bwfan_setting_business_name'];

		return $this->parse_shortcode_output( $bussiness_name, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'test';
	}


}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'bwfan_default', 'BWFAN_Business_Name' );
