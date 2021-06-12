<?php

class BWFAN_WC_Create_Coupon_Code extends BWFAN_Merge_Tag {

	private static $instance = null;

	public $support_fallback = false;
	public $coupon_id = null;

	public function __construct() {
		$this->tag_name        = 'create_coupon';
		$this->tag_description = __( 'Create a new coupon', 'wp-marketing-automations' );

		add_shortcode( 'bwfan_create_coupon', array( $this, 'parse_shortcode' ) );
		add_action( 'bwfan_coupon_created', array( $this, 'set_coupon_id' ), 10, 1 );
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Show the html in popup for the merge tag.
	 */
	public function get_view() {
		$this->get_back_button();
		$this->get_coupon_fields();
		if ( $this->support_fallback ) {
			$this->get_fallback();
		}

		$this->get_preview();
		$this->get_copy_button();
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

		$parameters             = [];
		$parameters['restrict'] = '';
		$parameters['email']    = BWFAN_Merge_Tag_Loader::get_data( 'email' );

		if ( isset( $attr['parent_coupon'] ) ) {
			$parameters['coupon'] = $attr['parent_coupon'];
		}
		if ( isset( $attr['coupon_name'] ) ) {
			$parameters['coupon_name'] = $attr['coupon_name'];
		}
		if ( isset( $attr['expiry_type'] ) ) {
			$parameters['expiry_type'] = $attr['expiry_type'];
		}
		if ( isset( $attr['expiry'] ) ) {
			$parameters['expiry'] = $attr['expiry'];
		}
		if ( isset( $attr['restrict'] ) ) {
			$parameters['restrict'] = 1;
		}

		$parameters['automation_id'] = BWFAN_Merge_Tag_Loader::get_data( 'automation_id' );
		$all_actions                 = BWFAN_Load_Integrations::get_all_integrations();
		$action_data                 = array(
			'processed_data' => $parameters,
		);
		$result                      = $all_actions['wc']['wc_create_coupon']->execute_action( $action_data );

		if ( is_array( $result ) && count( $result ) > 0 && 4 === $result['status'] ) { // Error in coupon creation
			// @todo log this error somewhere
			return '';
		}

		$coupon_id = $this->coupon_id;
		$coupon    = get_post( $coupon_id );

		return $this->parse_shortcode_output( strtoupper( $coupon->post_title ), $attr );
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return 'Dummy Coupon Text';
	}

	public function set_coupon_id( $coupon_id ) {
		$this->coupon_id = $coupon_id;
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_customer', 'BWFAN_WC_Create_Coupon_Code' );
	BWFAN_Merge_Tag_Loader::register( 'wc_cart', 'BWFAN_WC_Create_Coupon_Code' );
	BWFAN_Merge_Tag_Loader::register( 'wc_ab_cart', 'BWFAN_WC_Create_Coupon_Code' );
}
