<?php

class BWFAN_WC_Item_Attribute extends BWFAN_Cart_Display {

	private static $instance = null;


	public function __construct() {
		$this->tag_name        = 'item_attribute';
		$this->tag_description = __( 'Purchased Item Attribute', 'wp-marketing-automations' );
		add_shortcode( 'bwfan_item_attribute', array( $this, 'parse_shortcode' ) );
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
		$this->data_key();
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

		$this->item_type = 'item_attribute';
		$result          = $this->get_item_details( $attr );

		return $this->parse_shortcode_output( $result, $attr );
	}

	public function data_key() {
		?>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Slug', 'wp-marketing-automations' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <input type="text" class="bwfan-input-wrapper bwfan_tag_input" name="key"/>
                <div class="clearfix bwfan_field_desc"><?php esc_html_e( 'Leave empty to output all the attributes separated by comma or Use attributes slug to display its value', 'wp-marketing-automations' ); ?></div>
            </div>
        </div>
		<?php
	}

	/**
	 * Show dummy value of the current merge tag.
	 *
	 * @return string
	 */
	public function get_dummy_preview() {
		return __( 'Value of the key', 'wp-marketing-automations' );
	}


}

/**
 * Register this merge tag to a group.
 */
if ( bwfan_is_woocommerce_active() ) {
	BWFAN_Merge_Tag_Loader::register( 'wc_items', 'BWFAN_WC_Item_Attribute' );
}