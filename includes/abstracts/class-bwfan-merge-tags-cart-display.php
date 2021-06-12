<?php

class BWFAN_Cart_Display extends BWFAN_Merge_Tag {

	public $item_type = null;
	public $cart_number = null;

	public function get_currency_option() {
		$currencies = array(
			'symbol' => __( 'Currency Symbol', 'wp-marketing-automations' ),
			'code'   => __( '3 Letter Currency code', 'wp-marketing-automations' ),
		);

		return $currencies;
	}

	/**
	 * Show the html in popup for the merge tag.
	 */
	public function get_view() {
		$this->get_back_button();
		$this->get_item_number_html();

		if ( $this->support_fallback ) {
			$this->get_fallback();
		}

		$this->get_preview();
		$this->get_copy_button();
	}

	public function get_item_number_html() {
		$templates = $this->get_view_data();
		?>
        <label for="" class="bwfan-label-title"><?php esc_html_e( 'Select Item Number', 'wp-marketing-automations' ); ?></label>
        <select id="" class="bwfan-input-wrapper bwfan-mb-15 bwfan_tag_select" name="item_no" required>
            <option value=""><?php esc_html_e( 'Select', 'wp-marketing-automations' ); ?></option>
			<?php
			foreach ( $templates as $slug => $name ) {
				echo '<option value="' . esc_attr__( $slug ) . '">' . esc_html__( $name ) . '</option>';
			}
			?>
        </select>
		<?php
	}

	public function get_view_data() {
		$templates = array(
			'1'  => __( 'First Item', 'wp-marketing-automations' ),
			'2'  => __( 'Second Item', 'wp-marketing-automations' ),
			'3'  => __( 'Third Item', 'wp-marketing-automations' ),
			'4'  => __( 'Fourth Item', 'wp-marketing-automations' ),
			'5'  => __( 'Fifth Item', 'wp-marketing-automations' ),
			'6'  => __( 'Sixth Item', 'wp-marketing-automations' ),
			'7'  => __( 'Seventh Item', 'wp-marketing-automations' ),
			'8'  => __( 'Eighth Item', 'wp-marketing-automations' ),
			'9'  => __( 'Ninth Item', 'wp-marketing-automations' ),
			'10' => __( 'Tenth Item', 'wp-marketing-automations' ),
		);

		return $templates;
	}

	public function get_item_value() {
		$cart_row_details = BWFAN_Merge_Tag_Loader::get_data( 'cart_details' );
		$cart_items       = maybe_unserialize( $cart_row_details['items'] );
		$return_value     = '';
		$count            = 1;

		foreach ( $cart_items as $item_data ) {
			if ( absint( $this->cart_number ) === $count ) {
				$product_id = ( isset( $item_data['product_id'] ) ) ? $item_data['product_id'] : 0;
				$quantity   = ( isset( $item_data['quantity'] ) ) ? $item_data['quantity'] : 0;
				$_pf        = new WC_Product_Factory();
				$product    = $_pf->get_product( $product_id );

				switch ( $this->item_type ) {
					case 'image':
						$product_image = BWFAN_Common::get_product_image( $product, 'shop_catalog', true );
						$return_value  = $product_image;
						break;
					case 'name':
						$product_name = BWFAN_Common::get_name( $product );
						$return_value = $product_name;
						break;
					case 'price':
						$product_price = $product->get_price();
						$return_value  = $product_price;
						break;
					case 'quantity':
						$return_value = $quantity;
						break;
					case 'url':
						$product_url  = $product->get_permalink();
						$return_value = $product_url;
						break;

				}

				break;
			}

			$count ++;
		}

		return $return_value;
	}

	public function get_item_details( $parameters = null ) {
		$item    = BWFAN_Merge_Tag_Loader::get_data( 'wc_single_item' );
		$item_id = BWFAN_Merge_Tag_Loader::get_data( 'wc_single_item_id' );

		switch ( $this->item_type ) {
			case 'image':
				$product_image = BWFAN_Common::get_product_image( $item->get_product(), 'shop_catalog', true );
				$return_value  = $product_image;
				break;
			case 'name':
				$return_value = $item->get_name();
				break;
			case 'price':
				$return_value = $item->get_total();
				break;
			case 'quantity':
				$return_value = $item->get_quantity();
				break;
			case 'stock':
				$product      = $item->get_product();
				$return_value = $product->get_stock_quantity();
				break;
			case 'url':
				$product      = $item->get_product();
				$return_value = $product->get_permalink();
				break;
			case 'item_data':
				if ( is_array( $parameters ) && isset( $parameters['key'] ) ) {
					$return_value = wc_get_order_item_meta( (int) $item_id, $parameters['key'], true );
				} else {
					$return_value = '';
				}

				break;
			case 'item_attribute':
				if ( is_array( $parameters ) && isset( $parameters['key'] ) ) {
					$attribute = 'pa_' . $parameters['key'];
					$term      = $item->get_meta( $attribute );
					if ( ! $term ) {
						return false;
					}

					$term_obj = get_term_by( 'slug', $term, $attribute );

					if ( ! $term_obj || is_wp_error( $term_obj ) ) {
						return false;
					}

					return $term_obj->name;

				} else {
					$product = $item->get_product();
					if ( ! $product instanceof WC_Product ) {
						return false;
					}

					$attributes = $product->get_attributes();

					if ( empty( $attributes ) ) {
						return false;
					}

					$variation_names1 = array();
					foreach ( $attributes as $key => $value ) {
						$term     = $item->get_meta( $key );
						$term_obj = get_term_by( 'slug', $term, $key );
						if ( ! $term_obj || is_wp_error( $term_obj ) ) {
							continue;
						}

						$variation_names1[] = $term_obj->name;
					}

					$return_value = implode( ', ', $variation_names1 );
				}

				break;
			case 'item_meta':
				$product    = $item->get_product();
				$product_id = $product->get_id();

				if ( is_array( $parameters ) && isset( $parameters['key'] ) ) {
					$return_value = get_post_meta( (int) $product_id, $parameters['key'], true );
				} else {
					$return_value = '';
				}

				break;
			case 'item_id':
				$product      = $item->get_product();
				$return_value = $product->get_id();

				break;
			case 'item_sku':
				$product      = $item->get_product();
				$return_value = $product->get_sku();

				break;
		}

		return $return_value;
	}

}
