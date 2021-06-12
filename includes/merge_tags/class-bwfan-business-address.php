<?php

class BWFAN_Business_Address extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'business_address';
		$this->tag_description = __( 'Business Address', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_business_address', array( $this, 'parse_shortcode' ) );
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
		if ( ! isset( $global_settings['bwfan_setting_business_address'] ) || empty( $global_settings['bwfan_setting_business_address'] ) ) {
			return '';
		}

		$bussiness_address = $global_settings['bwfan_setting_business_address'];

		return $this->parse_shortcode_output( $bussiness_address, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'dummy address';
	}


}

/**
 * Register this merge tag to a group.
 */
BWFAN_Merge_Tag_Loader::register( 'bwfan_default', 'BWFAN_Business_Address' );
