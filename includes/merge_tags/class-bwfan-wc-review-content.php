<?php

class BWFAN_WC_Review_Content extends BWFAN_Merge_Tag {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'review_content';
		$this->tag_description = __( 'Review Content', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_review_content', array( $this, 'parse_shortcode' ) );
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

		$wc_comment_details = BWFAN_Merge_Tag_Loader::get_data( 'wc_comment_details' );
		$comment_message    = $wc_comment_details['comment_message'];

		return $this->parse_shortcode_output( $comment_message, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'This is the dummy review content', 'wp-marketing-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_review', 'BWFAN_WC_Review_Content' );
}