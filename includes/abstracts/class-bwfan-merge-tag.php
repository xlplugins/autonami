<?php

abstract class BWFAN_Merge_Tag {

	protected $support_fallback = false;
	protected $support_date = false;
	protected $support_modify = true;
	protected $fallback = '';
	protected $tag_name = '';
	protected $tag_description = '';
	protected $need_order_sync = false;
	protected $sync_order = null;

	public static function maybe_parse_nested_merge_tags( $string ) {
		$position_end = strpos( $string, ']' );
		if ( false === $position_end ) {
			return $string;
		}

		$split          = str_split( $string, $position_end );
		$position_start = strrpos( $split[0], '[', - 1 );

		if ( false === $position_start ) {
			return $string;
		}

		$shortcode_array = explode( '[', $split[0] );
		$shortcode       = end( $shortcode_array );
		$result          = do_shortcode( '[' . $shortcode . ']' );

		/** Handling in case shortcode is not available and the output again contains the shortcode that would results in infinite loop */
		$result = str_replace( '[', '&#91;', $result );
		$result = str_replace( ']', '&#93;', $result );

		$string = str_replace( '[' . $shortcode . ']', $result, $string );

		return unescape_invalid_shortcodes( self::maybe_parse_nested_merge_tags( $string ) );
	}

	public function get_coupon_fields() {
		$time_types = array(
			'days'    => __( 'Days', 'wp-marketing-automations' ),
			'hours'   => __( 'Hours', 'wp-marketing-automations' ),
			'minutes' => __( 'Minutes', 'wp-marketing-automations' ),
		);
		?>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select coupon', 'wp-marketing-automations' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <select required id="" data-search="coupon" data-search-text="<?php esc_attr_e( 'Select Coupon', 'wp-marketing-automations' ); ?>" class="bwfan-select2ajax-single bwfan-input-wrapper bwfan_tag_select" name="parent_coupon">
                    <option value=""><?php esc_html_e( 'Choose Coupon', 'wp-marketing-automations' ); ?></option>
                </select>
            </div>
        </div>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Coupon name', 'wp-marketing-automations' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <input required type="text" class="bwfan-input-wrapper bwfan_tag_input" name="coupon_name"/>
            </div>
        </div>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Expiry Type', 'wp-marketing-automations' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <select required id="" class="bwfan-input-wrapper bwfan_tag_select" name="expiry_type">
					<?php
					foreach ( $time_types as $value1 => $text ) {
						?>
                        <option value="<?php echo esc_attr__( $value1 ); ?>"><?php echo esc_attr__( $text ); ?></option>
						<?php
					}
					?>
                </select>
            </div>
        </div>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Expiry', 'wp-marketing-automations' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <input min="0" type="number" class="bwfan-input-wrapper bwfan_tag_input" name="expiry"/>
            </div>
        </div>
        <div class="bwfan_mtag_wrap">
            <label for="bwfan-restrict" class="bwfan-label-title"><input type="checkbox" name="restrict" id="bwfan-restrict" class="bwfan_tag_select" value="yes"/><?php esc_html_e( 'Restrict user email with coupon', 'wp-marketing-automations' ); ?>
            </label>
        </div>
		<?php
	}

	/**
	 * Show the html in popup for the merge tag.
	 */
	public function get_view() {
		$this->get_back_button();
		if ( $this->support_date ) {
			$this->date_format();
		}
		if ( $this->support_fallback ) {
			$this->get_fallback();
		}

		$this->get_preview();
		$this->get_copy_button();
	}

	public function get_back_button() {
		?>
        <div class="bwfan_inner_merge_tag_desc"></div>
		<?php
	}

	public function date_format() {
		$formats = array(
			array(
				'format' => 'j M Y',
			),
			array(
				'format' => 'jS M Y',
			),
			array(
				'format' => 'M j Y',
			),
			array(
				'format' => 'M jS Y',
			),
			array(
				'format' => 'd/m/Y',
			),
			array(
				'format' => 'd-m-Y',
			),
			array(
				'format' => 'Y/m/d',
			),
			array(
				'format' => 'Y-m-d',
			),
			array(
				'format' => 'd/m/Y H:i:s',
			),
			array(
				'format' => 'd-m-Y H:i:s',
			),
			array(
				'format' => 'Y/m/d H:i:s',
			),
			array(
				'format' => 'Y-m-d H:i:s',
			),
		);

		echo '<div class="bwfan_mtag_wrap">';
		echo '<div class="bwfan_label">';
		echo '<label class="bwfan-label-fallback-title">' . __( 'Select Date Format', 'wp-marketing-automations' ) . '</label>'; //phpcs:ignore WordPress.Security.EscapeOutput
		echo '</div>';
		echo '<div class="bwfan_label_val">';
		echo '<select class="bwfan_date_format bwfan-input-wrapper bwfan_tag_select" name="format" style="width:100%;">';
		foreach ( $formats as $parameters ) {
			$date_time = $this->format_datetime( date( 'Y-m-d H:i:s' ), $parameters );
			echo '<option value="' . $parameters['format'] . '">' . $date_time . '</option>'; //phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</select>';
		echo '</div></div>';

		if ( true === $this->support_modify ) {
			echo '<div class="bwfan_mtag_wrap">
                <div class="bwfan_label">
                    <label for="" class="bwfan-label-fallback-title">' . esc_html__( 'Modify (Optional)', 'wp-marketing-automations' ) . '</label>
                </div>
                <div class="bwfan_label_val">
                    <input type="text" class="bwfan-input-wrapper bwfan_tag_input" name="modify" placeholder="e.g. +2 months, -1 day, +6 hours"/>
                </div>
             </div>';
		}
	}

	public function format_datetime( $input, $parameters, $is_gmt = false ) {
		if ( ! $input ) {
			return false;
		}

		if ( is_a( $input, 'DateTime' ) ) {
			$date = $input;
		} else {
			if ( is_numeric( $input ) ) {
				$date = new DateTime();
				$date->setTimestamp( $input );
			} else {
				$date = new DateTime( $input );
			}
		}

		if ( $is_gmt ) {
			$date = BWFAN_Common::convert_to_site_time( $date );
		}

		$format = ( isset( $parameters['format'] ) && ! empty( $parameters['format'] ) ) ? $parameters['format'] : 'Y-m-d H:i:s';

		if ( isset( $parameters['modify'] ) && ! empty( $parameters['modify'] ) ) {
			$date->modify( $parameters['modify'] );
		}

		return $date->format( $format );
	}

	public function get_fallback() {
		?>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-fallback-title"><?php esc_html_e( 'Fallback', 'wp-marketing-automations' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <input type="text" class="bwfan-input-wrapper bwfan_tag_input" name="fallback"/>
            </div>
        </div>
		<?php
	}

	public function get_preview() {
		?>
        <textarea style="margin: 5px 20px 15px;  width: 93%;" class="bwfan-preview-merge-tag bwfan-input-wrapper" readonly></textarea>
		<?php
		if ( true === $this->need_order_sync && 0 === $this->sync_order() ) {
			echo '<div class="error" style="position:relative;" class="bwfan-display-none">';
			echo '<p>' . esc_html__( 'This merge tag requires indexing of previous orders. Kindly ', 'wp-marketing-automations' );
			echo '<a href="javascript:void(0)" class="bwfan_sync_customer_order">' . esc_html__( 'Sync Now', 'wp-marketing-automations' ) . '</a></p>';
			echo '</div>';
		}
	}

	protected function sync_order() {
		if ( null !== $this->sync_order ) {
			return $this->sync_order;
		}
		$this->sync_order = get_option( '_bwf_db_upgrade', 0 );

		return $this->sync_order;
	}

	public function get_copy_button() {
		?>
        <span style="line-height: 70px;" class="bwfan-use-merge-tag"><?php esc_html_e( 'Copy To Clipboard', 'wp-marketing-automations' ); ?></span>
        <input type="submit" class="bwfan-display-none"/>
		<?php
	}

	public function parse_shortcode_output( $output, $atts ) {
		if ( empty( $output ) && isset( $atts['fallback'] ) && ! empty( $atts['fallback'] ) ) {
			$this->fallback = $atts['fallback'];
			$output         = $this->fallback;
		}

		if ( ! empty( $output ) && isset( $atts['prefix'] ) && ! empty( $atts['prefix'] ) ) {
			$output = $atts['prefix'] . $output;
		}

		if ( ! empty( $output ) && isset( $atts['suffix'] ) && ! empty( $atts['suffix'] ) ) {
			$output = $output . $atts['suffix'];
		}

		return apply_filters( 'bwfan_parse_merge_tag_output', $output, $atts );
	}

	public function data_key() {
		?>
        <div class="bwfan_mtag_wrap">
            <div class="bwfan_label">
                <label for="" class="bwfan-label-title"><?php esc_html_e( 'Meta Key', 'wp-marketing-automations' ); ?></label>
            </div>
            <div class="bwfan_label_val">
                <input type="text" class="bwfan-input-wrapper bwfan_tag_input" name="key" required/>
                <div class="clearfix bwfan_field_desc"><?php esc_html_e( 'Input the correct meta key in order to get the data', 'wp-marketing-automations' ); ?></div>
            </div>
        </div>
		<?php
	}

	public function get_customer_city() {
		$customer_city = '';
		$order         = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
		$user_id       = BWFAN_Merge_Tag_Loader::get_data( 'user_id' );

		if ( ! empty( $order ) ) {
			return BWFAN_Woocommerce_Compatibility::get_order_billing_city( $order );
		}
		if ( ! empty( $user_id ) ) {
			return get_user_meta( (int) $user_id, 'billing_city', true );
		}

		return $customer_city;
	}

	public function get_customer_country() {
		$customer_country = '';
		$country_slug     = '';
		$order            = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
		$user_id          = BWFAN_Merge_Tag_Loader::get_data( 'user_id' );

		if ( ! empty( $order ) ) {
			$country_slug = BWFAN_Woocommerce_Compatibility::get_billing_country_from_order( $order );
		}
		if ( ! empty( $user_id ) ) {
			$country_slug = get_user_meta( (int) $user_id, 'billing_country', true );
		}

		if ( ! empty( $country_slug ) ) {
			$countries_obj    = new WC_Countries();
			$countries        = $countries_obj->__get( 'countries' );
			$customer_country = $countries[ $country_slug ];
		}

		return $customer_country;
	}

	public function initialize_product_details() {
		$product_details = BWFAN_Merge_Tag_Loader::get_data( 'product_details' );
		$product_id      = BWFAN_Merge_Tag_Loader::get_data( 'product_id' );
		$product         = BWFAN_Merge_Tag_Loader::get_data( 'product' );

		if ( empty( $product_details ) ) {
			BWFAN_Merge_Tag_Loader::set_data( array(
				'product_details' => get_post( $product_id ),
			) );
		}
		if ( empty( $product ) ) {
			BWFAN_Merge_Tag_Loader::set_data( array(
				'product' => wc_get_product( $product_id ),
			) );
		}
	}

	public function get_slug() {
		return sanitize_title( get_class( $this ) );
	}

	public function get_name() {
		return $this->tag_name;
	}

	public function get_description() {
		return $this->tag_description;
	}

	public function get_localize_data() {
		return [
			'tag_name'        => $this->tag_name,
			'tag_description' => $this->tag_description,
		];
	}

	public function get_formatted_billing_address( $empty_content = '' ) {
		$order = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
		if ( $order instanceof WC_Order ) {
			$address   = apply_filters( 'woocommerce_order_formatted_billing_address', $order->get_address( 'billing' ), $order );
			$separator = apply_filters( 'bwfan_' . $this->tag_name . '_separator', '</br>' );
			$address   = WC()->countries->get_formatted_address( $address, $separator );

			return $address ? $address : $empty_content;
		}

		return '';
	}

	public function get_formatted_shipping_address( $empty_content = '' ) {
		$order = BWFAN_Merge_Tag_Loader::get_data( 'wc_order' );
		if ( $order instanceof WC_Order ) {
			$address   = apply_filters( 'woocommerce_order_formatted_shipping_address', $order->get_address( 'shipping' ), $order );
			$separator = apply_filters( 'bwfan_' . $this->tag_name . '_separator', '</br>' );
			$address   = WC()->countries->get_formatted_address( $address, $separator );

			return $address ? $address : $empty_content;
		}

		return '';
	}

	/**
	 * Get date value in WordPress set date format
	 *
	 * @param $date_value
	 *
	 * @return string
	 */
	public function get_formatted_date_value( $date_value ) {
		if ( empty( $date_value ) ) {
			return '';
		}
		if ( false === $this->validate_date( $date_value ) ) {
			return $date_value;
		}
		$date_format = get_option( 'date_format' ); // e.g. "F j, Y"
		$date_value  = date( $date_format, strtotime( $date_value ) );

		return $date_value;
	}

	/**
	 * Validate date
	 *
	 * @param $date
	 * @param string $format
	 *
	 * @return bool
	 */
	public function validate_date( $date, $format = 'Y-m-d' ) {
		$d = DateTime::createFromFormat( $format, $date );

		return $d && $d->format( $format ) === $date;
	}

	/**
	 * to avoid unserialize of the current class
	 */
	public function __wakeup() {
		throw new ErrorException( __CLASS__ . ' can`t converted to string' );
	}

	/**
	 * to avoid serialize of the current class
	 */
	public function __sleep() {
		throw new ErrorException( __CLASS__ . ' can`t converted to string' );
	}

	/**
	 * To avoid cloning of current class
	 */
	protected function __clone() {
	}

}
