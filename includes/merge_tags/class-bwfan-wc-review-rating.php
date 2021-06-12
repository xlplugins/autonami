<?php

class BWFAN_WC_Review_Rating extends BWFAN_Merge_Tag {

	private static $instance = null;

	public function __construct() {
		$this->tag_name        = 'review_rating';
		$this->tag_description = __( 'Review Rating', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_review_rating', array( $this, 'parse_shortcode' ) );
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
	 * @return int|mixed|void
	 */
	public function parse_shortcode( $attr ) {
		if ( true === BWFAN_Merge_Tag_Loader::get_data( 'is_preview' ) ) {
			return $this->get_dummy_preview();
		}

		$wc_comment_details = BWFAN_Merge_Tag_Loader::get_data( 'wc_comment_details' );
		$rating_number      = $wc_comment_details['rating_number'];

		return $this->parse_shortcode_output( $rating_number, $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return integer
	 */
	public function get_dummy_preview() {
		return 4;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_review', 'BWFAN_WC_Review_Rating' );
}