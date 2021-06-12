<?php

class BWFAN_CF7_Form_ID extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'cf7_form_id';
		$this->tag_description = __( 'Form Id', 'autonami-automations-pro' );
		add_shortcode( 'bwfan_cf7_form_id', array( $this, 'parse_shortcode' ) );
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

		$form_id = BWFAN_Merge_Tag_Loader::get_data( 'form_id' );

		return $this->parse_shortcode_output( $form_id, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 *
	 * @todo:Hard values shouldn't be passed
	 */
	public function get_dummy_preview() {
		return '11';
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_cf7_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'cf7', 'BWFAN_CF7_Form_ID' );
}
